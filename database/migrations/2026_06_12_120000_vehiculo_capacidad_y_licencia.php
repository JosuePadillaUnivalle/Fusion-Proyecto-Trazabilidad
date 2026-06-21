<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tipo_vehiculo')) {
            Schema::table('tipo_vehiculo', function (Blueprint $table) {
                if (! Schema::hasColumn('tipo_vehiculo', 'tamano')) {
                    $table->string('tamano', 20)->nullable()->after('descripcion');
                }
                if (! Schema::hasColumn('tipo_vehiculo', 'licencia_requerida')) {
                    $table->string('licencia_requerida', 2)->nullable()->after('tamano');
                }
            });

            $mapa = [
                'Motocicleta' => ['tamano' => 'pequeno', 'licencia' => 'M'],
                'Camioneta' => ['tamano' => 'pequeno', 'licencia' => 'A'],
                'Furgoneta' => ['tamano' => 'mediano', 'licencia' => 'B'],
                'Camión pequeño' => ['tamano' => 'grande', 'licencia' => 'B'],
                'Camion pequeño' => ['tamano' => 'grande', 'licencia' => 'B'],
                'Camión grande' => ['tamano' => 'extra_grande', 'licencia' => 'C'],
                'Camion grande' => ['tamano' => 'extra_grande', 'licencia' => 'C'],
            ];

            foreach ($mapa as $nombre => $meta) {
                DB::table('tipo_vehiculo')
                    ->where('nombre', $nombre)
                    ->update([
                        'tamano' => $meta['tamano'],
                        'licencia_requerida' => $meta['licencia'],
                        'updated_at' => now(),
                    ]);
            }
        }

        if (Schema::hasTable('vehiculo')) {
            Schema::table('vehiculo', function (Blueprint $table) {
                if (! Schema::hasColumn('vehiculo', 'capacidad_kg_override')) {
                    $table->decimal('capacidad_kg_override', 10, 2)->nullable()->after('ambito_flota');
                }
                if (! Schema::hasColumn('vehiculo', 'capacidad_m3_override')) {
                    $table->decimal('capacidad_m3_override', 10, 2)->nullable()->after('capacidad_kg_override');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vehiculo')) {
            Schema::table('vehiculo', function (Blueprint $table) {
                if (Schema::hasColumn('vehiculo', 'capacidad_m3_override')) {
                    $table->dropColumn('capacidad_m3_override');
                }
                if (Schema::hasColumn('vehiculo', 'capacidad_kg_override')) {
                    $table->dropColumn('capacidad_kg_override');
                }
            });
        }

        if (Schema::hasTable('tipo_vehiculo')) {
            Schema::table('tipo_vehiculo', function (Blueprint $table) {
                if (Schema::hasColumn('tipo_vehiculo', 'licencia_requerida')) {
                    $table->dropColumn('licencia_requerida');
                }
                if (Schema::hasColumn('tipo_vehiculo', 'tamano')) {
                    $table->dropColumn('tamano');
                }
            });
        }
    }
};
