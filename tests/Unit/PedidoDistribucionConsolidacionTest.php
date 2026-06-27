<?php

namespace Tests\Unit;

use App\Models\DetallePedidoDistribucion;
use App\Models\Insumo;
use App\Models\InsumoPresentacion;
use App\Support\PedidoDistribucionConsolidacion;
use Tests\TestCase;

class PedidoDistribucionConsolidacionTest extends TestCase
{
    public function test_consolida_mismo_producto_lote_y_empaque(): void
    {
        $insumo = new Insumo(['nombre' => 'Cebolla en polvo']);
        $pres = new InsumoPresentacion(['nombre' => 'Lata 500g', 'tipoempaqueid' => 1, 'tipo_envase' => 'lata']);
        $pres->setRelation('tipoEmpaque', null);

        $d1 = new DetallePedidoDistribucion([
            'cantidad' => 100,
            'referencia_lote' => 'Lote 001',
            'insumo_presentacionid' => 1,
        ]);
        $d1->setRelation('insumo', $insumo);
        $d1->setRelation('presentacion', $pres);

        $d2 = new DetallePedidoDistribucion([
            'cantidad' => 50,
            'referencia_lote' => 'Lote 001',
            'insumo_presentacionid' => 1,
        ]);
        $d2->setRelation('insumo', $insumo);
        $d2->setRelation('presentacion', $pres);

        $grupos = PedidoDistribucionConsolidacion::consolidar([$d1, $d2]);

        $this->assertCount(1, $grupos);
        $this->assertSame(150.0, $grupos[0]['cantidad']);
        $this->assertStringContainsString('150', PedidoDistribucionConsolidacion::formatearEtiqueta($grupos[0]));
    }

    public function test_no_consolida_distinto_lote(): void
    {
        $insumo = new Insumo(['nombre' => 'Cebolla en polvo']);
        $pres = new InsumoPresentacion(['nombre' => 'Lata 500g', 'tipoempaqueid' => 1]);
        $pres->setRelation('tipoEmpaque', null);

        $d1 = new DetallePedidoDistribucion(['cantidad' => 100, 'referencia_lote' => 'Lote 001']);
        $d1->setRelation('insumo', $insumo);
        $d1->setRelation('presentacion', $pres);

        $d2 = new DetallePedidoDistribucion(['cantidad' => 50, 'referencia_lote' => 'Lote 002']);
        $d2->setRelation('insumo', $insumo);
        $d2->setRelation('presentacion', $pres);

        $this->assertCount(2, PedidoDistribucionConsolidacion::consolidar([$d1, $d2]));
    }
}
