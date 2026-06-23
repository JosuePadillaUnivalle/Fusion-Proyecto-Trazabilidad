<?php

use App\Models\Insumo;
use App\Support\CultivoSiembraCatalogo;
use App\Support\InsumoCatalogo;
use App\Support\PedidoCatalogo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Semillas/kg de referencia para cultivos sin entrada en catálogo. */
    private const SEMILLAS_EXTRA = [
        'repollo' => 320000.0,
        'brocoli' => 280000.0,
        'brócoli' => 280000.0,
        'espinaca' => 400000.0,
        'pepino' => 35000.0,
    ];

    public function up(): void
    {
        if (! Schema::hasTable('insumo')) {
            return;
        }

        Insumo::query()->with('tipo')->each(function (Insumo $insumo): void {
            if (InsumoCatalogo::slugFromNombreTipo($insumo->tipo?->nombre) !== 'material_siembra') {
                return;
            }

            $cambios = [];

            $sinDosis = $insumo->dosis_por_ha === null
                || (float) $insumo->dosis_por_ha <= 0
                || trim((string) $insumo->dosis_unidad) === '';

            if ($sinDosis) {
                $cultivo = PedidoCatalogo::cultivoDesdeInsumo($insumo);
                $ref = CultivoSiembraCatalogo::dosisPorNombreCultivo($cultivo !== '' ? $cultivo : null)
                    ?? CultivoSiembraCatalogo::dosisPorNombreCultivo($insumo->nombre);

                if ($ref !== null) {
                    $cambios['dosis_por_ha'] = $ref['por_ha'];
                    $cambios['dosis_unidad'] = $ref['unidad'];
                }
            }

            $sinSemillas = $insumo->semillas_por_kg === null || (float) $insumo->semillas_por_kg <= 0;
            if ($sinSemillas) {
                $estimado = CultivoSiembraCatalogo::semillasPorKgEstimado(
                    PedidoCatalogo::cultivoDesdeInsumo($insumo) ?: $insumo->nombre
                ) ?? $this->semillasExtraPorNombre($insumo->nombre);

                if ($estimado !== null && $estimado > 0) {
                    $cambios['semillas_por_kg'] = $estimado;
                }
            }

            if ($insumo->descripcion !== null && trim((string) $insumo->descripcion) !== '') {
                $cambios['descripcion'] = null;
            }

            if ($cambios !== []) {
                $insumo->update($cambios);
            }
        });
    }

    private function semillasExtraPorNombre(string $nombre): ?float
    {
        $key = mb_strtolower(trim($nombre));
        foreach (self::SEMILLAS_EXTRA as $fragmento => $valor) {
            if (str_contains($key, $fragmento)) {
                return $valor;
            }
        }

        return null;
    }

    public function down(): void
    {
        // Datos operativos; no reversible.
    }
};
