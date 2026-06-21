<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('vehiculo') || Schema::hasColumn('vehiculo', 'ambito_flota')) {
            return;
        }

        Schema::table('vehiculo', function (Blueprint $table) {
            $table->string('ambito_flota', 20)->default('agricola')->after('activo');
        });

        if (Schema::hasColumn('vehiculo', 'placa')) {
            DB::table('vehiculo')->where('placa', 'like', 'SCZ-PLT-%')->update(['ambito_flota' => 'planta']);
            DB::table('vehiculo')->where('placa', 'like', 'SCZ-MOD-%')->update(['ambito_flota' => 'agricola']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('vehiculo') && Schema::hasColumn('vehiculo', 'ambito_flota')) {
            Schema::table('vehiculo', function (Blueprint $table) {
                $table->dropColumn('ambito_flota');
            });
        }
    }
};
