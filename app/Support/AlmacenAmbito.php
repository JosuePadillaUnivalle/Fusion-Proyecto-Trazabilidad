<?php

namespace App\Support;

use App\Models\Almacen;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AlmacenAmbito
{
    public const AGRICOLA = 'agricola';

    public const PLANTA = 'planta';

    /** @var array<string, string> */
    public const TITULOS = [
        self::AGRICOLA => 'Almacén agrícola',
        self::PLANTA => 'Almacén de planta',
    ];

    public static function fromRequest(?Request $request = null): string
    {
        $request ??= request();

        $ambito = $request?->route('ambito');
        if (self::esValido($ambito)) {
            return $ambito;
        }

        $routeName = $request?->route()?->getName() ?? '';
        if (str_starts_with($routeName, self::routePrefix(self::PLANTA).'.')) {
            return self::PLANTA;
        }
        if (str_starts_with($routeName, self::routePrefix(self::AGRICOLA).'.')) {
            return self::AGRICOLA;
        }

        abort(404, 'Ámbito de almacén no definido.');
    }

    public static function esValido(?string $ambito): bool
    {
        return in_array($ambito, [self::AGRICOLA, self::PLANTA], true);
    }

    public static function routePrefix(string $ambito): string
    {
        return match ($ambito) {
            self::AGRICOLA => 'almacen-agricola',
            self::PLANTA => 'almacen-planta',
            default => 'almacen-agricola',
        };
    }

    public static function titulo(string $ambito): string
    {
        return self::TITULOS[$ambito] ?? 'Almacén';
    }

    /** @return array{ambito: string, rutaPrefijo: string, tituloModulo: string} */
    public static function contexto(?Request $request = null): array
    {
        $ambito = self::fromRequest($request);

        return [
            'ambito' => $ambito,
            'rutaPrefijo' => self::routePrefix($ambito),
            'tituloModulo' => self::titulo($ambito),
        ];
    }

    public static function usuarioPuedeVer(?Usuario $user, string $ambito): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($ambito === self::AGRICOLA) {
            return $user->hasRole('agricultor');
        }

        if ($ambito === self::PLANTA) {
            return $user->hasRole('planta');
        }

        return false;
    }

    public static function scope(Builder $query, string $ambito): Builder
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn('almacen', 'ambito')) {
            return $query->where('ambito', $ambito);
        }

        return self::scopeLegacyPorNombre($query, $ambito);
    }

    /** Compatibilidad si aún no existe la columna ambito. */
    private static function scopeLegacyPorNombre(Builder $query, string $ambito): Builder
    {
        if ($ambito === self::PLANTA) {
            return $query->where(function ($q) {
                $q->whereRaw('LOWER(nombre) LIKE ?', ['%planta%'])
                    ->orWhereHas('tipoAlmacen', fn ($t) => $t->whereRaw('LOWER(nombre) LIKE ?', ['%planta%']));
            });
        }

        return $query->where(function ($q) {
            $q->whereRaw('LOWER(nombre) NOT LIKE ?', ['%planta%'])
                ->whereDoesntHave('tipoAlmacen', fn ($t) => $t->whereRaw('LOWER(nombre) LIKE ?', ['%planta%']));
        });
    }

    public static function asegurarAmbitosEnRegistros(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasColumn('almacen', 'ambito')) {
            return;
        }

        Almacen::with('tipoAlmacen')
            ->where(function ($q) {
                $q->whereNull('ambito')->orWhere('ambito', '');
            })
            ->get()
            ->each(function (Almacen $a) {
                $nombre = mb_strtolower($a->nombre ?? '');
                $tipo = mb_strtolower($a->tipoAlmacen?->nombre ?? '');
                $ambito = (str_contains($nombre, 'planta') || str_contains($tipo, 'planta'))
                    ? self::PLANTA
                    : self::AGRICOLA;
                $a->update(['ambito' => $ambito]);
            });
    }
}
