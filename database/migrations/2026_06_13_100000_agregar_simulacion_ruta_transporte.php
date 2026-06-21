<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('envio_asignacion_multiple')) {
            Schema::table('envio_asignacion_multiple', function (Blueprint $table) {
                if (! Schema::hasColumn('envio_asignacion_multiple', 'simulacion_inicio_at')) {
                    $table->timestamp('simulacion_inicio_at')->nullable()->after('costo_bs');
                }
                if (! Schema::hasColumn('envio_asignacion_multiple', 'simulacion_duracion_seg')) {
                    $table->unsignedInteger('simulacion_duracion_seg')->nullable()->after('simulacion_inicio_at');
                }
                if (! Schema::hasColumn('envio_asignacion_multiple', 'simulacion_geojson')) {
                    $table->json('simulacion_geojson')->nullable()->after('simulacion_duracion_seg');
                }
            });
        }

        if (Schema::hasTable('ruta_distribucion')) {
            Schema::table('ruta_distribucion', function (Blueprint $table) {
                if (! Schema::hasColumn('ruta_distribucion', 'simulacion_inicio_at')) {
                    $table->timestamp('simulacion_inicio_at')->nullable()->after('costo_bs');
                }
                if (! Schema::hasColumn('ruta_distribucion', 'simulacion_duracion_seg')) {
                    $table->unsignedInteger('simulacion_duracion_seg')->nullable()->after('simulacion_inicio_at');
                }
                if (! Schema::hasColumn('ruta_distribucion', 'simulacion_geojson')) {
                    $table->json('simulacion_geojson')->nullable()->after('simulacion_duracion_seg');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('envio_asignacion_multiple')) {
            Schema::table('envio_asignacion_multiple', function (Blueprint $table) {
                $cols = array_filter([
                    Schema::hasColumn('envio_asignacion_multiple', 'simulacion_inicio_at') ? 'simulacion_inicio_at' : null,
                    Schema::hasColumn('envio_asignacion_multiple', 'simulacion_duracion_seg') ? 'simulacion_duracion_seg' : null,
                    Schema::hasColumn('envio_asignacion_multiple', 'simulacion_geojson') ? 'simulacion_geojson' : null,
                ]);
                if ($cols !== []) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('ruta_distribucion')) {
            Schema::table('ruta_distribucion', function (Blueprint $table) {
                $cols = array_filter([
                    Schema::hasColumn('ruta_distribucion', 'simulacion_inicio_at') ? 'simulacion_inicio_at' : null,
                    Schema::hasColumn('ruta_distribucion', 'simulacion_duracion_seg') ? 'simulacion_duracion_seg' : null,
                    Schema::hasColumn('ruta_distribucion', 'simulacion_geojson') ? 'simulacion_geojson' : null,
                ]);
                if ($cols !== []) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
