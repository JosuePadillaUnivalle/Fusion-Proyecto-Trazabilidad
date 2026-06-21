<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ruta_distribucion')) {
            return;
        }

        if (Schema::hasTable('detalle_traslado_planta_mayorista')) {
            return;
        }

        Schema::create('detalle_traslado_planta_mayorista', function (Blueprint $table) {
            $table->id('detalletrasladoid');
            $table->unsignedBigInteger('rutadistribucionid');
            $table->unsignedBigInteger('insumoid');
            $table->string('producto_nombre', 200);
            $table->decimal('cantidad', 12, 2);
            $table->text('observaciones')->nullable();

            $table->foreign('rutadistribucionid')
                ->references('rutadistribucionid')
                ->on('ruta_distribucion')
                ->cascadeOnDelete();
            $table->foreign('insumoid')->references('insumoid')->on('insumo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_traslado_planta_mayorista');
    }
};
