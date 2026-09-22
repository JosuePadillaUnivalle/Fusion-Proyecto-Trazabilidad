<?php

namespace App\Services\Blockchain;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BlockchainClient
{
    public function enabled(): bool
    {
        return (bool) config('blockchain.enabled')
            && filled(config('blockchain.api_url'))
            && filled(config('blockchain.api_key'));
    }

    public function baseUrl(): string
    {
        return (string) config('blockchain.api_url');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $headers
     * @return array{ok: bool, status: int, estado: ?string, solicitud_id: ?string, operacion_id: ?string, dato_id: ?string, tx_id: ?string, body: mixed, message: string}
     */
    public function crearDato(string $datoId, string $tipo, array $payload, array $headers = []): array
    {
        return $this->request('POST', '/datos', [
            'datoId' => $datoId,
            'tipo' => $tipo,
            'payload' => $payload,
        ], $headers);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $headers
     * @return array{ok: bool, status: int, estado: ?string, solicitud_id: ?string, operacion_id: ?string, dato_id: ?string, tx_id: ?string, body: mixed, message: string}
     */
    public function actualizarDato(string $datoId, string $tipo, array $payload, array $headers = []): array
    {
        return $this->request('PUT', '/datos/'.rawurlencode($datoId), [
            'datoId' => $datoId,
            'tipo' => $tipo,
            'payload' => $payload,
        ], $headers);
    }

    /**
     * @return array{ok: bool, status: int, estado: ?string, solicitud_id: ?string, operacion_id: ?string, dato_id: ?string, tx_id: ?string, body: mixed, message: string}
     */
    public function consultarDato(string $datoId): array
    {
        return $this->request('GET', '/datos/'.rawurlencode($datoId));
    }

    /**
     * @return array{ok: bool, status: int, estado: ?string, solicitud_id: ?string, operacion_id: ?string, dato_id: ?string, tx_id: ?string, body: mixed, message: string}
     */
    public function consultarOperacion(string $operacionId): array
    {
        return $this->request('GET', '/operaciones/'.rawurlencode($operacionId));
    }

    /**
     * @param  array<string, mixed>|null  $json
     * @param  array<string, string>  $headers
     * @return array{ok: bool, status: int, estado: ?string, solicitud_id: ?string, operacion_id: ?string, dato_id: ?string, tx_id: ?string, body: mixed, message: string}
     */
    private function request(string $method, string $path, ?array $json = null, array $headers = []): array
    {
        if (! $this->enabled()) {
            return [
                'ok' => false,
                'status' => 0,
                'estado' => null,
                'solicitud_id' => null,
                'operacion_id' => null,
                'dato_id' => null,
                'tx_id' => null,
                'body' => null,
                'message' => 'Blockchain deshabilitada o sin credenciales (BLOCKCHAIN_ENABLED / API_URL / API_KEY).',
            ];
        }

        $url = $this->baseUrl().$path;
        $timeout = (int) config('blockchain.timeout', 20);

        try {
            $pending = Http::timeout($timeout)
                ->acceptJson()
                ->withHeaders(array_filter([
                    'X-API-Key' => (string) config('blockchain.api_key'),
                    ...$headers,
                ], fn ($value) => filled($value)));

            /** @var Response $response */
            $response = match (strtoupper($method)) {
                'GET' => $pending->get($url),
                'PUT' => $pending->asJson()->put($url, $json ?? []),
                default => $pending->asJson()->post($url, $json ?? []),
            };

            $body = $response->json();
            $bodyArray = is_array($body) ? $body : [];
            $solicitud = is_array($bodyArray['solicitud'] ?? null) ? $bodyArray['solicitud'] : [];
            $txId = $this->firstString($bodyArray, ['txId', 'txid', 'txIdResultado'])
                ?? $this->firstString($solicitud, ['txIdResultado', 'txId', 'txid']);
            $estado = $this->firstString($bodyArray, ['estado'])
                ?? $this->firstString($solicitud, ['estado']);
            $solicitudId = $this->firstString($bodyArray, ['solicitudId'])
                ?? $this->firstString($solicitud, ['id']);
            $operacionId = $this->firstString($bodyArray, ['operacionId'])
                ?? $this->firstString($solicitud, ['operacionId']);
            $datoId = $this->firstString($bodyArray, ['datoId'])
                ?? $this->firstString($solicitud, ['datoId']);

            return [
                'ok' => $response->successful() && (($bodyArray['ok'] ?? true) !== false),
                'status' => $response->status(),
                'estado' => $estado,
                'solicitud_id' => $solicitudId,
                'operacion_id' => $operacionId,
                'dato_id' => $datoId,
                'tx_id' => $txId,
                'body' => $body,
                'message' => is_array($body)
                    ? (string) ($body['mensaje'] ?? $body['message'] ?? $body['motivo'] ?? $response->body())
                    : $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::warning('BlockchainClient: fallo HTTP', [
                'method' => $method,
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'status' => 0,
                'estado' => null,
                'solicitud_id' => null,
                'operacion_id' => null,
                'dato_id' => null,
                'tx_id' => null,
                'body' => null,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $body
     * @param  list<string>  $keys
     */
    private function firstString(array $body, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $body[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return $value;
            }
        }

        return null;
    }
}
