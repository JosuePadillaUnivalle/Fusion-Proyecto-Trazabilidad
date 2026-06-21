<?php

namespace App\Support;

use App\Models\CatalogoTamanoConteo;
use App\Models\CondicionTransporte;
use App\Models\Insumo;
use App\Models\TipoEmpaque;
use App\Models\TipoIncidenteTransporte;
use App\Models\TipoTransporte;
use App\Models\TipoVehiculo;
use App\Support\InsumoCatalogo;

/**
 * Registro de catálogos administrables bajo Envíos / Logística.
 */
final class LogisticaCatalogoRegistry
{
    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        return [
            'tipos-empaque' => [
                'titulo' => 'Tipos de empaque',
                'icono' => 'fa-box',
                'tema' => ['accent' => '#2c5530', 'soft' => '#e8f4ec', 'mid' => '#4a7c59'],
                'modelo' => TipoEmpaque::class,
                'pk' => 'tipoempaqueid',
                'orden' => 'nombre',
                'campos' => [
                    'nombre' => ['label' => 'Nombre', 'rules' => 'required|string|max:100'],
                    'descripcion' => ['label' => 'Descripción', 'rules' => 'nullable|string|max:500'],
                    'largo_cm' => ['label' => 'Largo (cm)', 'rules' => 'nullable|numeric|min:0'],
                    'ancho_cm' => ['label' => 'Ancho (cm)', 'rules' => 'nullable|numeric|min:0'],
                    'alto_cm' => ['label' => 'Alto (cm)', 'rules' => 'nullable|numeric|min:0'],
                    'tara_kg' => ['label' => 'Tara (kg)', 'rules' => 'nullable|numeric|min:0'],
                    'capacidad_unidades' => ['label' => 'Capacidad (unidades)', 'rules' => 'nullable|integer|min:1'],
                    'unidades_por_pallet' => ['label' => 'Unidades por pallet', 'rules' => 'nullable|integer|min:1'],
                ],
                'columnas' => ['nombre', 'largo_cm', 'ancho_cm', 'alto_cm', 'tara_kg', 'capacidad_unidades'],
            ],
            'tamano-conteo' => [
                'titulo' => 'Tamaño / conteo (calibres)',
                'menu' => 'Tamaño / conteo',
                'icono' => 'fa-ruler-combined',
                'tema' => ['accent' => '#2563eb', 'soft' => '#e8f2ff', 'mid' => '#3b82f6'],
                'modelo' => CatalogoTamanoConteo::class,
                'pk' => 'catalogotamanoconteoid',
                'orden' => 'nombre',
                'with' => ['insumo', 'tipoEmpaque'],
                'campos' => [
                    'insumoid' => [
                        'label' => 'Verdura / producto',
                        'rules' => 'required|exists:insumo,insumoid',
                        'tipo' => 'select',
                        'opciones' => fn () => InsumoCatalogo::insumosVerdurasParaLogistica(),
                    ],
                    'nombre' => ['label' => 'Nombre / calibre', 'rules' => 'required|string|max:150'],
                    'conteo_por_empaque' => ['label' => 'Conteo por empaque', 'rules' => 'required|integer|min:1'],
                    'peso_promedio_kg' => [
                        'label' => 'Peso promedio kg(Unidad)',
                        'rules' => 'required|numeric|min:0',
                        'ayuda' => 'Peso medio de una verdura en kilogramos (ej. 0,135 kg por tomate mediano).',
                    ],
                    'tipoempaqueid' => [
                        'label' => 'Tipo de empaque',
                        'rules' => 'nullable|exists:tipo_empaque,tipoempaqueid',
                        'tipo' => 'select',
                        'opciones' => fn () => TipoEmpaque::query()->where('activo', true)->orderBy('nombre')->pluck('nombre', 'tipoempaqueid'),
                    ],
                    'activo' => ['label' => 'Activo', 'rules' => 'boolean', 'tipo' => 'checkbox'],
                ],
                'columnas' => ['insumo.nombre', 'nombre', 'conteo_por_empaque', 'peso_promedio_kg'],
                'etiquetas_columnas' => [
                    'insumo.nombre' => 'Verdura / producto',
                    'nombre' => 'Nombre / calibre',
                    'conteo_por_empaque' => 'Conteo por empaque',
                    'peso_promedio_kg' => 'Peso promedio kg(Unidad)',
                ],
            ],
            'tipos-vehiculo' => [
                'titulo' => 'Tipos de vehículo',
                'tema' => ['accent' => '#0284c7', 'soft' => '#e0f2fe', 'mid' => '#0ea5e9'],
                'modelo' => TipoVehiculo::class,
                'pk' => 'tipovehiculoid',
                'orden' => 'nombre',
                'with' => ['tiposTransporte'],
                'sync' => ['tiposTransporte' => 'tipotransporteid'],
                'campos' => [
                    'nombre' => ['label' => 'Nombre', 'rules' => 'required|string|max:100'],
                    'codigo' => ['label' => 'Código', 'rules' => 'nullable|string|max:30'],
                    'tamano' => ['label' => 'Tamaño', 'rules' => 'nullable|string|max:50'],
                    'capacidad_kg' => [
                        'label' => 'Capacidad (kg)',
                        'rules' => 'nullable|numeric|min:0',
                        'ayuda' => 'Peso máximo de carga útil que puede transportar el vehículo.',
                    ],
                    'capacidad_m3' => [
                        'label' => 'Volumen de carga (m³)',
                        'rules' => 'nullable|numeric|min:0',
                        'placeholder' => 'Ej. 8',
                        'ayuda' => 'Metros cúbicos de espacio en la caja de carga.',
                    ],
                    'largo_m' => [
                        'label' => 'Largo caja (m)',
                        'rules' => 'nullable|numeric|min:0',
                        'ayuda' => 'Largo interno de la caja de carga en metros.',
                    ],
                    'ancho_m' => [
                        'label' => 'Ancho caja (m)',
                        'rules' => 'nullable|numeric|min:0',
                    ],
                    'alto_m' => [
                        'label' => 'Alto caja (m)',
                        'rules' => 'nullable|numeric|min:0',
                    ],
                    'factor_volumen_util' => [
                        'label' => 'Factor volumen útil',
                        'rules' => 'nullable|numeric|min:0.1|max:1',
                        'placeholder' => '0.85',
                        'ayuda' => 'Porción del volumen realmente aprovechable (estiba, holgura). Recomendado: 0,85.',
                    ],
                    'tipotransporteid' => [
                        'label' => 'Tipo de transporte por defecto',
                        'rules' => 'required|integer|exists:tipo_transporte,tipotransporteid',
                        'tipo' => 'select',
                        'opciones' => fn () => TipoTransporte::query()->orderBy('nombre')->pluck('nombre', 'tipotransporteid'),
                        'ayuda' => 'Modo de transporte habitual para unidades de este tipo (solo uno por vehículo).',
                        'no_persistir' => true,
                    ],
                    'licencia_requerida' => ['label' => 'Licencia requerida', 'rules' => 'nullable|string|max:10'],
                    'activo' => ['label' => 'Activo', 'rules' => 'boolean', 'tipo' => 'checkbox'],
                ],
                'columnas' => ['nombre', 'codigo', 'capacidad_kg', 'capacidad_m3'],
                'etiquetas_columnas' => [
                    'nombre' => 'Nombre',
                    'codigo' => 'Código',
                    'capacidad_kg' => 'Capacidad (kg)',
                    'capacidad_m3' => 'Volumen de carga (m³)',
                ],
                'icono' => 'fa-truck',
            ],
            'tipos-transporte' => [
                'titulo' => 'Tipos de transporte',
                'icono' => 'fa-shipping-fast',
                'tema' => ['accent' => '#0d9488', 'soft' => '#ccfbf1', 'mid' => '#14b8a6'],
                'modelo' => TipoTransporte::class,
                'pk' => 'tipotransporteid',
                'orden' => 'nombre',
                'campos' => [
                    'nombre' => ['label' => 'Nombre', 'rules' => 'required|string|max:100'],
                    'descripcion' => ['label' => 'Descripción', 'rules' => 'nullable|string|max:500'],
                ],
                'columnas' => ['nombre', 'descripcion'],
            ],
            'condiciones' => [
                'titulo' => 'Condiciones de transporte',
                'menu' => 'Condiciones',
                'icono' => 'fa-clipboard-check',
                'tema' => ['accent' => '#b45309', 'soft' => '#fef3c7', 'mid' => '#d97706'],
                'subtitulo' => 'Checklist de revisión del vehículo antes de salir con la carga.',
                'modelo' => CondicionTransporte::class,
                'pk' => 'condiciontransporteid',
                'orden' => 'codigo',
                'campos' => [
                    'codigo' => ['label' => 'Código', 'rules' => 'required|string|max:20'],
                    'titulo' => ['label' => 'Título', 'rules' => 'required|string|max:150'],
                    'descripcion' => ['label' => 'Descripción', 'rules' => 'nullable|string|max:500'],
                ],
                'columnas' => ['codigo', 'titulo', 'descripcion'],
            ],
            'incidentes' => [
                'titulo' => 'Catálogo de incidentes',
                'menu' => 'Incidentes',
                'icono' => 'fa-exclamation-triangle',
                'tema' => ['accent' => '#b91c1c', 'soft' => '#fee2e2', 'mid' => '#dc2626'],
                'modelo' => TipoIncidenteTransporte::class,
                'pk' => 'tipoincidentetransporteid',
                'orden' => 'codigo',
                'campos' => [
                    'codigo' => ['label' => 'Código', 'rules' => 'required|string|max:20'],
                    'titulo' => ['label' => 'Título', 'rules' => 'required|string|max:150'],
                    'descripcion' => ['label' => 'Descripción', 'rules' => 'nullable|string|max:500'],
                ],
                'columnas' => ['codigo', 'titulo', 'descripcion'],
            ],
        ];
    }

    public static function tema(string $slug): array
    {
        $meta = self::get($slug);

        return $meta['tema'] ?? ['accent' => '#2c5530', 'soft' => '#e8f4ec', 'mid' => '#4a7c59'];
    }

    public static function etiquetaColumna(array $config, string $col): string
    {
        if (isset($config['etiquetas_columnas'][$col])) {
            return $config['etiquetas_columnas'][$col];
        }

        $campo = str_contains($col, '.') ? null : $col;
        if ($campo && isset($config['campos'][$campo]['label'])) {
            return $config['campos'][$campo]['label'];
        }

        return str_replace(['.', '_'], [' / ', ' '], ucfirst($col));
    }

    public static function get(string $tipo): ?array
    {
        return self::all()[$tipo] ?? null;
    }

    public static function etiquetaMenu(string $slug, array $meta): string
    {
        return $meta['menu'] ?? $meta['titulo'];
    }

    public static function tiposValidos(): array
    {
        return array_keys(self::all());
    }
}
