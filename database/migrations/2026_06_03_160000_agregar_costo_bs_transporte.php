<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('envio_asignacion_multiple')
            && ! Schema::hasColumn('envio_asignacion_multiple', 'costo_bs')) {
            Schema::table('envio_asignacion_multiple', function (Blueprint $table) {
                $table->decimal('costo_bs', 12, 2)->nullable()->after('vehiculo_ref');
            });
        }

        if (Schema::hasTable('ruta_distribucion')
            && ! Schema::hasColumn('ruta_distribucion', 'costo_bs')) {
            Schema::table('ruta_distribucion', function (Blueprint $table) {
                $table->decimal('costo_bs', 12, 2)->nullable()->after('vehiculoid');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('envio_asignacion_multiple')
            && Schema::hasColumn('envio_asignacion_multiple', 'costo_bs')) {
            Schema::table('envio_asignacion_multiple', function (Blueprint $table) {
                $table->dropColumn('costo_bs');
            });
        }

        if (Schema::hasTable('ruta_distribucion')
            && Schema::hasColumn('ruta_distribucion', 'costo_bs')) {
            Schema::table('ruta_distribucion', function (Blueprint $table) {
                $table->dropColumn('costo_bs');
            });
        }
    }
};
