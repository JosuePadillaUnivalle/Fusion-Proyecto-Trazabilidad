<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cola local para envíos pendientes cuando la API externa no está disponible
     */
    public function up(): void
    {
        Schema::create('envios_pendientes', function (Blueprint $table) {
            $table->id();
            $table->json('datos_envio');           // Datos completos del envío en JSON
            $table->string('estado')->default('pendiente'); // pendiente, enviado, fallido
            $table->integer('intentos')->default(0);
            $table->text('ultimo_error')->nullable();
            $table->timestamp('ultimo_intento')->nullable();
            $table->timestamp('enviado_at')->nullable();
            $table->foreignId('usuarioid')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('envios_pendientes');
    }
};