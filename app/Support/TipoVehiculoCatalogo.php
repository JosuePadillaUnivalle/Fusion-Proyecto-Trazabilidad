<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Catálogo operativo de tipos de vehículo (camioneta, camión PQ, camión GR).
 */
final class TipoVehiculoCatalogo
{
    /** @var list<string> Orden de presentación: pequeño → mediano → grande */
    public const CODIGOS_ORDEN = ['CAMIONETA', 'CAMION_PQ', 'CAMION_GR'];

    /** @var array<string, string> */
    public const TAMANO_POR_CODIGO = [
        'CAMIONETA' => 'pequeno',
        'CAMION_PQ' => 'mediano',
        'CAMION_GR' => 'grande',
    ];

    /** @var array<string, array{icon: string, tone: string}> */
    public const META_UI = [
        'CAMIONETA' => ['icon' => 'fa-truck-pickup', 'tone' => 'pequeno'],
        'CAMION_PQ' => ['icon' => 'fa-truck', 'tone' => 'mediano'],
        'CAMION_GR' => ['icon' => 'fa-shipping-fast', 'tone' => 'grande'],
    ];

    public static function indiceOrden(?string $codigo): int
    {
        $pos = array_search(strtoupper((string) $codigo), self::CODIGOS_ORDEN, true);

        return $pos === false ? 99 : $pos;
    }

    /**
     * @param  Collection<int, \App\Models\TipoVehiculo>  $tipos
     * @return Collection<int, \App\Models\TipoVehiculo>
     */
    public static function ordenar(Collection $tipos): Collection
    {
        return $tipos
            ->filter(fn ($t) => ! in_array(strtoupper((string) ($t->codigo ?? '')), ['FURGONETA'], true))
            ->sortBy(fn ($t) => self::indiceOrden($t->codigo))
            ->values();
    }

    /**
     * @return array{icon: string, tone: string}
     */
    public static function metaUi(?string $codigo): array
    {
        return self::META_UI[strtoupper((string) $codigo)] ?? ['icon' => 'fa-truck', 'tone' => 'pequeno'];
    }
}
