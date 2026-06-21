<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Lote
        if (!Schema::hasTable('lote')) {
            Schema::create('lote', function (Blueprint $table) {
                $table->id('loteid');
                $table->unsignedBigInteger('usuarioid');
                $table->string('nombre');
                $table->string('ubicacion')->nullable();
                $table->float('superficie');
                $table->unsignedBigInteger('unidadsuperficieid'); // FK unidadmedida
                $table->unsignedBigInteger('cultivoid');
                $table->date('fechasiembra')->nullable();
                $table->unsignedBigInteger('estadolotetipoid');
                $table->float('latitud')->nullable();
                $table->float('longitud')->nullable();
                $table->dateTime('fechacreacion')->useCurrent();
                $table->dateTime('fechamodificacion')->useCurrent();
                $table->text('imagenurl')->nullable();

                $table->foreign('usuarioid')->references('usuarioid')->on('usuario');
                $table->foreign('unidadsuperficieid')->references('unidadmedidaid')->on('unidadmedida');
                $table->foreign('cultivoid')->references('cultivoid')->on('cultivo');
                $table->foreign('estadolotetipoid')->references('estadolotetipoid')->on('estadolote_tipo');
            });
        }

        // Insumo
        if (!Schema::hasTable('insumo')) {
            Schema::create('insumo', function (Blueprint $table) {
                $table->id('insumoid');
                $table->string('nombre');
                $table->unsignedBigInteger('tipoinsumoid');
                $table->unsignedBigInteger('unidadmedidaid');
                $table->float('stock');
                $table->float('stockminimo')->default(0);
                $table->string('proveedor')->nullable();
                $table->float('preciounitario')->nullable();
                $table->text('descripcion')->nullable();

                $table->foreign('tipoinsumoid')->references('tipoinsumoid')->on('tipoinsumo');
                $table->foreign('unidadmedidaid')->references('unidadmedidaid')->on('unidadmedida');
            });
        }

        // LoteInsumo
        if (!Schema::hasTable('loteinsumo')) {
            Schema::create('loteinsumo', function (Blueprint $table) {
                $table->id('loteinsumoid');
                $table->unsignedBigInteger('loteid');
                $table->unsignedBigInteger('insumoid');
                $table->unsignedBigInteger('usuarioid');

                $table->float('cantidadusada');
                $table->dateTime('fechauo')->useCurrent();
                $table->float('costototal')->nullable();
                $table->unsignedBigInteger('estadoloteinsumoid');
                $table->text('observaciones')->nullable();

                $table->foreign('loteid')->references('loteid')->on('lote');
                $table->foreign('insumoid')->references('insumoid')->on('insumo');
                $table->foreign('usuarioid')->references('usuarioid')->on('usuario');
                $table->foreign('estadoloteinsumoid')->references('estadoloteinsumoid')->on('estadoloteinsumo');
            });
        }

        // Produccion
        if (!Schema::hasTable('produccion')) {
            Schema::create('produccion', function (Blueprint $table) {
                $table->id('produccionid');
                $table->unsignedBigInteger('loteid');
                $table->float('cantidad');
                $table->unsignedBigInteger('unidadmedidaid');
                $table->float('cantidad_base')->nullable();
                $table->date('fechacosecha');
                $table->unsignedBigInteger('destinoproduccionid');
                $table->text('imagenurl')->nullable();
                $table->text('observaciones')->nullable();

                $table->foreign('loteid')->references('loteid')->on('lote');
                $table->foreign('unidadmedidaid')->references('unidadmedidaid')->on('unidadmedida');
                $table->foreign('destinoproduccionid')->references('destinoproduccionid')->on('destinoproduccion');
            });
        }

        // Clima
        if (!Schema::hasTable('clima')) {
            Schema::create('clima', function (Blueprint $table) {
                $table->id('climaid');
                $table->unsignedBigInteger('loteid');
                $table->dateTime('fecha');
                $table->float('temperatura')->nullable();
                $table->float('humedad')->nullable();
                $table->float('lluvia')->nullable();
                $table->float('viento')->nullable();
                $table->integer('presion')->nullable();
                $table->string('descripcion')->nullable();
                $table->string('icono')->nullable();
                $table->text('observaciones')->nullable();

                $table->foreign('loteid')->references('loteid')->on('lote')->onDelete('cascade');
            });
        }

        // Venta
        if (!Schema::hasTable('venta')) {
            Schema::create('venta', function (Blueprint $table) {
                $table->id('ventaid');
                $table->unsignedBigInteger('produccionid');

                $table->date('fechaventa');
                $table->string('cliente');
                $table->float('cantidad');
                $table->unsignedBigInteger('unidadmedidaid');
                $table->float('preciounitario');
                $table->float('total');
                $table->text('observaciones')->nullable();

                $table->foreign('produccionid')->references('produccionid')->on('produccion');
                $table->foreign('unidadmedidaid')->references('unidadmedidaid')->on('unidadmedida');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('venta');
        Schema::dropIfExists('clima');
        Schema::dropIfExists('produccion');
        Schema::dropIfExists('loteinsumo');
        Schema::dropIfExists('insumo');
        Schema::dropIfExists('lote');
    }
};
