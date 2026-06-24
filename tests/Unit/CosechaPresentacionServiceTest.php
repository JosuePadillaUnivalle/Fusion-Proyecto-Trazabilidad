<?php

namespace Tests\Unit;

use App\Services\CosechaPresentacionService;
use Tests\TestCase;

class CosechaPresentacionServiceTest extends TestCase
{
    public function test_desde_kg_calcula_cajas_y_unidades(): void
    {
        $calibre = new \App\Models\CatalogoTamanoConteo([
            'catalogotamanoconteoid' => 1,
            'nombre' => 'Mediana (120-150 g)',
            'peso_promedio_kg' => 0.135,
            'conteo_por_empaque' => 70,
        ]);
        $calibre->setRelation('tipoEmpaque', (object) ['nombre' => 'Caja de cartón']);

        $svc = new CosechaPresentacionService(
            app(\App\Services\AlmacenCapacidadService::class),
            app(\App\Services\PlanificacionCosechaService::class),
        );

        $r = $svc->desdeKg(100000.0, $calibre);

        $this->assertTrue($r['ok']);
        $this->assertSame(740741, $r['unidades']);
        $this->assertSame(10583, $r['empaques']);
        $this->assertSame('Cajas', $r['empaque_label']);
    }

    public function test_etiqueta_empaque_saco_no_se_confunde_con_caja(): void
    {
        $this->assertSame('Saco', CosechaPresentacionService::etiquetaEmpaque('Saco'));
        $this->assertSame('Sacos', CosechaPresentacionService::etiquetaEmpaquePlural('Saco'));
        $this->assertSame('Caja', CosechaPresentacionService::etiquetaEmpaque('Caja de cartón'));
    }

    public function test_cosecha_en_campo_mapea_saco_a_caja(): void
    {
        $this->assertSame('Caja de cartón', CosechaPresentacionService::tipoEmpaqueParaCosechaEnCampo('Saco'));

        $calibre = new \App\Models\CatalogoTamanoConteo([
            'catalogotamanoconteoid' => 17,
            'nombre' => 'Mediana (150-200 g)',
            'peso_promedio_kg' => 0.175,
            'conteo_por_empaque' => 50,
        ]);
        $calibre->setRelation('tipoEmpaque', (object) ['nombre' => 'Saco']);

        $svc = new CosechaPresentacionService(
            app(\App\Services\AlmacenCapacidadService::class),
            app(\App\Services\PlanificacionCosechaService::class),
        );

        $r = $svc->desdeKg(5250.0, $calibre);

        $this->assertTrue($r['ok']);
        $this->assertSame(30000, $r['unidades']);
        $this->assertSame(600, $r['empaques']);
        $this->assertSame('Cajas', $r['empaque_label']);
    }
}
