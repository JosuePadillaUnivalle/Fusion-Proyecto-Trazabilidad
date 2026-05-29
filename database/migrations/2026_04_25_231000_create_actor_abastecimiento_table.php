<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('actor_abastecimiento')) {
            Schema::create('actor_abastecimiento', function (Blueprint $table) {
                $table->id('actorid');
                $table->string('nombre', 120);
                $table->string('tipo_actor', 30)->default('proveedor');
                $table->string('email', 120)->nullable();
                $table->string('telefono', 30)->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('actor_abastecimiento');
    }
};

