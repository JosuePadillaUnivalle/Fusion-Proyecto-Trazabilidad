<?php

namespace Database\Seeders;

use App\Models\MaquinaVariablePlanta;
use App\Models\PlantillaTransformacion;
use App\Models\PlantillaTransformacionPasoVariable;
use App\Support\ParametroRangoPlanta;
use App\Support\ProcesoPlantaCatalogo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Copia parámetros de máquina a cada paso de plantilla (rango de proceso más estrecho).
 * php artisan db:seed --class=PlantillaPasoVariableSeeder
 */
class PlantillaPasoVariableSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('plantilla_transformacion_paso_variable')) {
            return;
        }

        $plantillas = PlantillaTransformacion::query()
            ->with(['pasos.proceso', 'pasos.maquina'])
            ->get();

        $total = 0;

        foreach ($plantillas as $plantilla) {
            foreach ($plantilla->pasos as $paso) {
                if (! $paso->maquinaplantaid) {
                    continue;
                }

                $paso->variables()->delete();

                $maqVars = MaquinaVariablePlanta::query()
                    ->where('maquinaplantaid', $paso->maquinaplantaid)
                    ->with('variableEstandar')
                    ->get();

                foreach ($maqVars as $mv) {
                    $min = (float) $mv->valor_minimo;
                    $max = (float) $mv->valor_maximo;
                    $codigo = $mv->variableEstandar?->codigo ?? '';

                    if (ProcesoPlantaCatalogo::esCierreTransformacion($paso->proceso?->nombre ?? '')
                        && $paso->maquina?->codigo === 'SE-10') {
                        if ($codigo === 'VAR-PRES') {
                            $min = max($min, 6);
                            $max = min($max, 8);
                        }
                        if ($codigo === 'VAR-CALVIS') {
                            $min = max($min, 7);
                            $max = min($max, 10);
                        }
                    } elseif ($codigo === 'VAR-TEMP' && $paso->maquina?->codigo === 'TR-600') {
                        $rango = ParametroRangoPlanta::rangoProcesoDesdeMaquina($min, $max, 0.2);
                        $min = $rango['min'];
                        $max = $rango['max'];
                    } else {
                        $rango = ParametroRangoPlanta::rangoProcesoDesdeMaquina($min, $max);
                        $min = $rango['min'];
                        $max = $rango['max'];
                    }

                    if ($max < $min) {
                        $min = (float) $mv->valor_minimo;
                        $max = (float) $mv->valor_maximo;
                    }

                    PlantillaTransformacionPasoVariable::create([
                        'plantillapasoid' => $paso->plantillapasoid,
                        'variableestandarid' => $mv->variableestandarid,
                        'valor_minimo' => $min,
                        'valor_maximo' => $max,
                        'valor_objetivo' => round(($min + $max) / 2, 2),
                        'obligatorio' => true,
                    ]);
                    $total++;
                }
            }
        }

        $this->command?->info("Variables en pasos de plantilla: {$total} rangos de proceso.");
    }
}
