<?php

namespace Tests\Unit;

use App\Models\CatalogoTamanoConteo;
use App\Models\Insumo;
use App\Models\ProduccionAlmacenamiento;
use App\Models\TipoInsumo;
use App\Models\UnidadMedida;
use App\Support\PedidoCatalogo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PedidoCatalogoCalibresTest extends TestCase
{
    use RefreshDatabase;

    public function test_lista_calibres_para_insumo_variante_por_raiz_cultivo(): void
    {
        $tipo = TipoInsumo::create(['nombre' => 'Verdura', 'descripcion' => 'Test']);
        $unidad = UnidadMedida::create(['nombre' => 'Kilogramo', 'abreviatura' => 'kg', 'factorconversion' => 1]);

        $papaBase = Insumo::create([
            'nombre' => 'Papa',
            'stock' => 100,
            'tipoinsumoid' => $tipo->tipoinsumoid,
            'unidadmedidaid' => $unidad->unidadmedidaid,
        ]);
        $papaRubiola = Insumo::create([
            'nombre' => 'Papa Rubiola',
            'stock' => 50,
            'tipoinsumoid' => $tipo->tipoinsumoid,
            'unidadmedidaid' => $unidad->unidadmedidaid,
        ]);

        CatalogoTamanoConteo::create([
            'insumoid' => $papaBase->insumoid,
            'nombre' => 'Mediana (150-200 g)',
            'conteo_por_empaque' => 50,
            'peso_promedio_kg' => 0.175,
            'activo' => true,
        ]);

        $calibres = PedidoCatalogo::listarCalibresParaProducto('insumo:'.$papaRubiola->insumoid);

        $this->assertCount(1, $calibres);
        $this->assertSame('Mediana (150-200 g)', $calibres[0]['nombre']);
    }

    public function test_sincroniza_calibres_automaticamente_para_producto_sin_catalogo(): void
    {
        $tipo = TipoInsumo::create(['nombre' => 'Verdura', 'descripcion' => 'Test']);
        $unidad = UnidadMedida::create(['nombre' => 'Kilogramo', 'abreviatura' => 'kg', 'factorconversion' => 1]);

        $lechuga = Insumo::create([
            'nombre' => 'Lechuga',
            'stock' => 10,
            'tipoinsumoid' => $tipo->tipoinsumoid,
            'unidadmedidaid' => $unidad->unidadmedidaid,
        ]);

        $calibresAntes = PedidoCatalogo::listarCalibresParaProducto('insumo:'.$lechuga->insumoid);
        $this->assertGreaterThanOrEqual(1, count($calibresAntes));
        $this->assertTrue(collect($calibresAntes)->contains(
            fn (array $c) => str_contains($c['nombre'], 'Unidad')
        ));
    }

    public function test_stock_kg_cosecha_convierte_unidades_usando_calibre(): void
    {
        $tipo = TipoInsumo::create(['nombre' => 'Verdura', 'descripcion' => 'Test']);
        $unidadUnd = UnidadMedida::create(['nombre' => 'Unidad', 'abreviatura' => 'und', 'factorconversion' => 1]);

        $calibre = CatalogoTamanoConteo::create([
            'insumoid' => Insumo::create([
                'nombre' => 'Lechuga',
                'stock' => 0,
                'tipoinsumoid' => $tipo->tipoinsumoid,
                'unidadmedidaid' => $unidadUnd->unidadmedidaid,
            ])->insumoid,
            'nombre' => 'Unidad estándar (300-400 g)',
            'conteo_por_empaque' => 24,
            'peso_promedio_kg' => 0.350,
            'activo' => true,
        ]);

        $cosecha = new ProduccionAlmacenamiento([
            'cantidad' => 800,
            'unidadmedidaid' => $unidadUnd->unidadmedidaid,
            'catalogotamanoconteoid' => $calibre->catalogotamanoconteoid,
        ]);
        $cosecha->setRelation('unidadMedida', $unidadUnd);
        $cosecha->setRelation('catalogoTamanoConteo', $calibre);

        $kg = PedidoCatalogo::stockKgCosecha($cosecha);

        $this->assertEqualsWithDelta(280.0, $kg, 0.01);
    }

    public function test_deduplica_calibres_mismo_nombre_entre_insumos_relacionados(): void
    {
        $tipo = TipoInsumo::create(['nombre' => 'Verdura', 'descripcion' => 'Test']);
        $unidad = UnidadMedida::create(['nombre' => 'Kilogramo', 'abreviatura' => 'kg', 'factorconversion' => 1]);

        $cebolla = Insumo::create([
            'nombre' => 'Cebolla',
            'stock' => 10,
            'tipoinsumoid' => $tipo->tipoinsumoid,
            'unidadmedidaid' => $unidad->unidadmedidaid,
        ]);
        $cebollaBlanca = Insumo::create([
            'nombre' => 'Cebolla Blanca',
            'stock' => 10,
            'tipoinsumoid' => $tipo->tipoinsumoid,
            'unidadmedidaid' => $unidad->unidadmedidaid,
        ]);

        foreach ([$cebolla, $cebollaBlanca] as $ins) {
            CatalogoTamanoConteo::create([
                'insumoid' => $ins->insumoid,
                'nombre' => 'Mediana (120-150 g)',
                'conteo_por_empaque' => 70,
                'peso_promedio_kg' => 0.135,
                'activo' => true,
            ]);
            CatalogoTamanoConteo::create([
                'insumoid' => $ins->insumoid,
                'nombre' => 'Pequeña (80-100 g)',
                'conteo_por_empaque' => 100,
                'peso_promedio_kg' => 0.090,
                'activo' => true,
            ]);
        }

        $calibres = PedidoCatalogo::listarCalibresParaProducto('insumo:'.$cebollaBlanca->insumoid);

        $this->assertCount(2, $calibres);
        $nombres = array_column($calibres, 'nombre');
        sort($nombres);
        $this->assertSame(['Mediana (120-150 g)', 'Pequeña (80-100 g)'], $nombres);
    }

    public function test_etiqueta_cosecha_evita_cultivo_duplicado_en_lote(): void
    {
        $this->assertSame(
            'Cebolla - Lote 002',
            PedidoCatalogo::etiquetaCosechaSelector('Cebolla', 'Cebolla - Lote 002')
        );
        $this->assertSame(
            'Papa — Lote Norte',
            PedidoCatalogo::etiquetaCosechaSelector('Papa', 'Lote Norte')
        );
    }
}
