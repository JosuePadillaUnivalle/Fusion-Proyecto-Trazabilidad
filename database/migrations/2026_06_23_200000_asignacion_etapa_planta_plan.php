<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('asignacion_etapa_planta')) {
            return;
        }

        Schema::table('asignacion_etapa_planta', function (Blueprint $table) {
            if (! Schema::hasColumn('asignacion_etapa_planta', 'loteproduccionrutapasoid')) {
                $table->unsignedBigInteger('loteproduccionrutapasoid')->nullable()->after('loteproduccionpedidoid');
            }
            if (! Schema::hasColumn('asignacion_etapa_planta', 'orden')) {
                $table->unsignedInteger('orden')->nullable()->after('loteproduccionrutapasoid');
            }
        });

        if (Schema::hasTable('lote_produccion_ruta_paso')) {
            Schema::table('asignacion_etapa_planta', function (Blueprint $table) {
                if (Schema::hasColumn('asignacion_etapa_planta', 'loteproduccionrutapasoid')) {
                    $table->index(['loteproduccionpedidoid', 'orden'], 'asig_etapa_lote_orden_idx');
                }
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('asignacion_etapa_planta')) {
            return;
        }

        Schema::table('asignacion_etapa_planta', function (Blueprint $table) {
            if (Schema::hasColumn('asignacion_etapa_planta', 'orden')) {
                $table->dropIndex('asig_etapa_lote_orden_idx');
                $table->dropColumn('orden');
            }
            if (Schema::hasColumn('asignacion_etapa_planta', 'loteproduccionrutapasoid')) {
                $table->dropColumn('loteproduccionrutapasoid');
            }
        });
    }
};
