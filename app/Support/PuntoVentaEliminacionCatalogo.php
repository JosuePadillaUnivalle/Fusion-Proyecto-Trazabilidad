<?php

namespace App\Support;

use App\Models\PedidoDistribucion;
use App\Models\PuntoVenta;
use App\Models\RutaDistribucionParada;
use App\Models\SolicitudProduccionPlanta;
use App\Services\PuntoVentaAlmacenService;

final class PuntoVentaEliminacionCatalogo
{
    /** @return array{ok: bool, titulo: string, mensaje: string} */
    public static function evaluar(PuntoVenta $punto): array
    {
        $stock = self::stockTotalEnPunto($punto);
        if ($stock > 0) {
            return [
                'ok' => false,
                'titulo' => 'Inventario con stock',
                'mensaje' => 'No se puede eliminar «'.$punto->nombre.'» mientras tenga productos en inventario. '
                    .'Vacíe el stock del punto de venta antes de eliminarlo.',
            ];
        }

        $pedidosEnCurso = $punto->pedidosDistribucion()
            ->whereIn('estado', [
                PedidoDistribucionCatalogo::ESTADO_CONFIRMADO,
                PedidoDistribucionCatalogo::ESTADO_EN_TRANSITO,
            ])
            ->count();

        if ($pedidosEnCurso > 0) {
            return [
                'ok' => false,
                'titulo' => 'Pedidos en curso',
                'mensaje' => 'No se puede eliminar «'.$punto->nombre.'»: hay '
                    .$pedidosEnCurso.' pedido(s) aceptados o en camino. '
                    .'Espere a que se entreguen o cancélelos desde logística.',
            ];
        }

        return ['ok' => true, 'titulo' => '', 'mensaje' => ''];
    }

    public static function stockTotalEnPunto(PuntoVenta $punto): float
    {
        $insumos = app(PuntoVentaAlmacenService::class)->insumosEnPuntoVenta($punto);

        return (float) $insumos->sum(fn ($insumo) => (float) $insumo->stock);
    }

    public static function cancelarPedidosPendientes(PuntoVenta $punto): int
    {
        return $punto->pedidosDistribucion()
            ->where('estado', PedidoDistribucionCatalogo::ESTADO_PENDIENTE)
            ->update(['estado' => PedidoDistribucionCatalogo::ESTADO_CANCELADO]);
    }

    /** Elimina pedidos históricos y paradas de ruta que bloquean el borrado del PDV. */
    public static function eliminarHistorialAsociado(PuntoVenta $punto): void
    {
        $pedidoIds = $punto->pedidosDistribucion()->pluck('pedidodistribucionid');

        RutaDistribucionParada::query()
            ->where(function ($q) use ($punto, $pedidoIds): void {
                $q->where('puntoventaid', $punto->puntoventaid);
                if ($pedidoIds->isNotEmpty()) {
                    $q->orWhereIn('pedidodistribucionid', $pedidoIds);
                }
            })
            ->delete();

        if ($pedidoIds->isNotEmpty()) {
            SolicitudProduccionPlanta::query()
                ->whereIn('pedidodistribucionid', $pedidoIds)
                ->delete();

            PedidoDistribucion::query()
                ->whereIn('pedidodistribucionid', $pedidoIds)
                ->update(['rutadistribucionid' => null]);

            $punto->pedidosDistribucion()->each(function (PedidoDistribucion $pedido): void {
                $pedido->detalles()->delete();
                $pedido->delete();
            });
        }
    }
}
