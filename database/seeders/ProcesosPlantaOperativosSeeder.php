<?php

namespace Database\Seeders;

use App\Support\ProcesoPlantaCatalogo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo único de procesos de planta (referencia PLANTA - IDEA).
 * php artisan db:seed --class=ProcesosPlantaOperativosSeeder
 */
class ProcesosPlantaOperativosSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('proceso_planta')) {
            return;
        }

        $descripciones = [
            'Preparación de Materias Primas' => 'Preparación y mezcla inicial de materias primas',
            'Mezclado' => 'Mezclado de componentes',
            'Extrusión' => 'Extrusión del material',
            'Moldeo' => 'Moldeo del producto',
            'Secado' => 'Secado del producto',
            'Tratamiento Térmico' => 'Tratamiento térmico',
            'Envasado' => 'Envasado del producto final',
            'Etiquetado' => 'Etiquetado de productos',
            'Empaquetado' => 'Empaquetado final',
            'Control de Calidad' => 'Inspección y control de calidad',
        ];

        foreach (ProcesoPlantaCatalogo::CANONICOS as $nombre) {
            \App\Models\ProcesoPlanta::updateOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => $descripciones[$nombre] ?? null, 'activo' => true]
            );
        }

        $off = ProcesoPlantaCatalogo::consolidarDuplicados();

        $this->command?->info(
            'Procesos de planta: '.count(ProcesoPlantaCatalogo::CANONICOS).' activos (sin duplicados). Desactivados: '.$off
        );
    }
}
