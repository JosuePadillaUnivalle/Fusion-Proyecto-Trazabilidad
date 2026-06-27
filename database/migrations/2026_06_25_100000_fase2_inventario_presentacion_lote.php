<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('insumo_presentacion')) {
            Schema::table('insumo_presentacion', function (Blueprint $table) {
                if (! Schema::hasColumn('insumo_presentacion', 'sku')) {
                    $table->string('sku', 60)->nullable()->after('unidades_por_caja');
                }
                if (! Schema::hasColumn('insumo_presentacion', 'codigo_barras')) {
                    $table->string('codigo_barras', 80)->nullable()->after('sku');
                }
            });
        }

        if (! Schema::hasTable('inventario_presentacion_lote')) {
            Schema::create('inventario_presentacion_lote', function (Blueprint $table) {
                $table->id('inventario_presentacion_loteid');
                $table->unsignedBigInteger('almacenid');
                $table->unsignedBigInteger('insumoid');
                $table->unsignedBigInteger('insumo_presentacionid');
                $table->unsignedBigInteger('loteproduccionpedidoid')->nullable();
                $table->string('referencia_lote', 80)->nullable();
                $table->decimal('cantidad_unidades', 12, 2)->default(0);
                $table->decimal('cantidad_kg', 12, 4)->default(0);
                $table->timestamps();

                $table->foreign('almacenid')->references('almacenid')->on('almacen')->cascadeOnDelete();
                $table->foreign('insumoid')->references('insumoid')->on('insumo')->cascadeOnDelete();
                $table->foreign('insumo_presentacionid')->references('insumo_presentacionid')->on('insumo_presentacion')->cascadeOnDelete();
                if (Schema::hasTable('lote_produccion_pedido')) {
                    $table->foreign('loteproduccionpedidoid')
                        ->references('loteproduccionpedidoid')
                        ->on('lote_produccion_pedido')
                        ->nullOnDelete();
                }
                $table->index(['almacenid', 'insumo_presentacionid'], 'inv_pres_lote_alm_pres_idx');
            });
        }

        if (Schema::hasTable('detalle_traslado_planta_mayorista')) {
            Schema::table('detalle_traslado_planta_mayorista', function (Blueprint $table) {
                if (! Schema::hasColumn('detalle_traslado_planta_mayorista', 'inventario_presentacion_loteid')) {
                    $table->unsignedBigInteger('inventario_presentacion_loteid')->nullable()->after('insumo_presentacionid');
                    $table->foreign('inventario_presentacion_loteid', 'det_trasl_inv_pres_lote_fk')
                        ->references('inventario_presentacion_loteid')
                        ->on('inventario_presentacion_lote')
                        ->nullOnDelete();
                }
                if (! Schema::hasColumn('detalle_traslado_planta_mayorista', 'loteproduccionpedidoid')) {
                    $table->unsignedBigInteger('loteproduccionpedidoid')->nullable()->after('inventario_presentacion_loteid');
                    if (Schema::hasTable('lote_produccion_pedido')) {
                        $table->foreign('loteproduccionpedidoid', 'det_trasl_lote_prod_fk')
                            ->references('loteproduccionpedidoid')
                            ->on('lote_produccion_pedido')
                            ->nullOnDelete();
                    }
                }
            });
        }

        if (Schema::hasTable('almacen_movimiento')) {
            Schema::table('almacen_movimiento', function (Blueprint $table) {
                if (! Schema::hasColumn('almacen_movimiento', 'insumo_presentacionid')) {
                    $table->unsignedBigInteger('insumo_presentacionid')->nullable()->after('insumoid');
                }
                if (! Schema::hasColumn('almacen_movimiento', 'loteproduccionpedidoid')) {
                    $table->unsignedBigInteger('loteproduccionpedidoid')->nullable()->after('insumo_presentacionid');
                }
                if (! Schema::hasColumn('almacen_movimiento', 'cantidad_unidades')) {
                    $table->decimal('cantidad_unidades', 12, 2)->nullable()->after('cantidad');
                }
            });
        }

        if (Schema::hasTable('detalle_pedido_distribucion')
            && Schema::hasColumn('detalle_pedido_distribucion', 'inventario_presentacion_loteid')) {
            try {
                Schema::table('detalle_pedido_distribucion', function (Blueprint $table) {
                    $table->foreign('inventario_presentacion_loteid', 'det_ped_dist_inv_pres_lote_fk')
                        ->references('inventario_presentacion_loteid')
                        ->on('inventario_presentacion_lote')
                        ->nullOnDelete();
                });
            } catch (\Throwable) {
                // FK ya aplicada por migración anterior.
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('almacen_movimiento')) {
            Schema::table('almacen_movimiento', function (Blueprint $table) {
                foreach (['cantidad_unidades', 'loteproduccionpedidoid', 'insumo_presentacionid'] as $col) {
                    if (Schema::hasColumn('almacen_movimiento', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }

        if (Schema::hasTable('detalle_traslado_planta_mayorista')) {
            Schema::table('detalle_traslado_planta_mayorista', function (Blueprint $table) {
                if (Schema::hasColumn('detalle_traslado_planta_mayorista', 'inventario_presentacion_loteid')) {
                    $table->dropForeign('det_trasl_inv_pres_lote_fk');
                    $table->dropColumn('inventario_presentacion_loteid');
                }
                if (Schema::hasColumn('detalle_traslado_planta_mayorista', 'loteproduccionpedidoid')) {
                    $table->dropForeign('det_trasl_lote_prod_fk');
                    $table->dropColumn('loteproduccionpedidoid');
                }
            });
        }

        Schema::dropIfExists('inventario_presentacion_lote');

        if (Schema::hasTable('insumo_presentacion')) {
            Schema::table('insumo_presentacion', function (Blueprint $table) {
                foreach (['codigo_barras', 'sku'] as $col) {
                    if (Schema::hasColumn('insumo_presentacion', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
