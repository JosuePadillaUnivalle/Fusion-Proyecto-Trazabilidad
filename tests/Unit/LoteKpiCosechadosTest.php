<?php

namespace Tests\Unit;

use App\Support\EstadoLoteCatalogo;
use PHPUnit\Framework\TestCase;

class LoteKpiCosechadosTest extends TestCase
{
    public function test_slug_finalizado_cuenta_como_cosechado_en_kpi(): void
    {
        $this->assertSame('finalizado', EstadoLoteCatalogo::slugFromNombre('Finalizado'));
        $this->assertSame('en_crecimiento', EstadoLoteCatalogo::slugFromNombre('En crecimiento'));
        $this->assertSame('planificado', EstadoLoteCatalogo::slugFromNombre('Planificación'));
    }

    public function test_colores_mapa_por_slug(): void
    {
        $this->assertSame('#22c55e', EstadoLoteCatalogo::colorMapaPorSlug('en_crecimiento'));
        $this->assertSame('#475569', EstadoLoteCatalogo::colorMapaPorSlug('finalizado'));
        $this->assertNotSame('#6c757d', EstadoLoteCatalogo::colorMapaPorSlug('en_crecimiento'));
    }

    public function test_lote_es_cerrado_incluye_finalizado(): void
    {
        $this->assertTrue(EstadoLoteCatalogo::loteEsCerrado('Finalizado'));
        $this->assertTrue(EstadoLoteCatalogo::loteEsCerrado('Cosechado'));
        $this->assertFalse(EstadoLoteCatalogo::loteEsCerrado('En crecimiento'));
    }
}
