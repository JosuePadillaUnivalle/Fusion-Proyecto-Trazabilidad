<?php

namespace Tests\Feature;

use App\Models\Almacen;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Insumo;
use App\Models\TipoAlmacen;
use App\Models\TipoInsumo;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AlmacenDashboardScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_almacen_filtra_metricas_por_almacen(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $um = UnidadMedida::create(['nombre' => 'Kilogramo', 'abreviatura' => 'kg', 'categoria' => 'peso']);
        $ta = TipoAlmacen::create(['nombre' => 'General', 'descripcion' => null]);
        $a1 = Almacen::create([
            'nombre' => 'Almacén 1',
            'descripcion' => '',
            'ubicacion' => 'X',
            'capacidad' => 1000,
            'unidadmedidaid' => $um->unidadmedidaid,
            'tipoalmacenid' => $ta->tipoalmacenid,
            'activo' => true,
        ]);
        $a2 = Almacen::create([
            'nombre' => 'Almacén 2',
            'descripcion' => '',
            'ubicacion' => 'Y',
            'capacidad' => 500,
            'unidadmedidaid' => $um->unidadmedidaid,
            'tipoalmacenid' => $ta->tipoalmacenid,
            'activo' => true,
        ]);

        $roleAlmacen = Role::findByName('almacen', 'web');
        $t1 = TipoInsumo::create(['nombre' => 'Semilla']);

        $transportista = Usuario::create([
            'nombre' => 'T',
            'apellido' => '1',
            'email' => 't1@test.local',
            'nombreusuario' => 't1',
            'passwordhash' => Hash::make('secret123'),
            'role' => 'transportista',
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);
        $transportista->assignRole('transportista');

        $almacenUser = Usuario::create([
            'nombre' => 'W',
            'apellido' => 'U',
            'email' => 'w@test.local',
            'nombreusuario' => 'wh',
            'passwordhash' => Hash::make('secret123'),
            'role' => 'almacen',
            'almacenid' => $a1->almacenid,
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);
        $almacenUser->syncRoles([$roleAlmacen->name]);

        EnvioAsignacionMultiple::create([
            'externo_envio_id' => 'EXT-A',
            'transportista_usuarioid' => $transportista->usuarioid,
            'estado' => 'entregado',
            'almacenid' => $a1->almacenid,
        ]);
        EnvioAsignacionMultiple::create([
            'externo_envio_id' => 'EXT-B',
            'transportista_usuarioid' => $transportista->usuarioid,
            'estado' => 'entregado',
            'almacenid' => $a2->almacenid,
        ]);

        Insumo::create([
            'nombre' => 'In A',
            'tipoinsumoid' => $t1->tipoinsumoid,
            'unidadmedidaid' => $um->unidadmedidaid,
            'stock' => 10,
            'stockminimo' => 0,
            'almacenid' => $a1->almacenid,
        ]);
        Insumo::create([
            'nombre' => 'In B',
            'tipoinsumoid' => $t1->tipoinsumoid,
            'unidadmedidaid' => $um->unidadmedidaid,
            'stock' => 999,
            'stockminimo' => 0,
            'almacenid' => $a2->almacenid,
        ]);

        $this->actingAs($almacenUser);
        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertViewIs('dashboard.roles.almacen');
        $stats = $response->viewData('stats');
        $this->assertSame(1, $stats['envios_recibidos']);
        $this->assertSame(10.0, $stats['inventario_total']);
        $this->assertCount(1, $response->viewData('ultimas_recepciones'));
    }
}
