<?php

use App\Support\CultivoSiembraCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lote') && ! Schema::hasColumn('lote', 'insumosemillaid')) {
            Schema::table('lote', function (Blueprint $table) {
                $table->unsignedBigInteger('insumosemillaid')->nullable()->after('cultivoid');
            });

            if (Schema::hasTable('insumo')) {
                Schema::table('lote', function (Blueprint $table) {
                    $table->foreign('insumosemillaid')
                        ->references('insumoid')
                        ->on('insumo')
                        ->nullOnDelete();
                });
            }
        }

        if (Schema::hasTable('insumo')) {
            Schema::table('insumo', function (Blueprint $table) {
                if (! Schema::hasColumn('insumo', 'dosis_por_ha')) {
                    $table->decimal('dosis_por_ha', 12, 3)->nullable()->after('descripcion');
                }
                if (! Schema::hasColumn('insumo', 'dosis_unidad')) {
                    $table->string('dosis_unidad', 20)->nullable()->after('dosis_por_ha');
                }
            });

            foreach (DB::table('insumo')->get(['insumoid', 'nombre', 'dosis_por_ha']) as $row) {
                if ($row->dosis_por_ha !== null) {
                    continue;
                }

                $nombreCultivo = preg_replace(
                    '/^(semilla\s+certificada|semilla|material de siembra)\s+/iu',
                    '',
                    (string) $row->nombre
                );
                $dosis = CultivoSiembraCatalogo::dosisPorNombreCultivo(trim($nombreCultivo));
                if ($dosis === null) {
                    continue;
                }

                DB::table('insumo')->where('insumoid', $row->insumoid)->update([
                    'dosis_por_ha' => $dosis['por_ha'],
                    'dosis_unidad' => $dosis['unidad'],
                ]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lote') && Schema::hasColumn('lote', 'insumosemillaid')) {
            Schema::table('lote', function (Blueprint $table) {
                try {
                    $table->dropForeign(['insumosemillaid']);
                } catch (\Throwable) {
                }
                $table->dropColumn('insumosemillaid');
            });
        }

        if (Schema::hasTable('insumo')) {
            Schema::table('insumo', function (Blueprint $table) {
                if (Schema::hasColumn('insumo', 'dosis_unidad')) {
                    $table->dropColumn('dosis_unidad');
                }
                if (Schema::hasColumn('insumo', 'dosis_por_ha')) {
                    $table->dropColumn('dosis_por_ha');
                }
            });
        }
    }
};
