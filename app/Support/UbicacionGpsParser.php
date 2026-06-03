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
}
