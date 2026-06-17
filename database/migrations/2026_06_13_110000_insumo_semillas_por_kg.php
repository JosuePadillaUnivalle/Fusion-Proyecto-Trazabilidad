<?php

use App\Support\CultivoSiembraCatalogo;
use App\Support\InsumoCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('insumo') && ! Schema::hasColumn('insumo', 'semillas_por_kg')) {
            Schema::table('insumo', function (Blueprint $table) {
                $table->decimal('semillas_por_kg', 12, 3)->nullable()->after('dosis_unidad');
            });
        }

        if (! Schema::hasTable('insumo')) {
            return;
        }

        $tipos = InsumoCatalogo::tiposOrdenados();
        $tipoSemillaIds = $tipos
            ->filter(fn ($t) => InsumoCatalogo::slugFromNombreTipo($t->nombre) === 'material_siembra')
            ->pluck('tipoinsumoid')
            ->all();

        if ($tipoSemillaIds === []) {
            return;
        }

        foreach (DB::table('insumo')->whereIn('tipoinsumoid', $tipoSemillaIds)->get() as $row) {
            if ($row->semillas_por_kg !== null) {
                continue;
            }

            $nombreCultivo = preg_replace(
                '/^(semilla\s+certificada|semilla|material de siembra)\s+/iu',
                '',
                (string) $row->nombre
            );
            $estimado = CultivoSiembraCatalogo::semillasPorKgEstimado(trim($nombreCultivo) ?: $row->nombre);

            if ($estimado === null) {
                continue;
            }

            DB::table('insumo')->where('insumoid', $row->insumoid)->update([
                'semillas_por_kg' => $estimado,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('insumo') && Schema::hasColumn('insumo', 'semillas_por_kg')) {
            Schema::table('insumo', function (Blueprint $table) {
                $table->dropColumn('semillas_por_kg');
            });
        }
    }
};
