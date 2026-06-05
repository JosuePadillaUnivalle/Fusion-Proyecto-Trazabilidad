<?php

namespace App\Support;

use App\Models\PedidoDistribucion;
use App\Models\PuntoVenta;
use App\Models\Usuario;

final class PuntoVentaAccess
{
    public static function puedeVerPunto(?Usuario $user, PuntoVenta $puntoVenta): bool
    {
        if (! $user) {
            return false;
        }

        if (UsuarioRol::esAdminGlobal($user)) {
            return true;
        }

        return UsuarioRol::esMinorista($user)
            && (int) $puntoVenta->usuarioid === (int) $user->usuarioid;
    }

    public static function puedeEditarPunto(?Usuario $user, PuntoVenta $puntoVenta): bool
    {
        return self::puedeVerPunto($user, $puntoVenta);
    }

    public static function puedeVerPedido(?Usuario $user, PedidoDistribucion $pedido): bool
    {
        if (! $user) {
            return false;
        }

        if (UsuarioRol::esAdminGlobal($user) || UsuarioRol::puedeGestionarDistribucionPlanta($user)) {
            return true;
        }

        if (! UsuarioRol::esMinorista($user)) {
            return false;
        }

        $pedido->loadMissing('puntoVenta');

        return $pedido->puntoVenta !== null
            && (int) $pedido->puntoVenta->usuarioid === (int) $user->usuarioid;
    }

    public static function scopePuntosDelUsuario($query, ?Usuario $user)
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if (UsuarioRol::esAdminGlobal($user)) {
            return $query;
        }

        if (UsuarioRol::esMinorista($user)) {
            return $query->where('usuarioid', $user->usuarioid);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function scopePedidosDelUsuario($query, ?Usuario $user)
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if (UsuarioRol::esAdminGlobal($user) || UsuarioRol::puedeGestionarDistribucionPlanta($user)) {
            return $query;
        }

        if (UsuarioRol::esMinorista($user)) {
            return $query->whereHas('puntoVenta', fn ($q) => $q->where('usuarioid', $user->usuarioid));
        }

        return $query->whereRaw('1 = 0');
    }
}
