<?php

namespace Tests\Unit;

use App\Support\ParametroRangoPlanta;
use Tests\TestCase;

class ParametroRangoPlantaTest extends TestCase
{
    public function test_validar_rango_rechaza_maximo_menor_que_minimo(): void
    {
        $error = ParametroRangoPlanta::validarRango(null, 1, 80.0, 50.0, 'Temperatura');

        $this->assertNotNull($error);
    }

    public function test_limites_escala_desde_unidad(): void
    {
        $lim = ParametroRangoPlanta::limitesEscala('escala 1-10');

        $this->assertSame(1.0, $lim['min']);
        $this->assertSame(10.0, $lim['max']);
    }

    public function test_combinar_limites_intersecta_maquina_y_escala(): void
    {
        $lim = ParametroRangoPlanta::combinarLimites(
            ['min' => 5.0, 'max' => 10.0],
            ['min' => 1.0, 'max' => 10.0],
        );

        $this->assertSame(5.0, $lim['min']);
        $this->assertSame(10.0, $lim['max']);
    }
}
