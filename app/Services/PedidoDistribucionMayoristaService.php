<?php



namespace App\Services;



use App\Models\Almacen;

use App\Models\PedidoDistribucion;

use App\Models\Usuario;

use App\Models\Vehiculo;

use App\Support\PedidoDistribucionCatalogo;

use App\Support\TransportistaFlotaCatalogo;

use Illuminate\Support\Facades\DB;

use InvalidArgumentException;



class PedidoDistribucionMayoristaService

{

    /** @return list<string> */

    public function verificarDisponibilidad(PedidoDistribucion $pedido): array

    {

        $pedido->loadMissing('detalles.insumo.unidadMedida', 'almacenMayoristaOrigen');

        $errores = [];



        foreach ($pedido->detalles as $detalle) {

            $nombre = $detalle->producto_nombre ?: 'Producto';

            $cantidad = (float) $detalle->cantidad;

            $insumo = $detalle->insumo;



            if ($insumo === null) {

                $errores[] = "«{$nombre}» no está disponible en el almacén mayorista.";



                continue;

            }



            if (! $insumo->tieneStockSuficiente($cantidad)) {

                $unidad = $insumo->unidadMedida?->abreviatura ?? '';

                $errores[] = "Stock insuficiente para «{$nombre}»: solicitado {$cantidad} {$unidad}, disponible ".number_format((float) $insumo->stock, 2)." {$unidad}.";

            }

        }



        return $errores;

    }



    public function designarTransportista(

        PedidoDistribucion $pedido,

        int $transportistaId,

        int $vehiculoId,

        int $creadoPorId

    ): PedidoDistribucion {

        if (! PedidoDistribucionCatalogo::puedeDesignarTransportista($pedido)) {

            throw new InvalidArgumentException('El pedido debe estar aceptado y sin transportista asignado.');

        }



        [$transportista, $vehiculo] = $this->validarFlotaMayorista($pedido, $transportistaId, $vehiculoId);



        $pedido->loadMissing('almacenMayoristaOrigen', 'puntoVenta');

        $almacen = $pedido->almacenMayoristaOrigen;

        if ($almacen === null) {

            throw new InvalidArgumentException('El pedido no tiene almacén mayorista de origen.');

        }



        $pdv = $pedido->puntoVenta;

        if ($pdv === null) {

            throw new InvalidArgumentException('El pedido no tiene punto de venta asociado.');

        }



        if ($pdv->latitud === null || $pdv->longitud === null) {

            throw new InvalidArgumentException("El punto «{$pdv->nombre}» no tiene ubicación GPS para planificar la ruta.");

        }



        return DB::transaction(function () use ($pedido, $transportistaId, $vehiculoId, $creadoPorId, $almacen, $transportista, $vehiculo) {

            app(DistribucionRutaService::class)->crear(

                $almacen,

                [$pedido->pedidodistribucionid],

                $transportistaId,

                $vehiculoId,

                $creadoPorId,

                'Envío directo '.$pedido->numero_solicitud,

                null

            );



            $pedido->update([

                'transportista_usuarioid' => $transportistaId,

                'vehiculoid' => $vehiculoId,

            ]);



            return $pedido->fresh(['transportista', 'vehiculo.tipoVehiculo', 'rutaDistribucion.transportista', 'rutaDistribucion.vehiculo']);

        });

    }



    /** @deprecated Use designarTransportista(); la salida en ruta se inicia desde la ruta asignada. */

    public function marcarEnTransito(PedidoDistribucion $pedido, int $transportistaId, int $vehiculoId): PedidoDistribucion

    {

        return $this->designarTransportista($pedido, $transportistaId, $vehiculoId, (int) auth()->id());

    }



    /**

     * @return array{0: Usuario, 1: Vehiculo}

     */

    private function validarFlotaMayorista(PedidoDistribucion $pedido, int $transportistaId, int $vehiculoId): array

    {

        app(DistribucionRutaService::class)->asegurarTransportistaMayorista($transportistaId);



        $transportista = Usuario::query()

            ->where('usuarioid', $transportistaId)

            ->where('role', 'transportista')

            ->where('activo', true)

            ->first();



        if ($transportista === null) {

            throw new InvalidArgumentException('El transportista seleccionado no está disponible.');

        }



        $vehiculo = Vehiculo::query()

            ->with('tipoVehiculo')

            ->where('vehiculoid', $vehiculoId)

            ->where('activo', true)

            ->first();



        if ($vehiculo === null) {

            throw new InvalidArgumentException('El vehículo seleccionado no está disponible.');

        }



        if ($vehiculo->ambito_flota !== null && $vehiculo->ambito_flota !== TransportistaFlotaCatalogo::MAYORISTA) {

            throw new InvalidArgumentException('Seleccione un vehículo de flota mayorista.');

        }



        $pedido->loadMissing('detalles');

        $capacidad = app(TransporteCapacidadService::class);

        $capacidad->validarAsignacionYCarga(

            $transportista,

            $vehiculo,

            $capacidad->pesoPedidosDistribucion([$pedido])

        );



        return [$transportista, $vehiculo];
    }

    public function liberarAsignacionLogistica(PedidoDistribucion $pedido): void
    {
        if ($pedido->rutadistribucionid === null) {
            $pedido->update([
                'transportista_usuarioid' => null,
                'vehiculoid' => null,
            ]);

            return;
        }

        $ruta = \App\Models\RutaDistribucion::query()
            ->with('pedidos')
            ->find($pedido->rutadistribucionid);

        if ($ruta === null) {
            $pedido->update([
                'rutadistribucionid' => null,
                'transportista_usuarioid' => null,
                'vehiculoid' => null,
            ]);

            return;
        }

        if ($ruta->estado !== \App\Support\RutaDistribucionCatalogo::ESTADO_PLANIFICADA) {
            throw new InvalidArgumentException('No se puede modificar el pedido: la ruta ya salió o finalizó.');
        }

        DB::transaction(function () use ($pedido, $ruta) {
            $pedido->update([
                'rutadistribucionid' => null,
                'transportista_usuarioid' => null,
                'vehiculoid' => null,
            ]);

            $otrosPedidos = $ruta->pedidos
                ->filter(fn ($p) => (int) $p->pedidodistribucionid !== (int) $pedido->pedidodistribucionid);

            if ($otrosPedidos->isEmpty()) {
                $ruta->paradas()->delete();
                $ruta->delete();
            }
        });
    }

    public function reabrirRevision(PedidoDistribucion $pedido): PedidoDistribucion
    {
        if (! PedidoDistribucionCatalogo::puedeReabrirRevision($pedido)) {
            throw new InvalidArgumentException('Este pedido no puede volver a revisión mayorista.');
        }

        return DB::transaction(function () use ($pedido) {
            $this->liberarAsignacionLogistica($pedido);

            $pedido->update([
                'estado' => PedidoDistribucionCatalogo::ESTADO_PENDIENTE,
                'fecha_aceptacion' => null,
                'aceptado_por_usuarioid' => null,
            ]);

            return $pedido->fresh(['detalles.insumo.unidadMedida', 'puntoVenta.minorista', 'almacenMayoristaOrigen']);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function actualizarSolicitud(PedidoDistribucion $pedido, array $data, Usuario $usuario): PedidoDistribucion
    {
        if (! PedidoDistribucionCatalogo::puedeEditarFlujoAntesDeRuta($pedido)) {
            throw new InvalidArgumentException('El pedido ya no puede editarse.');
        }

        $pedido->loadMissing(['detalles.insumo', 'puntoVenta']);

        $esAdmin = \App\Support\UsuarioRol::esAdminGlobal($usuario);
        $esDueño = \App\Support\UsuarioRol::esMinorista($usuario)
            && (int) $pedido->puntoVenta?->usuarioid === (int) $usuario->usuarioid;

        if (! $esAdmin && ! $esDueño) {
            throw new InvalidArgumentException('No tiene permiso para editar esta solicitud.');
        }

        $cantidad = (float) $data['cantidad'];
        if ($cantidad <= 0) {
            throw new InvalidArgumentException('La cantidad debe ser mayor que cero.');
        }

        $insumoId = (int) ($data['insumoid'] ?? $pedido->detalles->first()?->insumoid);
        $insumo = \App\Models\Insumo::query()->with('almacen')->findOrFail($insumoId);

        $almacenId = (int) ($data['almacen_mayorista_origenid'] ?? $pedido->almacen_mayorista_origenid ?? $insumo->almacenid);
        $almacen = \App\Models\Almacen::query()->findOrFail($almacenId);

        if ($almacen->ambito !== \App\Support\AlmacenAmbito::MAYORISTA) {
            throw new InvalidArgumentException('El almacén debe ser de tipo mayorista.');
        }

        if ((int) $insumo->almacenid !== $almacenId) {
            throw new InvalidArgumentException('El producto no pertenece al almacén mayorista indicado.');
        }

        if ($cantidad > (float) $insumo->stock) {
            throw new InvalidArgumentException('La cantidad supera el stock disponible en el almacén mayorista.');
        }

        $puntoId = (int) ($data['puntoventaid'] ?? $pedido->puntoventaid);
        $punto = \App\Models\PuntoVenta::query()->findOrFail($puntoId);

        if ($esDueño && (int) $punto->usuarioid !== (int) $usuario->usuarioid) {
            throw new InvalidArgumentException('Solo puede solicitar para sus propios puntos de venta.');
        }

        if ($esAdmin && isset($data['minorista_usuarioid']) && (int) $punto->usuarioid !== (int) $data['minorista_usuarioid']) {
            throw new InvalidArgumentException('El punto de venta no pertenece al minorista seleccionado.');
        }

        return DB::transaction(function () use ($pedido, $data, $cantidad, $insumo, $almacenId, $puntoId) {
            if (PedidoDistribucionCatalogo::tieneTransportistaDesignado($pedido)) {
                $this->liberarAsignacionLogistica($pedido);
            }

            $pedido->update([
                'puntoventaid' => $puntoId,
                'almacen_mayorista_origenid' => $almacenId,
                'fecha_entrega_deseada' => $data['fecha_entrega_deseada'] ?? $pedido->fecha_entrega_deseada,
                'observaciones' => $data['observaciones'] ?? $pedido->observaciones,
            ]);

            $detalle = $pedido->detalles->first();
            if ($detalle) {
                $detalle->update([
                    'insumoid' => $insumo->insumoid,
                    'producto_nombre' => $insumo->nombre,
                    'cantidad' => $cantidad,
                ]);
            }

            return $pedido->fresh(['detalles.insumo.unidadMedida', 'puntoVenta.minorista', 'almacenMayoristaOrigen']);
        });
    }
}


