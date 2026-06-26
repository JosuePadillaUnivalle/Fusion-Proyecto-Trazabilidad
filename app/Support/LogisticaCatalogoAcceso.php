<?php

namespace App\Support;

use App\Models\Usuario;

/**
 * Visibilidad y permisos de edición por catálogo logístico y rol.
 */
final class LogisticaCatalogoAcceso
{
    public static function puedeVer(?Usuario $user): bool
    {
        if ($user === null) {
            return false;
        }

        if (UsuarioRol::esAdminGlobal($user)) {
            return true;
        }

        if ($user->hasRole('transportista')) {
            return false;
        }

        return $user->hasAnyRole(['jefe_agricultor', 'jefe_planta', 'mayorista', 'jefe_mayorista'])
            || $user->can('envios.view');
    }

    public static function puedeGestionar(?Usuario $user, string $tipo): bool
    {
        if ($user === null) {
            return false;
        }

        if (UsuarioRol::esAdminGlobal($user)) {
            return true;
        }

        return match ($tipo) {
            'tipos-empaque', 'condiciones', 'incidentes' => false,
            'tamano-conteo' => $user->hasRole('jefe_agricultor'),
            'tipos-vehiculo', 'tipos-transporte' => $user->hasAnyRole([
                'jefe_agricultor',
                'jefe_planta',
                'mayorista',
                'jefe_mayorista',
            ]),
            default => false,
        };
    }

    public static function puedeCrear(?Usuario $user, string $tipo): bool
    {
        return self::puedeGestionar($user, $tipo);
    }

    public static function puedeEditar(?Usuario $user, string $tipo): bool
    {
        return self::puedeGestionar($user, $tipo);
    }

    public static function puedeEliminar(?Usuario $user, string $tipo): bool
    {
        return self::puedeGestionar($user, $tipo);
    }
}
