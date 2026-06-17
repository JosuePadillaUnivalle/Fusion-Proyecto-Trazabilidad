<?php

namespace App\Services;

use App\Models\PedidoDistribucion;
use App\Models\Usuario;
use App\Models\Vehiculo;
use App\Support\PedidoDistribucionCatalogo;
use App\Support\TransportistaFlotaCatalogo;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PedidoDistribucionPlantaService
{
    /** @return list<string> */
    public function verificarDisponibilidad(PedidoDistribucion $pedido): array
    {
        $pedido->loadMissing('detalles.insumo.unidadMedida');
        $errores = [];

        foreach ($pedido->detalles as $detalle) {
            $nombre = $detalle->producto_nombre ?: 'Producto';
            $cantidad = (float) $detalle->cantidad;
            $insumo = $detalle->insumo;

            if ($insumo === null) {
                $errores[] = "«{$nombre}» no está disponible en el almacén de planta.";

                continue;
            }

            if (! $insumo->tieneStockSuficiente($cantidad)) {
                $unidad = $insumo->unidadMedida?->abreviatura ?? '';
                $errores[] = "Stock insuficiente para «{$nombre}»: solicitado {$cantidad} {$unidad}, disponible ".number_format((float) $insumo->stock, 2)." {$unidad}.";
            }
        }

        return $errores;
    }

    public function marcarEnTransito(PedidoDistribucion $pedido, int $transportistaId, int $vehiculoId): PedidoDistribucion
    {
        if (! PedidoDistribucionCatalogo::puedeDespacharDirecto($pedido)) {
            if ($pedido->rutadistribucionid !== null) {
                throw new InvalidArgumentException('Este pedido está incluido en una ruta de distribución. Inicie la ruta desde el módulo de rutas.');
            }

            throw new InvalidArgumentException('El pedido debe estar aceptado por planta antes de marcar el envío.');
        }

        app(DistribucionRutaService::class)->asegurarTransportistaPlanta($transportistaId);

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

        if ($vehiculo->ambito_flota !== null && $vehiculo->ambito_flota !== TransportistaFlotaCatalogo::PLANTA) {
            throw new InvalidArgumentException('Seleccione un vehículo de flota planta.');
        }

        $pedido->loadMissing('detalles');
        $capacidad = app(TransporteCapacidadService::class);
        $capacidad->validarAsignacionYCarga(
            $transportista,
            $vehiculo,
            $capacidad->pesoPedidosDistribucion([$pedido])
        );

        return DB::transaction(function () use ($pedido, $transportistaId, $vehiculoId) {
            $pedido->update([
                'transportista_usuarioid' => $transportistaId,
                'vehiculoid' => $vehiculoId,
                'estado' => PedidoDistribucionCatalogo::ESTADO_EN_TRANSITO,
                'fecha_envio' => now(),
            ]);

            return $pedido->fresh(['transportista', 'vehiculo.tipoVehiculo']);
        });
    }
}
