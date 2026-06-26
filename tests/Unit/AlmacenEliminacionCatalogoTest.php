<?php

namespace Tests\Unit;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\Insumo;
use App\Models\TipoMovimientoAlmacen;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Support\AlmacenAmbito;
use App\Support\AlmacenEliminacionCatalogo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AlmacenEliminacionCatalogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_puede_eliminar_almacen_vacio_con_movimientos_historicos(): void
    {
        $unidad = UnidadMedida::query()->create([
            'nombre' => 'Kilogramo',
            'abreviatura' => 'kg',
            'activo' => true,
        ]);

        $almacen = Almacen::query()->create([
            'nombre' => 'Mayorista test',
            'ubicacion' => 'Test',
            'capacidad' => 1000,
            'unidadmedidaid' => $unidad->unidadmedidaid,
            'ambito' => AlmacenAmbito::MAYORISTA,
            'activo' => true,
        ]);

        $usuario = Usuario::query()->create([
            'nombre' => 'Test',
            'apellido' => 'User',
            'email' => 'alm-del@test.local',
            'nombreusuario' => 'alm_del',
            'passwordhash' => Hash::make('secret'),
            'role' => 'admin',
            'fecharegistro' => now(),
            'activo' => true,
        ]);

        $tipoMovId = (int) (TipoMovimientoAlmacen::query()->value('tipo_movimiento_almacenid') ?? 0);
        if ($tipoMovId <= 0) {
            $tipoMovId = (int) TipoMovimientoAlmacen::query()->create([
                'nombre' => 'Ingreso',
                'codigo' => 'ING',
                'naturaleza' => 'entrada',
                'activo' => true,
            ])->tipo_movimiento_almacenid;
        }

        $tipoinsumoid = (int) (\App\Models\TipoInsumo::query()->value('tipoinsumoid') ?? 0);
        if ($tipoinsumoid <= 0) {
            $tipoinsumoid = (int) \App\Models\TipoInsumo::query()->create([
                'nombre' => 'Producto terminado',
                'activo' => true,
            ])->tipoinsumoid;
        }

        $insumo = Insumo::query()->create([
            'nombre' => 'Producto test',
            'stock' => 0,
            'unidadmedidaid' => $unidad->unidadmedidaid,
            'tipoinsumoid' => $tipoinsumoid,
            'activo' => true,
        ]);

        AlmacenMovimiento::query()->create([
            'almacenid' => $almacen->almacenid,
            'insumoid' => $insumo->insumoid,
            'tipo_movimiento_almacenid' => $tipoMovId,
            'usuarioid' => $usuario->usuarioid,
            'fecha' => now()->toDateString(),
            'cantidad' => 50,
            'referencia' => 'TPM-TEST',
        ]);

        $eval = AlmacenEliminacionCatalogo::evaluar($almacen);
        $this->assertTrue($eval['ok']);

        AlmacenEliminacionCatalogo::eliminar($almacen);

        $this->assertDatabaseMissing('almacen', ['almacenid' => $almacen->almacenid]);
        $this->assertSame(0, AlmacenMovimiento::query()->where('almacenid', $almacen->almacenid)->count());
    }
}
