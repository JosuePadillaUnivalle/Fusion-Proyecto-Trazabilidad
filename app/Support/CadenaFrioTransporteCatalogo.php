<?php

namespace App\Support;

/**
 * Sugerencia de tipo de transporte según el trayecto del envío.
 */
final class CadenaFrioTransporteCatalogo
{
    public const CODIGO_GENERAL = 'CARGA_GENERAL';

    public const CODIGO_REFRIGERADO = 'REFRIGERADO';

    public static function codigoPorTrayecto(?string $trayecto): string
    {
        return match ($trayecto) {
            'mayorista', 'punto-venta' => self::CODIGO_REFRIGERADO,
            default => self::CODIGO_GENERAL,
        };
    }

    /**
     * @param  list<string>  $codigosTransporteVehiculo
     */
    public static function vehiculoCumpleRequisito(?string $codigoRequerido, array $codigosTransporteVehiculo): bool
    {
        return VehiculoTransporteCatalogo::vehiculoSatisfaceRequisito(
            $codigoRequerido,
            $codigosTransporteVehiculo
        );
    }

    /**
     * @param  list<string>  $codigosTransporteVehiculo
     */
    public static function advertenciaTermica(
        ?string $nombreInsumo,
        array $codigosTransporteVehiculo,
        ?int $insumoId = null
    ): ?string {
        return null;
    }
}
