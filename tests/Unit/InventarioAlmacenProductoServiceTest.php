<?php

namespace Tests\Unit;

use App\Models\Almacen;
use App\Models\Insumo;
use App\Models\InsumoPresentacion;
use App\Models\InventarioPresentacionLote;
use App\Models\TipoInsumo;
use App\Models\UnidadMedida;
use App\Services\InventarioAlmacenProductoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventarioAlmacenProductoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_eliminar_un_insumo_no_borra_otro_con_lote_en_presentacion_compartida(): void
    {
        $tipoId = TipoInsumo::create(['nombre' => 'Producto terminado', 'descripcion' => 'Test'])->tipoinsumoid;
        $unidadId = UnidadMedida::create(['nombre' => 'Kilogramo', 'abreviatura' => 'kg', 'factorconversion' => 1])->unidadmedidaid;

        $almacen = Almacen::create([
            'nombre' => 'PDV Test',
            'ubicacion' => 'Test',
            'ambito' => 'punto_venta',
            'capacidad' => 1000,
            'unidadmedidaid' => $unidadId,
            'activo' => true,
        ]);

        $insumoA = Insumo::create([
            'nombre' => 'Producto A · Bolsa 1 kg',
            'tipoinsumoid' => $tipoId,
            'unidadmedidaid' => $unidadId,
            'stock' => 10,
            'almacenid' => $almacen->almacenid,
        ]);

        $insumoB = Insumo::create([
            'nombre' => 'Producto B · Frasco 340 g',
            'tipoinsumoid' => $tipoId,
            'unidadmedidaid' => $unidadId,
            'stock' => 8,
            'almacenid' => $almacen->almacenid,
        ]);

        $presentacion = InsumoPresentacion::create([
            'insumoid' => $insumoA->insumoid,
            'nombre' => 'Bolsa 1 kg',
            'tipo_envase' => 'bolsa',
            'peso_neto_kg' => 1,
            'activo' => true,
        ]);

        InventarioPresentacionLote::create([
            'almacenid' => $almacen->almacenid,
            'insumoid' => $insumoA->insumoid,
            'insumo_presentacionid' => $presentacion->insumo_presentacionid,
            'cantidad_unidades' => 10,
            'cantidad_kg' => 10,
        ]);

        // Error de datos: B referencia la presentación de A.
        InventarioPresentacionLote::create([
            'almacenid' => $almacen->almacenid,
            'insumoid' => $insumoB->insumoid,
            'insumo_presentacionid' => $presentacion->insumo_presentacionid,
            'cantidad_unidades' => 8,
            'cantidad_kg' => 8,
        ]);

        app(InventarioAlmacenProductoService::class)->eliminarProducto($almacen, $insumoA);

        $this->assertDatabaseMissing('insumo', ['insumoid' => $insumoA->insumoid]);
        $this->assertDatabaseHas('insumo', ['insumoid' => $insumoB->insumoid, 'stock' => 8]);
        $this->assertTrue(
            InventarioPresentacionLote::query()->where('insumoid', $insumoB->insumoid)->exists(),
            'El producto B debe conservar su lote tras eliminar A'
        );
    }
}
