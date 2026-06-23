<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lote_produccion_ruta_paso')) {
            Schema::create('lote_produccion_ruta_paso', function (Blueprint $table) {
                $table->id('loteproduccionrutapasoid');
                $table->unsignedBigInteger('loteproduccionpedidoid');
                $table->unsignedInteger('orden');
                $table->unsignedBigInteger('procesoplantaid');
                $table->unsignedBigInteger('maquinaplantaid')->nullable();
                $table->string('notas', 255)->nullable();
                $table->unsignedBigInteger('plantillapasoid')->nullable();
                $table->timestamps();

                $table->foreign('loteproduccionpedidoid', 'lprp_lote_fk')
                    ->references('loteproduccionpedidoid')->on('lote_produccion_pedido')
                    ->cascadeOnDelete();
                $table->foreign('procesoplantaid', 'lprp_proc_fk')
                    ->references('procesoplantaid')->on('proceso_planta')
                    ->cascadeOnDelete();
                $table->foreign('maquinaplantaid', 'lprp_maq_fk')
                    ->references('maquinaplantaid')->on('maquina_planta')
                    ->nullOnDelete();
                $table->foreign('plantillapasoid', 'lprp_plt_paso_fk')
                    ->references('plantillapasoid')->on('plantilla_transformacion_paso')
                    ->nullOnDelete();
                $table->unique(['loteproduccionpedidoid', 'orden'], 'lprp_lote_orden_uq');
            });
        }

        if (! Schema::hasTable('lote_produccion_ruta_paso_variable')) {
            Schema::create('lote_produccion_ruta_paso_variable', function (Blueprint $table) {
                $table->id('loteproduccionrutapasovariableid');
                $table->unsignedBigInteger('loteproduccionrutapasoid');
                $table->unsignedBigInteger('variableestandarid');
                $table->decimal('valor_minimo', 10, 2);
                $table->decimal('valor_maximo', 10, 2);
                $table->boolean('obligatorio')->default(true);
                $table->timestamps();

                $table->foreign('loteproduccionrutapasoid', 'lprpv_paso_fk')
                    ->references('loteproduccionrutapasoid')->on('lote_produccion_ruta_paso')
                    ->cascadeOnDelete();
                $table->foreign('variableestandarid', 'lprpv_var_fk')
                    ->references('variableestandarid')->on('variable_estandar')
                    ->cascadeOnDelete();
                $table->unique(['loteproduccionrutapasoid', 'variableestandarid'], 'lprpv_paso_var_uq');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_produccion_ruta_paso_variable');
        Schema::dropIfExists('lote_produccion_ruta_paso');
    }
};
