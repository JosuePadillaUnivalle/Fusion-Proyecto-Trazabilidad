<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('asignacion_etapa_planta')) {
            Schema::create('asignacion_etapa_planta', function (Blueprint $table) {
                $table->id('asignacionetapaplantaid');
                $table->unsignedBigInteger('loteproduccionpedidoid');
                $table->unsignedBigInteger('procesoplantaid');
                $table->unsignedBigInteger('maquinaplantaid');
                $table->unsignedBigInteger('operador_usuarioid');
                $table->unsignedBigInteger('asignado_por_usuarioid');
                $table->string('estado', 20)->default('pendiente');
                $table->text('observaciones')->nullable();
                $table->unsignedBigInteger('registroprocesomaquinaplantaid')->nullable();
                $table->dateTime('creado_en');
                $table->dateTime('completada_en')->nullable();

                $table->index(['operador_usuarioid', 'estado']);
                $table->index(['loteproduccionpedidoid', 'estado']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asignacion_etapa_planta');
    }
};
