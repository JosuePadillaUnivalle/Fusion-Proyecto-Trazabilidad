<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class JefePlantaTrasladoNotificacionVista
{
    public static function claveRuta(int $rutaId): string
    {
        return 'traslado:'.$rutaId;
    }

    /** @return list<string> */
    private static function clavesVistas(int $usuarioid): array
    {
        $claves = Cache::get(self::cacheKey($usuarioid), []);

        return is_array($claves) ? array_values(array_unique($claves)) : [];
    }

    public static function yaVio(int $usuarioid, string $clave): bool
    {
        return in_array($clave, self::clavesVistas($usuarioid), true);
    }

    /**
     * @param  list<array{clave: string, codigo: string, url: string, destino: string, productos: string}>  $items
     * @return list<array{clave: string, codigo: string, url: string, destino: string, productos: string}>
     */
    public static function filtrarPendientes(int $usuarioid, array $items): array
    {
        return array_values(array_filter(
            $items,
            fn (array $row) => ! self::yaVio($usuarioid, (string) ($row['clave'] ?? ''))
        ));
    }

    /**
     * @param  list<array{clave: string, codigo: string, url: string, destino: string, productos: string}>  $items
     */
    public static function marcarVistas(int $usuarioid, array $items): void
    {
        if ($items === []) {
            return;
        }

        $claves = self::clavesVistas($usuarioid);
        foreach ($items as $row) {
            $clave = (string) ($row['clave'] ?? '');
            if ($clave !== '') {
                $claves[] = $clave;
            }
        }

        Cache::forever(self::cacheKey($usuarioid), array_values(array_unique($claves)));
    }

    private static function cacheKey(int $usuarioid): string
    {
        return 'jefe_planta_traslado_modal_vistas:'.$usuarioid;
    }
}
