<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('detalle_pedido_distribucion')) {
            return;
        }

        Schema::table('detalle_pedido_distribucion', function (Blueprint $table) {
            if (! Schema::hasColumn('detalle_pedido_distribucion', 'almacen_mayorista_origenid')) {
                $table->unsignedBigInteger('almacen_mayorista_origenid')->nullable()->after('pedidodistribucionid');
                $table->foreign('almacen_mayorista_origenid')
                    ->references('almacenid')
                    ->on('almacen')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('detalle_pedido_distribucion', 'inventario_presentacion_loteid')) {
                $table->unsignedBigInteger('inventario_presentacion_loteid')->nullable()->after('insumo_presentacionid');
                $table->foreign('inventario_presentacion_loteid')
                    ->references('inventario_presentacion_loteid')
                    ->on('inventario_presentacion_lote')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('detalle_pedido_distribucion', 'referencia_lote')) {
                $table->string('referencia_lote', 80)->nullable()->after('inventario_presentacion_loteid');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('detalle_pedido_distribucion')) {
            return;
        }

        Schema::table('detalle_pedido_distribucion', function (Blueprint $table) {
            if (Schema::hasColumn('detalle_pedido_distribucion', 'almacen_mayorista_origenid')) {
                $table->dropForeign(['almacen_mayorista_origenid']);
                $table->dropColumn('almacen_mayorista_origenid');
            }
            if (Schema::hasColumn('detalle_pedido_distribucion', 'inventario_presentacion_loteid')) {
                $table->dropForeign(['inventario_presentacion_loteid']);
                $table->dropColumn('inventario_presentacion_loteid');
            }
            if (Schema::hasColumn('detalle_pedido_distribucion', 'referencia_lote')) {
                $table->dropColumn('referencia_lote');
            }
        });
    }
};
