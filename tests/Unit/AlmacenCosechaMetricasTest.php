<?php

namespace Tests\Unit;

use App\Models\CatalogoTamanoConteo;
use App\Models\Produccion;
use App\Models\ProduccionAlmacenamiento;
use App\Models\UnidadMedida;
use App\Services\AlmacenCapacidadService;
use App\Support\AlmacenPlantaCosechaCatalogo;
use Tests\TestCase;

class AlmacenCosechaMetricasTest extends TestCase
{
    public function test_metricas_cosecha_agricola_muestra_unidades_y_kg_por_separado(): void
    {
        $kg = new UnidadMedida(['nombre' => 'Kilogramo', 'abreviatura' => 'kg', 'categoria' => 'peso']);
        $calibre = new CatalogoTamanoConteo([
            'catalogotamanoconteoid' => 1,
            'nombre' => 'Mediana (150-200 g)',
            'peso_promedio_kg' => 0.175,
            'conteo_por_empaque' => 50,
        ]);
        $calibre->setRelation('tipoEmpaque', (object) ['nombre' => 'Caja de cartón']);

        $produccion = new Produccion([
            'cantidad' => 6750,
            'cantidad_base' => 6750,
            'cantidad_unidades' => 38571,
            'cantidad_empaques' => 772,
            'catalogotamanoconteoid' => 1,
        ]);
        $produccion->setRelation('unidadMedida', $kg);
        $produccion->setRelation('catalogoTamanoConteo', $calibre);
        $produccion->setRelation('lote', null);

        $almacenamiento = new ProduccionAlmacenamiento([
            'cantidad' => 6750,
            'cantidad_unidades' => 38571,
            'cantidad_empaques' => 772,
            'catalogotamanoconteoid' => 1,
        ]);
        $almacenamiento->setRelation('unidadMedida', $kg);
        $almacenamiento->setRelation('catalogoTamanoConteo', $calibre);
        $almacenamiento->setRelation('produccion', $produccion);

        $metricas = AlmacenPlantaCosechaCatalogo::metricasProduccionAlmacenamiento(
            $almacenamiento,
            app(AlmacenCapacidadService::class)
        );

        $this->assertSame('unidades', $metricas['unidad']);
        $this->assertSame(38571.0, $metricas['cantidad']);
        $this->assertSame(6750.0, $metricas['kg']);
    }

    public function test_unidad_es_conteo_detecta_abreviaturas(): void
    {
        $this->assertTrue(AlmacenPlantaCosechaCatalogo::unidadEsConteo('und'));
        $this->assertTrue(AlmacenPlantaCosechaCatalogo::unidadEsConteo('Unidades'));
        $this->assertFalse(AlmacenPlantaCosechaCatalogo::unidadEsConteo('kg'));
    }
}
