<?php

namespace App\Support;

use App\Models\DetallePedidoDistribucion;
use App\Models\InventarioPresentacionLote;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class PedidoDistribucionConsolidacion
{
    /**
     * Agrupa líneas del mismo producto, lote y empaque (presentación comercial).
     * Conserva el detalle granular en BD; la consolidación es solo para presentación.
     *
     * @param  Collection<int, DetallePedidoDistribucion>|array<int, DetallePedidoDistribucion>  $detalles
     * @return array<int, array{
     *     producto: string,
     *     lote: string,
     *     empaque: string,
     *     cantidad: float,
     *     cantidad_kg: float,
     *     unidad: string,
     *     detalle_ids: array<int, int>
     * }>
     */
    public static function consolidar(Collection|array $detalles): array
    {
        $grupos = [];

        foreach (collect($detalles) as $detalle) {
            if (! $detalle instanceof DetallePedidoDistribucion) {
                continue;
            }

            $detalle->loadMissing(['insumo.unidadMedida', 'presentacion.tipoEmpaque', 'inventarioPresentacionLote']);
            $clave = self::clave($detalle);
            $cantidad = (float) $detalle->cantidad;
            if ($cantidad <= 0) {
                continue;
            }

            $presentacion = $detalle->presentacion;
            $pesoUnit = $presentacion ? $presentacion->pesoNetoKg() : 0.0;
            $kgLinea = $pesoUnit > 0 ? round($cantidad * $pesoUnit, 4) : $cantidad;

            if (! isset($grupos[$clave])) {
                $grupos[$clave] = [
                    'producto' => self::nombreProducto($detalle),
                    'lote' => self::referenciaLote($detalle),
                    'empaque' => self::nombreEmpaque($detalle),
                    'cantidad' => 0.0,
                    'cantidad_kg' => 0.0,
                    'unidad' => $presentacion?->etiquetaUnidad()
                        ?? $detalle->insumo?->unidadMedida?->abreviatura
                        ?? 'unidades',
                    'detalle_ids' => [],
                ];
            }

            $grupos[$clave]['cantidad'] += $cantidad;
            $grupos[$clave]['cantidad_kg'] += $kgLinea;
            $grupos[$clave]['detalle_ids'][] = (int) $detalle->detallepedidodistribucionid;
        }

        return array_values(array_map(function (array $grupo) {
            $grupo['cantidad'] = round($grupo['cantidad'], 4);
            $grupo['cantidad_kg'] = round($grupo['cantidad_kg'], 4);

            return $grupo;
        }, $grupos));
    }

    /** @param  Collection<int, DetallePedidoDistribucion>|array<int, DetallePedidoDistribucion>  $detalles */
    public static function etiquetaLineaConsolidada(Collection|array $detalles): string
    {
        return collect(self::consolidar($detalles))
            ->map(fn (array $g) => self::formatearEtiqueta($g))
            ->implode(' | ');
    }

    /** @param  array{producto: string, lote: string, empaque: string, cantidad: float, unidad: string, cantidad_kg?: float}  $grupo */
    public static function formatearEtiqueta(array $grupo): string
    {
        $nombre = trim($grupo['producto']);
        $lote = trim($grupo['lote'] ?? '');
        $empaque = trim($grupo['empaque'] ?? '');
        $cantidad = (float) ($grupo['cantidad'] ?? 0);
        $unidad = trim($grupo['unidad'] ?? 'unidades');

        $titulo = $nombre;
        if ($lote !== '') {
            $titulo .= ' - '.$lote;
        }

        $cantFmt = number_format($cantidad, 0, '.', ',');
        $texto = $titulo.' / '.$cantFmt.' '.$unidad;

        $kg = (float) ($grupo['cantidad_kg'] ?? 0);
        if ($kg > 0) {
            $texto .= ' ('.number_format($kg, 2, '.', '').' kg)';
        }

        if ($empaque !== '' && ! str_contains(strtolower($texto), strtolower($empaque))) {
            $texto = $titulo.' · '.$empaque.' / '.$cantFmt.' '.$unidad;
            if ($kg > 0) {
                $texto .= ' ('.number_format($kg, 2, '.', '').' kg)';
            }
        }

        return $texto;
    }

    public static function clave(DetallePedidoDistribucion $detalle): string
    {
        $producto = Str::lower(trim(self::nombreProducto($detalle)));
        $lote = Str::lower(trim(self::referenciaLote($detalle)));
        $presentacion = $detalle->presentacion;
        $empaque = Str::lower(trim(
            ($presentacion?->nombre ?? '').'|'.($presentacion?->tipoempaqueid ?? '').'|'.($presentacion?->tipo_envase ?? '')
        ));

        return $producto.'||'.$lote.'||'.$empaque;
    }

    public static function nombreProducto(DetallePedidoDistribucion $detalle): string
    {
        $nombre = trim((string) ($detalle->insumo?->nombre ?? ''));
        if ($nombre === '' && filled($detalle->producto_nombre)) {
            $nombre = trim(explode(' · ', (string) $detalle->producto_nombre)[0]);
        }

        return $nombre !== '' ? $nombre : 'Producto';
    }

    public static function referenciaLote(DetallePedidoDistribucion $detalle): string
    {
        if (filled($detalle->referencia_lote)) {
            return trim((string) $detalle->referencia_lote);
        }

        $inv = $detalle->inventarioPresentacionLote;
        if ($inv instanceof InventarioPresentacionLote) {
            return trim((string) ($inv->referencia_lote ?: $inv->etiquetaLote()));
        }

        return '';
    }

    public static function nombreEmpaque(DetallePedidoDistribucion $detalle): string
    {
        $pres = $detalle->presentacion;
        if ($pres === null) {
            if (str_contains((string) ($detalle->producto_nombre ?? ''), ' · ')) {
                [, $empaque] = explode(' · ', (string) $detalle->producto_nombre, 2);

                return trim($empaque);
            }

            return '';
        }

        $partes = array_filter([
            trim((string) ($pres->nombre ?? '')),
            trim((string) ($pres->tipoEmpaque?->nombre ?? '')),
        ]);

        return $partes !== [] ? implode(' · ', $partes) : '';
    }
}
