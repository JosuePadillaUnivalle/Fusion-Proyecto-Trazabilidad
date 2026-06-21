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
            if (! Schema::hasColumn('ruta_distribucion', 'tipo_ruta')) {
                $table->string('tipo_ruta', 32)->default('mayorista_pdv')->after('nombre');
            }
            if (! Schema::hasColumn('ruta_distribucion', 'almacen_mayorista_destinoid')) {
                $table->unsignedBigInteger('almacen_mayorista_destinoid')->nullable()->after('almacen_mayorista_origenid');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('ruta_distribucion')) {
            return;
        }

        Schema::table('ruta_distribucion', function (Blueprint $table) {
            if (Schema::hasColumn('ruta_distribucion', 'almacen_mayorista_destinoid')) {
                $table->dropColumn('almacen_mayorista_destinoid');
            }
            if (Schema::hasColumn('ruta_distribucion', 'tipo_ruta')) {
                $table->dropColumn('tipo_ruta');
            }
        });
    }
};
