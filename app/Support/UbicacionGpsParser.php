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

    public static function direccionLegible(?string $direccion): ?string
    {
        if ($direccion === null || trim($direccion) === '') {
            return null;
        }

        $trim = trim($direccion);
        if (preg_match('/^PDV\s*GPS/i', $trim)) {
            return null;
        }
        if (preg_match('/^PDV$/i', $trim)) {
            return null;
        }
        if (preg_match('/^(?:Parcela\s+)?GPS\s/i', $trim)) {
            return null;
        }
        if (preg_match('/^GPS\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/i', $trim)) {
            return null;
        }
        if (preg_match('/^(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)$/', $trim)) {
            return null;
        }

        return $trim;
    }

    /**
     * Quita coordenadas y prefijos GPS del texto de ubicación.
     */
    public static function limpiarCoordenadasDeTexto(?string $texto): ?string
    {
        if ($texto === null || trim($texto) === '') {
            return null;
        }

        $t = trim($texto);
        $t = preg_replace('/^(?:Parcela\s+)?GPS\s*-?\d+(?:\.\d+)?\s*,\s*-?\d+(?:\.\d+)?\s*$/iu', '', $t);
        $t = preg_replace('/\s*·\s*GPS\s*-?\d+(?:\.\d+)?\s*,\s*-?\d+(?:\.\d+)?\s*$/iu', '', $t);
        $t = preg_replace('/\s*GPS\s*-?\d+(?:\.\d+)?\s*,\s*-?\d+(?:\.\d+)?/iu', '', $t);
        $t = preg_replace('/\s*-?\d+(?:\.\d+)?\s*,\s*-?\d+(?:\.\d+)?\s*$/', '', $t);
        $t = trim($t);

        if ($t === '' || self::fromTexto($t) !== null) {
            return null;
        }

        return $t;
    }

    /**
     * Calle de referencia del punto de venta (sin coordenadas ni marcadores técnicos).
     *
     * @return array{direccion: string, estimada: bool}
     */
    public static function referenciaPuntoVenta(
        ?string $direccion,
        ?float $lat = null,
        ?float $lng = null,
        ?int $puntoVentaId = null,
        ?string $nombrePdv = null
    ): array {
        $legible = self::direccionLegible($direccion);
        if ($legible !== null) {
            return ['direccion' => $legible, 'estimada' => false];
        }

        $limpia = self::limpiarCoordenadasDeTexto($direccion);
        $legibleLimpia = $limpia !== null ? self::direccionLegible($limpia) : null;
        if ($legibleLimpia !== null) {
            return ['direccion' => $legibleLimpia, 'estimada' => false];
        }

        if ($lat !== null && $lng !== null) {
            $seed = $puntoVentaId ?? self::seedDesdeCoords($lat, $lng);
            $fallback = self::fallbackSantaCruz($seed, $nombrePdv);

            return ['direccion' => $fallback['direccion'], 'estimada' => true];
        }

        return ['direccion' => 'Sin calle de referencia registrada', 'estimada' => false];
    }

    /**
     * Texto de dirección para mostrar al usuario (sin coordenadas GPS).
     */
    public static function textoDireccionVisible(?string $ubicacion, ?string $nombreAlmacen = null, ?int $almacenId = null): ?string
    {
        if ($ubicacion === null || trim($ubicacion) === '') {
            return null;
        }

        $sinGps = preg_replace(
            '/\s*·\s*GPS\s*-?\d+(?:\.\d+)?\s*,\s*-?\d+(?:\.\d+)?\s*$/iu',
            '',
            trim($ubicacion)
        );

        $legible = self::direccionLegible(trim($sinGps));
        if ($legible !== null) {
            return $legible;
        }

        if ($almacenId !== null) {
            return self::fallbackSantaCruz($almacenId, $nombreAlmacen)['direccion'];
        }

        return null;
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
            $direccion = self::textoDireccionVisible($ubicacion, $nombre, $almacenId);
            $estimada = $direccion === null
                || $direccion === self::fallbackSantaCruz($almacenId, $nombre)['direccion'];

            return [
                'lat' => $coords['lat'],
                'lng' => $coords['lng'],
                'direccion' => $direccion ?? ($nombre ?? 'Santa Cruz de la Sierra'),
                'estimada' => $estimada,
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

    /**
     * Dirección legible de un lote agrícola (nunca coordenadas crudas).
     */
    public static function textoLoteVisible(
        ?string $ubicacion,
        ?int $loteId = null,
        ?float $lat = null,
        ?float $lng = null
    ): string {
        $limpia = self::limpiarCoordenadasDeTexto($ubicacion);
        if ($limpia !== null) {
            $legible = self::direccionLegible($limpia);
            if ($legible !== null) {
                return $legible;
            }

            return $limpia;
        }

        $seed = $loteId ?? (($lat !== null && $lng !== null) ? self::seedDesdeCoords($lat, $lng) : null);
        if ($seed !== null) {
            return self::fallbackSantaCruz($seed)['direccion'];
        }

        return 'Sin ubicación registrada';
    }

    /**
     * Normaliza el texto a guardar en lote.ubicacion (sin coordenadas GPS).
     */
    public static function normalizarUbicacionLote(
        ?string $ubicacion,
        ?float $lat = null,
        ?float $lng = null,
        ?int $loteId = null
    ): ?string {
        $limpia = self::limpiarCoordenadasDeTexto($ubicacion);
        if ($limpia !== null) {
            return self::direccionLegible($limpia) ?? $limpia;
        }

        if ($lat !== null && $lng !== null) {
            return self::fallbackSantaCruz(self::seedDesdeCoords($lat, $lng))['direccion'];
        }

        if ($loteId !== null) {
            return self::fallbackSantaCruz($loteId)['direccion'];
        }

        return null;
    }

    private static function seedDesdeCoords(float $lat, float $lng): int
    {
        return (int) abs((int) round($lat * 10000) ^ (int) round($lng * 10000));
    }
}
