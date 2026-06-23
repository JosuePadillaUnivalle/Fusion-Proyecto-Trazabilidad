<?php

namespace App\Services;

use App\Models\Insumo;
use App\Support\CultivoSiembraCatalogo;
use App\Support\PedidoCatalogo;

/**
 * Planificación bidireccional: hectáreas ↔ cosecha estimada ↔ semilla.
 */
class PlanificacionCosechaService
{
    public const MODO_HECTAREAS = 'hectareas';
    public const MODO_UNIDADES = 'unidades';
    public const MODO_EMPAQUES = 'empaques';

    /** @return array<string, mixed> */
    public function contexto(int $insumoId): array
    {
        $insumo = Insumo::query()->with(['unidadMedida', 'tipo'])->find($insumoId);
        if (! $insumo) {
            return ['ok' => false, 'mensaje' => 'Semilla no encontrada.'];
        }

        $dosis = CultivoSiembraCatalogo::sugerenciaParaInsumo($insumo, 1.0);
        $rendimientoKgHa = CultivoSiembraCatalogo::rendimientoCosechaKgHaDesdeInsumo($insumo);
        $calibres = $this->calibresParaInsumo($insumoId);

        return [
            'ok' => true,
            'insumo' => [
                'id' => $insumo->insumoid,
                'nombre' => $insumo->nombre,
                'cultivo' => \App\Support\PedidoCatalogo::cultivoDesdeInsumo($insumo),
            ],
            'dosis_por_ha' => $dosis['por_ha'],
            'dosis_unidad' => $dosis['unidad'],
            'tiene_dosis' => $dosis['tiene_dosis'],
            'rendimiento_kg_ha' => $rendimientoKgHa,
            'tiene_rendimiento' => $rendimientoKgHa !== null && $rendimientoKgHa > 0,
            'calibres' => $calibres,
            'calibre_default_id' => $this->calibreDefaultId($calibres),
        ];
    }

    /**
     * @param  array{
     *   modo?: string,
     *   insumoid?: int,
     *   calibre_id?: int|null,
     *   hectareas?: float|null,
     *   objetivo_unidades?: float|null,
     *   objetivo_empaques?: float|null,
     * }  $input
     * @return array<string, mixed>
     */
    public function calcular(array $input): array
    {
        $insumoId = (int) ($input['insumoid'] ?? 0);
        if ($insumoId <= 0) {
            return ['ok' => false, 'mensaje' => 'Seleccione una semilla / cultivo.'];
        }

        $ctx = $this->contexto($insumoId);
        if (! ($ctx['ok'] ?? false)) {
            return $ctx;
        }

        $calibre = $this->resolverCalibre($insumoId, isset($input['calibre_id']) ? (int) $input['calibre_id'] : null);
        if (! $calibre) {
            return ['ok' => false, 'mensaje' => 'No hay calibre de cosecha configurado para este producto. Regístrelo en Catálogos → Tamaño / conteo.'];
        }

        $rendimiento = (float) ($ctx['rendimiento_kg_ha'] ?? 0);
        if ($rendimiento <= 0) {
            return ['ok' => false, 'mensaje' => 'No hay rendimiento de cosecha (kg/ha) para este cultivo.'];
        }

        $dosisPorHa = (float) ($ctx['dosis_por_ha'] ?? 0);
        $pesoUnit = max(0.0001, (float) $calibre['peso_promedio_kg']);
        $conteoEmpaque = max(1, (int) $calibre['conteo_por_empaque']);

        $modo = (string) ($input['modo'] ?? self::MODO_HECTAREAS);
        if (! in_array($modo, [self::MODO_HECTAREAS, self::MODO_UNIDADES, self::MODO_EMPAQUES], true)) {
            $modo = self::MODO_HECTAREAS;
        }

        $hectareas = 0.0;
        $unidades = 0.0;
        $empaques = 0;

        if ($modo === self::MODO_UNIDADES) {
            $unidades = max(0.0, (float) ($input['objetivo_unidades'] ?? 0));
            if ($unidades <= 0) {
                return ['ok' => false, 'mensaje' => 'Indique cuántas unidades desea cosechar.'];
            }
            $unidades = (int) round($unidades);
            $kgCosecha = round($unidades * $pesoUnit, 2);
            $hectareas = round($kgCosecha / $rendimiento, 3);
            $empaques = (int) ceil($unidades / $conteoEmpaque);
        } elseif ($modo === self::MODO_EMPAQUES) {
            $empaques = max(0, (int) ceil((float) ($input['objetivo_empaques'] ?? 0)));
            if ($empaques <= 0) {
                return ['ok' => false, 'mensaje' => 'Indique cuántos empaques (cajas/sacas) desea obtener.'];
            }
            $unidades = $empaques * $conteoEmpaque;
            $kgCosecha = round($unidades * $pesoUnit, 2);
            $hectareas = round($kgCosecha / $rendimiento, 3);
        } else {
            $hectareas = max(0.0, (float) ($input['hectareas'] ?? 0));
            if ($hectareas <= 0) {
                return ['ok' => false, 'mensaje' => 'Indique la superficie en hectáreas.'];
            }
            $hectareas = round($hectareas, 3);
            $kgCosecha = round($rendimiento * $hectareas, 2);
            $unidades = (int) round($kgCosecha / $pesoUnit);
            $empaques = (int) ceil($unidades / $conteoEmpaque);
        }

        $semilla = $dosisPorHa > 0 ? round($dosisPorHa * $hectareas, 3) : null;

        return [
            'ok' => true,
            'modo' => $modo,
            'hectareas' => $hectareas,
            'semilla_cantidad' => $semilla,
            'semilla_unidad' => $ctx['dosis_unidad'] ?? 'kg',
            'kg_cosecha_estimados' => $kgCosecha,
            'unidades_estimadas' => $unidades,
            'empaques_estimados' => $empaques,
            'rendimiento_kg_ha' => $rendimiento,
            'dosis_por_ha' => $dosisPorHa,
            'calibre' => $calibre,
            'resumen' => $this->armarResumen($modo, $hectareas, $unidades, $empaques, $semilla, $ctx['dosis_unidad'] ?? 'kg', $calibre),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function calibresParaInsumo(int $insumoId): array
    {
        return array_map(
            fn (array $c) => [
                'id' => $c['id'],
                'nombre' => $c['nombre'],
                'peso_promedio_kg' => $c['peso_promedio_kg'],
                'conteo_por_empaque' => $c['conteo_por_empaque'],
                'empaque' => $c['tipo_empaque'] ?? 'Empaque',
                'empaque_label' => CosechaPresentacionService::etiquetaEmpaquePlural($c['tipo_empaque'] ?? null),
            ],
            PedidoCatalogo::listarCalibresParaProducto('insumo:'.$insumoId)
        );
    }

    /** @return array<string, mixed>|null */
    private function resolverCalibre(int $insumoId, ?int $calibreId): ?array
    {
        $calibres = $this->calibresParaInsumo($insumoId);
        if ($calibres === []) {
            return null;
        }

        if ($calibreId) {
            foreach ($calibres as $c) {
                if ((int) $c['id'] === $calibreId) {
                    return $c;
                }
            }
        }

        return $calibres[0];
    }

    /** @param  list<array<string, mixed>>  $calibres */
    private function calibreDefaultId(array $calibres): ?int
    {
        if ($calibres === []) {
            return null;
        }

        foreach ($calibres as $c) {
            if (stripos((string) ($c['nombre'] ?? ''), 'median') !== false) {
                return (int) $c['id'];
            }
        }

        $mid = (int) floor(count($calibres) / 2);

        return (int) ($calibres[$mid]['id'] ?? $calibres[0]['id']);
    }

    /** @param  array<string, mixed>  $calibre */
    private function armarResumen(
        string $modo,
        float $ha,
        int $unidades,
        int $empaques,
        ?float $semilla,
        string $semillaUnidad,
        array $calibre
    ): string {
        $empaqueNombre = $calibre['empaque_label'] ?? CosechaPresentacionService::etiquetaEmpaquePlural($calibre['empaque'] ?? null);
        $semillaTxt = $semilla !== null
            ? number_format($semilla, 2, ',', '.').' '.$semillaUnidad.' de semilla'
            : 'semilla según dosis';

        $cosechaTxt = number_format($unidades, 0, ',', '.').' unidades (~'
            .number_format($empaques, 0, ',', '.').' '.$empaqueNombre.')';

        $haTxt = number_format($ha, 2, ',', '.').' ha';

        return match ($modo) {
            self::MODO_UNIDADES, self::MODO_EMPAQUES => "Para su meta necesita {$haTxt}, {$semillaTxt} y cosechará aprox. {$cosechaTxt}.",
            default => "Con {$haTxt} cosechará aprox. {$cosechaTxt} y necesitará {$semillaTxt}.",
        };
    }

    /** @return array<string, mixed>|null */
    public function estimacionDesdeLote(\App\Models\Lote $lote): ?array
    {
        $lote->loadMissing(['insumoSemilla', 'catalogoTamanoConteo.tipoEmpaque']);

        if (! $lote->insumosemillaid || (float) $lote->superficie <= 0) {
            return null;
        }

        $resultado = $this->calcular([
            'modo' => self::MODO_HECTAREAS,
            'insumoid' => (int) $lote->insumosemillaid,
            'calibre_id' => $lote->catalogotamanoconteoid ? (int) $lote->catalogotamanoconteoid : null,
            'hectareas' => (float) $lote->superficie,
        ]);

        if (! ($resultado['ok'] ?? false)) {
            return null;
        }

        $calibre = $resultado['calibre'] ?? [];
        $conteo = (int) ($calibre['conteo_por_empaque'] ?? 0);
        $empaqueLabel = CosechaPresentacionService::etiquetaEmpaquePlural($calibre['empaque'] ?? null);

        $resultado['unidades_por_caja'] = $conteo > 0 ? $conteo : null;
        $resultado['empaque_label'] = $empaqueLabel;
        $resultado['calibre_nombre'] = $calibre['nombre'] ?? null;

        return $resultado;
    }

    /** Resumen compacto para formularios (registrar cosecha, selector de lotes). */
    public function estimacionUiDesdeLote(\App\Models\Lote $lote): ?array
    {
        $est = $this->estimacionDesdeLote($lote);
        if (! $est || ! ($est['ok'] ?? false)) {
            return null;
        }

        return [
            'kg_cosecha_estimados' => (float) ($est['kg_cosecha_estimados'] ?? 0),
            'unidades_estimadas' => (int) ($est['unidades_estimadas'] ?? 0),
            'empaques_estimados' => (int) ($est['empaques_estimados'] ?? 0),
            'empaque_label' => $est['empaque_label'] ?? 'Cajas',
            'unidades_por_caja' => $est['unidades_por_caja'] ?? null,
            'calibre_nombre' => $est['calibre_nombre'] ?? null,
            'peso_promedio_kg' => isset($est['calibre']['peso_promedio_kg'])
                ? (float) $est['calibre']['peso_promedio_kg']
                : null,
        ];
    }
}
