<?php

namespace App\Support;

final class UbicacionGpsParser
{
    /**
     * @return array{lat: float, lng: float}|null
     */
    public static function fromTexto(?string $ubicacion): ?array
    {
        if ($ubicacion === null || trim($ubicacion) === '') {
            return null;
        }

        if (preg_match('/GPS\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/i', $ubicacion, $m)) {
            return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
        }

        if (preg_match('/(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)/', $ubicacion, $m)) {
            return ['lat' => (float) $m[1], 'lng' => (float) $m[2]];
        }

        return null;
    }

    /** @return array{lat: float, lng: float} */
    public static function coordsOrDefault(?string $ubicacion): array
    {
        return self::fromTexto($ubicacion) ?? ['lat' => -17.7833, 'lng' => -63.1821];
    }

    /**
     * Coordenadas de respaldo dentro de Santa Cruz de la Sierra (determinísticas por id).
     *
     * @return array{lat: float, lng: float, direccion: string}
     */
    public static function fallbackSantaCruz(int $seed, ?string $nombreAlmacen = null): array
    {
        $puntos = [
            ['lat' => -17.7833, 'lng' => -63.1821, 'dir' => 'Av. Cristóbal de Mendoza, Centro, Santa Cruz de la Sierra'],
            ['lat' => -17.7942, 'lng' => -63.1615, 'dir' => 'Av. Roca y Coronado, Equipetrol, Santa Cruz de la Sierra'],
            ['lat' => -17.7516, 'lng' => -63.2367, 'dir' => 'Urubó, Municipio Porongo, Santa Cruz de la Sierra'],
            ['lat' => -17.8025, 'lng' => -63.1458, 'dir' => 'Av. San Aurelio, Plan 3000, Santa Cruz de la Sierra'],
            ['lat' => -17.7689, 'lng' => -63.1984, 'dir' => 'Av. Banzer, 4to anillo, Santa Cruz de la Sierra'],
            ['lat' => -17.8156, 'lng' => -63.1712, 'dir' => 'Av. Paragua, Barrio Lindo, Santa Cruz de la Sierra'],
            ['lat' => -17.7398, 'lng' => -63.1689, 'dir' => 'Zona Norte, Av. Beni, Santa Cruz de la Sierra'],
            ['lat' => -17.8567, 'lng' => -63.2103, 'dir' => 'Av. Virgen de Cotoca, Santa Cruz de la Sierra'],
        ];

        $idx = abs($seed) % count($puntos);
        $punto = $puntos[$idx];
        $prefijo = $nombreAlmacen ? trim($nombreAlmacen).' · ' : '';

        return [
            'lat' => $punto['lat'],
            'lng' => $punto['lng'],
            'direccion' => $prefijo.$punto['dir'],
        ];
    }

    /**
     * @return array{lat: float, lng: float, direccion: string, estimada: bool}
     */
    public static function resolverAlmacen(int $almacenId, ?string $nombre, ?string $ubicacion): array
    {
        $coords = self::fromTexto($ubicacion);
        if ($coords !== null) {
            return [
                'lat' => $coords['lat'],
                'lng' => $coords['lng'],
                'direccion' => trim($ubicacion) !== '' ? trim($ubicacion) : ($nombre ?? 'Santa Cruz de la Sierra'),
                'estimada' => false,
            ];
        }

        $fallback = self::fallbackSantaCruz($almacenId, $nombre);

        return [
            'lat' => $fallback['lat'],
            'lng' => $fallback['lng'],
            'direccion' => $fallback['direccion'],
            'estimada' => true,
        ];
    }
}
