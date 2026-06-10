<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('plantilla_transformacion')) {
            Schema::create('plantilla_transformacion', function (Blueprint $table) {
                $table->id('plantillatransformacionid');
                $table->string('nombre', 120);
                $table->text('descripcion')->nullable();
                $table->string('producto_ejemplo', 100)->nullable();
                $table->text('palabras_clave')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('plantilla_transformacion_paso')) {
            Schema::create('plantilla_transformacion_paso', function (Blueprint $table) {
                $table->id('plantillapasoid');
                $table->unsignedBigInteger('plantillatransformacionid');
                $table->unsignedInteger('orden');
                $table->unsignedBigInteger('procesoplantaid');
                $table->unsignedBigInteger('maquinaplantaid')->nullable();
                $table->string('notas', 255)->nullable();
                $table->timestamps();

                $table->foreign('plantillatransformacionid', 'plt_paso_plantilla_fk')
                    ->references('plantillatransformacionid')->on('plantilla_transformacion')
                    ->cascadeOnDelete();
                $table->foreign('procesoplantaid', 'plt_paso_proceso_fk')
                    ->references('procesoplantaid')->on('proceso_planta');
                $table->foreign('maquinaplantaid', 'plt_paso_maquina_fk')
                    ->references('maquinaplantaid')->on('maquina_planta');
                $table->unique(['plantillatransformacionid', 'orden'], 'plt_paso_orden_uq');
            });
        }

        if (Schema::hasTable('lote_produccion_pedido')
            && ! Schema::hasColumn('lote_produccion_pedido', 'plantillatransformacionid')) {
            Schema::table('lote_produccion_pedido', function (Blueprint $table) {
                $table->unsignedBigInteger('plantillatransformacionid')->nullable()->after('procesoplantaid');
                $table->foreign('plantillatransformacionid', 'lpp_plantilla_fk')
                    ->references('plantillatransformacionid')->on('plantilla_transformacion')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lote_produccion_pedido')
            && Schema::hasColumn('lote_produccion_pedido', 'plantillatransformacionid')) {
            Schema::table('lote_produccion_pedido', function (Blueprint $table) {
                $table->dropForeign('lpp_plantilla_fk');
                $table->dropColumn('plantillatransformacionid');
            });
        }

        Schema::dropIfExists('plantilla_transformacion_paso');
        Schema::dropIfExists('plantilla_transformacion');
    }
};
