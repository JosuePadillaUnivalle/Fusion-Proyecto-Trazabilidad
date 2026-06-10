<?php

namespace Tests\Unit;

use App\Support\EtiquetaDemo;
use PHPUnit\Framework\TestCase;

class EtiquetaDemoTest extends TestCase
{
    public function test_detecta_nombres_de_prueba(): void
    {
        $this->assertTrue(EtiquetaDemo::esDemo('Test 3'));
        $this->assertTrue(EtiquetaDemo::esDemo('Lote Demo Manual F1'));
        $this->assertTrue(EtiquetaDemo::esDemo('Mercado Prueba'));
    }

    public function test_no_marca_nombres_operativos(): void
    {
        $this->assertFalse(EtiquetaDemo::esDemo('Fungicida cobre hidróxido'));
        $this->assertFalse(EtiquetaDemo::esDemo('Mercado Alvaro'));
        $this->assertFalse(EtiquetaDemo::esDemo('Lote Zanahoria Imperator'));
    }
}
