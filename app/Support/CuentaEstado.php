<?php

namespace App\Support;

final class CuentaEstado
{
    public const PENDIENTE = 'pendiente';

    public const APROBADO = 'aprobado';

    /** @return list<string> */
    public static function rolesRegistroPublico(): array
    {
        return ['jefe_agricultor', 'jefe_planta', 'transportista', 'minorista'];
    }

    /** Rol operativo que crea un jefe al registrar empleados. */
    public static function rolEmpleadoDeJefe(string $rolJefe): ?string
    {
        return match ($rolJefe) {
            'jefe_agricultor' => 'agricultor',
            'jefe_planta' => 'planta',
            default => null,
        };
    }

    public static function etiqueta(?string $estado): string
    {
        return match ($estado) {
            self::PENDIENTE => 'Pendiente',
            default => 'Activo',
        };
    }

    public static function esPendiente(?string $estado): bool
    {
        return $estado === self::PENDIENTE;
    }

    public static function esActivo(?string $estado): bool
    {
        return $estado === null || $estado === self::APROBADO;
    }

    public static function puedeIniciarSesion(?string $estado, bool $activo): bool
    {
        return $activo && self::esActivo($estado);
    }
}
