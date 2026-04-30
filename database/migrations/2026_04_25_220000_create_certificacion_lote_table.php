<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('certificacion_lote')) {
            Schema::create('certificacion_lote', function (Blueprint $table) {
                $table->id('certificacionid');
                $table->unsignedBigInteger('loteid');
                $table->unsignedBigInteger('usuarioid');
                $table->string('codigo_certificado', 60)->unique();
                $table->text('observaciones')->nullable();
                $table->dateTime('fecha_certificacion');

                $table->foreign('loteid')->references('loteid')->on('lote')->onDelete('cascade');
                $table->foreign('usuarioid')->references('usuarioid')->on('usuario');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('certificacion_lote');
    }
};

