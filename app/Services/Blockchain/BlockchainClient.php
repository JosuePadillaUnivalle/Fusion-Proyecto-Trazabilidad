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
     * @return array{ok: bool, status: int, tx_id: ?string, body: mixed, message: string}
     */
    public function crearDato(string $datoId, string $tipo, array $payload): array
    {
        return $this->request('POST', '/datos', [
            'datoId' => $datoId,
            'tipo' => $tipo,
            'payload' => $payload,
        ]);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{ok: bool, status: int, tx_id: ?string, body: mixed, message: string}
     */
    public function actualizarDato(string $datoId, string $tipo, array $payload): array
    {
        return $this->request('PUT', '/datos/'.rawurlencode($datoId), [
            'datoId' => $datoId,
            'tipo' => $tipo,
            'payload' => $payload,
        ]);
    }

    /**
     * @return array{ok: bool, status: int, tx_id: ?string, body: mixed, message: string}
     */
    public function consultarDato(string $datoId): array
    {
        return $this->request('GET', '/datos/'.rawurlencode($datoId));
    }

    /**
     * @param  array<string, mixed>|null  $json
     * @return array{ok: bool, status: int, tx_id: ?string, body: mixed, message: string}
     */
    private function request(string $method, string $path, ?array $json = null): array
    {
        if (! $this->enabled()) {
            return [
                'ok' => false,
                'status' => 0,
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
                ->withHeaders([
                    'X-API-Key' => (string) config('blockchain.api_key'),
                ]);

            /** @var Response $response */
            $response = match (strtoupper($method)) {
                'GET' => $pending->get($url),
                'PUT' => $pending->asJson()->put($url, $json ?? []),
                default => $pending->asJson()->post($url, $json ?? []),
            };

            $body = $response->json();
            $txId = is_array($body) ? ($body['txId'] ?? $body['txid'] ?? null) : null;

            return [
                'ok' => $response->successful(),
                'status' => $response->status(),
                'tx_id' => is_string($txId) ? $txId : null,
                'body' => $body,
                'message' => is_array($body)
                    ? (string) ($body['mensaje'] ?? $body['message'] ?? $response->body())
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
                'tx_id' => null,
                'body' => null,
                'message' => $e->getMessage(),
            ];
        }
    }
}
