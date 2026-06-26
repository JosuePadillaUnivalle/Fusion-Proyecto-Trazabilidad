<?php

namespace App\Support;

use App\Models\RutaDistribucion;
use App\Models\RutaDistribucionParada;
use App\Services\CierreEnvioPlantaMayoristaService;
use Illuminate\Support\Collection;

final class TrasladoPlantaMayoristaPresentacion
{
    public static function nombreDestinoMayorista(RutaDistribucion $ruta): ?string
    {
        $ruta->loadMissing(['almacenMayoristaDestino', 'paradas']);

        $nombre = trim((string) ($ruta->almacenMayoristaDestino?->nombre ?? ''));
        if ($nombre !== '') {
            return AlmacenNombreCatalogo::etiquetaListaDesdeNombreCanonico($nombre);
        }

        $parada = self::paradaEntregaMayorista($ruta);
        if ($parada?->destino) {
            return AlmacenNombreCatalogo::etiquetaListaDesdeTexto(
                self::limpiarEtiquetaParada($parada->destino),
                'mayorista'
            );
        }

        $doc = app(CierreEnvioPlantaMayoristaService::class)->documentoEntrega($ruta);
        $meta = $doc?->metadata['destino_mayorista_nombre'] ?? null;

        if (is_string($meta) && trim($meta) !== '') {
            return AlmacenNombreCatalogo::etiquetaListaDesdeTexto(trim($meta), 'mayorista');
        }

        return null;
    }

    public static function paradaEntregaMayorista(RutaDistribucion $ruta): ?RutaDistribucionParada
    {
        $paradas = $ruta->relationLoaded('paradas') ? $ruta->paradas : $ruta->paradas()->get();

        return $paradas
            ->first(fn (RutaDistribucionParada $p) => ($p->tipo ?? '') === RutaDistribucionCatalogo::PARADA_ENTREGA_MAYORISTA)
            ?? $paradas->first(fn (RutaDistribucionParada $p) => str_contains(mb_strtolower((string) ($p->destino ?? '')), 'entrega:'));
    }

    /** @return Collection<int, array{producto: string, presentacion: ?string, cantidad_unidades: float|null, cantidad: float, unidad: string, observaciones: ?string}> */
    public static function lineasProducto(RutaDistribucion $ruta): Collection
    {
        $ruta->loadMissing(['detallesTraslado.insumo.unidadMedida']);

        if ($ruta->detallesTraslado->isNotEmpty()) {
            return $ruta->detallesTraslado->map(fn ($detalle) => [
                'producto' => trim((string) ($detalle->producto_nombre ?? $detalle->insumo?->nombre ?? 'Producto')),
                'presentacion' => $detalle->presentacion_nombre,
                'cantidad_unidades' => $detalle->cantidad_unidades !== null ? (float) $detalle->cantidad_unidades : null,
                'cantidad' => (float) $detalle->cantidad,
                'unidad' => $detalle->insumo?->unidadMedida?->abreviatura ?? 'kg',
                'observaciones' => $detalle->observaciones,
            ]);
        }

        $doc = app(CierreEnvioPlantaMayoristaService::class)->documentoEntrega($ruta);
        $snap = $doc?->metadata['lineas_producto'] ?? null;
        if ((! is_array($snap) || $snap === []) && $doc !== null) {
            app(CierreEnvioPlantaMayoristaService::class)->repararMetadataDocumento($ruta);
            $doc = $doc->fresh();
            $snap = $doc?->metadata['lineas_producto'] ?? null;
        }
        if (! is_array($snap) || $snap === []) {
            return collect();
        }

        return collect($snap)->map(function (array $linea) {
            return [
                'producto' => (string) ($linea['producto'] ?? 'Producto'),
                'presentacion' => $linea['presentacion'] ?? $linea['empaquetaje'] ?? null,
                'cantidad_unidades' => isset($linea['cantidad_unidades']) ? (float) $linea['cantidad_unidades'] : null,
                'cantidad' => (float) ($linea['cantidad'] ?? 0),
                'unidad' => (string) ($linea['unidad'] ?? 'kg'),
                'observaciones' => $linea['observaciones'] ?? null,
            ];
        });
    }

    public static function conteoProductos(RutaDistribucion $ruta): int
    {
        return self::lineasProducto($ruta)->count();
    }

    public static function resumenCargaLista(RutaDistribucion $ruta): ?string
    {
        $lineas = self::lineasProducto($ruta);
        if ($lineas->isEmpty()) {
            return null;
        }

        $primera = $lineas->first();
        $partes = [(string) ($primera['producto'] ?? 'Producto')];
        if (! empty($primera['presentacion'])) {
            $partes[] = (string) $primera['presentacion'];
        }
        if (! empty($primera['cantidad_unidades'])) {
            $partes[] = number_format((float) $primera['cantidad_unidades'], 0, ',', '.').' u';
        }
        $partes[] = number_format((float) ($primera['cantidad'] ?? 0), 2, ',', '.')
            .' '.($primera['unidad'] ?? 'kg');

        $texto = implode(' · ', $partes);

        if ($lineas->count() > 1) {
            $texto .= ' +'.($lineas->count() - 1).' más';
        }

        return $texto;
    }

    public static function totalKg(RutaDistribucion $ruta): float
    {
        return (float) self::lineasProducto($ruta)->sum('cantidad');
    }

    public static function tieneCargaRegistrada(RutaDistribucion $ruta): bool
    {
        return self::lineasProducto($ruta)->isNotEmpty();
    }

    /** @return array<int, array<string, mixed>> */
    public static function snapshotLineasParaMetadata(RutaDistribucion $ruta): array
    {
        $ruta->loadMissing(['detallesTraslado.insumo.unidadMedida']);

        return $ruta->detallesTraslado->map(fn ($detalle) => [
            'producto' => trim((string) ($detalle->producto_nombre ?? $detalle->insumo?->nombre ?? 'Producto')),
            'presentacion' => $detalle->presentacion_nombre,
            'empaquetaje' => $detalle->presentacion_nombre,
            'cantidad_unidades' => $detalle->cantidad_unidades !== null ? (float) $detalle->cantidad_unidades : null,
            'cantidad' => (float) $detalle->cantidad,
            'unidad' => $detalle->insumo?->unidadMedida?->abreviatura ?? 'kg',
            'observaciones' => $detalle->observaciones,
        ])->values()->all();
    }

    private static function limpiarEtiquetaParada(string $texto): string
    {
        return trim(preg_replace('/^(Entrega|Carga):\s*/iu', '', $texto) ?? $texto);
    }
}
