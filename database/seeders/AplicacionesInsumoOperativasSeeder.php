<?php

namespace Database\Seeders;

use App\Models\EstadoLoteInsumo;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\LoteInsumo;
use App\Services\OperacionAgricolaAutomaticaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Aplicaciones de insumo operativas (no demo) sobre lotes reales del equipo agrícola.
 * Ejecutar: php artisan db:seed --class=AplicacionesInsumoOperativasSeeder
 */
class AplicacionesInsumoOperativasSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('loteinsumo')) {
            return;
        }

        if (LoteInsumo::query()->exists()) {
            $this->command?->info('AplicacionesInsumoOperativasSeeder: ya hay registros; no se insertó nada.');

            return;
        }

        $estadoAplicado = EstadoLoteInsumo::query()
            ->whereRaw('LOWER(TRIM(nombre)) = ?', ['aplicado'])
            ->first();

        if (! $estadoAplicado) {
            $this->command?->warn('AplicacionesInsumoOperativasSeeder: falta estado Aplicado.');

            return;
        }

        $aplicaciones = [
            [
                'lote' => 'Lote Jannik',
                'insumo' => 'Fungicida cobre hidróxido',
                'cantidad' => 10.0,
                'fecha' => '2026-05-18',
                'observaciones' => 'Aplicación preventiva foliar al inicio del ciclo.',
            ],
            [
                'lote' => 'Lote Cebolla Blanca',
                'insumo' => 'Fungicida cobre hidróxido',
                'cantidad' => 14.0,
                'fecha' => '2026-05-20',
                'observaciones' => 'Tratamiento fungicida en etapa de bulbificación.',
            ],
            [
                'lote' => 'Lote de Zanahoria',
                'insumo' => 'Fungicida cobre hidróxido',
                'cantidad' => 8.5,
                'fecha' => '2026-05-22',
                'observaciones' => 'Aspersión en hojas tras riego intenso.',
            ],
            [
                'lote' => 'Prueba real',
                'insumo' => 'Fungicida cobre hidróxido',
                'cantidad' => 6.0,
                'fecha' => '2026-05-25',
                'observaciones' => 'Refuerzo nutricional y control preventivo de hongos.',
            ],
            [
                'lote' => 'Lote Zanahoria Imperator',
                'insumo' => 'Fungicida cobre hidróxido',
                'cantidad' => 11.0,
                'fecha' => '2026-05-27',
                'observaciones' => 'Aplicación asignada a campo por Luis Guerrero.',
            ],
            [
                'lote' => 'Lote Pimentón La Guardia',
                'insumo' => 'Aceite vegetal refinado',
                'cantidad' => 4.5,
                'fecha' => '2026-05-30',
                'observaciones' => 'Aceite de barrido para control de plagas en frutos.',
            ],
        ];

        $servicio = app(OperacionAgricolaAutomaticaService::class);
        $creadas = 0;

        DB::beginTransaction();

        try {
            foreach ($aplicaciones as $fila) {
                $lote = Lote::query()->where('nombre', $fila['lote'])->first();
                $insumo = Insumo::query()->where('nombre', $fila['insumo'])->first();

                if (! $lote || ! $insumo) {
                    $this->command?->warn("Omitido: lote «{$fila['lote']}» o insumo «{$fila['insumo']}» no encontrado.");

                    continue;
                }

                if ($insumo->stock < $fila['cantidad']) {
                    $this->command?->warn("Stock insuficiente para {$insumo->nombre} ({$insumo->stock}).");

                    continue;
                }

                $insumo->stock -= $fila['cantidad'];
                $insumo->save();

                $registro = LoteInsumo::create([
                    'loteid' => $lote->loteid,
                    'insumoid' => $insumo->insumoid,
                    'usuarioid' => $lote->usuarioid,
                    'cantidadusada' => $fila['cantidad'],
                    'fechauo' => Carbon::parse($fila['fecha'].' 09:00:00'),
                    'costototal' => round((float) $insumo->preciounitario * $fila['cantidad'], 2),
                    'estadoloteinsumoid' => $estadoAplicado->estadoloteinsumoid,
                    'observaciones' => $fila['observaciones'],
                ]);

                $servicio->desdeLoteInsumo($registro);
                $creadas++;
            }

            DB::commit();
            $this->command?->info("AplicacionesInsumoOperativasSeeder: {$creadas} aplicaciones reales creadas.");
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
