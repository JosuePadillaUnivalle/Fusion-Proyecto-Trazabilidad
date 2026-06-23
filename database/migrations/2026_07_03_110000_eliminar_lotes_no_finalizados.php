<?php

use App\Support\EstadoLoteCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lote') || ! Schema::hasTable('estadolote_tipo')) {
            return;
        }

        $idsFinalizado = $this->idsEstadoFinalizado();
        if ($idsFinalizado === []) {
            return;
        }

        $loteIds = DB::table('lote')
            ->whereNotIn('estadolotetipoid', $idsFinalizado)
            ->pluck('loteid');

        foreach ($loteIds as $loteId) {
            $this->eliminarLoteAgricola((int) $loteId);
        }
    }

    public function down(): void
    {
        // Limpieza operativa; no reversible.
    }

    /** @return list<int> */
    private function idsEstadoFinalizado(): array
    {
        $porSlug = EstadoLoteCatalogo::idsPorSlugs(['finalizado']);

        $porNombre = DB::table('estadolote_tipo')
            ->whereRaw('LOWER(TRIM(nombre)) = ?', ['finalizado'])
            ->pluck('estadolotetipoid')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_values(array_unique(array_merge($porSlug, $porNombre)));
    }

    private function eliminarLoteAgricola(int $loteId): void
    {
        if (Schema::hasTable('produccion')) {
            $produccionIds = DB::table('produccion')->where('loteid', $loteId)->pluck('produccionid');
            foreach ($produccionIds as $produccionId) {
                if (Schema::hasTable('venta') && Schema::hasColumn('venta', 'produccionid')) {
                    DB::table('venta')->where('produccionid', $produccionId)->delete();
                }
                if (Schema::hasTable('produccionalmacenamiento')) {
                    $almIds = DB::table('produccionalmacenamiento')
                        ->where('produccionid', $produccionId)
                        ->pluck('produccionalmacenamientoid');
                    if ($almIds->isNotEmpty()
                        && Schema::hasTable('detallepedido')
                        && Schema::hasColumn('detallepedido', 'produccionalmacenamientoid')) {
                        DB::table('detallepedido')
                            ->whereIn('produccionalmacenamientoid', $almIds)
                            ->update(['produccionalmacenamientoid' => null]);
                    }
                    DB::table('produccionalmacenamiento')->where('produccionid', $produccionId)->delete();
                }
                if (Schema::hasTable('detallepedido') && Schema::hasColumn('detallepedido', 'produccionid')) {
                    DB::table('detallepedido')->where('produccionid', $produccionId)->update(['produccionid' => null]);
                }
            }
            DB::table('produccion')->where('loteid', $loteId)->delete();
        }

        if (Schema::hasTable('actividad')) {
            $actividadIds = DB::table('actividad')->where('loteid', $loteId)->pluck('actividadid');
            if ($actividadIds->isNotEmpty() && Schema::hasTable('actividad_insumo_detalle')) {
                DB::table('actividad_insumo_detalle')->whereIn('actividadid', $actividadIds)->delete();
            }
            DB::table('actividad')->where('loteid', $loteId)->delete();
        }

        if (Schema::hasTable('loteinsumo')) {
            DB::table('loteinsumo')->where('loteid', $loteId)->delete();
        }

        if (Schema::hasTable('clima')) {
            DB::table('clima')->where('loteid', $loteId)->delete();
        }

        if (Schema::hasTable('historial_estados_lote')) {
            DB::table('historial_estados_lote')->where('loteid', $loteId)->delete();
        }

        if (Schema::hasTable('certificacion_lote')) {
            DB::table('certificacion_lote')->where('loteid', $loteId)->delete();
        }

        if (Schema::hasTable('estadolote')) {
            DB::table('estadolote')->where('loteid', $loteId)->delete();
        }

        if (Schema::hasTable('registro_proceso_maquina_planta')) {
            DB::table('registro_proceso_maquina_planta')->where('loteid', $loteId)->delete();
        }

        if (Schema::hasTable('siembra') && Schema::hasColumn('siembra', 'loteid')) {
            DB::table('siembra')->where('loteid', $loteId)->delete();
        }

        $imagen = DB::table('lote')->where('loteid', $loteId)->value('imagenurl');
        if (is_string($imagen) && $imagen !== '') {
            $path = str_replace('/storage/', '', $imagen);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        DB::table('lote')->where('loteid', $loteId)->delete();
    }
};
