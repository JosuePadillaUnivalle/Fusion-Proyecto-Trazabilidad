<?php

namespace App\Support;

use App\Models\Usuario;

final class UsuarioRol
{
    public static function esAdminGlobal(?Usuario $user): bool
    {
        return (bool) ($user && $user->hasRole('admin'));
    }

    public static function esAgricultorOperativo(?Usuario $user): bool
    {
        return (bool) ($user && $user->hasRole('agricultor'));
    }

    public static function esPlantaOperativo(?Usuario $user): bool
    {
        return (bool) ($user && $user->hasRole('planta'));
    }

    public static function esTransportista(?Usuario $user): bool
    {
        return (bool) ($user && $user->hasRole('transportista'));
    }

    /** Admin global o admin de área agrícola. */
    public static function gestionaCampo(?Usuario $user): bool
    {
        return self::esAdminGlobal($user);
    }

    /** Admin global o admin de planta. */
    public static function gestionaPlanta(?Usuario $user): bool
    {
        return self::esAdminGlobal($user);
    }

    /** Agricultor de campo: solo ve lotes/actividades asignados a él. */
    public static function debeAcotarPorAsignacion(?Usuario $user): bool
    {
        return self::esAgricultorOperativo($user)
            && ! self::esAdminGlobal($user);
    }

    public static function puedeAprobarSolicitud(?Usuario $user, ?string $rolSolicitado): bool
    {
        if (! $user) {
            return false;
        }

        return self::esAdminGlobal($user);
    }
}
