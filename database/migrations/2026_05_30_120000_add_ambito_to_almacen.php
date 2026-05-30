<?php

use App\Support\AlmacenAmbito;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('almacen')) {
            return;
        }

        if (! Schema::hasColumn('almacen', 'ambito')) {
            Schema::table('almacen', function (Blueprint $table) {
                $table->string('ambito', 20)->default(AlmacenAmbito::AGRICOLA)->after('tipoalmacenid');
            });
        }

        AlmacenAmbito::asegurarAmbitosEnRegistros();
    }

    public function down(): void
    {
        if (Schema::hasTable('almacen') && Schema::hasColumn('almacen', 'ambito')) {
            Schema::table('almacen', function (Blueprint $table) {
                $table->dropColumn('ambito');
            });
        }
    }
};
