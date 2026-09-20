<?php

namespace Tests\Unit;

use App\Services\Blockchain\BlockchainClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BlockchainClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'blockchain.enabled' => true,
            'blockchain.api_url' => 'http://blockchain.test',
            'blockchain.api_key' => 'test-key',
            'blockchain.timeout' => 5,
        ]);
    }

    public function test_crear_dato_interpreta_solicitud_pendiente(): void
    {
        Http::fake([
            'http://blockchain.test/datos' => Http::response([
                'ok' => true,
                'estado' => 'pendiente',
                'solicitudId' => 'sol-123',
                'operacionId' => 'op-123',
                'datoId' => 'AF-CERT-1',
            ], 202),
        ]);

        $result = app(BlockchainClient::class)->crearDato(
            'AF-CERT-1',
            'certificacion_lote',
            ['hashRegistro' => 'abc'],
            ['X-Operacion-Id' => 'op-123']
        );

        $this->assertTrue($result['ok']);
        $this->assertSame(202, $result['status']);
        $this->assertSame('pendiente', $result['estado']);
        $this->assertSame('sol-123', $result['solicitud_id']);
        $this->assertSame('op-123', $result['operacion_id']);
        $this->assertNull($result['tx_id']);

        Http::assertSent(fn ($request) => $request->hasHeader('X-API-Key', 'test-key')
            && $request->hasHeader('X-Operacion-Id', 'op-123'));
    }

    public function test_consultar_operacion_interpreta_tx_id_anidado(): void
    {
        Http::fake([
            'http://blockchain.test/operaciones/op-123' => Http::response([
                'ok' => true,
                'solicitud' => [
                    'id' => 'sol-123',
                    'estado' => 'aprobada',
                    'operacionId' => 'op-123',
                    'datoId' => 'AF-CERT-1',
                    'txIdResultado' => 'tx-abc',
                ],
            ]),
        ]);

        $result = app(BlockchainClient::class)->consultarOperacion('op-123');

        $this->assertTrue($result['ok']);
        $this->assertSame('aprobada', $result['estado']);
        $this->assertSame('sol-123', $result['solicitud_id']);
        $this->assertSame('op-123', $result['operacion_id']);
        $this->assertSame('AF-CERT-1', $result['dato_id']);
        $this->assertSame('tx-abc', $result['tx_id']);
    }
}
