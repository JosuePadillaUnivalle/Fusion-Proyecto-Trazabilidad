<?php

namespace Tests\Unit;

use App\Models\Almacen;
use App\Models\AlmacenajeLoteProduccion;
use App\Models\Insumo;
use App\Models\InsumoPresentacion;
use App\Models\InventarioPresentacionLote;
use App\Models\LoteProduccionPedido;
use App\Models\Pedido;
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

        $pedido = Pedido::create([
            'numero_solicitud' => 'TEST-CAP-1',
            'nombre_planta' => 'Planta Test',
            'latitud' => -17.77,
            'longitud' => -63.17,
            'estado' => 'completado',
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

        $lotes = [];
        foreach (['LOTE-A' => 51, 'LOTE-B' => 40, 'LOTE-C' => 50, 'LOTE-D' => 0] as $codigo => $kg) {
            $lote = LoteProduccionPedido::create([
                'pedidoid' => $pedido->pedidoid,
                'codigo_lote' => $codigo,
                'nombre' => 'Producto '.$codigo,
                'producto' => 'Producto test',
                'fecha_creacion' => now()->toDateString(),
                'cantidad_objetivo' => $kg > 0 ? $kg * 2 : 100,
                'cantidad_producida' => $kg > 0 ? $kg * 2 : 100,
            ]);
            $lotes[$codigo] = $lote;

            AlmacenajeLoteProduccion::create([
                'loteproduccionpedidoid' => $lote->loteproduccionpedidoid,
                'almacenid' => $planta->almacenid,
                'ubicacion' => 'Planta Test',
                'condicion' => 'Buena',
                'cantidad' => $kg > 0 ? $kg * 2 : 100,
                'fecha_almacenaje' => now(),
            ]);

            InventarioPresentacionLote::create([
                'almacenid' => $planta->almacenid,
                'insumoid' => $insumoTerminado->insumoid,
                'insumo_presentacionid' => $presentacion->insumo_presentacionid,
                'loteproduccionpedidoid' => $lote->loteproduccionpedidoid,
                'referencia_lote' => $codigo,
                'cantidad_unidades' => $kg > 0 ? $kg * 2 : 0,
                'cantidad_kg' => $kg,
            ]);
        }

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

    public function test_ocupado_planta_usa_almacenaje_cuando_lote_no_tiene_inventario(): void
    {
        $unidad = UnidadMedida::create(['nombre' => 'Kilogramo', 'abreviatura' => 'kg', 'activo' => true]);
        $tipoTerminado = TipoInsumo::create(['nombre' => 'Producto terminado']);

        $planta = Almacen::create([
            'nombre' => 'Planta Mixta',
            'ubicacion' => 'GPS -17.77,-63.17',
            'capacidad' => 50000,
            'unidadmedidaid' => $unidad->unidadmedidaid,
            'ambito' => AlmacenAmbito::PLANTA,
            'activo' => true,
        ]);

        $pedido = Pedido::create([
            'numero_solicitud' => 'TEST-CAP-2',
            'nombre_planta' => 'Planta Mixta',
            'latitud' => -17.77,
            'longitud' => -63.17,
            'estado' => 'completado',
        ]);

        $insumo = Insumo::create([
            'nombre' => 'Cebolla en cubos 500 g',
            'tipoinsumoid' => $tipoTerminado->tipoinsumoid,
            'unidadmedidaid' => $unidad->unidadmedidaid,
            'stock' => 50,
            'stockminimo' => 1,
            'almacenid' => $planta->almacenid,
        ]);

        $presentacion = InsumoPresentacion::create([
            'insumoid' => $insumo->insumoid,
            'nombre' => 'Bolsa plástica 500 g',
            'tipo_envase' => 'bolsa',
            'peso_neto_kg' => 0.5,
            'orden' => 1,
            'activo' => true,
        ]);

        $loteConInventarioCero = LoteProduccionPedido::create([
            'pedidoid' => $pedido->pedidoid,
            'codigo_lote' => 'LOTE-CERO',
            'nombre' => 'Cebolla cubos',
            'producto' => 'Cebolla en cubos 500 g',
            'fecha_creacion' => now()->toDateString(),
            'cantidad_objetivo' => 100,
            'cantidad_producida' => 100,
            'empaque_catalogo_slug' => 'bolsa_500g',
            'empaque_peso_neto_kg' => 0.5,
            'cantidad_empaques_objetivo' => 100,
        ]);

        AlmacenajeLoteProduccion::create([
            'loteproduccionpedidoid' => $loteConInventarioCero->loteproduccionpedidoid,
            'almacenid' => $planta->almacenid,
            'ubicacion' => 'Planta Mixta',
            'condicion' => 'Buena',
            'cantidad' => 100,
            'fecha_almacenaje' => now(),
        ]);

        InventarioPresentacionLote::create([
            'almacenid' => $planta->almacenid,
            'insumoid' => $insumo->insumoid,
            'insumo_presentacionid' => $presentacion->insumo_presentacionid,
            'loteproduccionpedidoid' => $loteConInventarioCero->loteproduccionpedidoid,
            'referencia_lote' => 'LOTE-CERO',
            'cantidad_unidades' => 0,
            'cantidad_kg' => 0,
        ]);

        $loteSinInventario = LoteProduccionPedido::create([
            'pedidoid' => $pedido->pedidoid,
            'codigo_lote' => 'LOTE-SIN-INV',
            'nombre' => 'Pure de Papa',
            'producto' => 'Pure de Papa',
            'fecha_creacion' => now()->toDateString(),
            'cantidad_objetivo' => 50,
            'cantidad_producida' => 50,
        ]);

        AlmacenajeLoteProduccion::create([
            'loteproduccionpedidoid' => $loteSinInventario->loteproduccionpedidoid,
            'almacenid' => $planta->almacenid,
            'ubicacion' => 'Planta Mixta',
            'condicion' => 'Buena',
            'cantidad' => 50,
            'fecha_almacenaje' => now(),
        ]);

        $service = app(AlmacenCapacidadService::class);
        $ocupado = $service->ocupadoKg($planta->fresh());

        $this->assertEqualsWithDelta(50.0, $ocupado, 0.01);
    }
}
