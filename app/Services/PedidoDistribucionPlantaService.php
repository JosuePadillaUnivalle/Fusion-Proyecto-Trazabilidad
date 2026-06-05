<?php

namespace App\Services;

use App\Models\PedidoDistribucion;

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
}
