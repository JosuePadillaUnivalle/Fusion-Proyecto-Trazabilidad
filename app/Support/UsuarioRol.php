<?php

namespace App\Support;

use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;

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

    public static function esJefeAgricultor(?Usuario $user): bool
    {
        return (bool) ($user && $user->hasRole('jefe_agricultor'));
    }

    public static function esJefePlanta(?Usuario $user): bool
    {
        return (bool) ($user && $user->hasRole('jefe_planta'));
    }

    public static function esPlantaOperativo(?Usuario $user): bool
    {
        return (bool) ($user && $user->hasAnyRole(['planta', 'jefe_planta']));
    }

    /** Operario de planta (Spatie rol planta, sin jefe_planta ni admin). */
    public static function esOperarioPlanta(?Usuario $user): bool
    {
        return (bool) ($user && $user->hasRole('planta') && ! self::esJefePlanta($user) && ! self::esAdminGlobal($user));
    }

    /** Usuarios asignables como operarios en transformación (solo Spatie «planta», nunca jefe). */
    public static function queryOperariosPlanta(): Builder
    {
        return Usuario::query()
            ->where('activo', true)
            ->whereHas('roles', fn (Builder $q) => $q->where('name', 'planta'))
            ->whereDoesntHave('roles', fn (Builder $q) => $q->whereIn('name', ['jefe_planta', 'admin']));
    }

    public static function puedeConfirmarRecepcionPlanta(?Usuario $user): bool
    {
        return (bool) ($user && ($user->hasAnyRole(['planta', 'jefe_planta']) || $user->hasRole('admin')));
    }

    public static function esTransportista(?Usuario $user): bool
    {
        return (bool) ($user && $user->hasRole('transportista'));
    }

    public static function esMinorista(?Usuario $user): bool
    {
        return (bool) ($user && $user->hasRole('minorista'));
    }

    public static function puedeGestionarDistribucionPlanta(?Usuario $user): bool
    {
        return self::esAdminGlobal($user) || self::esJefePlanta($user);
    }

    public static function gestionaCampo(?Usuario $user): bool
    {
        return self::esAdminGlobal($user) || self::esJefeAgricultor($user);
    }

    public static function gestionaPlanta(?Usuario $user): bool
    {
        return self::esAdminGlobal($user) || self::esJefePlanta($user);
    }

    /** Agricultor de campo: solo ve lotes/actividades asignados a él (no jefes). */
    public static function debeAcotarPorAsignacion(?Usuario $user): bool
    {
        return self::esAgricultorOperativo($user)
            && ! self::esAdminGlobal($user)
            && ! self::esJefeAgricultor($user);
    }

    public static function puedeGestionarEmpleados(?Usuario $user): bool
    {
        return self::esJefeAgricultor($user) || self::esJefePlanta($user);
    }

    /** @return list<string> */
    public static function rolesEmpleadosGestionables(?Usuario $jefe): array
    {
        if (self::esJefeAgricultor($jefe)) {
            return ['agricultor'];
        }
        if (self::esJefePlanta($jefe)) {
            return ['planta'];
        }

        return [];
    }

    /** Rol operativo que un jefe puede asignar al crear empleados. */
    public static function rolEmpleadoAsignable(?Usuario $jefe): ?string
    {
        if (self::esJefeAgricultor($jefe)) {
            return 'agricultor';
        }
        if (self::esJefePlanta($jefe)) {
            return 'planta';
        }

        return null;
    }

    public static function puedeAprobarSolicitud(?Usuario $user, ?string $rolSolicitado): bool
    {
        if (! $user) {
            return false;
        }

        return self::esAdminGlobal($user);
    }
}
