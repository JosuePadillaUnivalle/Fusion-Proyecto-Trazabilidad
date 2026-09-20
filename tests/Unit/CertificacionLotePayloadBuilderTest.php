<?php

namespace Tests\Unit;

use App\Services\Blockchain\CertificacionLotePayloadBuilder;
use Tests\TestCase;

class CertificacionLotePayloadBuilderTest extends TestCase
{
    public function test_hash_canonico_no_depende_del_orden_de_las_claves(): void
    {
        $builder = app(CertificacionLotePayloadBuilder::class);

        $payloadA = [
            'lote' => [
                'nombre' => 'Lote Norte',
                'loteId' => 7,
            ],
            'evento' => 'certificacion_lote',
        ];

        $payloadB = [
            'evento' => 'certificacion_lote',
            'lote' => [
                'loteId' => 7,
                'nombre' => 'Lote Norte',
            ],
        ];

        $this->assertSame($builder->jsonCanonico($payloadA), $builder->jsonCanonico($payloadB));
        $this->assertSame($builder->hash($payloadA), $builder->hash($payloadB));
    }
}
