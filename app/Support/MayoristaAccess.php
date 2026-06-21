<?php

namespace App\Support;

use App\Models\Almacen;
use App\Models\RutaDistribucion;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;

final class MayoristaAccess
{
    public static function puedeGestionarAlmacen(?Usuario $user, Almacen $almacen): bool
    {
        if (! $user) {
            return false;
        }

        if (UsuarioRol::esAdminGlobal($user)) {
            return AlmacenAmbito::resolverAmbito($almacen) === AlmacenAmbito::MAYORISTA;
        }

        if (! UsuarioRol::esMayorista($user)) {
            return false;
        }

        if (AlmacenAmbito::resolverAmbito($almacen) !== AlmacenAmbito::MAYORISTA) {
            return false;
        }

        if (! \Illuminate\Support\Facades\Schema::hasColumn('almacen', 'responsable_usuarioid')) {
            return true;
        }

        return (int) $almacen->responsable_usuarioid === (int) $user->usuarioid;
    }

    public static function puedeGestionarTraslado(?Usuario $user, RutaDistribucion $ruta): bool
    {
        if (! \App\Support\RutaDistribucionCatalogo::esTrasladoPlantaMayorista($ruta)) {
            return false;
        }

        if (UsuarioRol::esAdminGlobal($user)) {
            return true;
        }

        $ruta->loadMissing('almacenMayoristaDestino');
        $almacen = $ruta->almacenMayoristaDestino;

        if ($almacen === null) {
            return false;
        }

        return self::puedeGestionarAlmacen($user, $almacen);
    }

    public static function scopeAlmacenesMayorista(Builder $query, ?Usuario $user): Builder
    {
        $query = AlmacenAmbito::scope($query, AlmacenAmbito::MAYORISTA);

        if (! $user || UsuarioRol::esAdminGlobal($user)) {
            return $query;
        }

        if (! UsuarioRol::esMayorista($user)) {
            return $query->whereRaw('1 = 0');
        }

        if (\Illuminate\Support\Facades\Schema::hasColumn('almacen', 'responsable_usuarioid')) {
            return $query->where('responsable_usuarioid', $user->usuarioid);
        }

        return $query;
    }

    public static function idsAlmacenesMayorista(?Usuario $user): array
    {
        return self::scopeAlmacenesMayorista(Almacen::query(), $user)
            ->pluck('almacenid')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public static function asegurarPuedeGestionar(?Usuario $user, Almacen $almacen): void
    {
        if (! self::puedeGestionarAlmacen($user, $almacen)) {
            abort(403, 'No tiene permiso para gestionar este almacén mayorista.');
        }
    }
}
