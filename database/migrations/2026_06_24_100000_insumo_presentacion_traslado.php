<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('insumo')) {
            return;
        }

        if (! Schema::hasTable('insumo_presentacion')) {
            Schema::create('insumo_presentacion', function (Blueprint $table) {
                $table->id('insumo_presentacionid');
                $table->unsignedBigInteger('insumoid');
                $table->string('nombre', 120);
                $table->string('tipo_envase', 30)->default('bolsa');
                $table->decimal('peso_neto_kg', 12, 4);
                $table->unsignedSmallInteger('unidades_por_caja')->nullable();
                $table->unsignedSmallInteger('orden')->default(0);
                $table->boolean('activo')->default(true);

                $table->foreign('insumoid')->references('insumoid')->on('insumo')->cascadeOnDelete();
                $table->index(['insumoid', 'activo']);
            });
        }

        if (Schema::hasTable('detalle_traslado_planta_mayorista')) {
            Schema::table('detalle_traslado_planta_mayorista', function (Blueprint $table) {
                if (! Schema::hasColumn('detalle_traslado_planta_mayorista', 'insumo_presentacionid')) {
                    $table->unsignedBigInteger('insumo_presentacionid')->nullable()->after('insumoid');
                    $table->foreign('insumo_presentacionid')
                        ->references('insumo_presentacionid')
                        ->on('insumo_presentacion')
                        ->nullOnDelete();
                }
                if (! Schema::hasColumn('detalle_traslado_planta_mayorista', 'presentacion_nombre')) {
                    $table->string('presentacion_nombre', 120)->nullable()->after('insumo_presentacionid');
                }
                if (! Schema::hasColumn('detalle_traslado_planta_mayorista', 'cantidad_unidades')) {
                    $table->decimal('cantidad_unidades', 12, 2)->nullable()->after('cantidad');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('detalle_traslado_planta_mayorista')) {
            Schema::table('detalle_traslado_planta_mayorista', function (Blueprint $table) {
                if (Schema::hasColumn('detalle_traslado_planta_mayorista', 'insumo_presentacionid')) {
                    $table->dropForeign(['insumo_presentacionid']);
                    $table->dropColumn('insumo_presentacionid');
                }
                if (Schema::hasColumn('detalle_traslado_planta_mayorista', 'presentacion_nombre')) {
                    $table->dropColumn('presentacion_nombre');
                }
                if (Schema::hasColumn('detalle_traslado_planta_mayorista', 'cantidad_unidades')) {
                    $table->dropColumn('cantidad_unidades');
                }
            });
        }

        Schema::dropIfExists('insumo_presentacion');
    }
};
