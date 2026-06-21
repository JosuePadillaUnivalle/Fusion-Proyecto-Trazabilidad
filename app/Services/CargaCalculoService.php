<?php

namespace App\Services;

use App\Models\CatalogoTamanoConteo;
use App\Models\TipoEmpaque;

/**
 * Cálculos de carga para envíos de verduras (empaques, pesos, pallets).
 */
class CargaCalculoService
{
    /**
     * @param  array{
     *   conteo_por_empaque?: int|null,
     *   peso_promedio_kg?: float|null,
     *   largo_cm?: float|null,
     *   ancho_cm?: float|null,
     *   alto_cm?: float|null,
     *   tara_kg?: float|null,
     *   capacidad_unidades?: int|null,
     *   unidades_por_pallet?: int|null,
     *   forma_pedido?: string|null,
     *   cantidad_pedido?: float|null,
     * }  $input
     * @return array{
     *   peso_neto_kg: float,
     *   peso_bruto_kg: float,
     *   empaques_calculados: int,
     *   numero_pallets: int,
     *   capacidad_por_empaque: int,
     *   volumen_empaque_m3: float|null,
     * }
     */
    public function calcular(array $input): array
    {
        $conteo = max(1, (int) ($input['conteo_por_empaque'] ?? 1));
        $pesoUnit = max(0.0, (float) ($input['peso_promedio_kg'] ?? 0));
        $tara = max(0.0, (float) ($input['tara_kg'] ?? 0));
        $capacidadStd = (int) ($input['capacidad_unidades'] ?? $conteo);
        $capacidadEmpaque = $conteo > 0 ? $conteo : max(1, $capacidadStd);
        $unidadesPorPallet = max(1, (int) ($input['unidades_por_pallet'] ?? 48));

        $forma = (string) ($input['forma_pedido'] ?? 'empaques');
        $cantidad = max(0.0, (float) ($input['cantidad_pedido'] ?? 0));

        $empaques = match ($forma) {
            'unidades' => $conteo > 0 ? (int) ceil($cantidad / $conteo) : 0,
            default => (int) ceil($cantidad),
        };

        $unidadesTotales = match ($forma) {
            'unidades' => (int) ceil($cantidad),
            default => $empaques * $conteo,
        };

        $pesoNeto = round($unidadesTotales * $pesoUnit, 3);
        $pesoBruto = round($pesoNeto + ($empaques * $tara), 3);
        $pallets = $unidadesPorPallet > 0 ? (int) ceil($empaques / $unidadesPorPallet) : 0;

        $largo = isset($input['largo_cm']) ? (float) $input['largo_cm'] : null;
        $ancho = isset($input['ancho_cm']) ? (float) $input['ancho_cm'] : null;
        $alto = isset($input['alto_cm']) ? (float) $input['alto_cm'] : null;
        $volumen = ($largo && $ancho && $alto)
            ? round(($largo / 100) * ($ancho / 100) * ($alto / 100), 4)
            : null;

        return [
            'peso_neto_kg' => $pesoNeto,
            'peso_bruto_kg' => $pesoBruto,
            'empaques_calculados' => $empaques,
            'numero_pallets' => $pallets,
            'capacidad_por_empaque' => $capacidadEmpaque,
            'volumen_empaque_m3' => $volumen,
            'unidades_totales' => $unidadesTotales,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function desdeCatalogos(?int $tamanoConteoId, ?int $tipoEmpaqueId, string $formaPedido, float $cantidad): array
    {
        $tamano = $tamanoConteoId
            ? CatalogoTamanoConteo::query()->with('tipoEmpaque')->find($tamanoConteoId)
            : null;

        $empaque = $tipoEmpaqueId
            ? TipoEmpaque::query()->find($tipoEmpaqueId)
            : ($tamano?->tipoEmpaque);

        return $this->calcular([
            'conteo_por_empaque' => $tamano?->conteo_por_empaque,
            'peso_promedio_kg' => $tamano?->peso_promedio_kg,
            'largo_cm' => $empaque?->largo_cm,
            'ancho_cm' => $empaque?->ancho_cm,
            'alto_cm' => $empaque?->alto_cm,
            'tara_kg' => $empaque?->tara_kg,
            'capacidad_unidades' => $empaque?->capacidad_unidades,
            'unidades_por_pallet' => $empaque?->unidades_por_pallet,
            'forma_pedido' => $formaPedido,
            'cantidad_pedido' => $cantidad,
        ]);
    }

    /**
     * Estima cuántos empaques caben en un vehículo según peso y volumen.
     */
    public function empaquesMaximosEnVehiculo(float $capacidadKg, float $capacidadM3, float $pesoBrutoEmpaque, ?float $volumenEmpaqueM3): int
    {
        if ($pesoBrutoEmpaque <= 0) {
            return 0;
        }

        $porPeso = (int) floor($capacidadKg / $pesoBrutoEmpaque);
        $porVolumen = ($volumenEmpaqueM3 && $volumenEmpaqueM3 > 0 && $capacidadM3 > 0)
            ? (int) floor($capacidadM3 / $volumenEmpaqueM3)
            : $porPeso;

        return max(0, min($porPeso, $porVolumen));
    }
}
