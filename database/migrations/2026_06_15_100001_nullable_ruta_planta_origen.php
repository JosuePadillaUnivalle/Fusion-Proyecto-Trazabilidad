<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ruta_distribucion') && Schema::hasColumn('ruta_distribucion', 'almacen_planta_origenid')) {
            Schema::table('ruta_distribucion', function (Blueprint $table) {
                $table->unsignedBigInteger('almacen_planta_origenid')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        // No revert: rutas nuevas usan almacen_mayorista_origenid.
    }
};
