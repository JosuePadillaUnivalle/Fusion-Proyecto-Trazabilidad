<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lote_produccion_pedido') || Schema::hasColumn('lote_produccion_pedido', 'producto')) {
            return;
        }

        Schema::table('lote_produccion_pedido', function (Blueprint $table) {
            $table->string('producto', 100)->nullable()->after('nombre');
        });

        DB::table('lote_produccion_pedido')->orderBy('loteproduccionpedidoid')->get()->each(function ($row) {
            $producto = $row->nombre;
            if (is_string($row->nombre) && preg_match('/^(.+?) - Lote \d+\s*$/i', $row->nombre, $m)) {
                $producto = trim($m[1]);
            }

            DB::table('lote_produccion_pedido')
                ->where('loteproduccionpedidoid', $row->loteproduccionpedidoid)
                ->update(['producto' => $producto]);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('lote_produccion_pedido') || ! Schema::hasColumn('lote_produccion_pedido', 'producto')) {
            return;
        }

        Schema::table('lote_produccion_pedido', function (Blueprint $table) {
            $table->dropColumn('producto');
        });
    }
};
