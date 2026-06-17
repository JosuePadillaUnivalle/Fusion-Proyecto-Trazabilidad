<?php

namespace App\Services;

use App\Services\WeatherOpenWeatherService;

/**
 * Estimación de costo de envío tipo delivery urbano (referencia Uber Bolivia / Santa Cruz).
 */
class CostoEnvioRutaService
{
    public function __construct(
        private readonly WeatherOpenWeatherService $weather
    ) {}

    /**
     * @param  array<int, array{lat: float, lng: float}>  $paradas  Orden de recogidas + destino
     * @return array{
     *     costo_bs: float,
     *     distancia_km: float,
     *     paradas: int,
     *     recargo_clima_pct: float,
     *     recargo_clima_bs: float,
     *     base_bs: float,
     *     lluvia: bool,
     *     descripcion_clima: string|null,
     *     detalle: string
     * }
     */
    public function calcular(array $paradas, ?float $distanciaMetros = null): array
    {
        $paradas = array_values(array_filter($paradas, fn ($p) => isset($p['lat'], $p['lng'])));

        if (count($paradas) < 2) {
            return $this->respuestaVacia();
        }

        $distanciaKmExacta = $distanciaMetros !== null && $distanciaMetros > 0
            ? $distanciaMetros / 1000
            : $this->distanciaLineaKmExacta($paradas);
        $distanciaKm = (int) round($distanciaKmExacta);

        $numParadas = count($paradas);
        $paradasExtra = max(0, $numParadas - 2);

        $tarifaBase = (float) config('logistica.costo_envio.tarifa_base_bs', 10);
        $porKm = (float) config('logistica.costo_envio.por_km_bs', 2.4);
        $porParadaExtra = (float) config('logistica.costo_envio.por_parada_extra_bs', 5);
        $minimo = (float) config('logistica.costo_envio.minimo_bs', 15);

        $base = (int) round($tarifaBase + ($distanciaKm * $porKm) + ($paradasExtra * $porParadaExtra));
        $base = max((int) round($minimo), $base);

        $clima = $this->resolverRecargoClima();
        $recargoBs = (int) round($base * ($clima['pct'] / 100));
        $total = $base + $recargoBs;

        $detalle = sprintf(
            'Base Bs %d (%d km, %d paradas)',
            $base,
            $distanciaKm,
            $numParadas
        );
        if ($clima['lluvia']) {
            $detalle .= sprintf(' + recargo lluvia %d%% (Bs %d)', (int) $clima['pct'], $recargoBs);
        }

        return [
            'costo_bs' => $total,
            'distancia_km' => $distanciaKm,
            'paradas' => $numParadas,
            'recargo_clima_pct' => $clima['pct'],
            'recargo_clima_bs' => $recargoBs,
            'base_bs' => $base,
            'lluvia' => $clima['lluvia'],
            'descripcion_clima' => $clima['descripcion'],
            'detalle' => $detalle,
        ];
    }

    /** @return array{lluvia: bool, pct: float, descripcion: string|null} */
    private function resolverRecargoClima(): array
    {
        $pctLluvia = (float) config('logistica.costo_envio.recargo_lluvia_pct', 25.0);
        $weatherData = $this->weather->resolveForDisplay();
        $actual = $weatherData['actual'] ?? null;
        $descripcion = is_array($actual) ? (string) ($actual['descripcion'] ?? '') : '';

        if ($descripcion === '') {
            return ['lluvia' => false, 'pct' => 0.0, 'descripcion' => null];
        }

        $norm = mb_strtolower($descripcion);
        $esLluvia = str_contains($norm, 'lluv')
            || str_contains($norm, 'rain')
            || str_contains($norm, 'drizzle')
            || str_contains($norm, 'torment')
            || str_contains($norm, 'storm')
            || str_contains($norm, 'chubasc');

        return [
            'lluvia' => $esLluvia,
            'pct' => $esLluvia ? $pctLluvia : 0.0,
            'descripcion' => $descripcion,
        ];
    }

    /** @param  array<int, array{lat: float, lng: float}>  $paradas */
    private function distanciaLineaKmExacta(array $paradas): float
    {
        $total = 0.0;
        for ($i = 1, $n = count($paradas); $i < $n; $i++) {
            $total += $this->haversineKm(
                (float) $paradas[$i - 1]['lat'],
                (float) $paradas[$i - 1]['lng'],
                (float) $paradas[$i]['lat'],
                (float) $paradas[$i]['lng']
            );
        }

        // Factor ~1.35: línea recta → recorrido urbano aproximado si no hay ruta por calles.
        return $total * 1.35;
    }

    private function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $r * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /** @return array<string, mixed> */
    private function respuestaVacia(): array
    {
        return [
            'costo_bs' => 0.0,
            'distancia_km' => 0.0,
            'paradas' => 0,
            'recargo_clima_pct' => 0.0,
            'recargo_clima_bs' => 0.0,
            'base_bs' => 0.0,
            'lluvia' => false,
            'descripcion_clima' => null,
            'detalle' => '',
        ];
    }
}
