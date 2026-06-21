<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Tabla Usuario
        if (!Schema::hasTable('usuario')) {
            Schema::create('usuario', function (Blueprint $table) {
                $table->id('usuarioid');
                $table->string('nombre');
                $table->string('apellido');
                $table->string('email')->unique();
                $table->string('nombreusuario')->unique();
                $table->string('telefono')->nullable();
                $table->string('passwordhash');
                $table->text('imagenurl')->nullable();
                $table->text('informacionadicional')->nullable();
                $table->dateTime('fecharegistro')->useCurrent();
                $table->dateTime('fechamodificacion')->useCurrent();
                $table->dateTime('ultimologin')->nullable();
                $table->boolean('activo')->default(true);
            });
        }


    }

    public function down(): void
    {
        Schema::dropIfExists('usuariorol');
        Schema::dropIfExists('rol');
        Schema::dropIfExists('usuario');
    }
};
