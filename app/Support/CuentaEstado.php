<?php

namespace App\Support;

final class CuentaEstado
{
    public const PENDIENTE = 'pendiente';

    public const APROBADO = 'aprobado';

    /** @return list<string> */
    public static function rolesRegistroPublico(): array
    {
        return ['agricultor', 'planta', 'transportista'];
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
