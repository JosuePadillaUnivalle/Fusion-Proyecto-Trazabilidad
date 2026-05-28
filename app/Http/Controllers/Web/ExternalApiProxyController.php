<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\LocalOrgTrackFallback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalApiProxyController extends Controller
{
    protected function getToken(): ?string
    {
        $token = config('services.orgtrack.token');
        return is_string($token) && trim($token) !== '' ? $token : null;
    }

    protected function getTimeout(): int
    {
        return (int) config('services.orgtrack.timeout', 30);
    }

    /**
     * Get the base URL for the external API
     */
    protected function getBaseUrl(): string
    {
        return rtrim(
            config('services.orgtrack.url', config('external_api.orgtrack_url', '')),
            '/'
        );
    }

    protected function buildRequest()
    {
        $request = Http::timeout($this->getTimeout());
        $token = $this->getToken();

        if ($token !== null) {
            $request = $request->withToken($token);
        }

        return $request->acceptJson();
    }

    /**
     * Proxy GET request to external API
     */
    protected function proxyGet(string $endpoint)
    {
        $url = $this->getBaseUrl() . $endpoint;

        try {
            Log::info('External API Proxy GET', ['url' => $url]);

            $response = $this->buildRequest()->get($url);

            Log::info('External API GET Response', [
                'status' => $response->status(),
                'body_preview' => substr($response->body(), 0, 300)
            ]);

            // Handle non-JSON responses
            $jsonResponse = $response->json();
            if ($jsonResponse === null && $response->status() >= 400) {
                return response()->json([
                    'error' => 'No se pudo obtener información del servicio de envíos',
                    'status' => $response->status(),
                    'body' => substr($response->body(), 0, 500)
                ], $response->status());
            }

            return response()->json($jsonResponse, $response->status());
        } catch (\Exception $e) {
            Log::error('External API Proxy Error (GET): ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo conectar con el servicio de envíos', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Intenta OrgTrack; si falla, si hay error en JSON, o si la lista viene vacía (según config),
     * devuelve payload local compatible con las vistas de envíos.
     *
     * @param  callable(): array  $localPayload
     */
    protected function proxyGetWithLocalFallback(string $endpoint, callable $localPayload)
    {
        $base = trim($this->getBaseUrl());
        if ($base === '') {
            Log::info('OrgTrack: URL vacía, usando datos locales.');

            return response()->json($localPayload(), 200);
        }

        try {
            $url = $base.$endpoint;
            Log::info('External API Proxy GET', ['url' => $url]);

            $response = $this->buildRequest()->get($url);
            $jsonResponse = $response->json();
            $successful = $response->successful();

            $list = [];
            if (is_array($jsonResponse)) {
                if (isset($jsonResponse['data']) && is_array($jsonResponse['data'])) {
                    $list = $jsonResponse['data'];
                } elseif (array_is_list($jsonResponse)) {
                    $list = $jsonResponse;
                }
            }

            $hasErrorKey = is_array($jsonResponse) && array_key_exists('error', $jsonResponse);
            $emptyRemote = count($list) === 0;
            $whenEmpty = (bool) config('services.orgtrack.local_fallback_when_empty', true);

            $useLocal = ! $successful
                || $hasErrorKey
                || ($successful && $emptyRemote && $whenEmpty);

            if ($useLocal) {
                Log::info('OrgTrack: usando fallback local', [
                    'endpoint' => $endpoint,
                    'successful' => $successful,
                    'empty_remote' => $emptyRemote,
                ]);

                return response()->json($localPayload(), 200);
            }

            return response()->json($jsonResponse, $response->status());
        } catch (\Throwable $e) {
            Log::warning('OrgTrack: excepción en proxy GET, fallback local', ['message' => $e->getMessage()]);

            return response()->json($localPayload(), 200);
        }
    }

    /**
     * Proxy POST request to external API
     */
    protected function proxyPost(string $endpoint, Request $request)
    {
        $url = $this->getBaseUrl() . $endpoint;
        $data = $request->all();

        Log::info('External API Proxy POST', [
            'url' => $url,
            'data_keys' => array_keys($data)
        ]);

        try {
            $response = $this->buildRequest()->post($url, $data);

            Log::info('External API Proxy Response', [
                'status' => $response->status(),
                'body_preview' => substr($response->body(), 0, 500)
            ]);

            // If response is empty or not JSON, handle gracefully
            $jsonResponse = $response->json();
            if (empty($jsonResponse) && $response->status() >= 400) {
                return response()->json([
                    'error' => 'El servicio de envíos devolvió un error',
                    'status' => $response->status(),
                    'body' => $response->body()
                ], $response->status());
            }

            return response()->json($jsonResponse, $response->status());
        } catch (\Exception $e) {
            Log::error('External API Proxy Error (POST): ' . $e->getMessage());
            return response()->json(['error' => 'No se pudo conectar con el servicio de envíos', 'details' => $e->getMessage()], 500);
        }
    }

    // =============================================
    // CATÁLOGOS
    // =============================================

    public function getCategorias()
    {
        return $this->proxyGet('/api/catalogo-categorias');
    }

    public function getProductos()
    {
        return $this->proxyGet('/api/catalogo-productos');
    }

    public function getTiposEmpaque()
    {
        return $this->proxyGetWithLocalFallback(
            '/api/catalogo-tipos-empaque',
            fn () => LocalOrgTrackFallback::tiposEmpaqueCatalogList()
        );
    }

    public function getTamanoConteo()
    {
        return $this->proxyGet('/api/catalogo-tamano-conteo');
    }

    /**
     * Comprobación rápida de disponibilidad del proxy (siempre responde 200 en la app Fusion).
     */
    public function ping()
    {
        return response()->json([
            'ok' => true,
            'fuente' => 'fusion',
            'servidor' => now()->toIso8601String(),
        ]);
    }

    public function getTiposTransporte()
    {
        return $this->proxyGetWithLocalFallback(
            '/api/tipo-transporte',
            fn () => LocalOrgTrackFallback::tiposTransporteList()
        );
    }

    // =============================================
    // ENVÍOS
    // =============================================

    public function crearDireccion(Request $request)
    {
        return $this->proxyPost('/api/public/direccion', $request);
    }

    public function crearEnvioProductor(Request $request)
    {
        // Endpoint para crear envío desde el productor
        return $this->proxyPost('/api/public/envios', $request);
    }

    public function getEnvios(Request $request)
    {
        return $this->proxyGetWithLocalFallback(
            '/api/public/envios/all',
            fn () => LocalOrgTrackFallback::enviosPayload()
        );
    }

    public function getEnvioDetalle($id)
    {
        return $this->proxyGetWithLocalFallback(
            '/api/public/envios/'.$id.'/seguimiento',
            fn () => LocalOrgTrackFallback::envioDetallePayload($id)
        );
    }

    public function getTransportistas()
    {
        return $this->proxyGetWithLocalFallback(
            '/api/transportistas',
            fn () => LocalOrgTrackFallback::transportistasPayload()
        );
    }

    public function getVehiculos()
    {
        return $this->proxyGetWithLocalFallback(
            '/api/vehiculos',
            fn () => LocalOrgTrackFallback::vehiculosPayload()
        );
    }
}
