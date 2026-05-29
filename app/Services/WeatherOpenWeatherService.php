<?php

namespace App\Services;

use App\Models\Clima;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class WeatherOpenWeatherService
{
    public function cacheKey(): string
    {
        $city = $this->normalizedCity();
        $country = strtoupper((string) config('services.weather.country', 'BO'));

        return 'openweather:'.strtolower($city).':'.$country;
    }

    public function ttlSeconds(): int
    {
        return (int) config('services.weather.cache_ttl', 1200);
    }

    /**
     * Para la vista: caché válido → BD local → (opcional) refresco API en segundo plano.
     *
     * @return array<string, mixed>
     */
    public function resolveForDisplay(): array
    {
        $cached = $this->fromCache();
        if ($this->hasActual($cached)) {
            return $cached;
        }

        $local = $this->fromLocalDatabase();
        if ($this->hasActual($local)) {
            $local['needs_api_refresh'] = $this->hasApiKey();

            return $local;
        }

        if ($this->hasApiKey()) {
            return [
                'actual' => null,
                'pronostico' => [],
                'error' => null,
                'needs_api_refresh' => true,
            ];
        }

        return [
            'actual' => null,
            'pronostico' => [],
            'error' => 'No hay registros climáticos. Configura WEATHER_API_KEY en .env o espera el registro automático.',
            'needs_api_refresh' => false,
        ];
    }

    /**
     * API (sin cachear errores) → fallback local.
     *
     * @return array<string, mixed>
     */
    public function resolveWithApi(bool $forceRefresh = false): array
    {
        if ($forceRefresh) {
            Cache::forget($this->cacheKey());
        }

        if ($this->hasApiKey()) {
            $api = $this->fetchFromApi();
            if ($this->hasActual($api)) {
                Cache::put($this->cacheKey(), $api, $this->ttlSeconds());

                return $api;
            }
        }

        $local = $this->fromLocalDatabase();
        if ($this->hasActual($local)) {
            $local['aviso'] = $this->hasApiKey()
                ? 'OpenWeather no respondió; se muestra el último registro guardado en Fusion.'
                : 'Sin API Key; se muestra el último registro guardado en Fusion.';

            return $local;
        }

        return [
            'actual' => null,
            'pronostico' => [],
            'error' => 'No hay datos climáticos disponibles. Revisa la conexión o el historial.',
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function fromCache(): ?array
    {
        $cached = Cache::get($this->cacheKey());

        if (! is_array($cached) || ! $this->hasActual($cached)) {
            if (is_array($cached)) {
                Cache::forget($this->cacheKey());
            }

            return null;
        }

        return $cached;
    }

    /**
     * @return array<string, mixed>
     */
    public function fromLocalDatabase(): array
    {
        if (! Schema::hasTable('clima')) {
            return ['actual' => null, 'pronostico' => [], 'error' => null];
        }

        $latest = Clima::query()->orderByDesc('fecha')->first();
        if (! $latest) {
            return ['actual' => null, 'pronostico' => [], 'error' => null];
        }

        $city = $this->normalizedCity();
        $icono = $latest->icono ?: '02d';
        $hora = $latest->fecha ? Carbon::parse($latest->fecha)->hour : (int) date('G');

        $actual = [
            'ciudad' => $city,
            'pais' => strtoupper((string) config('services.weather.country', 'BO')),
            'temperatura' => round((float) ($latest->temperatura ?? 0), 1),
            'humedad' => (int) round((float) ($latest->humedad ?? 0)),
            'viento_kmh' => round((float) ($latest->viento ?? 0), 1),
            'presion' => (int) ($latest->presion ?? 0),
            'descripcion' => (string) ($latest->descripcion ?: $latest->observaciones ?: 'Registro local'),
            'icono' => $icono,
            'amanecer' => '06:30',
            'atardecer' => '18:15',
            'es_noche' => str_ends_with($icono, 'n') || $hora < 6 || $hora >= 19,
            'lluvia' => (float) ($latest->lluvia ?? 0),
            'fuente' => 'registro_local',
            'registrado_el' => $latest->fecha?->format('d/m/Y H:i'),
        ];

        return [
            'actual' => $actual,
            'pronostico' => $this->pronosticoDesdeHistorial(),
            'error' => null,
            'fuente' => 'registro_local',
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function pronosticoDesdeHistorial(): array
    {
        $rows = Clima::query()
            ->where('fecha', '>=', now()->subDays(10))
            ->orderByDesc('fecha')
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $porDia = [];
        foreach ($rows as $row) {
            if (! $row->fecha) {
                continue;
            }
            $key = $row->fecha->format('Y-m-d');
            if (! isset($porDia[$key])) {
                $porDia[$key] = $row;
            }
        }

        $pronostico = [];
        foreach (array_slice(array_values($porDia), 0, 5) as $row) {
            $fecha = Carbon::parse($row->fecha);
            $pronostico[] = [
                'dia' => mb_substr($fecha->locale('es')->dayName, 0, 3),
                'fecha' => $fecha->format('d/m'),
                'temperatura' => round((float) ($row->temperatura ?? 0)),
                'descripcion' => (string) ($row->descripcion ?: $row->observaciones ?: 'Registro'),
                'icono' => (string) ($row->icono ?: '02d'),
            ];
        }

        return $pronostico;
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    private function hasActual(?array $payload): bool
    {
        return is_array($payload) && ! empty($payload['actual']);
    }

    private function hasApiKey(): bool
    {
        return ! blank(config('services.weather.key'));
    }

    private function normalizedCity(): string
    {
        $city = trim((string) config('services.weather.city', 'Santa Cruz de la Sierra'), " \t\n\r\0\x0B\"'");

        return $city !== '' ? $city : 'Santa Cruz de la Sierra';
    }

    /**
     * @return array{actual: ?array, pronostico: list, error: ?string}
     */
    private function fetchFromApi(): array
    {
        $city = $this->normalizedCity();
        $country = strtoupper((string) config('services.weather.country', 'BO'));
        $units = config('services.weather.units', 'metric');
        $apiKey = config('services.weather.key');

        $params = [
            'q' => "{$city},{$country}",
            'appid' => $apiKey,
            'units' => $units,
            'lang' => 'es',
        ];

        try {
            $responses = Http::pool(fn ($pool) => [
                $pool->as('current')->timeout(10)->connectTimeout(5)
                    ->get('https://api.openweathermap.org/data/2.5/weather', $params),
                $pool->as('forecast')->timeout(10)->connectTimeout(5)
                    ->get('https://api.openweathermap.org/data/2.5/forecast', $params),
            ]);

            $currentResponse = $responses['current'] ?? null;
            $forecastResponse = $responses['forecast'] ?? null;

            if (! $currentResponse instanceof \Illuminate\Http\Client\Response) {
                throw new \RuntimeException('Respuesta inválida del servicio de clima (current).');
            }

            if ($currentResponse->status() === 401) {
                return [
                    'actual' => null,
                    'pronostico' => [],
                    'error' => 'La API Key de clima no es válida o aún no está activa en OpenWeather.',
                ];
            }

            if (! $currentResponse->successful()) {
                Log::warning('OpenWeather current HTTP '.$currentResponse->status(), [
                    'body' => substr($currentResponse->body(), 0, 300),
                ]);

                return [
                    'actual' => null,
                    'pronostico' => [],
                    'error' => 'No se pudo obtener la información climática en este momento.',
                ];
            }

            $actualJson = $currentResponse->json();
            $actual = [
                'ciudad' => $actualJson['name'] ?? $city,
                'pais' => $actualJson['sys']['country'] ?? $country,
                'temperatura' => round((float) ($actualJson['main']['temp'] ?? 0), 1),
                'humedad' => (int) ($actualJson['main']['humidity'] ?? 0),
                'viento_kmh' => round((float) ($actualJson['wind']['speed'] ?? 0) * 3.6, 1),
                'presion' => (int) ($actualJson['main']['pressure'] ?? 0),
                'descripcion' => (string) ($actualJson['weather'][0]['description'] ?? 'Sin datos'),
                'icono' => (string) ($actualJson['weather'][0]['icon'] ?? '01d'),
                'amanecer' => isset($actualJson['sys']['sunrise'])
                    ? Carbon::createFromTimestamp($actualJson['sys']['sunrise'])->format('H:i') : '06:30',
                'atardecer' => isset($actualJson['sys']['sunset'])
                    ? Carbon::createFromTimestamp($actualJson['sys']['sunset'])->format('H:i') : '18:15',
                'es_noche' => isset($actualJson['weather'][0]['icon'])
                    && str_ends_with($actualJson['weather'][0]['icon'], 'n'),
                'lluvia' => (float) ($actualJson['rain']['1h'] ?? $actualJson['rain']['3h'] ?? 0),
                'fuente' => 'openweather',
            ];

            $pronostico = [];
            if ($forecastResponse instanceof \Illuminate\Http\Client\Response && $forecastResponse->successful()) {
                $pronostico = $this->buildForecast($forecastResponse->json());
            }

            if ($pronostico === []) {
                $pronostico = $this->pronosticoDesdeHistorial();
            }

            return [
                'actual' => $actual,
                'pronostico' => $pronostico,
                'error' => null,
                'fuente' => 'openweather',
            ];
        } catch (\Throwable $e) {
            Log::warning('Clima no disponible: '.$e->getMessage());

            return [
                'actual' => null,
                'pronostico' => [],
                'error' => 'No se pudo obtener la información climática en este momento.',
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $forecastJson
     * @return list<array<string, mixed>>
     */
    private function buildForecast(array $forecastJson): array
    {
        $daily = [];

        foreach (($forecastJson['list'] ?? []) as $item) {
            $timestamp = (int) ($item['dt'] ?? 0);
            if ($timestamp <= 0) {
                continue;
            }
            $key = date('Y-m-d', $timestamp);
            $hour = (int) date('G', $timestamp);
            if (! isset($daily[$key]) || ($hour >= 11 && $hour <= 14)) {
                $daily[$key] = $item;
            }
        }

        $pronostico = [];
        foreach (array_slice(array_values($daily), 0, 5) as $item) {
            $timestamp = (int) $item['dt'];
            $pronostico[] = [
                'dia' => mb_substr(Carbon::createFromTimestamp($timestamp)->locale('es')->dayName, 0, 3),
                'fecha' => Carbon::createFromTimestamp($timestamp)->format('d/m'),
                'temperatura' => round((float) ($item['main']['temp'] ?? 0)),
                'descripcion' => (string) ($item['weather'][0]['description'] ?? 'Sin datos'),
                'icono' => (string) ($item['weather'][0]['icon'] ?? '01d'),
            ];
        }

        return $pronostico;
    }
}
