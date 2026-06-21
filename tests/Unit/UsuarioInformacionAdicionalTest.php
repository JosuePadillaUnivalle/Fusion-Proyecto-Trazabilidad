<?php

namespace Tests\Unit;

use App\Support\UsuarioInformacionAdicional;
use PHPUnit\Framework\TestCase;

class UsuarioInformacionAdicionalTest extends TestCase
{
    public function test_json_demo_logistico_no_se_muestra_en_detalle_usuario(): void
    {
        $json = '{"demo_xtra2":{"ci":"7894561","licencia":"C","estado_logistico":"Disponible"}}';

        $lineas = UsuarioInformacionAdicional::lineasParaVista($json);

        $this->assertCount(0, $lineas);
    }

    public function test_texto_plano_sin_prefijo_demo(): void
    {
        $lineas = UsuarioInformacionAdicional::lineasParaVista('[MOD-ADMIN] Usuario de prueba.');

        $this->assertCount(1, $lineas);
        $this->assertSame('Usuario de prueba.', $lineas[0]['value']);
    }
}
