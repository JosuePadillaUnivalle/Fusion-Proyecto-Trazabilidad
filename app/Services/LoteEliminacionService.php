<?php

namespace App\Services;

use App\Models\Lote;
use App\Support\EliminacionSegura;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class LoteEliminacionService
{
    public function eliminar(Lote $lote): void
    {
        $lote->loadMissing('estadoTipo');
        $nombre = (string) $lote->nombre;

        if (! \App\Support\EstadoLoteCatalogo::loteSePuedeEliminar($lote->estadoTipo?->nombre)) {
            throw new \InvalidArgumentException(
                'Solo se pueden eliminar lotes en estado Planificado.'
            );
        }

        $id = (int) $lote->loteid;

        EliminacionSegura::ejecutar(function () use ($lote, $id): void {
            DB::transaction(function () use ($lote, $id): void {
                $this->eliminarDependencias($id);
                $this->eliminarImagen($lote->imagenurl);
                $lote->delete();
            });
        }, 'No se puede eliminar el lote «'.$nombre.'» porque tiene registros vinculados en el sistema.');
    }

    private function eliminarDependencias(int $loteId): void
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
    }

    private function eliminarImagen(?string $imagenUrl): void
    {
        if (! is_string($imagenUrl) || $imagenUrl === '') {
            return;
        }

        if (str_contains($imagenUrl, '/storage/')) {
            $path = str_replace('/storage/', '', $imagenUrl);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}
