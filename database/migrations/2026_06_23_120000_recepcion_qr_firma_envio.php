<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('firma_transportista_envio') && ! Schema::hasColumn('firma_transportista_envio', 'nombrefirmante')) {
            Schema::table('firma_transportista_envio', function (Blueprint $table) {
                $table->string('nombrefirmante', 200)->nullable()->after('imagenfirma');
            });
        }

        if (Schema::hasTable('firma_recepcion_envio') && ! Schema::hasColumn('firma_recepcion_envio', 'nombrefirmante')) {
            Schema::table('firma_recepcion_envio', function (Blueprint $table) {
                $table->string('nombrefirmante', 200)->nullable()->after('imagenfirma');
            });
        }

        if (! Schema::hasTable('recepcion_qr_envio')) {
            Schema::create('recepcion_qr_envio', function (Blueprint $table) {
                $table->id('recepcionqrenvioid');
                $table->string('token', 64)->unique();
                $table->unsignedBigInteger('rutadistribucionid')->nullable();
                $table->unsignedBigInteger('envioasignacionmultipleid')->nullable();
                $table->timestamps();

                $table->foreign('rutadistribucionid')
                    ->references('rutadistribucionid')
                    ->on('ruta_distribucion')
                    ->nullOnDelete();
                $table->foreign('envioasignacionmultipleid')
                    ->references('envioasignacionmultipleid')
                    ->on('envio_asignacion_multiple')
                    ->nullOnDelete();

                $table->unique('rutadistribucionid');
                $table->unique('envioasignacionmultipleid');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recepcion_qr_envio');

        if (Schema::hasTable('firma_recepcion_envio') && Schema::hasColumn('firma_recepcion_envio', 'nombrefirmante')) {
            Schema::table('firma_recepcion_envio', function (Blueprint $table) {
                $table->dropColumn('nombrefirmante');
            });
        }

        if (Schema::hasTable('firma_transportista_envio') && Schema::hasColumn('firma_transportista_envio', 'nombrefirmante')) {
            Schema::table('firma_transportista_envio', function (Blueprint $table) {
                $table->dropColumn('nombrefirmante');
            });
        }
    }
};
