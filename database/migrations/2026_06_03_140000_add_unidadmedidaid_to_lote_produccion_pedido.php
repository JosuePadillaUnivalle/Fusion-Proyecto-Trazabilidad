<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lote_produccion_pedido') || Schema::hasColumn('lote_produccion_pedido', 'unidadmedidaid')) {
            return;
        }

        Schema::table('lote_produccion_pedido', function (Blueprint $table) {
            $table->unsignedBigInteger('unidadmedidaid')->nullable()->after('cantidad_objetivo');
        });

        if (Schema::hasTable('unidadmedida')) {
            Schema::table('lote_produccion_pedido', function (Blueprint $table) {
                $table->foreign('unidadmedidaid')
                    ->references('unidadmedidaid')
                    ->on('unidadmedida')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('lote_produccion_pedido') || ! Schema::hasColumn('lote_produccion_pedido', 'unidadmedidaid')) {
            return;
        }

        Schema::table('lote_produccion_pedido', function (Blueprint $table) {
            try {
                $table->dropForeign(['unidadmedidaid']);
            } catch (\Throwable) {
                // SQLite u otros drivers sin FK nombrada
            }
            $table->dropColumn('unidadmedidaid');
        });
    }
};
