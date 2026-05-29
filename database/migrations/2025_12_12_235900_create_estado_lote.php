<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('estadolote')) {
            Schema::create('estadolote', function (Blueprint $table) {
                $table->id('estadoid');
                $table->unsignedBigInteger('loteid');
                $table->unsignedBigInteger('estadolotetipoid');
                $table->dateTime('fecharegistro')->useCurrent();
                $table->text('observaciones')->nullable();
                $table->text('imagenurl')->nullable();

                $table->foreign('loteid')->references('loteid')->on('lote')->onDelete('cascade');
                $table->foreign('estadolotetipoid')->references('estadolotetipoid')->on('estadolote_tipo');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estadolote');
    }
};
