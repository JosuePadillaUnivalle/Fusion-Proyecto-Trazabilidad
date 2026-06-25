<?php

namespace App\Support;

use App\Models\Lote;
use Illuminate\Database\Eloquent\Builder;

/** KPIs de lotes reutilizables (mapa, índice, etc.). */
final class LoteKpiStats
{
    /**
     * @param  Builder<Lote>  $query  Query ya acotada al usuario / filtros.
     * @return array{total: int, en_produccion: int, cosechados: int, hectareas: float, en_mapa: int}
     */
    public static function desdeQuery(Builder $query, bool $incluirEnMapa = false): array
    {
        $stats = [
            'total' => (clone $query)->count(),
            'en_produccion' => EstadoLoteCatalogo::scopeKpiEnProduccion(clone $query)->count(),
            'cosechados' => EstadoLoteCatalogo::scopeKpiCosechados(clone $query)->count(),
            'hectareas' => round((float) ((clone $query)->sum('superficie') ?? 0), 2),
        ];

        if ($incluirEnMapa) {
            $stats['en_mapa'] = (clone $query)
                ->whereNotNull('latitud')
                ->whereNotNull('longitud')
                ->count();
        }

        return $stats;
    }
}
