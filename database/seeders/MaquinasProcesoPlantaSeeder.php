<?php

namespace Database\Seeders;

use App\Models\MaquinaPlanta;
use App\Models\ProcesoMaquinaPlanta;
use App\Models\ProcesoPlanta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Máquinas de planta con vínculo coherente a cada proceso de transformación.
 * php artisan db:seed --class=MaquinasProcesoPlantaSeeder
 */
class MaquinasProcesoPlantaSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('maquina_planta') || ! Schema::hasTable('proceso_maquina_planta')) {
            return;
        }

        $definiciones = [
            ['codigo' => 'L-100', 'nombre' => 'Lavadora Industrial L-100', 'descripcion' => 'Lava y prepara materias primas.', 'procesos' => ['Preparación de Materias Primas']],
            ['codigo' => 'MX-200', 'nombre' => 'Mezcladora Industrial MX-200', 'descripcion' => 'Mezcla ingredientes y aditivos.', 'procesos' => ['Mezclado']],
            ['codigo' => 'EX-300', 'nombre' => 'Extrusora EX-300', 'descripcion' => 'Extrusión del producto.', 'procesos' => ['Extrusión']],
            ['codigo' => 'MD-400', 'nombre' => 'Moldeadora MD-400', 'descripcion' => 'Moldea el producto.', 'procesos' => ['Moldeo']],
            ['codigo' => 'SC-500', 'nombre' => 'Secador Industrial SC-500', 'descripcion' => 'Secado del producto.', 'procesos' => ['Secado']],
            ['codigo' => 'TR-600', 'nombre' => 'Freidora / Horno TR-600', 'descripcion' => 'Tratamiento térmico (freír, hornear).', 'procesos' => ['Tratamiento Térmico']],
            ['codigo' => 'EV-700', 'nombre' => 'Envasadora EV-700', 'descripcion' => 'Envasado del producto.', 'procesos' => ['Envasado']],
            ['codigo' => 'ET-800', 'nombre' => 'Etiquetadora ET-800', 'descripcion' => 'Etiquetado de productos.', 'procesos' => ['Etiquetado']],
            ['codigo' => 'SE-10', 'nombre' => 'Selladora de Empaque SE-10', 'descripcion' => 'Empaquetado final.', 'procesos' => ['Empaquetado']],
            ['codigo' => 'BC-20', 'nombre' => 'Banda Clasificadora BC-20', 'descripcion' => 'Selección y clasificación inicial.', 'procesos' => ['Preparación de Materias Primas']],
            ['codigo' => 'BD-500', 'nombre' => 'Balanza Digital BD-500', 'descripcion' => 'Pesaje en control de calidad (no transformación).', 'procesos' => []],
        ];

        $creados = 0;

        foreach ($definiciones as $def) {
            $maquina = MaquinaPlanta::updateOrCreate(
                ['codigo' => $def['codigo']],
                [
                    'nombre' => $def['nombre'],
                    'descripcion' => $def['descripcion'],
                    'activo' => $def['procesos'] !== [] || $def['codigo'] === 'BD-500',
                ]
            );

            foreach ($def['procesos'] as $nombreProceso) {
                $proceso = ProcesoPlanta::query()->where('nombre', $nombreProceso)->first();
                if (! $proceso) {
                    continue;
                }

                ProcesoMaquinaPlanta::updateOrCreate(
                    [
                        'procesoplantaid' => $proceso->procesoplantaid,
                        'maquinaplantaid' => $maquina->maquinaplantaid,
                    ],
                    [
                        'orden_paso' => 1,
                        'nombre' => $nombreProceso,
                        'descripcion' => 'Línea '.$def['codigo'].' · '.$nombreProceso,
                    ]
                );
                $creados++;
            }
        }

        $this->command?->info(
            'Máquinas-proceso: '.count($definiciones).' equipos, '.$creados.' vínculos coherentes.'
        );
    }
}
