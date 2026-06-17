<?php

namespace App\Support;

use App\Models\Cultivo;
use App\Models\Insumo;

final class CultivoSiembraCatalogo
{
    /**
     * Dosis de referencia por nombre de cultivo (semilla o plántulas por hectárea).
     *
     * @return array{por_ha: float, unidad: string, etiqueta_unidad: string}|null
     */
    public static function dosisPorNombreCultivo(?string $nombre): ?array
    {
        if ($nombre === null || trim($nombre) === '') {
            return null;
        }

        $key = mb_strtolower(trim($nombre));

        $tabla = [
            'zanahoria' => ['por_ha' => 3.0, 'unidad' => 'kg', 'etiqueta_unidad' => 'kilogramos'],
            'tomate' => ['por_ha' => 0.4, 'unidad' => 'kg', 'etiqueta_unidad' => 'kilogramos'],
            'papa' => ['por_ha' => 2500, 'unidad' => 'kg', 'etiqueta_unidad' => 'kilogramos'],
            'cebolla' => ['por_ha' => 8.0, 'unidad' => 'kg', 'etiqueta_unidad' => 'kilogramos'],
            'lechuga' => ['por_ha' => 0.35, 'unidad' => 'kg', 'etiqueta_unidad' => 'kilogramos'],
            'maíz' => ['por_ha' => 25.0, 'unidad' => 'kg', 'etiqueta_unidad' => 'kilogramos'],
            'maiz' => ['por_ha' => 25.0, 'unidad' => 'kg', 'etiqueta_unidad' => 'kilogramos'],
            'mango' => ['por_ha' => 200.0, 'unidad' => 'unidad', 'etiqueta_unidad' => 'plantas'],
            'pimentón' => ['por_ha' => 0.25, 'unidad' => 'kg', 'etiqueta_unidad' => 'kilogramos'],
            'pimenton' => ['por_ha' => 0.25, 'unidad' => 'kg', 'etiqueta_unidad' => 'kilogramos'],
        ];

        foreach ($tabla as $fragmento => $dosis) {
            if (str_contains($key, $fragmento)) {
                return $dosis;
            }
        }

        return null;
    }

    public static function sugerenciaParaInsumo(\App\Models\Insumo $insumo, float $superficieHa): array
    {
        $porHa = $insumo->dosis_por_ha;
        $unidad = $insumo->dosis_unidad;

        if ($porHa === null || (float) $porHa <= 0 || $unidad === null || trim((string) $unidad) === '') {
            $fallback = self::dosisPorNombreCultivo(
                \App\Support\PedidoCatalogo::cultivoDesdeInsumo($insumo)
            );
            $porHa = $fallback['por_ha'] ?? $porHa;
            $unidad = $fallback['unidad'] ?? ($unidad ?: ($insumo->unidadMedida?->abreviatura ?? 'kg'));
            $etiqueta = $fallback['etiqueta_unidad'] ?? 'kilogramos';
        } else {
            $etiqueta = self::etiquetaUnidadAmigable((string) $unidad);
        }

        $superficie = max(0.0, $superficieHa);
        $tieneDosis = $porHa !== null && (float) $porHa > 0;
        $sugerido = $tieneDosis ? round((float) $porHa * $superficie, 2) : 0.0;

        return [
            'por_ha' => (float) ($porHa ?? 0),
            'unidad' => (string) ($unidad ?? 'kg'),
            'etiqueta_unidad' => $etiqueta ?? 'kilogramos',
            'superficie_ha' => $superficie,
            'sugerido' => $sugerido,
            'tiene_dosis' => $tieneDosis,
            'insumoid' => (int) $insumo->insumoid,
            'insumo_nombre' => $insumo->nombre,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function sugerenciaDesdeLote(\App\Models\Lote $lote): ?array
    {
        $lote->loadMissing('insumoSemilla');
        if (! $lote->insumoSemilla) {
            return null;
        }

        $sugerencia = self::sugerenciaParaInsumo($lote->insumoSemilla, (float) $lote->superficie);
        if ($lote->cantidad_semilla_planificada !== null && (float) $lote->cantidad_semilla_planificada > 0) {
            $sugerencia['sugerido'] = round((float) $lote->cantidad_semilla_planificada, 2);
            $sugerencia['planificado_en_lote'] = true;
        }

        return $sugerencia;
    }

    /**
     * @return array{
     *     por_ha: float,
     *     unidad: string,
     *     etiqueta_unidad: string,
     *     superficie_ha: float,
     *     sugerido: float,
     *     tiene_dosis: bool
     * }
     */
    public static function sugerenciaParaLote(Cultivo $cultivo, float $superficieHa): array
    {
        $porHa = $cultivo->dosis_siembra_por_ha;
        $unidad = $cultivo->dosis_siembra_unidad;

        if ($porHa === null || $unidad === null || trim((string) $unidad) === '') {
            $fallback = self::dosisPorNombreCultivo($cultivo->nombre);
            $porHa = $fallback['por_ha'] ?? null;
            $unidad = $fallback['unidad'] ?? 'kg';
            $etiqueta = $fallback['etiqueta_unidad'] ?? 'kilogramos';
        } else {
            $etiqueta = self::etiquetaUnidadAmigable((string) $unidad);
        }

        $superficie = max(0.0, $superficieHa);
        $tieneDosis = $porHa !== null && (float) $porHa > 0;
        $sugerido = $tieneDosis ? round((float) $porHa * $superficie, 2) : 0.0;

        return [
            'por_ha' => (float) ($porHa ?? 0),
            'unidad' => (string) ($unidad ?? 'kg'),
            'etiqueta_unidad' => $etiqueta ?? 'kilogramos',
            'superficie_ha' => $superficie,
            'sugerido' => $sugerido,
            'tiene_dosis' => $tieneDosis,
        ];
    }

    public static function etiquetaUnidadAmigable(string $unidad): string
    {
        return match (mb_strtolower(trim($unidad))) {
            'kg', 'kilogramo' => 'kilogramos',
            'g', 'gramo' => 'gramos',
            'qq', 'quintal' => 'quintales',
            'und', 'unidad', 'planta', 'plantas' => 'plantas',
            'semilla', 'semillas' => 'semillas',
            default => $unidad,
        };
    }

    public static function unidadLegible(?string $unidad): string
    {
        $u = mb_strtolower(trim((string) $unidad));

        return match ($u) {
            'und', 'unidad', 'planta', 'plantas' => 'plantas',
            'semilla', 'semillas' => 'semillas',
            'kg' => 'kg',
            'g' => 'g',
            default => $u !== '' ? $u : 'kg',
        };
    }

    public static function dosisEnUnidadesSiembra(?string $unidad): bool
    {
        return in_array(mb_strtolower(trim((string) $unidad)), [
            'und', 'unidad', 'planta', 'plantas', 'semilla', 'semillas',
        ], true);
    }

    /** Plantas o semillas estimadas por kilogramo de material de siembra. */
    public static function semillasPorKgEstimado(?string $nombreCultivo): ?float
    {
        if ($nombreCultivo === null || trim($nombreCultivo) === '') {
            return null;
        }

        $key = mb_strtolower(trim($nombreCultivo));

        $tabla = [
            'mango' => 50.0,
            'tomate' => 50000.0,
            'zanahoria' => 800000.0,
            'lechuga' => 120000.0,
            'cebolla' => 250000.0,
            'papa' => 15000.0,
            'maíz' => 4000.0,
            'maiz' => 4000.0,
            'pimentón' => 45000.0,
            'pimenton' => 45000.0,
        ];

        foreach ($tabla as $fragmento => $valor) {
            if (str_contains($key, $fragmento)) {
                return $valor;
            }
        }

        return null;
    }

    public static function semillasPorKgDesdeInsumo(\App\Models\Insumo $insumo): ?float
    {
        if ($insumo->semillas_por_kg !== null && (float) $insumo->semillas_por_kg > 0) {
            return (float) $insumo->semillas_por_kg;
        }

        return self::semillasPorKgEstimado(
            \App\Support\PedidoCatalogo::cultivoDesdeInsumo($insumo)
        );
    }

    public static function etiquetaStockSemilla(\App\Models\Insumo $insumo): string
    {
        $insumo->loadMissing('unidadMedida', 'tipo');
        $stock = (float) ($insumo->stock ?? 0);
        $unidadStock = $insumo->unidadMedida?->abreviatura ?? $insumo->unidadMedida?->nombre ?? 'ud';
        $base = 'Stock: '.number_format($stock, 2).' '.$unidadStock;

        if (InsumoCatalogo::slugFromNombreTipo($insumo->tipo?->nombre) !== 'material_siembra') {
            return $base;
        }

        if (! self::dosisEnUnidadesSiembra($insumo->dosis_unidad)) {
            return $base;
        }

        $factor = self::semillasPorKgDesdeInsumo($insumo);
        if ($factor === null || $factor <= 0 || mb_strtolower($unidadStock) !== 'kg') {
            return $base;
        }

        $estimado = (int) round($stock * $factor);
        $etiqueta = self::unidadLegible($insumo->dosis_unidad);

        return $base.' (~'.number_format($estimado, 0, ',', '.').' '.$etiqueta.' est.)';
    }
}
