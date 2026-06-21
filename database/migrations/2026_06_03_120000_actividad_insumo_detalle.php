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
        if (Schema::hasTable('cultivo')) {
            Schema::table('cultivo', function (Blueprint $table) {
                if (! Schema::hasColumn('cultivo', 'dosis_siembra_por_ha')) {
                    $table->decimal('dosis_siembra_por_ha', 12, 3)->nullable()->after('detalle');
                }
                if (! Schema::hasColumn('cultivo', 'dosis_siembra_unidad')) {
                    $table->string('dosis_siembra_unidad', 20)->nullable()->after('dosis_siembra_por_ha');
                }
            });

            foreach (DB::table('cultivo')->get(['cultivoid', 'nombre', 'dosis_siembra_por_ha']) as $row) {
                if ($row->dosis_siembra_por_ha !== null) {
                    continue;
                }
                $dosis = CultivoSiembraCatalogo::dosisPorNombreCultivo($row->nombre);
                if ($dosis === null) {
                    continue;
                }
                DB::table('cultivo')->where('cultivoid', $row->cultivoid)->update([
                    'dosis_siembra_por_ha' => $dosis['por_ha'],
                    'dosis_siembra_unidad' => $dosis['unidad'],
                ]);
            }
        }

        if (Schema::hasTable('actividad') && ! Schema::hasColumn('actividad', 'detalle_json')) {
            Schema::table('actividad', function (Blueprint $table) {
                $table->text('detalle_json')->nullable()->after('observaciones');
            });
        }

        if (Schema::hasTable('loteinsumo') && ! Schema::hasColumn('loteinsumo', 'actividadid')) {
            Schema::table('loteinsumo', function (Blueprint $table) {
                $table->unsignedBigInteger('actividadid')->nullable()->after('loteid');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cultivo')) {
            Schema::table('cultivo', function (Blueprint $table) {
                if (Schema::hasColumn('cultivo', 'dosis_siembra_unidad')) {
                    $table->dropColumn('dosis_siembra_unidad');
                }
                if (Schema::hasColumn('cultivo', 'dosis_siembra_por_ha')) {
                    $table->dropColumn('dosis_siembra_por_ha');
                }
            });
        }

        if (Schema::hasTable('actividad') && Schema::hasColumn('actividad', 'detalle_json')) {
            Schema::table('actividad', function (Blueprint $table) {
                $table->dropColumn('detalle_json');
            });
        }

        if (Schema::hasTable('loteinsumo') && Schema::hasColumn('loteinsumo', 'actividadid')) {
            Schema::table('loteinsumo', function (Blueprint $table) {
                $table->dropColumn('actividadid');
            });
        }
    }
};
