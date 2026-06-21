<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('almacenaje_lote_produccion')) {
            return;
        }

        if (! Schema::hasColumn('almacenaje_lote_produccion', 'almacenid')) {
            Schema::table('almacenaje_lote_produccion', function (Blueprint $table) {
                $table->unsignedBigInteger('almacenid')->nullable()->after('loteproduccionpedidoid');
            });

            if (Schema::hasTable('almacen')) {
                try {
                    Schema::table('almacenaje_lote_produccion', function (Blueprint $table) {
                        $table->foreign('almacenid')
                            ->references('almacenid')
                            ->on('almacen')
                            ->nullOnDelete();
                    });
                } catch (\Throwable) {
                }
            }
        }

        if (! Schema::hasColumn('almacenaje_lote_produccion', 'almacenid')) {
            return;
        }

        $filas = DB::table('almacenaje_lote_produccion')
            ->whereNull('almacenid')
            ->get(['almacenajeloteid', 'ubicacion']);

        foreach ($filas as $fila) {
            $almacenid = DB::table('almacen')
                ->where('nombre', $fila->ubicacion)
                ->value('almacenid');

            if ($almacenid) {
                DB::table('almacenaje_lote_produccion')
                    ->where('almacenajeloteid', $fila->almacenajeloteid)
                    ->update(['almacenid' => $almacenid]);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('almacenaje_lote_produccion') || ! Schema::hasColumn('almacenaje_lote_produccion', 'almacenid')) {
            return;
        }

        try {
            Schema::table('almacenaje_lote_produccion', function (Blueprint $table) {
                $table->dropForeign(['almacenid']);
            });
        } catch (\Throwable) {
        }

        Schema::table('almacenaje_lote_produccion', function (Blueprint $table) {
            $table->dropColumn('almacenid');
        });
    }
};
