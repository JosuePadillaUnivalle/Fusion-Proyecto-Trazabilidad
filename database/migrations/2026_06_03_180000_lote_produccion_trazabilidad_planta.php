<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lote_produccion_pedido') && ! Schema::hasColumn('lote_produccion_pedido', 'procesoplantaid')) {
            Schema::table('lote_produccion_pedido', function (Blueprint $table) {
                $table->unsignedBigInteger('procesoplantaid')->nullable()->after('pedidoid');
            });

            if (Schema::hasTable('proceso_planta')) {
                try {
                    Schema::table('lote_produccion_pedido', function (Blueprint $table) {
                        $table->foreign('procesoplantaid')
                            ->references('procesoplantaid')
                            ->on('proceso_planta')
                            ->nullOnDelete();
                    });
                } catch (\Throwable) {
                }
            }
        }

        if (Schema::hasTable('registro_proceso_maquina_planta')) {
            if (! Schema::hasColumn('registro_proceso_maquina_planta', 'loteproduccionpedidoid')) {
                Schema::table('registro_proceso_maquina_planta', function (Blueprint $table) {
                    $table->unsignedBigInteger('loteproduccionpedidoid')->nullable()->after('procesomaquinaplantaid');
                });
            }

            if (Schema::hasColumn('registro_proceso_maquina_planta', 'loteid')) {
                try {
                    Schema::table('registro_proceso_maquina_planta', function (Blueprint $table) {
                        $table->unsignedBigInteger('loteid')->nullable()->change();
                    });
                } catch (\Throwable) {
                }
            }

            if (Schema::hasTable('lote_produccion_pedido')) {
                try {
                    Schema::table('registro_proceso_maquina_planta', function (Blueprint $table) {
                        $table->foreign('loteproduccionpedidoid')
                            ->references('loteproduccionpedidoid')
                            ->on('lote_produccion_pedido')
                            ->cascadeOnDelete();
                    });
                } catch (\Throwable) {
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('registro_proceso_maquina_planta') && Schema::hasColumn('registro_proceso_maquina_planta', 'loteproduccionpedidoid')) {
            try {
                Schema::table('registro_proceso_maquina_planta', function (Blueprint $table) {
                    $table->dropForeign(['loteproduccionpedidoid']);
                });
            } catch (\Throwable) {
            }
            Schema::table('registro_proceso_maquina_planta', function (Blueprint $table) {
                $table->dropColumn('loteproduccionpedidoid');
            });
        }

        if (Schema::hasTable('lote_produccion_pedido') && Schema::hasColumn('lote_produccion_pedido', 'procesoplantaid')) {
            try {
                Schema::table('lote_produccion_pedido', function (Blueprint $table) {
                    $table->dropForeign(['procesoplantaid']);
                });
            } catch (\Throwable) {
            }
            Schema::table('lote_produccion_pedido', function (Blueprint $table) {
                $table->dropColumn('procesoplantaid');
            });
        }
    }
};
