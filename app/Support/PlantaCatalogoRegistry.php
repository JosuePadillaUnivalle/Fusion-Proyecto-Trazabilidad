<?php

namespace App\Support;

use App\Models\TipoEmpaque;

/**
 * Catálogos administrables bajo Producción planta.
 */
final class PlantaCatalogoRegistry
{
    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        return [
            'tipos-empaque' => LogisticaCatalogoRegistry::get('tipos-empaque'),
        ];
    }

    public static function get(string $tipo): ?array
    {
        return self::all()[$tipo] ?? null;
    }

    public static function tema(string $slug): array
    {
        $meta = self::get($slug);

        return $meta['tema'] ?? ['accent' => '#5b21b6', 'soft' => '#f5f3ff', 'mid' => '#6d28d9'];
    }

    public static function etiquetaColumna(array $config, string $col): string
    {
        return LogisticaCatalogoRegistry::etiquetaColumna($config, $col);
    }

    public static function tiposValidos(): array
    {
        return array_keys(self::all());
    }
}
