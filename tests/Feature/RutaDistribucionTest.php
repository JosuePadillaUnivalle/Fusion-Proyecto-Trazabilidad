<?php

namespace Tests\Feature;

use App\Models\Almacen;
use App\Models\DetallePedidoDistribucion;
use App\Models\Insumo;
use App\Models\PedidoDistribucion;
use App\Models\PerfilTransportista;
use App\Models\PuntoVenta;
use App\Models\Usuario;
use App\Models\TipoInsumo;
use App\Models\UnidadMedida;
use App\Models\Vehiculo;
use App\Support\AlmacenAmbito;
use App\Support\PedidoDistribucionCatalogo;
use App\Support\TransportistaFlotaCatalogo;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RutaDistribucionTest extends TestCase
{
    use RefreshDatabase;

    private function usuarioJefePlanta(): Usuario
    {
        $this->seed(RolePermissionSeeder::class);
        Role::findOrCreate('jefe_planta', 'web');

        $user = Usuario::create([
            'nombre' => 'Jefe',
            'apellido' => 'Planta',
            'email' => 'jefe.planta.ruta@test.local',
            'nombreusuario' => 'jefe_planta_ruta',
            'passwordhash' => Hash::make('secret'),
            'role' => 'jefe_planta',
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);
        $user->assignRole('jefe_planta');

        return $user;
    }

    public function test_jefe_planta_puede_ver_planificacion_distribucion(): void
    {
        $user = $this->usuarioJefePlanta();
        $this->actingAs($user);

        $this->get(route('punto-venta.rutas.index'))->assertOk();
        $this->get(route('punto-venta.rutas.create'))->assertOk();
    }

    public function test_crea_ruta_con_pedidos_confirmados(): void
    {
        $user = $this->usuarioJefePlanta();
        $this->actingAs($user);

        $unidad = UnidadMedida::create(['nombre' => 'Kilogramo', 'abreviatura' => 'kg']);

        $almacen = Almacen::create([
            'nombre' => 'Almacén Planta Test',
            'ubicacion' => 'GPS -17.79420, -63.16150',
            'capacidad' => 1000,
            'unidadmedidaid' => $unidad->unidadmedidaid,
            'activo' => true,
            'ambito' => AlmacenAmbito::PLANTA,
        ]);

        $chofer = Usuario::create([
            'nombre' => 'Chofer',
            'apellido' => 'Planta',
            'email' => 'chofer.planta@test.local',
            'nombreusuario' => 'chofer_planta',
            'passwordhash' => Hash::make('secret'),
            'role' => 'transportista',
            'fecharegistro' => now(),
            'activo' => true,
        ]);

        $vehiculo = Vehiculo::create([
            'placa' => 'PDV-001',
            'marca' => 'Toyota',
            'modelo' => 'Hilux',
            'activo' => true,
        ]);

        PerfilTransportista::create([
            'usuarioid' => $chofer->usuarioid,
            'ambito_flota' => TransportistaFlotaCatalogo::PLANTA,
            'vehiculoid' => $vehiculo->vehiculoid,
            'disponible' => true,
        ]);

        $minorista = Usuario::create([
            'nombre' => 'Min',
            'apellido' => 'Test',
            'email' => 'minorista@test.local',
            'nombreusuario' => 'minorista_test',
            'passwordhash' => Hash::make('secret'),
            'role' => 'minorista',
            'fecharegistro' => now(),
            'activo' => true,
        ]);

        $pdv = PuntoVenta::create([
            'usuarioid' => $minorista->usuarioid,
            'nombre' => 'PDV Centro',
            'direccion' => 'Av. Test',
            'latitud' => -17.78,
            'longitud' => -63.18,
            'activo' => true,
        ]);

        $tipo = TipoInsumo::create(['nombre' => 'Producto terminado']);

        $insumo = Insumo::create([
            'nombre' => 'Producto PDV',
            'stock' => 100,
            'tipoinsumoid' => $tipo->tipoinsumoid,
            'unidadmedidaid' => $unidad->unidadmedidaid,
            'almacenid' => $almacen->almacenid,
        ]);

        $pedido = PedidoDistribucion::create([
            'numero_solicitud' => 'PDV-TEST-0001',
            'puntoventaid' => $pdv->puntoventaid,
            'almacen_planta_origenid' => $almacen->almacenid,
            'estado' => PedidoDistribucionCatalogo::ESTADO_CONFIRMADO,
            'fechapedido' => now(),
            'fecha_aceptacion' => now(),
            'aceptado_por_usuarioid' => $user->usuarioid,
        ]);

        DetallePedidoDistribucion::create([
            'pedidodistribucionid' => $pedido->pedidodistribucionid,
            'insumoid' => $insumo->insumoid,
            'producto_nombre' => $insumo->nombre,
            'cantidad' => 10,
        ]);

        $response = $this->post(route('punto-venta.rutas.store'), [
            'almacen_planta_origenid' => $almacen->almacenid,
            'transportista_usuarioid' => $chofer->usuarioid,
            'vehiculoid' => $vehiculo->vehiculoid,
            'pedidos' => [$pedido->pedidodistribucionid],
        ]);

        $response->assertRedirect();
        $pedido->refresh();
        $this->assertNotNull($pedido->rutadistribucionid);
        $this->assertSame(PedidoDistribucionCatalogo::ESTADO_EN_TRANSITO, $pedido->estado);
    }
}
