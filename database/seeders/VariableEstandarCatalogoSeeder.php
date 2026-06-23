<?php

namespace Database\Seeders;

use App\Models\VariableEstandar;
use Illuminate\Database\Seeder;

/**
 * Catálogo de variables estándar con descripciones realistas para planta.
 * Ejecutar: php artisan db:seed --class=VariableEstandarCatalogoSeeder
 */
class VariableEstandarCatalogoSeeder extends Seeder
{
    public function run(): void
    {
        $variables = [
            [
                'codigo' => 'VAR-TEMP',
                'nombre' => 'Temperatura',
                'unidad' => '°C',
                'descripcion' => 'Temperatura del producto o del equipo durante la operación.',
            ],
            [
                'codigo' => 'VAR-PRES',
                'nombre' => 'Presión',
                'unidad' => 'PSI',
                'descripcion' => 'Presión de trabajo del sistema (vapor, aire o fluido).',
            ],
            [
                'codigo' => 'VAR-HUM',
                'nombre' => 'Humedad relativa',
                'unidad' => '%',
                'descripcion' => 'Humedad del ambiente o del producto en la etapa.',
            ],
            [
                'codigo' => 'VAR-VEL',
                'nombre' => 'Velocidad',
                'unidad' => 'RPM',
                'descripcion' => 'Velocidad de rotación de mezcladora, molino o cinta.',
            ],
            [
                'codigo' => 'VAR-TIEMPO',
                'nombre' => 'Tiempo de proceso',
                'unidad' => 'min',
                'descripcion' => 'Duración estimada o real de la etapa en minutos.',
            ],
            [
                'codigo' => 'VAR-PH',
                'nombre' => 'pH',
                'unidad' => 'pH',
                'descripcion' => 'Acidez o alcalinidad de la mezcla o solución.',
            ],
            [
                'codigo' => 'VAR-PESO',
                'nombre' => 'Peso',
                'unidad' => 'kg',
                'descripcion' => 'Peso de muestra, lote o porción verificada en báscula.',
            ],
            [
                'codigo' => 'VAR-DENS',
                'nombre' => 'Densidad',
                'unidad' => 'g/cm³',
                'descripcion' => 'Densidad del producto después de mezclado o tratamiento.',
            ],
            [
                'codigo' => 'VAR-CALVIS',
                'nombre' => 'Calidad visual',
                'unidad' => 'escala 1-10',
                'descripcion' => 'Evaluación visual del color, textura y defectos aparentes.',
            ],
            [
                'codigo' => 'VAR-HUMGR',
                'nombre' => 'Humedad del grano',
                'unidad' => '%',
                'descripcion' => 'Contenido de humedad del grano o materia prima seca.',
            ],
        ];

        foreach ($variables as $v) {
            VariableEstandar::updateOrCreate(
                ['codigo' => $v['codigo']],
                [
                    'nombre' => $v['nombre'],
                    'unidad' => $v['unidad'],
                    'descripcion' => $v['descripcion'],
                    'activo' => true,
                ]
            );
        }

        // Limpiar marcadores antiguos del demo
        VariableEstandar::query()
            ->where('descripcion', '[MOD-PLANTA]')
            ->orWhere('descripcion', 'like', '%[MOD-PLANTA]%')
            ->get()
            ->each(function (VariableEstandar $var) use ($variables) {
                $match = collect($variables)->firstWhere('codigo', $var->codigo);
                if ($match) {
                    $var->update(['descripcion' => $match['descripcion']]);
                } elseif (! $var->descripcion || str_contains((string) $var->descripcion, '[MOD-PLANTA]')) {
                    $var->update(['descripcion' => 'Parámetro operativo de la línea de planta.']);
                }
            });

        $this->command?->info('Variables estándar actualizadas: '.count($variables).' definiciones.');
    }
}
