<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pedido_distribucion')) {
            return;
        }

        $ids = DB::table('pedido_distribucion')
            ->whereRaw("UPPER(COALESCE(numero_solicitud, '')) LIKE ?", ['%DEMO%'])
            ->orWhereRaw("UPPER(COALESCE(observaciones, '')) LIKE ?", ['%[DEMO%'])
            ->pluck('pedidodistribucionid');

        if ($ids->isEmpty()) {
            return;
        }

        if (Schema::hasTable('detalle_pedido_distribucion')) {
            DB::table('detalle_pedido_distribucion')
                ->whereIn('pedidodistribucionid', $ids)
                ->delete();
        }

        if (Schema::hasTable('ruta_distribucion_parada')) {
            DB::table('ruta_distribucion_parada')
                ->whereIn('pedidodistribucionid', $ids)
                ->delete();
        }

        DB::table('pedido_distribucion')
            ->whereIn('pedidodistribucionid', $ids)
            ->delete();
    }

    public function down(): void
    {
        // Los pedidos demo se regeneran con seeders si hiciera falta.
    }
};
