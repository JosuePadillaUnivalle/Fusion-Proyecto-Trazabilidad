<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('incidente_envio')) {
            Schema::create('incidente_envio', function (Blueprint $table) {
                $table->id('incidenteenvioid');
                $table->string('externo_envio_id', 64)->nullable()->index();
                $table->unsignedBigInteger('pedidoid')->nullable();
                $table->unsignedBigInteger('reportadopor_usuarioid')->nullable();
                $table->string('tipo', 100)->default('general');
                $table->text('descripcion');
                $table->string('estado', 50)->default('abierto')->index();
                $table->unsignedBigInteger('resueltopor_usuarioid')->nullable();
                $table->dateTime('fecha_resolucion')->nullable();
                $table->text('nota_resolucion')->nullable();
                $table->timestamps();

                $table->foreign('pedidoid')->references('pedidoid')->on('pedido')->nullOnDelete();
                $table->foreign('reportadopor_usuarioid')->references('usuarioid')->on('usuario')->nullOnDelete();
                $table->foreign('resueltopor_usuarioid')->references('usuarioid')->on('usuario')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('documento_entrega')) {
            Schema::create('documento_entrega', function (Blueprint $table) {
                $table->id('documentoentregaid');
                $table->string('externo_envio_id', 64)->nullable()->index();
                $table->unsignedBigInteger('pedidoid')->nullable();
                $table->unsignedBigInteger('usuarioid')->nullable();
                $table->string('tipo_documento', 50)->default('nota_entrega')->index();
                $table->string('titulo');
                $table->string('archivo_path');
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->foreign('pedidoid')->references('pedidoid')->on('pedido')->nullOnDelete();
                $table->foreign('usuarioid')->references('usuarioid')->on('usuario')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('ruta_multi_entrega')) {
            Schema::create('ruta_multi_entrega', function (Blueprint $table) {
                $table->id('rutamultientregaid');
                $table->string('nombre');
                $table->unsignedBigInteger('creadopor_usuarioid')->nullable();
                $table->unsignedBigInteger('transportista_usuarioid')->nullable();
                $table->string('estado', 50)->default('planificada')->index();
                $table->dateTime('fecha_salida')->nullable();
                $table->dateTime('fecha_cierre')->nullable();
                $table->json('resumen')->nullable();
                $table->timestamps();

                $table->foreign('creadopor_usuarioid')->references('usuarioid')->on('usuario')->nullOnDelete();
                $table->foreign('transportista_usuarioid')->references('usuarioid')->on('usuario')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('ruta_parada')) {
            Schema::create('ruta_parada', function (Blueprint $table) {
                $table->id('rutaparadaid');
                $table->unsignedBigInteger('rutamultientregaid');
                $table->unsignedBigInteger('pedidoid')->nullable();
                $table->string('externo_envio_id', 64)->nullable()->index();
                $table->unsignedInteger('orden');
                $table->string('destino', 255)->nullable();
                $table->string('estado', 50)->default('pendiente');
                $table->dateTime('eta')->nullable();
                $table->dateTime('fecha_entrega')->nullable();
                $table->timestamps();

                $table->foreign('rutamultientregaid')->references('rutamultientregaid')->on('ruta_multi_entrega')->cascadeOnDelete();
                $table->foreign('pedidoid')->references('pedidoid')->on('pedido')->nullOnDelete();
                $table->unique(['rutamultientregaid', 'orden'], 'ruta_parada_orden_unico');
            });
        }

        if (!Schema::hasTable('envio_asignacion_multiple')) {
            Schema::create('envio_asignacion_multiple', function (Blueprint $table) {
                $table->id('envioasignacionmultipleid');
                $table->string('externo_envio_id', 64)->index();
                $table->unsignedBigInteger('pedidoid')->nullable();
                $table->unsignedBigInteger('transportista_usuarioid')->nullable();
                $table->unsignedBigInteger('asignadopor_usuarioid')->nullable();
                $table->unsignedBigInteger('rutamultientregaid')->nullable();
                $table->string('vehiculo_ref', 80)->nullable();
                $table->string('estado', 50)->default('asignado')->index();
                $table->dateTime('fecha_asignacion')->useCurrent();
                $table->timestamps();

                $table->foreign('pedidoid')->references('pedidoid')->on('pedido')->nullOnDelete();
                $table->foreign('transportista_usuarioid')->references('usuarioid')->on('usuario')->nullOnDelete();
                $table->foreign('asignadopor_usuarioid')->references('usuarioid')->on('usuario')->nullOnDelete();
                $table->foreign('rutamultientregaid')->references('rutamultientregaid')->on('ruta_multi_entrega')->nullOnDelete();
                $table->unique(['externo_envio_id', 'transportista_usuarioid'], 'envio_asignacion_unica');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('envio_asignacion_multiple');
        Schema::dropIfExists('ruta_parada');
        Schema::dropIfExists('ruta_multi_entrega');
        Schema::dropIfExists('documento_entrega');
        Schema::dropIfExists('incidente_envio');
    }
};

