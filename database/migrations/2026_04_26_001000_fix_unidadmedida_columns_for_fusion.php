<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('unidadmedida')) {
            return;
        }

        Schema::table('unidadmedida', function (Blueprint $table) {
            if (! Schema::hasColumn('unidadmedida', 'abreviatura')) {
                $table->string('abreviatura', 20)->nullable()->after('nombre');
            }
            if (! Schema::hasColumn('unidadmedida', 'categoria')) {
                $table->string('categoria', 40)->nullable()->after('abreviatura');
            }
        });

        // Valores base para las unidades usadas por los módulos fusionados.
        DB::table('unidadmedida')
            ->where('nombre', 'Kilogramo')
            ->update(['abreviatura' => 'kg', 'categoria' => 'peso']);

        DB::table('unidadmedida')
            ->where('nombre', 'Hectárea')
            ->update(['abreviatura' => 'ha', 'categoria' => 'superficie']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('unidadmedida')) {
            return;
        }

        Schema::table('unidadmedida', function (Blueprint $table) {
            if (Schema::hasColumn('unidadmedida', 'categoria')) {
                $table->dropColumn('categoria');
            }
            if (Schema::hasColumn('unidadmedida', 'abreviatura')) {
                $table->dropColumn('abreviatura');
            }
        });
    }
};

