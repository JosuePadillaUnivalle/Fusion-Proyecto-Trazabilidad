<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Clima;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GuardarClimaCommand extends Command
{
    protected $signature = 'clima:guardar {--ciudad=Santa Cruz de la Sierra : Ciudad para obtener el clima}';
    protected $description = 'Guarda el clima actual desde OpenWeather API';

    public function handle()
    {
        $apiKey = env('OPENWEATHER_API_KEY', '');
        
        if (empty($apiKey)) {
            $this->error('No hay API key de OpenWeather configurada');
            return 1;
        }

        $ciudad = $this->option('ciudad');
        $pais = 'BO';

        // Verificar si ya existe registro de hoy
        $existeHoy = Clima::whereNull('loteid')
            ->whereDate('fecha', today())
            ->exists();

        if ($existeHoy) {
            $this->info('Ya existe un registro de clima para hoy');
            return 0;
        }

        try {
            $response = Http::timeout(15)->get("https://api.openweathermap.org/data/2.5/weather", [
                'q' => "{$ciudad},{$pais}",
                'appid' => $apiKey,
                'units' => 'metric',
                'lang' => 'es'
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $clima = Clima::create([
                    'loteid' => null,
                    'fecha' => now(),
                    'temperatura' => round($data['main']['temp'], 1),
                    'humedad' => $data['main']['humidity'],
                    'lluvia' => $data['rain']['1h'] ?? $data['rain']['3h'] ?? 0,
                    'viento' => round($data['wind']['speed'] * 3.6, 1),
                    'presion' => $data['main']['pressure'],
                    'descripcion' => $data['weather'][0]['description'],
                    'icono' => $data['weather'][0]['icon'],
                    'observaciones' => "Guardado automáticamente - {$ciudad}",
                ]);

                $this->info("✅ Clima guardado: {$clima->temperatura}°C, {$clima->descripcion}");
                Log::info("Clima guardado automáticamente", ['clima_id' => $clima->climaid]);
                
                return 0;
            }

            $this->error('Error en la respuesta de la API');
            return 1;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Error al guardar clima automático: ' . $e->getMessage());
            return 1;
        }
    }
}