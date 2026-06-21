<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('insumo')) {
            return;
        }

        Schema::table('insumo', function (Blueprint $table) {
            if (! Schema::hasColumn('insumo', 'codigo_transporte_requerido')) {
                $after = Schema::hasColumn('insumo', 'requiere_cadena_frio')
                    ? 'requiere_cadena_frio'
                    : 'descripcion';
                $table->string('codigo_transporte_requerido', 30)
                    ->default('CARGA_GENERAL')
                    ->after($after);
            }
        });

        if (Schema::hasColumn('insumo', 'requiere_cadena_frio')) {
            DB::table('insumo')
                ->where('requiere_cadena_frio', true)
                ->update(['codigo_transporte_requerido' => 'REFRIGERADO']);

            DB::table('insumo')
                ->where('requiere_cadena_frio', false)
                ->update(['codigo_transporte_requerido' => 'CARGA_GENERAL']);

            Schema::table('insumo', function (Blueprint $table) {
                $table->dropColumn('requiere_cadena_frio');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('insumo')) {
            return;
        }

        if (! Schema::hasColumn('insumo', 'requiere_cadena_frio')) {
            Schema::table('insumo', function (Blueprint $table) {
                $table->boolean('requiere_cadena_frio')->default(false)->after('descripcion');
            });
        }

        if (Schema::hasColumn('insumo', 'codigo_transporte_requerido')) {
            DB::table('insumo')
                ->whereIn('codigo_transporte_requerido', ['REFRIGERADO', 'ISOTERMICO'])
                ->update(['requiere_cadena_frio' => true]);

            Schema::table('insumo', function (Blueprint $table) {
                $table->dropColumn('codigo_transporte_requerido');
            });
        }
    }
};
