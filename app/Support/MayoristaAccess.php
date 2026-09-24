<?php

namespace App\Support;

use App\Models\Almacen;
use App\Models\PedidoDistribucion;
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

        // El admin supervisa almacenes mayoristas pero no los opera.
        if (! UsuarioRol::puedeOperar($user) || ! UsuarioRol::esMayorista($user)) {
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

    public static function puedeGestionarRutaDistribucion(?Usuario $user, RutaDistribucion $ruta): bool
    {
        if (RutaDistribucionCatalogo::esTrasladoPlantaMayorista($ruta)) {
            return false;
        }

        $ruta->loadMissing('almacenOrigen');
        $almacen = $ruta->almacenOrigen;

        return $almacen !== null && self::puedeGestionarAlmacen($user, $almacen);
    }

    public static function puedeGestionarTraslado(?Usuario $user, RutaDistribucion $ruta): bool
    {
        if (! RutaDistribucionCatalogo::esTrasladoPlantaMayorista($ruta)) {
            return false;
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

    /** Almacenes mayorista vinculados al usuario (responsable o almacén asignado). */
    public static function idsAlmacenesOperados(?Usuario $user): array
    {
        if (! $user) {
            return [];
        }

        if (UsuarioRol::esAdminGlobal($user)) {
            return AlmacenAmbito::scope(Almacen::query()->where('activo', true), AlmacenAmbito::MAYORISTA)
                ->pluck('almacenid')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        $ids = collect();
        if ($user->almacenid) {
            $ids->push((int) $user->almacenid);
        }

        return $ids->merge(self::idsAlmacenesMayorista($user))
            ->unique()
            ->filter()
            ->values()
            ->all();
    }

    public static function puedeVerPedidoDistribucion(?Usuario $user, PedidoDistribucion $pedido): bool
    {
        if (! $user) {
            return false;
        }

        if (UsuarioRol::esAdminGlobal($user)) {
            return true;
        }

        if (! UsuarioRol::puedeGestionarDistribucionMayorista($user)) {
            return false;
        }

        $almacenId = (int) $pedido->almacen_mayorista_origenid;

        if ($almacenId <= 0) {
            return $pedido->tipo_solicitud === PedidoDistribucionCatalogo::TIPO_SOLICITUD_CUSTOM
                && self::idsAlmacenesOperados($user) !== [];
        }

        return in_array($almacenId, self::idsAlmacenesOperados($user), true);
    }

    public static function asegurarPuedeVerPedido(?Usuario $user, PedidoDistribucion $pedido): void
    {
        if (! self::puedeVerPedidoDistribucion($user, $pedido)) {
            abort(403, 'Este pedido no está dirigido a su almacén mayorista.');
        }
    }

    public static function asegurarPuedeGestionar(?Usuario $user, Almacen $almacen): void
    {
        if (! self::puedeGestionarAlmacen($user, $almacen)) {
            abort(403, 'No tiene permiso para gestionar este almacén mayorista.');
        }
    }
}
