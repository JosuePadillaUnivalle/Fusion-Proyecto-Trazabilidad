<?php

namespace App\Services;

use App\Models\CatalogoTamanoConteo;
use App\Models\Lote;
use App\Models\Produccion;
use App\Models\ProduccionAlmacenamiento;

/**
 * Convierte kg de cosecha en unidades y cajas según el calibre planificado.
 */
class CosechaPresentacionService
{
    public function __construct(
        private readonly AlmacenCapacidadService $capacidadService,
        private readonly PlanificacionCosechaService $planificacion,
    ) {}

    public static function etiquetaEmpaque(?string $nombreTipo): string
    {
        $n = mb_strtolower(trim($nombreTipo ?? ''));

        if ($n === '' || $n === 'empaque') {
            return 'Empaque';
        }
        if ($n === 'saco' || str_contains($n, 'saco')) {
            return 'Saco';
        }
        if (str_contains($n, 'canasta')) {
            return 'Canasta';
        }
        if (str_contains($n, 'caja')) {
            return 'Caja';
        }
        if (str_contains($n, 'bolsa')) {
            return 'Bolsa';
        }
        if (str_contains($n, 'bandeja')) {
            return 'Bandeja';
        }

        return $nombreTipo ?: 'Empaque';
    }

    public static function etiquetaEmpaquePlural(?string $nombreTipo): string
    {
        $singular = self::etiquetaEmpaque($nombreTipo);

        return match (mb_strtolower($singular)) {
            'caja' => 'Cajas',
            'saco' => 'Sacos',
            'canasta' => 'Canastas',
            'bolsa' => 'Bolsas',
            'bandeja' => 'Bandejas',
            default => $singular.'s',
        };
    }

    /** @return array<string, mixed> */
    public function desdeKg(float $kg, ?CatalogoTamanoConteo $calibre): array
    {
        if (! $calibre || (float) $calibre->peso_promedio_kg <= 0) {
            return [
                'ok' => false,
                'kg' => round($kg, 2),
                'unidades' => null,
                'empaques' => null,
                'empaque_label' => 'Cajas',
                'calibre_nombre' => null,
            ];
        }

        $pesoUnit = max(0.0001, (float) $calibre->peso_promedio_kg);
        $conteo = max(1, (int) $calibre->conteo_por_empaque);
        $unidades = (int) round($kg / $pesoUnit);
        $empaques = (int) ceil($unidades / $conteo);
        $empaqueLabel = self::etiquetaEmpaquePlural($calibre->tipoEmpaque?->nombre);

        return [
            'ok' => true,
            'kg' => round($kg, 2),
            'unidades' => $unidades,
            'empaques' => $empaques,
            'empaque_label' => $empaqueLabel,
            'empaque_singular' => self::etiquetaEmpaque($calibre->tipoEmpaque?->nombre),
            'calibre_nombre' => $calibre->nombre,
            'calibre_id' => (int) $calibre->catalogotamanoconteoid,
            'conteo_por_empaque' => $conteo,
            'peso_promedio_kg' => $pesoUnit,
            'resumen' => number_format($empaques, 0, ',', '.').' '.$empaqueLabel
                .' · '.number_format($unidades, 0, ',', '.').' unidades',
        ];
    }

    /** @return array<string, mixed> */
    public function paraProduccion(Produccion $produccion, ?Lote $lote = null): array
    {
        $produccion->loadMissing(['unidadMedida', 'lote.catalogoTamanoConteo.tipoEmpaque']);
        $lote = $lote ?? $produccion->lote;
        $lote?->loadMissing('catalogoTamanoConteo.tipoEmpaque');

        $kg = (float) ($produccion->cantidad_base ?? $this->capacidadService->convertirAKg(
            (float) $produccion->cantidad,
            $produccion->unidadMedida
        ));

        $calibre = $lote?->catalogoTamanoConteo;
        if (! $calibre && $lote?->insumosemillaid) {
            $calibre = $this->resolverCalibrePorDefecto((int) $lote->insumosemillaid);
        }

        return $this->desdeKg($kg, $calibre);
    }

    /** @return array<string, mixed> */
    public function paraAlmacenamiento(ProduccionAlmacenamiento $row): array
    {
        $row->loadMissing([
            'produccion.unidadMedida',
            'produccion.lote.catalogoTamanoConteo.tipoEmpaque',
            'catalogoTamanoConteo.tipoEmpaque',
            'unidadMedida',
        ]);

        if ($row->cantidad_empaques !== null && $row->cantidad_unidades !== null) {
            $calibre = $row->catalogoTamanoConteo ?? $row->produccion?->lote?->catalogoTamanoConteo;
            $empaqueLabel = self::etiquetaEmpaquePlural($calibre?->tipoEmpaque?->nombre);

            return [
                'ok' => true,
                'kg' => round((float) $this->capacidadService->convertirAKg((float) $row->cantidad, $row->unidadMedida), 2),
                'unidades' => (int) $row->cantidad_unidades,
                'empaques' => (int) $row->cantidad_empaques,
                'empaque_label' => $empaqueLabel,
                'empaque_singular' => self::etiquetaEmpaque($calibre?->tipoEmpaque?->nombre),
                'calibre_nombre' => $calibre?->nombre,
                'calibre_id' => $calibre ? (int) $calibre->catalogotamanoconteoid : null,
                'resumen' => number_format((int) $row->cantidad_empaques, 0, ',', '.').' '.$empaqueLabel
                    .' · '.number_format((int) $row->cantidad_unidades, 0, ',', '.').' unidades',
            ];
        }

        return $this->paraProduccion($row->produccion, $row->produccion?->lote);
    }

    private function resolverCalibrePorDefecto(int $insumoSemillaId): ?CatalogoTamanoConteo
    {
        $ctx = $this->planificacion->contexto($insumoSemillaId);
        $calibreId = $ctx['calibre_default_id'] ?? null;

        if (! $calibreId) {
            return null;
        }

        return CatalogoTamanoConteo::query()
            ->with('tipoEmpaque')
            ->find((int) $calibreId);
    }
}
