<?php

use App\Support\EstadoLoteCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $estadoCosechadoId = EstadoLoteCatalogo::idPorSlug('cosechado');

        if ($estadoCosechadoId && Schema::hasTable('lote') && Schema::hasTable('produccion')) {
            $loteIdsConCosecha = DB::table('produccion')
                ->whereNotNull('loteid')
                ->distinct()
                ->pluck('loteid');

            if ($loteIdsConCosecha->isNotEmpty()) {
                DB::table('lote')
                    ->whereIn('loteid', $loteIdsConCosecha)
                    ->where('estadolotetipoid', '!=', $estadoCosechadoId)
                    ->update([
                        'estadolotetipoid' => $estadoCosechadoId,
                        'fechamodificacion' => now(),
                    ]);
            }
        }

        if (! Schema::hasTable('insumo')) {
            return;
        }

        $insumoIds = DB::table('insumo')
            ->whereRaw('LOWER(TRIM(nombre)) IN (?, ?)', ['test', 'test 2'])
            ->pluck('insumoid');

        if ($insumoIds->isEmpty()) {
            return;
        }

        if (Schema::hasTable('almacen_movimiento')) {
            DB::table('almacen_movimiento')->whereIn('insumoid', $insumoIds)->delete();
        }

        if (Schema::hasTable('loteinsumo')) {
            DB::table('loteinsumo')->whereIn('insumoid', $insumoIds)->delete();
        }

        if (Schema::hasTable('detallepedido')) {
            DB::table('detallepedido')->whereIn('insumoid', $insumoIds)->delete();
        }

        if (Schema::hasTable('lote_produccion_materia_prima')) {
            DB::table('lote_produccion_materia_prima')->whereIn('insumoid', $insumoIds)->delete();
        }

        DB::table('insumo')->whereIn('insumoid', $insumoIds)->delete();
    }

    public function down(): void
    {
        // No reversible: corrección de estados y limpieza de datos de prueba.
    }
};
