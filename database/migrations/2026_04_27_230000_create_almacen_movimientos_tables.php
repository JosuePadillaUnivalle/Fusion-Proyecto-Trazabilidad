<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tipo_movimiento_almacen')) {
            Schema::create('tipo_movimiento_almacen', function (Blueprint $table) {
                $table->id('tipo_movimiento_almacenid');
                $table->string('nombre', 50);
                $table->string('naturaleza', 10); // ingreso | salida
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });

            DB::table('tipo_movimiento_almacen')->insert([
                ['nombre' => 'Compra', 'naturaleza' => 'ingreso', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Devolucion', 'naturaleza' => 'ingreso', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Ajuste positivo', 'naturaleza' => 'ingreso', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Consumo interno', 'naturaleza' => 'salida', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Despacho', 'naturaleza' => 'salida', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
                ['nombre' => 'Ajuste negativo', 'naturaleza' => 'salida', 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (!Schema::hasTable('almacen_movimiento')) {
            Schema::create('almacen_movimiento', function (Blueprint $table) {
                $table->id('almacen_movimientoid');
                $table->unsignedBigInteger('almacenid');
                $table->unsignedBigInteger('insumoid');
                $table->unsignedBigInteger('tipo_movimiento_almacenid');
                $table->unsignedBigInteger('usuarioid');
                $table->date('fecha');
                $table->decimal('cantidad', 14, 3);
                $table->string('referencia', 100)->nullable();
                $table->string('destino_motivo', 150)->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();

                $table->foreign('almacenid')->references('almacenid')->on('almacen');
                $table->foreign('insumoid')->references('insumoid')->on('insumo');
                $table->foreign('tipo_movimiento_almacenid')->references('tipo_movimiento_almacenid')->on('tipo_movimiento_almacen');
                $table->foreign('usuarioid')->references('usuarioid')->on('usuario');
                $table->index(['almacenid', 'fecha']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('almacen_movimiento');
        Schema::dropIfExists('tipo_movimiento_almacen');
    }
};
