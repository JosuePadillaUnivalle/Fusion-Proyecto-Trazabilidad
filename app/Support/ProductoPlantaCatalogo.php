<?php

namespace App\Support;

use App\Models\LoteProduccionPedido;
use App\Models\UnidadMedida;
use App\Services\AlmacenCapacidadService;
use Illuminate\Support\Str;

class ProductoPlantaCatalogo
{
    /** Peso neto por envase de puré (kg) — ~300 g */
    public const PESO_KG_POR_UNIDAD_PURE = 0.3;

    /** Merma estimada al transformar materia prima en puré (pelado, cocción, etc.) */
    public const RENDIMIENTO_PURE = 0.85;

    public static function esProductoPorUnidades(?string $producto): bool
    {
        if ($producto === null || trim($producto) === '') {
            return false;
        }

        $lower = Str::lower(trim($producto));

        return Str::contains($lower, 'puré')
            || Str::contains($lower, 'pure');
    }

    public static function pesoKgPorUnidad(?string $producto): ?float
    {
        return self::esProductoPorUnidades($producto) ? self::PESO_KG_POR_UNIDAD_PURE : null;
    }

    public static function rendimiento(?string $producto): float
    {
        return self::esProductoPorUnidades($producto) ? self::RENDIMIENTO_PURE : 1.0;
    }

    public static function unidadMedidaIdPorDefecto(?string $producto): ?int
    {
        if (! self::esProductoPorUnidades($producto)) {
            return null;
        }

        return UnidadMedida::query()
            ->where(function ($q) {
                $q->whereRaw("LOWER(TRIM(COALESCE(abreviatura, ''))) = ?", ['und'])
                    ->orWhereRaw('LOWER(TRIM(nombre)) = ?', ['unidad']);
            })
            ->value('unidadmedidaid');
    }

    public static function unidadEtiqueta(?string $producto, ?UnidadMedida $unidad): string
    {
        if (self::esProductoPorUnidades($producto)) {
            return 'und';
        }

        return $unidad?->abreviatura ?? $unidad?->nombre ?? 'kg';
    }

    public static function resolverUnidadMedidaId(?string $producto, ?int $unidadmedidaid): ?int
    {
        $defecto = self::unidadMedidaIdPorDefecto($producto);

        if ($defecto === null) {
            return $unidadmedidaid;
        }

        if ($unidadmedidaid === null) {
            return $defecto;
        }

        $unidad = UnidadMedida::query()->find($unidadmedidaid);
        if ($unidad === null) {
            return $defecto;
        }

        $abbr = Str::lower(trim($unidad->abreviatura ?? $unidad->nombre ?? ''));
        if (str_contains($abbr, 'kg') || str_contains($abbr, 'kilogramo')) {
            return $defecto;
        }

        return $unidadmedidaid;
    }

    public static function nombreProducto(LoteProduccionPedido $lote): string
    {
        return LoteProduccionNombre::productoDesdeLote($lote);
    }

    public static function masaMateriasPrimasKg(LoteProduccionPedido $lote, AlmacenCapacidadService $capacidad): float
    {
        $lote->loadMissing('materiasPrimas.insumo.unidadMedida');

        return (float) $lote->materiasPrimas->sum(function ($mp) use ($capacidad) {
            return $capacidad->convertirAKg(
                (float) $mp->cantidad_usada,
                $mp->insumo?->unidadMedida
            );
        });
    }

    public static function kgProducidos(LoteProduccionPedido $lote, AlmacenCapacidadService $capacidad): float
    {
        $entradaKg = self::masaMateriasPrimasKg($lote, $capacidad);
        if ($entradaKg <= 0) {
            return 0.0;
        }

        return round($entradaKg * self::rendimiento(self::nombreProducto($lote)), 3);
    }

    public static function unidadesProducidas(LoteProduccionPedido $lote, AlmacenCapacidadService $capacidad): int
    {
        $producto = self::nombreProducto($lote);
        $pesoUnd = self::pesoKgPorUnidad($producto);
        $kg = self::kgProducidos($lote, $capacidad);

        if ($pesoUnd === null || $pesoUnd <= 0 || $kg <= 0) {
            return (int) floor($kg);
        }

        return (int) floor($kg / $pesoUnd);
    }

    /**
     * Cantidad y peso coherentes con la materia prima consumida.
     *
     * @return array{cantidad: float, kg: float, unidad: string, entrada_kg: float, rendimiento: float}
     */
    public static function resumenProduccion(LoteProduccionPedido $lote, AlmacenCapacidadService $capacidad): array
    {
        $producto = self::nombreProducto($lote);
        $entradaKg = self::masaMateriasPrimasKg($lote, $capacidad);
        $salidaKg = self::kgProducidos($lote, $capacidad);
        $rendimiento = self::rendimiento($producto);

        if (self::esProductoPorUnidades($producto)) {
            $und = self::unidadesProducidas($lote, $capacidad);

            return [
                'cantidad' => (float) $und,
                'kg' => $salidaKg,
                'unidad' => 'und',
                'entrada_kg' => $entradaKg,
                'rendimiento' => $rendimiento,
            ];
        }

        $cantidad = (float) ($lote->cantidad_objetivo ?? 0);
        if ($cantidad <= 0 && $salidaKg > 0) {
            $cantidad = $salidaKg;
        }

        return [
            'cantidad' => $cantidad,
            'kg' => $salidaKg > 0 ? $salidaKg : $cantidad,
            'unidad' => $lote->unidadMedida?->abreviatura ?? $lote->unidadMedida?->nombre ?? 'kg',
            'entrada_kg' => $entradaKg,
            'rendimiento' => $rendimiento,
        ];
    }

    public static function cantidadParaAlmacenaje(LoteProduccionPedido $lote, AlmacenCapacidadService $capacidad): float
    {
        $resumen = self::resumenProduccion($lote, $capacidad);

        if ($resumen['kg'] <= 0 && $resumen['cantidad'] <= 0) {
            return (float) ($lote->cantidad_objetivo ?? 0);
        }

        return $resumen['cantidad'];
    }

    public static function kgParaAlmacenaje(LoteProduccionPedido $lote, AlmacenCapacidadService $capacidad): float
    {
        $resumen = self::resumenProduccion($lote, $capacidad);

        return $resumen['kg'] > 0 ? $resumen['kg'] : (float) ($lote->cantidad_objetivo ?? 0);
    }

    /**
     * Materia prima en kg para alcanzar un objetivo en unidades (purés).
     *
     * @return array{unidades: float, salida_kg: float, entrada_kg: float, rendimiento: float, peso_kg_por_unidad: float}|null
     */
    public static function recomendacionMateriaPrima(?string $producto, float $unidadesObjetivo): ?array
    {
        if (! self::esProductoPorUnidades($producto) || $unidadesObjetivo <= 0) {
            return null;
        }

        $pesoUnd = self::PESO_KG_POR_UNIDAD_PURE;
        $rendimiento = self::RENDIMIENTO_PURE;
        $salidaKg = $unidadesObjetivo * $pesoUnd;
        $entradaKg = $salidaKg / $rendimiento;

        return [
            'unidades' => $unidadesObjetivo,
            'salida_kg' => round($salidaKg, 2),
            'entrada_kg' => round($entradaKg, 2),
            'rendimiento' => $rendimiento,
            'peso_kg_por_unidad' => $pesoUnd,
        ];
    }
}
