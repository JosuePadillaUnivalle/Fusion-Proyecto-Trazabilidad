<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('proceso_planta')) {
            Schema::create('proceso_planta', function (Blueprint $table) {
                $table->id('procesoplantaid');
                $table->string('nombre', 100);
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('maquina_planta')) {
            Schema::create('maquina_planta', function (Blueprint $table) {
                $table->id('maquinaplantaid');
                $table->string('nombre', 100);
                $table->string('codigo', 60)->nullable();
                $table->text('descripcion')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('maquina_planta');
        Schema::dropIfExists('proceso_planta');
    }
};

