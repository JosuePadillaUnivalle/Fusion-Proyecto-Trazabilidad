<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pedido_distribucion') && ! Schema::hasColumn('pedido_distribucion', 'almacen_mayorista_origenid')) {
            Schema::table('pedido_distribucion', function (Blueprint $table) {
                $table->unsignedBigInteger('almacen_mayorista_origenid')->nullable()->after('almacen_planta_origenid');
                $table->foreign('almacen_mayorista_origenid')->references('almacenid')->on('almacen')->nullOnDelete();
            });
        }

        if (Schema::hasTable('ruta_distribucion') && ! Schema::hasColumn('ruta_distribucion', 'almacen_mayorista_origenid')) {
            Schema::table('ruta_distribucion', function (Blueprint $table) {
                $table->unsignedBigInteger('almacen_mayorista_origenid')->nullable()->after('almacen_planta_origenid');
                $table->foreign('almacen_mayorista_origenid')->references('almacenid')->on('almacen')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pedido_distribucion') && Schema::hasColumn('pedido_distribucion', 'almacen_mayorista_origenid')) {
            Schema::table('pedido_distribucion', function (Blueprint $table) {
                $table->dropForeign(['almacen_mayorista_origenid']);
                $table->dropColumn('almacen_mayorista_origenid');
            });
        }

        if (Schema::hasTable('ruta_distribucion') && Schema::hasColumn('ruta_distribucion', 'almacen_mayorista_origenid')) {
            Schema::table('ruta_distribucion', function (Blueprint $table) {
                $table->dropForeign(['almacen_mayorista_origenid']);
                $table->dropColumn('almacen_mayorista_origenid');
            });
        }
    }
};
