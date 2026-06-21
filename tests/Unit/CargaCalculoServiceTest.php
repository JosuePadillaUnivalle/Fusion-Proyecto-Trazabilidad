<?php

namespace Tests\Unit;

use App\Services\CargaCalculoService;
use App\Support\LoteAgricolaNombre;
use App\Support\SimulacionRutaCatalogo;
use Tests\TestCase;

class CargaCalculoServiceTest extends TestCase
{
    public function test_calcula_peso_y_empaques_por_unidades(): void
    {
        $svc = new CargaCalculoService();
        $r = $svc->calcular([
            'conteo_por_empaque' => 184,
            'peso_promedio_kg' => 0.125,
            'tara_kg' => 1.2,
            'forma_pedido' => 'unidades',
            'cantidad_pedido' => 1000,
            'unidades_por_pallet' => 48,
        ]);

        $this->assertSame(6, $r['empaques_calculados']);
        $this->assertSame(1000, $r['unidades_totales']);
        $this->assertGreaterThan(120, $r['peso_neto_kg']);
    }

    public function test_simulacion_dura_dos_minutos(): void
    {
        $this->assertSame(120, SimulacionRutaCatalogo::DURACION_DEMO_SEG);
        $this->assertSame(120, SimulacionRutaCatalogo::duracionEfectiva(3600));
    }

    public function test_lote_agricola_nombre_secuencial(): void
    {
        $n1 = LoteAgricolaNombre::formatear('Tomate', 1);
        $n2 = LoteAgricolaNombre::formatear('Tomate', 2);

        $this->assertStringContainsString('Tomate - Lote 001', $n1);
        $this->assertStringContainsString('Tomate - Lote 002', $n2);
    }
}
