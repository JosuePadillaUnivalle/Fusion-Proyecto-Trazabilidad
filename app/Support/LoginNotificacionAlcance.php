<?php

namespace App\Support;

final class LoginNotificacionAlcance
{
    public const TRANSPORTISTA = 'transportista';

    public const OPERARIO_PLANTA = 'operario_planta';

    public const JEFE_PLANTA_TRASLADO = 'jefe_planta_traslado';

    public const MAYORISTA = 'mayorista';

    public const AGRICULTOR = 'agricultor';

    public const JEFE_AGRICULTOR = 'jefe_agricultor';

    public const MINORISTA = 'minorista';

    /** @return list<string> */
    public static function todos(): array
    {
        return [
            self::TRANSPORTISTA,
            self::OPERARIO_PLANTA,
            self::JEFE_PLANTA_TRASLADO,
            self::MAYORISTA,
            self::AGRICULTOR,
            self::JEFE_AGRICULTOR,
            self::MINORISTA,
        ];
    }

    /**
     * @param  list<array{clave: string}>  $items
     * @return list<array{clave: string}>
     */
    public static function filtrarPendientes(string $alcance, int $usuarioid, array $items): array
    {
        return self::registro($alcance)->filtrar($usuarioid, $items);
    }

    /** @param  list<string>  $claves */
    public static function marcarVistas(string $alcance, int $usuarioid, array $claves): void
    {
        self::registro($alcance)->marcarClaves($usuarioid, $claves);
    }

    private static function registro(string $alcance): LoginNotificacionModalRegistro
    {
        return match ($alcance) {
            self::TRANSPORTISTA => new LoginNotificacionModalRegistro('transportista_asignacion_modal_vistas'),
            self::OPERARIO_PLANTA => new LoginNotificacionModalRegistro('operario_planta_tarea_modal_vistas'),
            self::JEFE_PLANTA_TRASLADO => new LoginNotificacionModalRegistro('jefe_planta_traslado_modal_vistas'),
            self::MAYORISTA => new LoginNotificacionModalRegistro('mayorista_pedido_modal_vistas'),
            self::AGRICULTOR => new LoginNotificacionModalRegistro('agricultor_actividad_modal_vistas'),
            self::JEFE_AGRICULTOR => new LoginNotificacionModalRegistro('jefe_agricultor_pedido_modal_vistas'),
            self::MINORISTA => new LoginNotificacionModalRegistro('minorista_pedido_modal_vistas'),
            default => throw new \InvalidArgumentException("Alcance de notificación desconocido: {$alcance}"),
        };
    }
}
