<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('punto_venta')) {
            Schema::create('punto_venta', function (Blueprint $table) {
                $table->id('puntoventaid');
                $table->unsignedBigInteger('usuarioid');
                $table->unsignedBigInteger('almacenid')->nullable();
                $table->string('nombre', 150);
                $table->text('direccion')->nullable();
                $table->decimal('latitud', 10, 7)->nullable();
                $table->decimal('longitud', 10, 7)->nullable();
                $table->boolean('activo')->default(true);
                $table->text('observaciones')->nullable();
                $table->timestamp('fechacreacion')->useCurrent();

                $table->foreign('usuarioid')->references('usuarioid')->on('usuario');
                $table->foreign('almacenid')->references('almacenid')->on('almacen')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('pedido_distribucion')) {
            Schema::create('pedido_distribucion', function (Blueprint $table) {
                $table->id('pedidodistribucionid');
                $table->string('numero_solicitud', 64)->unique();
                $table->unsignedBigInteger('puntoventaid');
                $table->unsignedBigInteger('almacen_planta_origenid')->nullable();
                $table->string('estado', 50)->default('pendiente');
                $table->timestamp('fechapedido')->useCurrent();
                $table->date('fecha_entrega_deseada')->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamp('fecha_aceptacion')->nullable();
                $table->unsignedBigInteger('aceptado_por_usuarioid')->nullable();
                $table->timestamp('fecha_envio')->nullable();
                $table->timestamp('fecha_recepcion')->nullable();
                $table->unsignedBigInteger('creado_por_usuarioid')->nullable();

                $table->foreign('puntoventaid')->references('puntoventaid')->on('punto_venta');
                $table->foreign('almacen_planta_origenid')->references('almacenid')->on('almacen')->nullOnDelete();
                $table->foreign('aceptado_por_usuarioid')->references('usuarioid')->on('usuario')->nullOnDelete();
                $table->foreign('creado_por_usuarioid')->references('usuarioid')->on('usuario')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('detalle_pedido_distribucion')) {
            Schema::create('detalle_pedido_distribucion', function (Blueprint $table) {
                $table->id('detallepedidodistribucionid');
                $table->unsignedBigInteger('pedidodistribucionid');
                $table->unsignedBigInteger('insumoid')->nullable();
                $table->string('producto_nombre', 200);
                $table->decimal('cantidad', 12, 2);
                $table->text('observaciones')->nullable();

                $table->foreign('pedidodistribucionid')
                    ->references('pedidodistribucionid')
                    ->on('pedido_distribucion')
                    ->cascadeOnDelete();
                $table->foreign('insumoid')->references('insumoid')->on('insumo')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_pedido_distribucion');
        Schema::dropIfExists('pedido_distribucion');
        Schema::dropIfExists('punto_venta');
    }
};
