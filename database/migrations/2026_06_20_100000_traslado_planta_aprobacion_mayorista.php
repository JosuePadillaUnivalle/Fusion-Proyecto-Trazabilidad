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

        Schema::table('ruta_distribucion', function (Blueprint $table) {
            if (! Schema::hasColumn('ruta_distribucion', 'fecha_aprobacion_mayorista')) {
                $table->timestamp('fecha_aprobacion_mayorista')->nullable();
            }
            if (! Schema::hasColumn('ruta_distribucion', 'aprobado_por_usuarioid')) {
                $table->unsignedBigInteger('aprobado_por_usuarioid')->nullable();
            }
            if (! Schema::hasColumn('ruta_distribucion', 'motivo_rechazo_mayorista')) {
                $table->text('motivo_rechazo_mayorista')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ruta_distribucion')) {
            return;
        }

        Schema::table('ruta_distribucion', function (Blueprint $table) {
            foreach (['motivo_rechazo_mayorista', 'aprobado_por_usuarioid', 'fecha_aprobacion_mayorista'] as $col) {
                if (Schema::hasColumn('ruta_distribucion', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
