<?php

namespace Tests\Unit;

use App\Models\Almacen;
use App\Models\Insumo;
use App\Models\InsumoPresentacion;
use App\Models\InventarioPresentacionLote;
use App\Models\TipoInsumo;
use App\Models\UnidadMedida;
use App\Services\AlmacenCapacidadService;
use App\Support\AlmacenAmbito;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlmacenCapacidadServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_ocupado_planta_no_duplica_producto_terminado_con_inventario(): void
    {
        $unidad = UnidadMedida::create(['nombre' => 'Kilogramo', 'abreviatura' => 'kg', 'activo' => true]);
        $tipoOperativo = TipoInsumo::create(['nombre' => 'Fertilizante']);
        $tipoTerminado = TipoInsumo::create(['nombre' => 'Producto terminado']);

        $planta = Almacen::create([
            'nombre' => 'Planta Test',
            'ubicacion' => 'GPS -17.77,-63.17',
            'capacidad' => 50000,
            'unidadmedidaid' => $unidad->unidadmedidaid,
            'ambito' => AlmacenAmbito::PLANTA,
            'activo' => true,
        ]);

        $insumoTerminado = Insumo::create([
            'nombre' => 'Cebolla en cubos 500 g',
            'tipoinsumoid' => $tipoTerminado->tipoinsumoid,
            'unidadmedidaid' => $unidad->unidadmedidaid,
            'stock' => 50,
            'stockminimo' => 1,
            'almacenid' => $planta->almacenid,
        ]);

        $presentacion = InsumoPresentacion::create([
            'insumoid' => $insumoTerminado->insumoid,
            'nombre' => 'Bolsa plástica 500 g',
            'tipo_envase' => 'bolsa',
            'peso_neto_kg' => 0.5,
            'orden' => 1,
            'activo' => true,
        ]);

        InventarioPresentacionLote::create([
            'almacenid' => $planta->almacenid,
            'insumoid' => $insumoTerminado->insumoid,
            'insumo_presentacionid' => $presentacion->insumo_presentacionid,
            'referencia_lote' => 'LOTE-A',
            'cantidad_unidades' => 102,
            'cantidad_kg' => 51,
        ]);

        InventarioPresentacionLote::create([
            'almacenid' => $planta->almacenid,
            'insumoid' => $insumoTerminado->insumoid,
            'insumo_presentacionid' => $presentacion->insumo_presentacionid,
            'referencia_lote' => 'LOTE-B',
            'cantidad_unidades' => 80,
            'cantidad_kg' => 40,
        ]);

        InventarioPresentacionLote::create([
            'almacenid' => $planta->almacenid,
            'insumoid' => $insumoTerminado->insumoid,
            'insumo_presentacionid' => $presentacion->insumo_presentacionid,
            'referencia_lote' => 'LOTE-C',
            'cantidad_unidades' => 100,
            'cantidad_kg' => 50,
        ]);

        InventarioPresentacionLote::create([
            'almacenid' => $planta->almacenid,
            'insumoid' => $insumoTerminado->insumoid,
            'insumo_presentacionid' => $presentacion->insumo_presentacionid,
            'referencia_lote' => 'LOTE-D',
            'cantidad_unidades' => 0,
            'cantidad_kg' => 0,
        ]);

        Insumo::create([
            'nombre' => 'Urea',
            'tipoinsumoid' => $tipoOperativo->tipoinsumoid,
            'unidadmedidaid' => $unidad->unidadmedidaid,
            'stock' => 10,
            'stockminimo' => 1,
            'almacenid' => $planta->almacenid,
        ]);

        $service = app(AlmacenCapacidadService::class);
        $ocupado = $service->ocupadoKg($planta->fresh());

        $this->assertEqualsWithDelta(151.0, $ocupado, 0.01);
    }
}
