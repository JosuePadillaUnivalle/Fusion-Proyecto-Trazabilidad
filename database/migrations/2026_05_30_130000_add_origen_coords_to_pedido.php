<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pedido')) {
            return;
        }

        Schema::table('pedido', function (Blueprint $table) {
            if (! Schema::hasColumn('pedido', 'origen_latitud')) {
                $table->decimal('origen_latitud', 10, 7)->nullable()->after('nombre_planta');
            }
            if (! Schema::hasColumn('pedido', 'origen_longitud')) {
                $table->decimal('origen_longitud', 10, 7)->nullable()->after('origen_latitud');
            }
            if (! Schema::hasColumn('pedido', 'origen_direccion')) {
                $table->string('origen_direccion', 255)->nullable()->after('origen_longitud');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('pedido')) {
            return;
        }

        Schema::table('pedido', function (Blueprint $table) {
            foreach (['origen_direccion', 'origen_longitud', 'origen_latitud'] as $col) {
                if (Schema::hasColumn('pedido', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
