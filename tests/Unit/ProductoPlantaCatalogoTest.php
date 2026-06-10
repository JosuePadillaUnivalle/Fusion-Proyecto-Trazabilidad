<?php

namespace Tests\Unit;

use App\Support\ProductoPlantaCatalogo;
use PHPUnit\Framework\TestCase;

class ProductoPlantaCatalogoTest extends TestCase
{
    public function test_recomendacion_materia_prima_para_pure_de_papa(): void
    {
        $rec = ProductoPlantaCatalogo::recomendacionMateriaPrima('Puré de papa', 100);

        $this->assertNotNull($rec);
        $this->assertSame(100.0, $rec['unidades']);
        $this->assertSame(30.0, $rec['salida_kg']);
        $this->assertSame(35.29, $rec['entrada_kg']);
    }

    public function test_recomendacion_null_si_no_es_pure(): void
    {
        $this->assertNull(ProductoPlantaCatalogo::recomendacionMateriaPrima('Papas fritas', 100));
    }
}
