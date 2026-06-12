<?php

namespace App\Services;

use App\Models\Almacen;
use App\Models\PedidoDistribucion;
use App\Models\PuntoVenta;
use App\Models\RutaDistribucion;
use App\Models\RutaDistribucionParada;
use App\Support\PedidoDistribucionCatalogo;
use App\Support\RutaDistribucionCatalogo;
use App\Support\TransportistaFlotaCatalogo;
use App\Support\UbicacionGpsParser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DistribucionRutaService
{
    /**
     * @param  array<int>  $pedidoIds  Orden de visita a PDV
     */
    public function crear(
        Almacen $almacenOrigen,
        array $pedidoIds,
        int $transportistaId,
        ?int $vehiculoId,
        int $creadoPorId,
        ?string $nombre = null
    ): RutaDistribucion {
        if ($pedidoIds === []) {
            throw new InvalidArgumentException('Seleccione al menos un pedido para la ruta.');
        }

        $pedidos = PedidoDistribucion::query()
            ->with(['puntoVenta.minorista', 'detalles'])
            ->whereIn('pedidodistribucionid', $pedidoIds)
            ->get();

        if ($pedidos->count() !== count($pedidoIds)) {
            throw new InvalidArgumentException('Uno o más pedidos no existen.');
        }

        foreach ($pedidos as $pedido) {
            if ($pedido->estado !== PedidoDistribucionCatalogo::ESTADO_CONFIRMADO) {
                throw new InvalidArgumentException("El pedido {$pedido->numero_solicitud} no está listo para distribución.");
            }
            if ($pedido->rutadistribucionid !== null) {
                throw new InvalidArgumentException("El pedido {$pedido->numero_solicitud} ya está asignado a otra ruta.");
            }
            if ($pedido->puntoVenta === null) {
                throw new InvalidArgumentException("El pedido {$pedido->numero_solicitud} no tiene punto de venta.");
            }
            if ($pedido->puntoVenta->latitud === null || $pedido->puntoVenta->longitud === null) {
                throw new InvalidArgumentException("El punto «{$pedido->puntoVenta->nombre}» no tiene ubicación GPS.");
            }
        }

        $ordenPedidos = collect($pedidoIds)
            ->map(fn (int $id) => $pedidos->firstWhere('pedidodistribucionid', $id))
            ->filter()
            ->values();

        return DB::transaction(function () use (
            $almacenOrigen,
            $ordenPedidos,
            $transportistaId,
            $vehiculoId,
            $creadoPorId,
            $nombre
        ) {
            $coordsOrigen = UbicacionGpsParser::resolverAlmacen(
                (int) $almacenOrigen->almacenid,
                $almacenOrigen->nombre,
                $almacenOrigen->ubicacion
            );

            $ruta = RutaDistribucion::create([
                'codigo' => RutaDistribucionCatalogo::generarCodigo(),
                'nombre' => $nombre ?: 'Distribución '.$almacenOrigen->nombre,
                'almacen_planta_origenid' => $almacenOrigen->almacenid,
                'transportista_usuarioid' => $transportistaId,
                'vehiculoid' => $vehiculoId,
                'creado_por_usuarioid' => $creadoPorId,
                'estado' => RutaDistribucionCatalogo::ESTADO_EN_RUTA,
                'fecha_salida' => now(),
            ]);

            RutaDistribucionParada::create([
                'rutadistribucionid' => $ruta->rutadistribucionid,
                'orden' => 1,
                'tipo' => RutaDistribucionCatalogo::PARADA_CARGA_PLANTA,
                'almacenid' => $almacenOrigen->almacenid,
                'destino' => 'Carga: '.$almacenOrigen->nombre,
                'latitud' => $coordsOrigen['lat'],
                'longitud' => $coordsOrigen['lng'],
                'estado' => 'completada',
            ]);

            $orden = 2;
            foreach ($ordenPedidos as $pedido) {
                /** @var PedidoDistribucion $pedido */
                $pdv = $pedido->puntoVenta;

                RutaDistribucionParada::create([
                    'rutadistribucionid' => $ruta->rutadistribucionid,
                    'orden' => $orden++,
                    'tipo' => RutaDistribucionCatalogo::PARADA_ENTREGA_PDV,
                    'puntoventaid' => $pdv->puntoventaid,
                    'pedidodistribucionid' => $pedido->pedidodistribucionid,
                    'destino' => 'Entrega: '.$pdv->nombre,
                    'latitud' => (float) $pdv->latitud,
                    'longitud' => (float) $pdv->longitud,
                    'estado' => 'pendiente',
                ]);

                $pedido->update([
                    'rutadistribucionid' => $ruta->rutadistribucionid,
                    'estado' => PedidoDistribucionCatalogo::ESTADO_EN_TRANSITO,
                    'fecha_envio' => now(),
                ]);
            }

            return $ruta->load(['paradas', 'pedidos.puntoVenta', 'transportista', 'vehiculo', 'almacenOrigen']);
        });
    }

    /** @return array{origen: string, destinos: array<int, string>}|null */
    public function trayectoPartes(RutaDistribucion $ruta): ?array
    {
        $ruta->loadMissing(['paradas', 'almacenOrigen']);

        $carga = $ruta->paradas->first(fn (RutaDistribucionParada $p) => $p->tipo === RutaDistribucionCatalogo::PARADA_CARGA_PLANTA);
        $entregas = $ruta->paradas
            ->filter(fn (RutaDistribucionParada $p) => $p->tipo === RutaDistribucionCatalogo::PARADA_ENTREGA_PDV)
            ->sortBy('orden')
            ->map(fn (RutaDistribucionParada $p) => $this->nombreParada($p))
            ->values()
            ->all();

        $origen = $carga ? $this->nombreParada($carga) : ($ruta->almacenOrigen?->nombre);

        if ($origen === null && $entregas === []) {
            return null;
        }

        return [
            'origen' => $origen ?? 'Planta',
            'destinos' => $entregas,
        ];
    }

    public function trayectoTexto(RutaDistribucion $ruta): ?string
    {
        $partes = $this->trayectoPartes($ruta);
        if ($partes === null) {
            return null;
        }

        $destinos = $partes['destinos'];
        if ($destinos === []) {
            return $partes['origen'];
        }

        $textoDestinos = count($destinos) === 1
            ? $destinos[0]
            : implode(' → ', $destinos);

        return $partes['origen'].' a '.$textoDestinos;
    }

    /**
     * @return array<int, array{lat: float, lng: float, orden: int, label: string, tipo: string}>
     */
    public function paradasMapa(RutaDistribucion $ruta): array
    {
        $ruta->loadMissing('paradas');

        return $ruta->paradas
            ->sortBy('orden')
            ->map(fn (RutaDistribucionParada $p) => [
                'lat' => (float) $p->latitud,
                'lng' => (float) $p->longitud,
                'orden' => (int) $p->orden,
                'label' => $this->nombreParada($p),
                'tipo' => $p->tipo,
            ])
            ->filter(fn (array $punto) => $punto['lat'] && $punto['lng'])
            ->values()
            ->all();
    }

    /** @return Collection<int, PedidoDistribucion> */
    public function pedidosListosParaRuta(): Collection
    {
        return PedidoDistribucion::query()
            ->with(['puntoVenta.minorista', 'detalles.insumo.unidadMedida', 'almacenPlantaOrigen'])
            ->where('estado', PedidoDistribucionCatalogo::ESTADO_CONFIRMADO)
            ->whereNull('rutadistribucionid')
            ->orderByDesc('fecha_aceptacion')
            ->get();
    }

    private function nombreParada(RutaDistribucionParada $parada): string
    {
        $texto = trim((string) $parada->destino);
        if (str_starts_with($texto, 'Carga:')) {
            return trim(substr($texto, 6));
        }
        if (str_starts_with($texto, 'Entrega:')) {
            return trim(substr($texto, 8));
        }

        return $texto !== '' ? $texto : 'Parada';
    }

    public function asegurarTransportistaPlanta(int $transportistaId): void
    {
        $usuario = \App\Models\Usuario::query()
            ->with('perfilTransportista')
            ->where('usuarioid', $transportistaId)
            ->where('role', 'transportista')
            ->first();

        if ($usuario === null) {
            throw new InvalidArgumentException('Transportista no válido.');
        }

        $ambito = $usuario->perfilTransportista?->ambito_flota ?? TransportistaFlotaCatalogo::AGRICOLA;
        if ($ambito !== TransportistaFlotaCatalogo::PLANTA) {
            throw new InvalidArgumentException('Seleccione un transportista de flota planta.');
        }
    }
}
