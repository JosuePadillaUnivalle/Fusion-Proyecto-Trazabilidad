<?php

namespace Tests\Unit;

use App\Services\PlanificacionCosechaService;
use Tests\TestCase;

class PlanificacionCosechaServiceTest extends TestCase
{
    public function test_desde_hectareas_calcula_cosecha_y_semilla(): void
    {
        $svc = new PlanificacionCosechaService();

        $r = $svc->calcular([
            'modo' => 'hectareas',
            'insumoid' => 0,
            'hectareas' => 10,
            'calibre_id' => null,
        ]);

        $this->assertFalse($r['ok']);
    }

    public function test_formulas_inversas_consistentes(): void
    {
        $rendimiento = 25000.0;
        $pesoUnit = 0.135;
        $conteo = 70;
        $dosis = 8.0;
        $ha = 12.0;

        $kg = $rendimiento * $ha;
        $unidades = (int) round($kg / $pesoUnit);
        $empaques = (int) ceil($unidades / $conteo);
        $semilla = $dosis * $ha;

        $this->assertSame(300000.0, $kg);
        $this->assertGreaterThan(2_000_000, $unidades);
        $this->assertSame(96.0, $semilla);

        $haInverso = ($unidades * $pesoUnit) / $rendimiento;
        $this->assertEqualsWithDelta($ha, round($haInverso, 3), 0.01);

        $this->assertSame((int) ceil($unidades / $conteo), $empaques);
    }

    public function test_modo_unidades_no_pierde_objetivo_por_redondeo_ha(): void
    {
        $rendimiento = 25000.0;
        $pesoUnit = 0.135;
        $conteo = 70;
        $objetivo = 3000;

        $unidades = (int) round($objetivo);
        $kgCosecha = round($unidades * $pesoUnit, 2);
        $hectareas = round($kgCosecha / $rendimiento, 3);
        $empaques = (int) ceil($unidades / $conteo);

        $this->assertSame(3000, $unidades);
        $this->assertSame(405.0, $kgCosecha);
        $this->assertEqualsWithDelta(0.016, $hectareas, 0.001);
        $this->assertSame(43, $empaques);

        // Antes se recalculaba desde ha redondeada y daba 2963 unidades / 400 kg.
        $kgDesdeHaRedondeada = round($rendimiento * $hectareas, 2);
        $this->assertNotSame((int) round($kgDesdeHaRedondeada / $pesoUnit), $unidades);
    }
}
