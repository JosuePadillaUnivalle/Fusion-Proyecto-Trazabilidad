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
        $config = LogisticaCatalogoRegistry::get('tipos-empaque');
        if ($config) {
            $config['titulo'] = 'Tipos de empaque';
            $config['subtitulo'] = 'Empaques comerciales para productos terminados de planta';
            $config['tema'] = ['accent' => '#5b21b6', 'soft' => '#f5f3ff', 'mid' => '#6d28d9'];
            $config['scope'] = fn ($q) => TipoEmpaqueAmbito::scopePlanta($q);
            $config['defaults'] = [
                'ambito' => TipoEmpaqueAmbito::PLANTA,
                'capacidad_unidades' => 1,
                'activo' => true,
            ];
            $config['campos'] = [
                'nombre' => ['label' => 'Nombre', 'rules' => 'required|string|max:100'],
                'descripcion' => ['label' => 'Descripción', 'rules' => 'nullable|string|max:500'],
                'largo_cm' => ['label' => 'Largo (cm)', 'rules' => 'nullable|numeric|min:0'],
                'ancho_cm' => ['label' => 'Ancho (cm)', 'rules' => 'nullable|numeric|min:0'],
                'alto_cm' => ['label' => 'Alto (cm)', 'rules' => 'nullable|numeric|min:0'],
                'tara_kg' => ['label' => 'Tara (kg)', 'rules' => 'nullable|numeric|min:0'],
                'unidades_por_pallet' => [
                    'label' => 'Unidades por pallet',
                    'rules' => 'nullable|integer|min:1',
                    'ayuda' => 'Cuántos empaques de este tipo caben estibados en un pallet (referencia logística).',
                ],
            ];
            $config['columnas'] = ['nombre', 'largo_cm', 'ancho_cm', 'alto_cm', 'tara_kg', 'unidades_por_pallet'];
        }

        return [
            'tipos-empaque' => $config,
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
