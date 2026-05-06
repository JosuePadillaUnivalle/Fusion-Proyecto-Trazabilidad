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
                if (! Schema::hasColumn('envio_asignacion_multiple', 'detalles_productos')) {
                    $table->json('detalles_productos')->nullable()->after('vehiculo_ref');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('envio_asignacion_multiple')) {
            Schema::table('envio_asignacion_multiple', function (Blueprint $table) {
                if (Schema::hasColumn('envio_asignacion_multiple', 'detalles_productos')) {
                    $table->dropColumn('detalles_productos');
                }
            });
        }
    }
};
