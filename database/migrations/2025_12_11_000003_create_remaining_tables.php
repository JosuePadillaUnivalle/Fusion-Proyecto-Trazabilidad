<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Almacen
        if (!Schema::hasTable('almacen')) {
            Schema::create('almacen', function (Blueprint $table) {
                $table->id('almacenid');
                $table->string('nombre');
                $table->text('descripcion')->nullable();
                $table->string('ubicacion')->nullable();
                $table->float('capacidad');
                $table->unsignedBigInteger('unidadmedidaid');
                $table->unsignedBigInteger('tipoalmacenid');
                $table->boolean('activo')->default(true);

                $table->foreign('unidadmedidaid')->references('unidadmedidaid')->on('unidadmedida');
                $table->foreign('tipoalmacenid')->references('tipoalmacenid')->on('tipoalmacen');
            });
        }

        // ProduccionAlmacenamiento
        if (!Schema::hasTable('produccionalmacenamiento')) {
            Schema::create('produccionalmacenamiento', function (Blueprint $table) {
                $table->id('produccionalmacenamientoid');
                $table->unsignedBigInteger('produccionid');
                $table->unsignedBigInteger('almacenid');
                $table->float('cantidad');
                $table->unsignedBigInteger('unidadmedidaid');
                $table->float('temperatura')->nullable();
                $table->float('humedad')->nullable();
                $table->float('temperatura_min')->nullable();
                $table->float('temperatura_max')->nullable();
                $table->float('humedad_min')->nullable();
                $table->float('humedad_max')->nullable();
                $table->dateTime('fechaentrada')->useCurrent();
                $table->dateTime('fechasalida')->nullable();
                $table->text('observaciones')->nullable();

                $table->foreign('produccionid')->references('produccionid')->on('produccion');
                $table->foreign('almacenid')->references('almacenid')->on('almacen');
                $table->foreign('unidadmedidaid')->references('unidadmedidaid')->on('unidadmedida');
            });
        }

        // Historial Estados Lote
        if (!Schema::hasTable('historial_estados_lote')) {
            Schema::create('historial_estados_lote', function (Blueprint $table) {
                $table->id('historial_estado_id');
                $table->unsignedBigInteger('loteid');
                $table->unsignedBigInteger('estadolotetipoid');
                $table->dateTime('fecha_cambio')->useCurrent();
                $table->text('observaciones')->nullable();
                $table->text('imagenurl')->nullable();
                $table->unsignedBigInteger('usuarioid');
                $table->timestamps(); // created_at, updated_at

                $table->foreign('loteid')->references('loteid')->on('lote');
                $table->foreign('estadolotetipoid')->references('estadolotetipoid')->on('estadolote_tipo');
                $table->foreign('usuarioid')->references('usuarioid')->on('usuario');
            });
        }

        // Actividad
        if (!Schema::hasTable('actividad')) {
            Schema::create('actividad', function (Blueprint $table) {
                $table->id('actividadid');
                $table->unsignedBigInteger('loteid');
                $table->unsignedBigInteger('usuarioid');
                $table->text('descripcion');
                $table->dateTime('fechainicio');
                $table->dateTime('fechafin')->nullable();
                $table->unsignedBigInteger('tipoactividadid');
                $table->unsignedBigInteger('prioridadid');
                $table->text('observaciones')->nullable();

                $table->foreign('loteid')->references('loteid')->on('lote');
                $table->foreign('usuarioid')->references('usuarioid')->on('usuario');
                $table->foreign('tipoactividadid')->references('tipoactividadid')->on('tipoactividad');
                $table->foreign('prioridadid')->references('prioridadid')->on('prioridad');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('actividad');
        Schema::dropIfExists('historial_estados_lote');
        Schema::dropIfExists('produccionalmacenamiento');
        Schema::dropIfExists('almacen');
    }
};
