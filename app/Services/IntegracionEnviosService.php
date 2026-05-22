<?php

namespace App\Services;

use App\Models\EnvioPendiente;
use App\Support\LocalOrgTrackFallback;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class IntegracionEnviosService
{
    private string $apiUrl;
    private int $timeout;
    private string $cacheKey = 'orgtrack_api_status';

    public function __construct()
    {
        // URL de la API externa (OrgTrack)
        $this->apiUrl = config('services.orgtrack.url', env('ORGTRACK_API_URL', 'http://192.168.0.11:8000'));
        $this->timeout = config('services.orgtrack.timeout', 10);
    }

    /**
     * Verificar si la API externa está disponible
     */
    public function verificarConexion(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->apiUrl . '/api/tipo-transporte');
            $disponible = $response->successful();
            
            Cache::put($this->cacheKey, $disponible, 30);
            
            return $disponible;
        } catch (\Exception $e) {
            Cache::put($this->cacheKey, false, 30);
            Log::warning('API OrgTrack no disponible: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener estado de conexión (desde cache o verificando)
     */
    public function estaConectado(): bool
    {
        return Cache::remember($this->cacheKey, 30, function () {
            return $this->verificarConexion();
        });
    }

    /**
     * Crear dirección en la API externa
     */
    public function crearDireccion(array $datos): array
    {
        if (!$this->estaConectado()) {
            return [
                'success' => false,
                'offline' => true,
                'message' => 'API no disponible'
            ];
        }

        try {
            $response = Http::timeout($this->timeout)
                ->post($this->apiUrl . '/api/public/direccion', $datos);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => $response->body()
            ];
        } catch (\Exception $e) {
            Cache::put($this->cacheKey, false, 30);
            return [
                'success' => false,
                'offline' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Crear envío en la API externa con tolerancia a fallos
     */
    public function crearEnvio(array $datos, ?int $usuarioId = null): array
    {
        // Intentar enviar directamente
        if ($this->estaConectado()) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post($this->apiUrl . '/api/public/envios', $datos);

                if ($response->successful()) {
                    Log::info('Envío exitoso a OrgTrack', ['response' => $response->json()]);
                    return [
                        'success' => true,
                        'message' => 'Envío registrado correctamente',
                        'data' => $response->json(),
                        'modo' => 'online'
                    ];
                }

                // Si falló, guardar en cola
                return $this->guardarEnCola($datos, $usuarioId, $response->body());

            } catch (\Exception $e) {
                Log::error('Error al enviar a OrgTrack: ' . $e->getMessage());
                Cache::put($this->cacheKey, false, 30);
                return $this->guardarEnCola($datos, $usuarioId, $e->getMessage());
            }
        }

        // Si no hay conexión, guardar en cola local
        return $this->guardarEnCola($datos, $usuarioId, 'API no disponible');
    }

    /**
     * Guardar envío en cola local cuando la API no está disponible
     */
    private function guardarEnCola(array $datos, ?int $usuarioId, string $error): array
    {
        $pendiente = EnvioPendiente::create([
            'datos_envio' => $datos,
            'estado' => 'pendiente',
            'intentos' => 1,
            'ultimo_error' => $error,
            'ultimo_intento' => now(),
            'usuarioid' => $usuarioId,
        ]);

        Log::info('Envío guardado en cola local', ['id' => $pendiente->id]);

        return [
            'success' => true,
            'message' => 'Sin conexión con el servidor. El envío se guardó localmente y se sincronizará automáticamente cuando la conexión esté disponible.',
            'data' => ['id_local' => $pendiente->id],
            'modo' => 'offline',
            'pendiente' => true
        ];
    }

    /**
     * Sincronizar envíos pendientes cuando la API vuelva
     */
    public function sincronizarPendientes(): array
    {
        if (!$this->verificarConexion()) {
            return [
                'success' => false,
                'message' => 'API aún no disponible',
                'sincronizados' => 0
            ];
        }

        $pendientes = EnvioPendiente::where('estado', 'pendiente')
            ->orWhere(function ($q) {
                $q->where('estado', 'fallido')->where('intentos', '<', 5);
            })
            ->get();

        $sincronizados = 0;
        $fallidos = 0;

        foreach ($pendientes as $pendiente) {
            try {
                $datos = $pendiente->datos_envio;
                
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post($this->apiUrl . '/api/public/envios', $datos);

                if ($response->successful()) {
                    $pendiente->update([
                        'estado' => 'enviado',
                        'enviado_at' => now(),
                    ]);
                    $sincronizados++;
                    Log::info('Envío pendiente sincronizado', ['id' => $pendiente->id]);
                } else {
                    $this->marcarFallido($pendiente, $response->body());
                    $fallidos++;
                }
            } catch (\Exception $e) {
                $this->marcarFallido($pendiente, $e->getMessage());
                $fallidos++;
            }
        }

        return [
            'success' => true,
            'message' => "Sincronización completada",
            'sincronizados' => $sincronizados,
            'fallidos' => $fallidos,
            'pendientes_restantes' => EnvioPendiente::where('estado', 'pendiente')->count()
        ];
    }

    /**
     * Marcar envío como fallido
     */
    private function marcarFallido(EnvioPendiente $pendiente, string $error): void
    {
        $pendiente->update([
            'estado' => $pendiente->intentos >= 4 ? 'fallido_permanente' : 'fallido',
            'intentos' => $pendiente->intentos + 1,
            'ultimo_error' => $error,
            'ultimo_intento' => now(),
        ]);
    }

    /**
     * Obtener tipos de transporte (con cache local para modo offline)
     */
    public function getTiposTransporte(): array
    {
        $cacheKey = 'orgtrack_tipos_transporte';

        $cached = Cache::get($cacheKey);
        if (! $this->estaConectado() && is_array($cached) && $cached !== []) {
            return $cached;
        }

        if ($this->estaConectado()) {
            try {
                $response = Http::timeout(5)->get($this->apiUrl . '/api/tipo-transporte');
                if ($response->successful()) {
                    $tipos = $response->json();
                    if (is_array($tipos) && isset($tipos['data']) && is_array($tipos['data'])) {
                        $tipos = $tipos['data'];
                    }
                    if (is_array($tipos) && $tipos !== []) {
                        Cache::put($cacheKey, $tipos, 3600);

                        return $tipos;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error obteniendo tipos transporte: ' . $e->getMessage());
            }
        }

        $local = LocalOrgTrackFallback::tiposTransporteList();
        if ($local !== []) {
            return $local;
        }

        return is_array($cached) ? $cached : [];
    }

    /**
     * Obtener todos los envíos
     */
    public function getEnvios(): array
    {
        $cacheKey = 'orgtrack_envios_all';
        
        if (!$this->estaConectado()) {
            return Cache::get($cacheKey, []);
        }

        try {
            $response = Http::timeout(10)->get($this->apiUrl . '/api/public/envios/all');
            if ($response->successful()) {
                $envios = $response->json();
                Cache::put($cacheKey, $envios, 60);
                return $envios;
            }
        } catch (\Exception $e) {
            Log::warning('Error obteniendo envíos: ' . $e->getMessage());
        }

        return Cache::get($cacheKey, []);
    }

    /**
     * Obtener detalle de un envío
     */
    public function getEnvioDetalle(int $id): ?array
    {
        $cacheKey = "orgtrack_envio_{$id}";
        
        if (!$this->estaConectado()) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::timeout(10)->get($this->apiUrl . "/api/public/envios/{$id}/seguimiento");
            if ($response->successful()) {
                $envio = $response->json();
                Cache::put($cacheKey, $envio, 60);
                return $envio;
            }
        } catch (\Exception $e) {
            Log::warning('Error obteniendo detalle envío: ' . $e->getMessage());
        }

        return Cache::get($cacheKey);
    }

    /**
     * Obtener estadísticas de la cola
     */
    public function getEstadisticasCola(): array
    {
        return [
            'pendientes' => EnvioPendiente::where('estado', 'pendiente')->count(),
            'fallidos' => EnvioPendiente::where('estado', 'fallido')->count(),
            'enviados_hoy' => EnvioPendiente::where('estado', 'enviado')
                ->whereDate('enviado_at', today())->count(),
            'api_conectada' => $this->estaConectado(),
        ];
    }

    /**
     * Obtener URL de la API
     */
    public function getApiUrl(): string
    {
        return $this->apiUrl;
    }
}