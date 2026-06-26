<?php

namespace App\Support;

use App\Models\DetallePedido;
use App\Models\DetallePedidoDistribucion;
use App\Models\Pedido;
use App\Models\RutaDistribucion;

final class EnvioListaCargaResumen
{
    public static function desdePedido(Pedido $pedido): ?string
    {
        $detalles = $pedido->detalles ?? collect();
        if ($detalles->isEmpty()) {
            return null;
        }

        $primera = self::lineaDesdeDetallePedido($detalles->first());
        if ($detalles->count() === 1) {
            return $primera;
        }

        return $primera.' +'.($detalles->count() - 1).' más';
    }

    public static function desdeRuta(RutaDistribucion $ruta): ?string
    {
        if ($ruta->esTrasladoPlantaMayorista()) {
            return TrasladoPlantaMayoristaPresentacion::resumenCargaLista($ruta);
        }

        $detalles = $ruta->pedidos?->flatMap->detalles ?? collect();
        if ($detalles->isEmpty()) {
            return null;
        }

        $primera = self::lineaDesdeDetalleDistribucion($detalles->first());
        if ($detalles->count() === 1) {
            return $primera;
        }

        return $primera.' +'.($detalles->count() - 1).' más';
    }

    private static function lineaDesdeDetallePedido(DetallePedido $detalle): string
    {
        $nombre = trim((string) (
            $detalle->cultivo_personalizado
            ?? $detalle->producto_nombre
            ?? $detalle->insumo?->nombre
            ?? 'Producto'
        ));
        $presentacion = PedidoCatalogo::presentacionDetalle($detalle);
        $partes = [$nombre];

        $empaque = $presentacion['empaque'] ?? null;
        if (is_string($empaque) && trim($empaque) !== '') {
            $partes[] = trim($empaque);
        }

        $unidades = $presentacion['unidades_fmt'] ?? null;
        if (is_string($unidades) && $unidades !== '') {
            $partes[] = $unidades.' u';
        }

        $partes[] = ($presentacion['kg_fmt'] ?? number_format((float) $detalle->cantidad, 2, ',', '.')).' kg';

        return implode(' · ', $partes);
    }

    private static function lineaDesdeDetalleDistribucion(DetallePedidoDistribucion $detalle): string
    {
        $detalle->loadMissing(['insumo.unidadMedida', 'presentacion.tipoEmpaque', 'presentacion.unidadMedida']);

        $nombre = trim((string) ($detalle->insumo?->nombre ?? $detalle->producto_nombre ?? 'Producto'));
        if (str_contains($nombre, ' · ')) {
            [$nombre] = explode(' · ', $nombre, 2);
        }

        $empaque = PedidoCatalogo::descripcionEmpaqueDetalle($detalle->observaciones);
        if ($empaque === null && $detalle->presentacion) {
            $etiquetas = array_filter([
                trim((string) ($detalle->presentacion->nombre ?? '')),
                trim((string) ($detalle->presentacion->tipoEmpaque?->nombre ?? '')),
            ]);
            $empaque = $etiquetas !== [] ? implode(' · ', $etiquetas) : null;
        }
        if ($empaque === null && str_contains((string) ($detalle->producto_nombre ?? ''), ' · ')) {
            [, $empaque] = explode(' · ', (string) $detalle->producto_nombre, 2);
        }

        $partes = [$nombre];
        if (is_string($empaque) && trim($empaque) !== '') {
            $partes[] = trim($empaque);
        }

        $meta = PedidoCatalogo::descripcionEmpaqueDetalle($detalle->observaciones);
        if (is_string($meta) && preg_match('/^([\d][\d.,]*)\s+unidades?\b/iu', $meta, $coincidencias)) {
            $unidades = (int) preg_replace('/[^\d]/', '', $coincidencias[1]);
            if ($unidades > 0) {
                $partes[] = number_format($unidades, 0, ',', '.').' u';
            }
        }

        $unidad = $detalle->insumo?->unidadMedida?->abreviatura
            ?? $detalle->presentacion?->unidadMedida?->abreviatura
            ?? 'kg';
        $partes[] = number_format((float) $detalle->cantidad, 2, ',', '.').' '.$unidad;

        return implode(' · ', $partes);
    }
}
