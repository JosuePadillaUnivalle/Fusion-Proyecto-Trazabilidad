<?php

namespace App\Support;

use App\Models\Usuario;

final class UsuarioSolicitud
{
    public static function esPendiente(?Usuario $usuario): bool
    {
        return ($usuario->estado_cuenta ?? CuentaEstado::APROBADO) === CuentaEstado::PENDIENTE;
    }

    public static function adminSoloPuedeRevisar(?Usuario $usuario): bool
    {
        return self::esPendiente($usuario);
    }
}
