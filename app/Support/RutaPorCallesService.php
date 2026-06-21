<?php

namespace App\Support;

use App\Models\Pedido;
use App\Models\RutaParada;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RutaPorCallesService
{
    /** Hub logístico por defecto (Santa Cruz) si falta coordenada. */
    public const HUB_LAT = -17.7833;

    public const HUB_LNG = -63.1821;

    /**
     * @param  array<int, array{lat: float, lng: float}>  $waypoints
     * @return array{type: string, features: array}|null GeoJSON FeatureCollection
     */
    public function rutaPorCalles(array $waypoints): ?array
    {
        $coords = collect($waypoints)
            ->filter(fn ($p) => isset($p['lat'], $p['lng']) && is_numeric($p['lat']) && is_numeric($p['lng']))
            ->map(fn ($p) => ['lat' => (float) $p['lat'], 'lng' => (float) $p['lng']])
            ->values();

        if ($coords->count() < 2) {
            return $this->lineaRectaGeoJson($coords->all());
        }

        $osrm = $this->consultarOsrm($coords->all());
        if ($osrm) {
            return $osrm;
        }

        return $this->lineaRectaGeoJson($coords->all());
    }

    /**
     * @param  Collection<int, RutaParada>|array<int, RutaParada>  $paradas
     */
    public function rutaDesdeParadas(Collection|array $paradas): ?array
    {
        $puntos = [];
        foreach ($paradas as $parada) {
            $c = $this->coordenadasParada($parada);
            if ($c) {
                $puntos[] = $c;
            }
        }

        return $this->rutaPorCalles($puntos);
    }

    public function coordenadasParada(RutaParada $parada): ?array
    {
        if ($parada->latitud !== null && $parada->longitud !== null) {
            return ['lat' => (float) $parada->latitud, 'lng' => (float) $parada->longitud];
        }

        $parada->loadMissing('pedido');
        if ($parada->pedido?->latitud && $parada->pedido?->longitud) {
            return [
                'lat' => (float) $parada->pedido->latitud,
                'lng' => (float) $parada->pedido->longitud,
            ];
        }

        return null;
    }

    /**
     * @param  array<int, array{lat: float, lng: float}>  $puntos
     */
    public function puntosParaMapa(array $puntos): array
    {
        return collect($puntos)->map(function ($p, $i) {
            return [
                'lat' => $p['lat'],
                'lng' => $p['lng'],
                'orden' => $i + 1,
                'label' => $p['label'] ?? ('Parada '.($i + 1)),
            ];
        })->values()->all();
    }

    /**
     * @param  iterable<int, RutaParada>  $paradas
     * @return array<int, array<string, mixed>>
     */
    public function paradasConCoordenadas(iterable $paradas): array
    {
        $out = [];
        foreach ($paradas as $parada) {
            $c = $this->coordenadasParada($parada);
            if (! $c) {
                continue;
            }
            $out[] = array_merge($c, [
                'orden' => $parada->orden,
                'label' => $parada->destino ?? ('Parada '.$parada->orden),
                'externo_envio_id' => $parada->externo_envio_id,
            ]);
        }

        return $out;
    }

    /** @var array<string, array{0: float, 1: float}> */
    private const PUNTOS_CIUDAD = [
        'quillacollo' => [-17.3923, -66.2784],
        'cochabamba' => [-17.3935, -66.1570],
        'el alto' => [-16.5047, -68.1632],
        'la paz' => [-16.5000, -68.1500],
        'santa cruz' => [-17.7833, -63.1821],
        'oruro' => [-17.9754, -67.1130],
        'sucre' => [-19.0478, -65.2595],
        'tarija' => [-21.5318, -64.7311],
    ];

    /**
     * @return array{lat: float, lng: float, aproximada?: bool}|null
     */
    public function coordsDesdePedido(?Pedido $pedido): ?array
    {
        if ($pedido?->latitud && $pedido?->longitud) {
            return [
                'lat' => (float) $pedido->latitud,
                'lng' => (float) $pedido->longitud,
                'aproximada' => false,
            ];
        }

        $texto = strtolower(trim(
            ($pedido->nombre_planta ?? '').' '.($pedido->direccion_texto ?? '')
        ));

        foreach (self::PUNTOS_CIUDAD as $clave => $coords) {
            if ($texto !== '' && str_contains($texto, $clave)) {
                return [
                    'lat' => $coords[0],
                    'lng' => $coords[1],
                    'aproximada' => true,
                ];
            }
        }

        return null;
    }

    /**
     * @param  array<int, array{lat: float, lng: float}>  $coords
     */
    private function consultarOsrm(array $coords): ?array
    {
        if (count($coords) < 2) {
            return null;
        }

        $path = collect($coords)->map(fn ($p) => $p['lng'].','.$p['lat'])->implode(';');
        $url = 'https://router.project-osrm.org/route/v1/driving/'.$path.'?overview=full&geometries=geojson&steps=false';

        try {
            $response = Http::timeout(20)->get($url);
            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            if (($data['code'] ?? '') !== 'Ok' || empty($data['routes'][0]['geometry'])) {
                return null;
            }

            $geometry = $data['routes'][0]['geometry'];

            return [
                'type' => 'FeatureCollection',
                'features' => [[
                    'type' => 'Feature',
                    'properties' => [
                        'provider' => 'osrm',
                        'distance_m' => $data['routes'][0]['distance'] ?? null,
                        'duration_s' => $data['routes'][0]['duration'] ?? null,
                    ],
                    'geometry' => $geometry,
                ]],
            ];
        } catch (\Throwable $e) {
            Log::warning('OSRM routing failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * @param  array<int, array{lat: float, lng: float}>  $coords
     */
    private function lineaRectaGeoJson(array $coords): ?array
    {
        if (count($coords) < 2) {
            return null;
        }

        return [
            'type' => 'FeatureCollection',
            'features' => [[
                'type' => 'Feature',
                'properties' => ['provider' => 'straight', 'warning' => 'Sin ruta por calles (faltan coordenadas o servicio no disponible)'],
                'geometry' => [
                    'type' => 'LineString',
                    'coordinates' => collect($coords)->map(fn ($p) => [$p['lng'], $p['lat']])->values()->all(),
                ],
            ]],
        ];
    }
}
