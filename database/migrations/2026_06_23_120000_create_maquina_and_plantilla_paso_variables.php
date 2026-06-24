<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('maquina_variable_planta')) {
            Schema::create('maquina_variable_planta', function (Blueprint $table) {
                $table->id('maquinavariableid');
                $table->unsignedBigInteger('maquinaplantaid');
                $table->unsignedBigInteger('variableestandarid');
                $table->decimal('valor_minimo', 10, 2);
                $table->decimal('valor_maximo', 10, 2);
                $table->decimal('valor_objetivo', 10, 2)->nullable();
                $table->boolean('obligatorio')->default(true);
                $table->timestamps();

                $table->foreign('maquinaplantaid')->references('maquinaplantaid')->on('maquina_planta')->cascadeOnDelete();
                $table->foreign('variableestandarid')->references('variableestandarid')->on('variable_estandar')->cascadeOnDelete();
                $table->unique(['maquinaplantaid', 'variableestandarid'], 'maq_var_std_unique');
            });
        }

        if (! Schema::hasTable('plantilla_transformacion_paso_variable')) {
            Schema::create('plantilla_transformacion_paso_variable', function (Blueprint $table) {
                $table->id('plantillapasovariableid');
                $table->unsignedBigInteger('plantillapasoid');
                $table->unsignedBigInteger('variableestandarid');
                $table->decimal('valor_minimo', 10, 2);
                $table->decimal('valor_maximo', 10, 2);
                $table->decimal('valor_objetivo', 10, 2)->nullable();
                $table->boolean('obligatorio')->default(true);
                $table->timestamps();

                $table->foreign('plantillapasoid')->references('plantillapasoid')->on('plantilla_transformacion_paso')->cascadeOnDelete();
                $table->foreign('variableestandarid')->references('variableestandarid')->on('variable_estandar')->cascadeOnDelete();
                $table->unique(['plantillapasoid', 'variableestandarid'], 'plant_paso_var_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('plantilla_transformacion_paso_variable');
        Schema::dropIfExists('maquina_variable_planta');
    }
};
