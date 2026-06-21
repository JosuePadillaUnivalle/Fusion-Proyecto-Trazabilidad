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
            if (! Schema::hasColumn('insumo', 'requiere_cadena_frio')) {
                $table->boolean('requiere_cadena_frio')->default(false)->after('descripcion');
            }
        });

        $patrones = [
            'lechuga', 'espinaca', 'acelga', 'rúcula', 'rucula', 'apio',
            'brócoli', 'brocoli', 'coliflor', 'fresa', 'frutilla',
            'champiñón', 'champinon', 'hierba', 'ensalada',
        ];

        foreach (DB::table('insumo')->select('insumoid', 'nombre')->get() as $insumo) {
            $norm = mb_strtolower((string) $insumo->nombre);
            foreach ($patrones as $patron) {
                if (str_contains($norm, $patron)) {
                    DB::table('insumo')
                        ->where('insumoid', $insumo->insumoid)
                        ->update(['requiere_cadena_frio' => true]);

                    break;
                }
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('insumo') || ! Schema::hasColumn('insumo', 'requiere_cadena_frio')) {
            return;
        }

        Schema::table('insumo', function (Blueprint $table) {
            $table->dropColumn('requiere_cadena_frio');
        });
    }
};
