<?php

namespace App\Support;

use App\Models\Actividad;
use App\Models\Usuario;

final class ActividadPermisos
{
    public static function puedeAcceder(?Usuario $user, Actividad $actividad): bool
    {
        if (! $user) {
            return false;
        }

        if (UsuarioRol::esAdminGlobal($user)) {
            return true;
        }

        if (UsuarioRol::esJefeAgricultor($user)) {
            return in_array(
                (int) $actividad->usuarioid,
                UsuarioRol::idsEmpleadosOperativosDeJefeAgricultor($user),
                true
            );
        }

        if (UsuarioRol::debeAcotarPorAsignacion($user)) {
            return (int) $actividad->usuarioid === (int) $user->usuarioid;
        }

        return true;
    }

    public static function puedeMarcarCompletada(?Usuario $user, Actividad $actividad): bool
    {
        if (! $user || $actividad->fechafin !== null) {
            return false;
        }

        if (UsuarioRol::esAdminGlobal($user)) {
            return true;
        }

        if (UsuarioRol::esJefeAgricultor($user)) {
            return in_array(
                (int) $actividad->usuarioid,
                UsuarioRol::idsEmpleadosOperativosDeJefeAgricultor($user),
                true
            );
        }

        if (UsuarioRol::debeAcotarPorAsignacion($user)) {
            return (int) $actividad->usuarioid === (int) $user->usuarioid;
        }

        return $user->can('lotes.update');
    }
}
