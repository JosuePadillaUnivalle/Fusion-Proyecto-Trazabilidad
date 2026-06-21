<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ruta_multi_entrega') && ! Schema::hasColumn('ruta_multi_entrega', 'rutageojson')) {
            Schema::table('ruta_multi_entrega', function (Blueprint $table) {
                $table->text('rutageojson')->nullable()->after('resumen');
            });
        }

        if (Schema::hasTable('ruta_parada')) {
            Schema::table('ruta_parada', function (Blueprint $table) {
                if (! Schema::hasColumn('ruta_parada', 'latitud')) {
                    $table->decimal('latitud', 10, 7)->nullable()->after('destino');
                }
                if (! Schema::hasColumn('ruta_parada', 'longitud')) {
                    $table->decimal('longitud', 10, 7)->nullable()->after('latitud');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ruta_multi_entrega', 'rutageojson')) {
            Schema::table('ruta_multi_entrega', function (Blueprint $table) {
                $table->dropColumn('rutageojson');
            });
        }

        if (Schema::hasTable('ruta_parada')) {
            Schema::table('ruta_parada', function (Blueprint $table) {
                if (Schema::hasColumn('ruta_parada', 'longitud')) {
                    $table->dropColumn('longitud');
                }
                if (Schema::hasColumn('ruta_parada', 'latitud')) {
                    $table->dropColumn('latitud');
                }
            });
        }
    }
};
