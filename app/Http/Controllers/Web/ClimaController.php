<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Clima;
use App\Models\Lote;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ClimaController extends Controller
{
    /**
     * Mostrar vista de clima con datos actuales, pronóstico e historial.
     */
    public function index()
    {
        $historial = Clima::where('fecha', '>=', now()->subDays(30))
            ->orderBy('fecha', 'desc')
            ->get();

        $weatherData = [
            'actual' => null,
            'pronostico' => [],
            'error' => null,
        ];

        if (blank(config('services.weather.key'))) {
            $weatherData['error'] = 'API Key no configurada. Agrega WEATHER_API_KEY en el archivo .env.';
            return view('climas.index', compact('historial', 'weatherData'));
        }

        $weatherData = $this->obtenerDatosClima();
        $this->guardarClimaDesdeActual($weatherData['actual'] ?? null);

        return view('climas.index', compact('historial', 'weatherData'));
    }

    private function obtenerDatosClima(): array
    {
        $city = config('services.weather.city', 'Santa Cruz');
        $country = config('services.weather.country', 'BO');
        $units = config('services.weather.units', 'metric');
        $apiKey = config('services.weather.key');

        $params = [
            'q' => "{$city},{$country}",
            'appid' => $apiKey,
            'units' => $units,
            'lang' => 'es',
        ];

        try {
            $currentResponse = Http::timeout(12)->get('https://api.openweathermap.org/data/2.5/weather', $params);
            $forecastResponse = Http::timeout(12)->get('https://api.openweathermap.org/data/2.5/forecast', $params);

            if ($currentResponse->status() === 401) {
                return [
                    'actual' => null,
                    'pronostico' => [],
                    'error' => 'La API Key de clima no es válida o aún no está activa en OpenWeather.',
                ];
            }

            if (! $currentResponse->successful()) {
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
                'amanecer' => isset($actualJson['sys']['sunrise']) ? Carbon::createFromTimestamp($actualJson['sys']['sunrise'])->format('H:i') : '--:--',
                'atardecer' => isset($actualJson['sys']['sunset']) ? Carbon::createFromTimestamp($actualJson['sys']['sunset'])->format('H:i') : '--:--',
                'es_noche' => isset($actualJson['weather'][0]['icon']) && str_ends_with($actualJson['weather'][0]['icon'], 'n'),
                'lluvia' => (float) ($actualJson['rain']['1h'] ?? $actualJson['rain']['3h'] ?? 0),
            ];

            $pronostico = [];
            if ($forecastResponse->successful()) {
                $forecastJson = $forecastResponse->json();
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
            }

            return [
                'actual' => $actual,
                'pronostico' => $pronostico,
                'error' => null,
            ];
        } catch (\Throwable $e) {
            Log::warning('Clima no disponible: ' . $e->getMessage());
            return [
                'actual' => null,
                'pronostico' => [],
                'error' => 'No se pudo obtener la información climática en este momento.',
            ];
        }
    }

    private function guardarClimaDesdeActual(?array $actual): void
    {
        if (! $actual) {
            return;
        }

        $existeReciente = Clima::where('fecha', '>=', now()->subHours(4))->exists();
        if ($existeReciente) {
            return;
        }

        $loteId = Lote::query()->value('loteid');
        if (! $loteId) {
            return;
        }

        Clima::create([
            'loteid' => $loteId,
            'fecha' => now(),
            'temperatura' => $actual['temperatura'] ?? null,
            'humedad' => $actual['humedad'] ?? null,
            'lluvia' => $actual['lluvia'] ?? 0,
            'viento' => $actual['viento_kmh'] ?? null,
            'presion' => $actual['presion'] ?? null,
            'descripcion' => $actual['descripcion'] ?? null,
            'icono' => $actual['icono'] ?? null,
            'observaciones' => isset($actual['descripcion']) ? ucfirst($actual['descripcion']) : null,
        ]);
    }
}