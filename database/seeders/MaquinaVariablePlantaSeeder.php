<?php

namespace Database\Seeders;

use App\Models\MaquinaPlanta;
use App\Models\MaquinaVariablePlanta;
use App\Models\VariableEstandar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Rangos estándar de parámetros por máquina de planta (límite físico del equipo).
 * php artisan db:seed --class=MaquinaVariablePlantaSeeder
 */
class MaquinaVariablePlantaSeeder extends Seeder
{
    /** @var array<string, list<array{0: string, 1: float, 2: float}>> */
    private const POR_CODIGO = [
        'L-100' => [
            ['VAR-TEMP', 5, 35],
            ['VAR-TIEMPO', 5, 45],
        ],
        'BC-20' => [
            ['VAR-VEL', 10, 80],
            ['VAR-CALVIS', 1, 10],
        ],
        'TR-600' => [
            ['VAR-TEMP', 50, 100],
            ['VAR-TIEMPO', 5, 120],
        ],
        'MX-200' => [
            ['VAR-VEL', 20, 150],
            ['VAR-TIEMPO', 2, 60],
            ['VAR-PH', 4, 9],
        ],
        'EX-300' => [
            ['VAR-TEMP', 80, 180],
            ['VAR-PRES', 3, 15],
            ['VAR-VEL', 50, 200],
        ],
        'MD-400' => [
            ['VAR-PRES', 2, 12],
            ['VAR-TIEMPO', 1, 30],
        ],
        'SC-500' => [
            ['VAR-TEMP', 40, 90],
            ['VAR-HUMGR', 5, 25],
            ['VAR-TIEMPO', 30, 480],
        ],
        'EV-700' => [
            ['VAR-TEMP', 10, 40],
            ['VAR-PRES', 5, 10],
            ['VAR-TIEMPO', 1, 20],
        ],
        'ET-800' => [
            ['VAR-TIEMPO', 0.5, 10],
            ['VAR-VEL', 20, 100],
        ],
        'SE-10' => [
            ['VAR-PRES', 5, 10],
            ['VAR-CALVIS', 1, 10],
        ],
        'BD-500' => [
            ['VAR-PESO', 0.1, 50],
        ],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('maquina_variable_planta') || ! Schema::hasTable('variable_estandar')) {
            return;
        }

        $this->call(VariableEstandarCatalogoSeeder::class);

        $varsPorCodigo = VariableEstandar::query()
            ->whereIn('codigo', collect(self::POR_CODIGO)->flatten(1)->pluck(0)->unique()->all())
            ->pluck('variableestandarid', 'codigo');

        $creados = 0;

        foreach (self::POR_CODIGO as $codigoMaquina => $parametros) {
            $maquina = MaquinaPlanta::query()->where('codigo', $codigoMaquina)->first();
            if (! $maquina) {
                continue;
            }

            MaquinaVariablePlanta::query()
                ->where('maquinaplantaid', $maquina->maquinaplantaid)
                ->delete();

            foreach ($parametros as [$codigoVar, $min, $max]) {
                $varId = $varsPorCodigo[$codigoVar] ?? null;
                if (! $varId) {
                    continue;
                }

                MaquinaVariablePlanta::create([
                    'maquinaplantaid' => $maquina->maquinaplantaid,
                    'variableestandarid' => $varId,
                    'valor_minimo' => $min,
                    'valor_maximo' => $max,
                    'valor_objetivo' => round(($min + $max) / 2, 2),
                    'obligatorio' => true,
                ]);
                $creados++;
            }
        }

        $this->command?->info("Parámetros estándar de máquinas: {$creados} rangos en ".count(self::POR_CODIGO).' equipos.');
    }
}
