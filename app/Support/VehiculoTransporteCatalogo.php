<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Un vehículo opera con un solo tipo de transporte según su equipamiento real.
 */
final class VehiculoTransporteCatalogo
{
    /** @var list<string> De más específico a más general. */
    public const CODIGOS_PRIORIDAD = [
        'MULTITEMPERATURA',
        'REFRIGERADO',
        'ISOTERMICO',
        'CARGA_GENERAL',
    ];

    /**
     * @param  Collection<int, object{tipotransporteid: int, codigo?: string|null}>  $tipos
     */
    public static function idPrincipalDesdeColeccion(Collection $tipos): ?int
    {
        if ($tipos->isEmpty()) {
            return null;
        }

        foreach (self::CODIGOS_PRIORIDAD as $codigo) {
            $match = $tipos->first(fn ($t) => strtoupper((string) ($t->codigo ?? '')) === $codigo);
            if ($match) {
                return (int) $match->tipotransporteid;
            }
        }

        return (int) $tipos->first()->tipotransporteid;
    }

    /**
     * @return array{icon: string, tone: string, hint: string, nombre: string}
     */
    public static function metaUi(?string $codigo): array
    {
        return match (strtoupper((string) $codigo)) {
            'REFRIGERADO' => [
                'nombre' => 'Refrigerado',
                'icon' => 'fa-snowflake',
                'tone' => 'frio',
                'hint' => 'Equipo de frío activo para cadena de frío.',
            ],
            'MULTITEMPERATURA' => [
                'nombre' => 'Multitemperatura',
                'icon' => 'fa-layer-group',
                'tone' => 'multi',
                'hint' => 'Zonas con distintas temperaturas en el mismo viaje.',
            ],
            'ISOTERMICO' => [
                'nombre' => 'Isotérmico',
                'icon' => 'fa-thermometer-half',
                'tone' => 'iso',
                'hint' => 'Caja aislada sin refrigeración activa.',
            ],
            default => [
                'nombre' => 'Carga general',
                'icon' => 'fa-box',
                'tone' => 'general',
                'hint' => 'Mercancía estándar sin control térmico.',
            ],
        };
    }

    /** @return list<array{codigo: string, nombre: string}> */
    public static function modosReferencia(): array
    {
        return [
            ['codigo' => 'CARGA_GENERAL', 'nombre' => 'Carga general'],
            ['codigo' => 'ISOTERMICO', 'nombre' => 'Isotérmico'],
            ['codigo' => 'REFRIGERADO', 'nombre' => 'Refrigerado'],
            ['codigo' => 'MULTITEMPERATURA', 'nombre' => 'Multitemperatura'],
        ];
    }

    public static function codigoDesdeNombre(?string $nombre): ?string
    {
        if ($nombre === null || trim($nombre) === '') {
            return null;
        }

        $norm = mb_strtolower(trim($nombre));

        foreach (self::modosReferencia() as $modo) {
            if (mb_strtolower($modo['nombre']) === $norm) {
                return $modo['codigo'];
            }
        }

        return null;
    }

    public static function badgeClaseBootstrap(?string $codigo): string
    {
        return match (strtoupper((string) $codigo)) {
            'REFRIGERADO' => 'badge-primary',
            'ISOTERMICO' => 'badge-warning',
            'MULTITEMPERATURA' => 'badge-info',
            default => 'badge-secondary',
        };
    }

    /** @return array<string, string> */
    public static function opcionesTransporteProducto(): array
    {
        return [
            'CARGA_GENERAL' => 'Carga general',
            'ISOTERMICO' => 'Isotérmico',
            'REFRIGERADO' => 'Refrigerado',
        ];
    }

    public static function normalizarCodigoProducto(?string $codigo): string
    {
        $norm = strtoupper(trim((string) $codigo));

        return array_key_exists($norm, self::opcionesTransporteProducto())
            ? $norm
            : 'CARGA_GENERAL';
    }

    public static function prioridadRequisito(?string $codigo): int
    {
        return match (self::normalizarCodigoProducto($codigo)) {
            'REFRIGERADO' => 3,
            'ISOTERMICO' => 2,
            default => 1,
        };
    }

    /**
     * @param  list<string>  $codigosTransporteVehiculo
     */
    public static function vehiculoSatisfaceRequisito(?string $codigoRequerido, array $codigosTransporteVehiculo): bool
    {
        $req = self::normalizarCodigoProducto($codigoRequerido);

        if ($req === 'CARGA_GENERAL') {
            return true;
        }

        $veh = array_map(fn ($c) => strtoupper((string) $c), $codigosTransporteVehiculo);

        if (in_array('MULTITEMPERATURA', $veh, true)) {
            return true;
        }

        return match ($req) {
            'REFRIGERADO' => in_array('REFRIGERADO', $veh, true),
            'ISOTERMICO' => in_array('REFRIGERADO', $veh, true) || in_array('ISOTERMICO', $veh, true),
            default => true,
        };
    }
}
