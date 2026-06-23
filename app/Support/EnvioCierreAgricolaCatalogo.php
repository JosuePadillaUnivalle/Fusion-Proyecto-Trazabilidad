<?php

namespace App\Support;

final class EnvioCierreAgricolaCatalogo
{
    public const ESTADO_VEHICULO_PERFECTO = 'vehiculo_perfecto';

    public const ESTADO_VEHICULO_REVISADO = 'vehiculo_revisado';

    public const PASO_CONDICIONES = 'condiciones';

    public const PASO_EN_RUTA = 'en_ruta';

    public const PASO_ESPERA_LLEGADA = 'espera_llegada';

    public const PASO_INCIDENTES = 'incidentes';

    public const PASO_FIRMA_TRANSPORTISTA = 'firma_transportista';

    public const PASO_FIRMA_RECEPCION = 'firma_recepcion';

    public const PASO_COMPLETADO = 'completado';

    /** @return list<string> */
    public static function pasosOrdenados(): array
    {
        return [
            self::PASO_CONDICIONES,
            self::PASO_EN_RUTA,
            self::PASO_ESPERA_LLEGADA,
            self::PASO_INCIDENTES,
            self::PASO_FIRMA_TRANSPORTISTA,
            self::PASO_FIRMA_RECEPCION,
            self::PASO_COMPLETADO,
        ];
    }

    public static function etiquetaPaso(string $paso): string
    {
        return match ($paso) {
            self::PASO_CONDICIONES => 'Condiciones del vehículo',
            self::PASO_EN_RUTA => 'En ruta',
            self::PASO_ESPERA_LLEGADA => 'Confirmar llegada',
            self::PASO_INCIDENTES => 'Incidentes de transporte',
            self::PASO_FIRMA_TRANSPORTISTA => 'Firma transportista',
            self::PASO_FIRMA_RECEPCION => 'Firma recepción',
            self::PASO_COMPLETADO => 'Entrega finalizada',
            default => ucfirst(str_replace('_', ' ', $paso)),
        };
    }

    public static function etiquetaEstadoVehiculo(?string $estado): string
    {
        return match ($estado) {
            self::ESTADO_VEHICULO_PERFECTO => 'Vehículo en perfectas condiciones',
            self::ESTADO_VEHICULO_REVISADO => 'Vehículo revisado con observaciones',
            default => '—',
        };
    }

    public static function iconoPaso(string $paso): string
    {
        return match ($paso) {
            self::PASO_CONDICIONES => 'fa-clipboard-check',
            self::PASO_EN_RUTA => 'fa-shipping-fast',
            self::PASO_ESPERA_LLEGADA => 'fa-map-marker-alt',
            self::PASO_INCIDENTES => 'fa-exclamation-triangle',
            self::PASO_FIRMA_TRANSPORTISTA => 'fa-signature',
            self::PASO_FIRMA_RECEPCION => 'fa-file-signature',
            self::PASO_COMPLETADO => 'fa-check-double',
            default => 'fa-circle',
        };
    }

    /** @param  array<string, mixed>  $resumen */
    public static function pasoEstaCompletado(string $paso, array $resumen): bool
    {
        return match ($paso) {
            self::PASO_CONDICIONES => array_key_exists('condiciones_vigentes', $resumen)
                ? (bool) $resumen['condiciones_vigentes']
                : (bool) ($resumen['tiene_condiciones'] ?? false),
            self::PASO_EN_RUTA => (bool) ($resumen['en_ruta'] ?? false)
                || (bool) ($resumen['llegada_confirmada'] ?? false)
                || (bool) ($resumen['tiene_incidentes'] ?? false)
                || (bool) ($resumen['firma_transportista'] ?? false)
                || (bool) ($resumen['recibido_planta'] ?? false),
            self::PASO_ESPERA_LLEGADA => (bool) ($resumen['llegada_confirmada'] ?? false)
                || (bool) ($resumen['tiene_incidentes'] ?? false)
                || (bool) ($resumen['firma_transportista'] ?? false)
                || (bool) ($resumen['recibido_planta'] ?? false),
            self::PASO_INCIDENTES => (bool) ($resumen['tiene_incidentes'] ?? false)
                || (bool) ($resumen['firma_transportista'] ?? false)
                || (bool) ($resumen['recibido_planta'] ?? false),
            self::PASO_FIRMA_TRANSPORTISTA => (bool) ($resumen['firma_transportista'] ?? false)
                || (bool) ($resumen['recibido_planta'] ?? false),
            self::PASO_FIRMA_RECEPCION => (bool) ($resumen['firma_recepcion'] ?? false)
                || (bool) ($resumen['recibido_planta'] ?? false),
            self::PASO_COMPLETADO => (bool) ($resumen['recibido_planta'] ?? false)
                || (bool) ($resumen['recibido_pdv'] ?? false),
            default => false,
        };
    }

    /** @param  array<string, mixed>  $resumen
     * @return list<string>
     */
    public static function pasosCompletados(array $resumen): array
    {
        return array_values(array_filter(
            self::pasosOrdenados(),
            fn (string $paso) => self::pasoEstaCompletado($paso, $resumen)
        ));
    }
}
