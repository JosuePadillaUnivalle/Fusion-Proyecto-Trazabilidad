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

    /** IDs de usuarios cuyos lotes/actividades ve un jefe agrícola (él + su equipo). */
    public static function idsUsuariosBajoJefeAgricultor(?Usuario $jefe): array
    {
        if (! $jefe || ! self::esJefeAgricultor($jefe)) {
            return [];
        }

        $ids = self::idsEmpleadosOperativosDeJefeAgricultor($jefe);
        $ids[] = (int) $jefe->usuarioid;

        return array_values(array_unique($ids));
    }

    /** Empleados agrícolas operativos bajo un jefe (sin incluir al jefe). */
    public static function idsEmpleadosOperativosDeJefeAgricultor(?Usuario $jefe): array
    {
        if (! $jefe || ! self::esJefeAgricultor($jefe)) {
            return [];
        }

        return Usuario::query()
            ->where('supervisor_usuarioid', $jefe->usuarioid)
            ->whereIn('role', self::rolesEmpleadosGestionables($jefe))
            ->where('activo', true)
            ->whereDoesntHave('roles', fn (Builder $q) => $q->where('name', 'jefe_agricultor'))
            ->pluck('usuarioid')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /** Usuario que puede ejecutar una actividad de campo (no jefe ni admin). */
    public static function esResponsableActividadPermitido(?Usuario $user): bool
    {
        if (! $user || ! $user->activo) {
            return false;
        }

        if (self::esAdminGlobal($user) || self::esJefeAgricultor($user)) {
            return false;
        }

        $rolColumna = strtolower((string) ($user->role ?? ''));

        return $rolColumna === 'agricultor' || $user->hasRole('agricultor');
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

    /** Etiqueta legible para mostrar en pantalla (sin guiones bajos ni jerga técnica). */
    public static function etiquetaRol(?string $nombre): string
    {
        return match (strtolower((string) $nombre)) {
            'admin' => 'Administrador',
            'agricultor' => 'Campo — operario',
            'jefe_agricultor' => 'Jefe de campo',
            'jefe_planta' => 'Jefe de planta',
            'minorista' => 'Punto de venta',
            'planta' => 'Planta — operario',
            'transportista' => 'Transporte',
            default => ucfirst(str_replace('_', ' ', (string) $nombre)),
        };
    }

    public static function puedeAprobarSolicitud(?Usuario $user, ?string $rolSolicitado): bool
    {
        if (! $user) {
            return false;
        }

        return self::esAdminGlobal($user);
    }
}
