<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lote_produccion_paso_variable')) {
            Schema::create('lote_produccion_paso_variable', function (Blueprint $table) {
                $table->id('loteproduccionpasovariableid');
                $table->unsignedBigInteger('loteproduccionpedidoid');
                $table->unsignedBigInteger('plantillapasoid');
                $table->unsignedBigInteger('variableestandarid');
                $table->decimal('valor_minimo', 10, 2);
                $table->decimal('valor_maximo', 10, 2);
                $table->boolean('obligatorio')->default(true);
                $table->timestamps();

                $table->foreign('loteproduccionpedidoid', 'lppv_lote_fk')
                    ->references('loteproduccionpedidoid')->on('lote_produccion_pedido')
                    ->cascadeOnDelete();
                $table->foreign('plantillapasoid', 'lppv_paso_fk')
                    ->references('plantillapasoid')->on('plantilla_transformacion_paso')
                    ->cascadeOnDelete();
                $table->foreign('variableestandarid', 'lppv_var_fk')
                    ->references('variableestandarid')->on('variable_estandar')
                    ->cascadeOnDelete();
                $table->unique(
                    ['loteproduccionpedidoid', 'plantillapasoid', 'variableestandarid'],
                    'lppv_lote_paso_var_uq'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lote_produccion_paso_variable');
    }
};
