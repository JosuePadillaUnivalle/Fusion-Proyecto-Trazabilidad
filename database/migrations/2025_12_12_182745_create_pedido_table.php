<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ===================== TABLA: pedido =====================
        Schema::create('pedido', function (Blueprint $table) {
            $table->id('pedidoid');

            // Código / número de solicitud (código de pedido)
            $table->string('numero_solicitud')->unique();

            // Se mantiene
            $table->string('nombre_planta');

            // Ubicación de entrega (mapa)
            $table->decimal('latitud', 10, 7);
            $table->decimal('longitud', 10, 7);
            $table->string('direccion_texto')->nullable();

            // Estado del pedido
            $table->enum('estado', [
                'pendiente',
                'confirmado',
                'en produccion',
                'rechazado'
            ])->default('pendiente');

            // Fechas
            $table->timestamp('fechapedido')->useCurrent();
            $table->date('fechaEntregaDeseada')->nullable();

            // Observaciones
            $table->text('observaciones')->nullable();
        });

        // ===================== TABLA: detallepedido =====================
        Schema::create('detallepedido', function (Blueprint $table) {
            $table->id('detallepedidoid');

            // Relación con pedido (un pedido puede tener varios detalles)
            $table->foreignId('pedidoid')
                ->constrained('pedido', 'pedidoid')
                ->cascadeOnDelete();

            // Producto solicitado (manual)
            $table->string('cultivo_personalizado');

            // Cantidad solicitada (se sobreentiende en kilos)
            $table->decimal('cantidad', 12, 2);

            // Observaciones por ítem
            $table->text('observaciones')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detallepedido');
        Schema::dropIfExists('pedido');
    }
};