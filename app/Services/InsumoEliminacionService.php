<?php

namespace App\Services;

use App\Models\Insumo;
use App\Support\CultivoSiembraCatalogo;
use App\Support\InsumoDosisReferenciaCatalogo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class InsumoEliminacionService
{
    /**
     * Elimina un insumo y sus referencias operativas.
     *
     * @throws ValidationException
     */
    public function eliminar(Insumo $insumo): void
    {
        DB::transaction(function () use ($insumo): void {
            $insumoId = (int) $insumo->insumoid;

            if (Schema::hasTable('lote') && Schema::hasColumn('lote', 'insumosemillaid')) {
                DB::table('lote')->where('insumosemillaid', $insumoId)->update(['insumosemillaid' => null]);
            }

            if (Schema::hasTable('loteinsumo')) {
                DB::table('loteinsumo')->where('insumoid', $insumoId)->delete();
            }

            if (Schema::hasTable('almacen_movimiento')) {
                DB::table('almacen_movimiento')->where('insumoid', $insumoId)->delete();
            }

            if (Schema::hasTable('detallepedido') && Schema::hasColumn('detallepedido', 'insumoid')) {
                DB::table('detallepedido')->where('insumoid', $insumoId)->update(['insumoid' => null]);
            }

            if (Schema::hasTable('lote_produccion_materia_prima') && Schema::hasColumn('lote_produccion_materia_prima', 'insumoid')) {
                DB::table('lote_produccion_materia_prima')->where('insumoid', $insumoId)->update(['insumoid' => null]);
            }

            if (Schema::hasTable('detalle_traslado_planta_mayorista')) {
                DB::table('detalle_traslado_planta_mayorista')->where('insumoid', $insumoId)->delete();
            }

            if (Schema::hasTable('punto_venta_stock') && Schema::hasColumn('punto_venta_stock', 'insumoid')) {
                DB::table('punto_venta_stock')->where('insumoid', $insumoId)->update(['insumoid' => null]);
            }

            $insumo->delete();
        });
    }

    /**
     * Fusiona referencias de un insumo duplicado hacia el canónico y elimina el duplicado.
     */
    public function fusionarEn(int $desdeId, int $haciaId): void
    {
        if ($desdeId === $haciaId) {
            return;
        }

        DB::transaction(function () use ($desdeId, $haciaId): void {
            if (Schema::hasTable('lote') && Schema::hasColumn('lote', 'insumosemillaid')) {
                DB::table('lote')->where('insumosemillaid', $desdeId)->update(['insumosemillaid' => $haciaId]);
            }

            if (Schema::hasTable('loteinsumo')) {
                DB::table('loteinsumo')->where('insumoid', $desdeId)->update(['insumoid' => $haciaId]);
            }

            if (Schema::hasTable('almacen_movimiento')) {
                DB::table('almacen_movimiento')->where('insumoid', $desdeId)->update(['insumoid' => $haciaId]);
            }

            if (Schema::hasTable('detallepedido') && Schema::hasColumn('detallepedido', 'insumoid')) {
                DB::table('detallepedido')->where('insumoid', $desdeId)->update(['insumoid' => $haciaId]);
            }

            if (Schema::hasTable('lote_produccion_materia_prima') && Schema::hasColumn('lote_produccion_materia_prima', 'insumoid')) {
                DB::table('lote_produccion_materia_prima')->where('insumoid', $desdeId)->update(['insumoid' => $haciaId]);
            }

            if (Schema::hasTable('detalle_traslado_planta_mayorista')) {
                DB::table('detalle_traslado_planta_mayorista')->where('insumoid', $desdeId)->update(['insumoid' => $haciaId]);
            }

            if (Schema::hasTable('catalogo_tamano_conteo')) {
                DB::table('catalogo_tamano_conteo')->where('insumoid', $desdeId)->delete();
            }

            Insumo::query()->where('insumoid', $desdeId)->delete();
        });
    }

    /** Rellena o corrige dosis_por_ha / dosis_unidad según catálogo de referencia. */
    public static function aplicarDosisReferencia(Insumo $insumo, bool $forzar = false): bool
    {
        if (! $forzar
            && $insumo->dosis_por_ha !== null
            && (float) $insumo->dosis_por_ha > 0
            && trim((string) $insumo->dosis_unidad) !== '') {
            return false;
        }

        $referencia = self::resolverDosisCatalogo($insumo);
        if ($referencia === null) {
            return false;
        }

        $insumo->update([
            'dosis_por_ha' => $referencia['por_ha'],
            'dosis_unidad' => $referencia['unidad'],
        ]);

        return true;
    }

    /** @return array{por_ha: float, unidad: string, etiqueta_unidad: string}|null */
    public static function resolverDosisCatalogo(Insumo $insumo): ?array
    {
        $insumo->loadMissing(['tipo', 'unidadMedida']);
        $slug = \App\Support\InsumoCatalogo::slugFromNombreTipo($insumo->tipo?->nombre ?? '');

        if (in_array($slug, ['fertilizantes', 'pesticidas', 'bioinsumo'], true)) {
            return InsumoDosisReferenciaCatalogo::paraInsumo($insumo);
        }

        if ($slug !== 'material_siembra') {
            return null;
        }

        $cultivo = \App\Support\PedidoCatalogo::cultivoDesdeInsumo($insumo);
        if ($cultivo !== '') {
            $dosis = CultivoSiembraCatalogo::dosisPorNombreCultivo($cultivo);
            if ($dosis !== null) {
                return $dosis;
            }
        }

        return CultivoSiembraCatalogo::dosisPorNombreCultivo($insumo->nombre);
    }
}
