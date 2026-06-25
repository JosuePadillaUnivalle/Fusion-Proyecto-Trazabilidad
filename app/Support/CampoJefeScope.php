<?php

namespace App\Support;

use App\Models\Almacen;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Lote;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;

/** Acota datos de campo al jefe agrícola (su equipo, lotes y almacenes). Admin sin filtro. */
final class CampoJefeScope
{
    public static function debeAcotar(?Usuario $user): bool
    {
        return $user !== null
            && UsuarioRol::esJefeAgricultor($user)
            && ! UsuarioRol::esAdminGlobal($user);
    }

    /** @return array<int> */
    public static function idsEquipo(?Usuario $user): array
    {
        return UsuarioRol::idsUsuariosBajoJefeAgricultor($user);
    }

    /** @return array<int> */
    public static function idsAlmacenesAgricolas(?Usuario $user): array
    {
        if (! $user) {
            return [];
        }

        return Almacen::query()
            ->where('responsable_usuarioid', (int) $user->usuarioid)
            ->when(
                \Illuminate\Support\Facades\Schema::hasColumn('almacen', 'ambito'),
                fn (Builder $q) => $q->where(fn (Builder $sub) => AlmacenAmbito::scope($sub, AlmacenAmbito::AGRICOLA))
            )
            ->pluck('almacenid')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /** @param  Builder<Lote>  $query */
    public static function aplicarEnLote(Builder $query, ?Usuario $user): void
    {
        if (! self::debeAcotar($user)) {
            return;
        }

        $query->whereIn('usuarioid', self::idsEquipo($user));
    }

    /** @param  Builder<\Illuminate\Database\Eloquent\Model>  $query */
    public static function aplicarEnRelacionLote(Builder $query, ?Usuario $user, string $relacion = 'lote'): void
    {
        if (! self::debeAcotar($user)) {
            return;
        }

        $ids = self::idsEquipo($user);
        $query->whereHas($relacion, fn (Builder $l) => $l->whereIn('usuarioid', $ids));
    }

    /** @param  Builder<EnvioAsignacionMultiple>  $query */
    public static function aplicarEnEnvioAgricola(Builder $query, ?Usuario $user): void
    {
        if (! self::debeAcotar($user)) {
            return;
        }

        $almacenIds = self::idsAlmacenesAgricolas($user);
        $jefeId = (int) $user->usuarioid;

        $query->where(function (Builder $q) use ($almacenIds, $jefeId) {
            $q->where('asignadopor_usuarioid', $jefeId);
            if ($almacenIds !== []) {
                $q->orWhereIn('almacenid', $almacenIds);
            }
        });
    }

    public static function envioPerteneceAJefe(EnvioAsignacionMultiple $envio, ?Usuario $user): bool
    {
        if (! self::debeAcotar($user)) {
            return true;
        }

        if ((int) $envio->asignadopor_usuarioid === (int) $user->usuarioid) {
            return true;
        }

        $almacenIds = self::idsAlmacenesAgricolas($user);

        return $almacenIds !== [] && in_array((int) $envio->almacenid, $almacenIds, true);
    }

    public static function almacenPerteneceAJefe(?int $almacenId, ?Usuario $user): bool
    {
        if (! self::debeAcotar($user)) {
            return true;
        }

        return in_array((int) $almacenId, self::idsAlmacenesAgricolas($user), true);
    }

    /** Movimientos de insumo registrados por el jefe agrícola. */
    public static function aplicarEnMovimientoPorUsuario(Builder $query, ?Usuario $user): void
    {
        if (! self::debeAcotar($user)) {
            return;
        }

        $query->where('usuarioid', (int) $user->usuarioid);
    }

    /** Ingresos por cosecha en almacenes del jefe o lotes de su equipo. */
    public static function aplicarEnProduccionAlmacenamiento(Builder $query, ?Usuario $user): void
    {
        if (! self::debeAcotar($user)) {
            return;
        }

        $almacenIds = self::idsAlmacenesAgricolas($user);
        $idsEquipo = self::idsEquipo($user);

        $query->where(function (Builder $q) use ($almacenIds, $idsEquipo) {
            if ($almacenIds !== []) {
                $q->whereIn('almacenid', $almacenIds);
            }
            $q->orWhereHas('produccion.lote', fn (Builder $l) => $l->whereIn('usuarioid', $idsEquipo));
        });
    }

    /** @return array<int> */
    public static function idsEnviosAgricolasVisibles(?Usuario $user): array
    {
        if (! self::debeAcotar($user)) {
            return [];
        }

        $query = EnvioAsignacionMultiple::query();
        self::aplicarEnEnvioAgricola($query, $user);

        return $query->pluck('envioasignacionmultipleid')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}
