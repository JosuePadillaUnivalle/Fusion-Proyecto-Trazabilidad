<?php

namespace App\Support;

use App\Models\Usuario;
use Illuminate\Support\Collection;

class ReporteCatalogo
{
    /** @return list<array<string, mixed>> */
    public static function items(): array
    {
        return config('reportes.items', []);
    }

    public static function find(string $slug): ?array
    {
        foreach (self::items() as $item) {
            if (($item['slug'] ?? '') === $slug) {
                return $item;
            }
        }

        return null;
    }

    /** @return Collection<int, array<string, mixed>> */
    public static function paraUsuario(?Usuario $user): Collection
    {
        return collect(self::items())->filter(fn (array $item) => self::usuarioPuedeVer($user, $item))->values();
    }

    public static function usuarioTieneAcceso(?Usuario $user): bool
    {
        return self::paraUsuario($user)->isNotEmpty();
    }

    public static function usuarioPuedeVer(?Usuario $user, array $item): bool
    {
        if ($user === null) {
            return false;
        }

        if (UsuarioRol::esAdminGlobal($user)) {
            return true;
        }

        $permiso = (string) ($item['permission'] ?? '');
        if ($permiso !== '' && $user->can($permiso)) {
            return true;
        }

        $roles = $item['roles'] ?? [];
        foreach ($roles as $rol) {
            if ($user->hasRole($rol)) {
                return true;
            }
        }

        return false;
    }

    public static function autorizar(?Usuario $user, array $item): void
    {
        abort_unless(self::usuarioPuedeVer($user, $item), 403);
    }
}
