<?php



namespace Tests\Feature;



use App\Models\Almacen;

use App\Models\AlmacenMovimiento;

use App\Models\DetallePedidoDistribucion;

use App\Models\Insumo;

use App\Models\PedidoDistribucion;

use App\Models\PerfilTransportista;

use App\Models\PuntoVenta;

use App\Models\TipoInsumo;

use App\Models\TipoMovimientoAlmacen;

use App\Models\UnidadMedida;

use App\Models\Usuario;

use App\Models\Vehiculo;

use App\Models\CondicionTransporte;
use App\Services\CierreEnvioDistribucionPdvService;
use App\Services\SimulacionRutaService;

use App\Support\AlmacenAmbito;

use App\Support\PedidoDistribucionCatalogo;

use App\Support\TransportistaFlotaCatalogo;

use Database\Seeders\RolePermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Support\Facades\Hash;

use Spatie\Permission\Models\Role;

use Tests\TestCase;



class PedidoDistribucionFase2Test extends TestCase

{

    use RefreshDatabase;



    private function admin(): Usuario

    {

        $this->seed(RolePermissionSeeder::class);

        Role::findOrCreate('admin', 'web');



        $user = Usuario::create([

            'nombre' => 'Admin',

            'apellido' => 'Test',

            'email' => 'admin.pdv.fase2@test.local',

            'nombreusuario' => 'admin_pdv_fase2',

            'passwordhash' => Hash::make('secret'),

            'role' => 'admin',

            'fecharegistro' => now(),

            'activo' => true,

        ]);

        $user->assignRole('admin');



        return $user;

    }



    private function mayorista(): Usuario

    {

        $this->seed(RolePermissionSeeder::class);

        Role::findOrCreate('mayorista', 'web');



        $user = Usuario::create([

            'nombre' => 'Carlos',

            'apellido' => 'Mayorista',

            'email' => 'mayorista.fase2@test.local',

            'nombreusuario' => 'mayorista_fase2',

            'passwordhash' => Hash::make('secret'),

            'role' => 'mayorista',

            'fecharegistro' => now(),

            'activo' => true,

        ]);

        $user->assignRole('mayorista');



        return $user;

    }



    /** @return array{0: Usuario, 1: Usuario, 2: Vehiculo, 3: Almacen, 4: PuntoVenta, 5: Insumo, 6: PedidoDistribucion} */

    private function escenarioPedidoConfirmado(): array

    {

        $admin = $this->admin();



        $unidad = UnidadMedida::create(['nombre' => 'Kilogramo', 'abreviatura' => 'kg']);



        $almacen = Almacen::create([

            'nombre' => 'Centro Mayorista Fase2',

            'ubicacion' => 'GPS -17.79420, -63.16150',

            'capacidad' => 1000,

            'unidadmedidaid' => $unidad->unidadmedidaid,

            'activo' => true,

            'ambito' => AlmacenAmbito::MAYORISTA,

        ]);



        $chofer = Usuario::create([

            'nombre' => 'Chofer',

            'apellido' => 'Fase2',

            'email' => 'chofer.fase2@test.local',

            'nombreusuario' => 'chofer_fase2',

            'passwordhash' => Hash::make('secret'),

            'role' => 'transportista',

            'fecharegistro' => now(),

            'activo' => true,

        ]);



        $vehiculo = Vehiculo::create([

            'placa' => 'FASE2-01',

            'marca' => 'Toyota',

            'modelo' => 'Hilux',

            'activo' => true,

            'ambito_flota' => TransportistaFlotaCatalogo::MAYORISTA,

        ]);



        PerfilTransportista::create([

            'usuarioid' => $chofer->usuarioid,

            'ambito_flota' => TransportistaFlotaCatalogo::MAYORISTA,

            'vehiculoid' => $vehiculo->vehiculoid,

            'disponible' => true,

        ]);



        $minorista = Usuario::create([

            'nombre' => 'Min',

            'apellido' => 'Fase2',

            'email' => 'minorista.fase2@test.local',

            'nombreusuario' => 'minorista_fase2',

            'passwordhash' => Hash::make('secret'),

            'role' => 'minorista',

            'fecharegistro' => now(),

            'activo' => true,

        ]);



        $pdv = PuntoVenta::create([

            'usuarioid' => $minorista->usuarioid,

            'nombre' => 'PDV Fase2',

            'direccion' => 'Av. Test',

            'latitud' => -17.78,

            'longitud' => -63.18,

            'activo' => true,

        ]);



        $tipo = TipoInsumo::query()->firstOrCreate(['nombre' => 'Producto terminado Fase2']);



        TipoMovimientoAlmacen::query()->firstOrCreate(
            ['codigo' => 'ING-F2'],
            ['nombre' => 'Ingreso', 'naturaleza' => 'ingreso', 'activo' => true]
        );

        TipoMovimientoAlmacen::query()->firstOrCreate(
            ['codigo' => 'SAL-F2'],
            ['nombre' => 'Salida', 'naturaleza' => 'salida', 'activo' => true]
        );



        $insumo = Insumo::create([

            'nombre' => 'Papas fritas',

            'stock' => 100,

            'tipoinsumoid' => $tipo->tipoinsumoid,

            'unidadmedidaid' => $unidad->unidadmedidaid,

            'almacenid' => $almacen->almacenid,

        ]);



        $pedido = PedidoDistribucion::create([

            'numero_solicitud' => 'PDV-FASE2-0001',

            'puntoventaid' => $pdv->puntoventaid,

            'almacen_mayorista_origenid' => $almacen->almacenid,

            'estado' => PedidoDistribucionCatalogo::ESTADO_CONFIRMADO,

            'fechapedido' => now(),

            'fecha_aceptacion' => now(),

            'aceptado_por_usuarioid' => $admin->usuarioid,

        ]);



        DetallePedidoDistribucion::create([

            'pedidodistribucionid' => $pedido->pedidodistribucionid,

            'insumoid' => $insumo->insumoid,

            'producto_nombre' => $insumo->nombre,

            'cantidad' => 10,

        ]);



        return [$admin, $chofer, $vehiculo, $almacen, $pdv, $insumo, $pedido];

    }



    public function test_designar_transportista_crea_ruta_sin_marcar_en_transito(): void

    {

        [$admin, $chofer, $vehiculo, , , , $pedido] = $this->escenarioPedidoConfirmado();

        $this->actingAs($admin);



        $response = $this->post(route('punto-venta.pedidos.designar-transportista', $pedido), [

            'transportista_usuarioid' => $chofer->usuarioid,

            'vehiculoid' => $vehiculo->vehiculoid,

        ]);



        $response->assertRedirect();

        $pedido->refresh();



        $this->assertNotNull($pedido->rutadistribucionid);

        $this->assertSame(PedidoDistribucionCatalogo::ESTADO_CONFIRMADO, $pedido->estado);

        $this->assertSame($chofer->usuarioid, $pedido->transportista_usuarioid);

        $this->assertNull($pedido->fecha_envio);

        $this->assertTrue(PedidoDistribucionCatalogo::tieneTransportistaDesignado($pedido));

    }



    public function test_empezar_ruta_como_admin_marca_en_transito_y_tiempo_real(): void

    {

        [$admin, $chofer, $vehiculo, , , , $pedido] = $this->escenarioPedidoConfirmado();

        $this->actingAs($admin);



        $this->post(route('punto-venta.pedidos.designar-transportista', $pedido), [

            'transportista_usuarioid' => $chofer->usuarioid,

            'vehiculoid' => $vehiculo->vehiculoid,

        ]);



        $pedido->refresh();

        $this->registrarCondicionesRutaPdv($pedido->rutaDistribucion, $admin);

        $response = $this->patch(route('punto-venta.pedidos.empezar-ruta', $pedido));



        $response->assertRedirect(route('punto-venta.pedidos.show', ['pedido' => $pedido, 'paso' => 4]));

        $pedido->refresh();

        $this->assertSame(PedidoDistribucionCatalogo::ESTADO_EN_TRANSITO, $pedido->estado);

        $this->assertTrue(PedidoDistribucionCatalogo::estaEnRutaTiempoReal($pedido));

    }



    public function test_mayorista_no_puede_marcar_en_ruta(): void

    {

        [$admin, $chofer, $vehiculo, , , , $pedido] = $this->escenarioPedidoConfirmado();

        $mayorista = $this->mayorista();



        $this->actingAs($admin);

        $this->post(route('punto-venta.pedidos.designar-transportista', $pedido), [

            'transportista_usuarioid' => $chofer->usuarioid,

            'vehiculoid' => $vehiculo->vehiculoid,

        ]);



        $this->actingAs($mayorista);

        $this->patch(route('punto-venta.pedidos.empezar-ruta', $pedido))->assertForbidden();



        $pedido->refresh();

        $this->assertSame(PedidoDistribucionCatalogo::ESTADO_CONFIRMADO, $pedido->estado);

    }



    public function test_completar_distribucion_mueve_stock_al_pdv(): void

    {

        [$admin, $chofer, $vehiculo, , , $insumo, $pedido] = $this->escenarioPedidoConfirmado();

        $this->actingAs($admin);



        $this->post(route('punto-venta.pedidos.designar-transportista', $pedido), [

            'transportista_usuarioid' => $chofer->usuarioid,

            'vehiculoid' => $vehiculo->vehiculoid,

        ]);



        $pedido->refresh();

        $ruta = $pedido->rutaDistribucion;

        $this->assertNotNull($ruta);

        $this->registrarCondicionesRutaPdv($ruta, $admin);

        app(SimulacionRutaService::class)->empezarDistribucion($ruta);

        $pedido->refresh();

        $this->assertSame(PedidoDistribucionCatalogo::ESTADO_EN_TRANSITO, $pedido->estado);



        app(SimulacionRutaService::class)->completarDistribucion($ruta->fresh());



        $pedido->refresh();

        $insumo->refresh();



        $this->assertSame(PedidoDistribucionCatalogo::ESTADO_RECIBIDO, $pedido->estado);

        $this->assertSame(90.0, (float) $insumo->stock);

        $this->assertTrue(

            AlmacenMovimiento::query()->where('referencia', $pedido->numero_solicitud)->exists()

        );

    }

    public function test_minorista_ve_confirmacion_envio_mayorista_en_pedido(): void
    {
        [$admin, $chofer, $vehiculo, , , , $pedido] = $this->escenarioPedidoConfirmado();

        $minorista = Usuario::query()->where('email', 'minorista.fase2@test.local')->firstOrFail();
        Role::findOrCreate('minorista', 'web');
        $minorista->syncRoles(['minorista']);
        $pedido->update([
            'envio_iniciado_mayorista' => true,
            'fecha_confirmacion_minorista' => null,
            'creado_por_usuarioid' => $admin->usuarioid,
        ]);

        $this->actingAs($admin);
        $this->post(route('punto-venta.pedidos.designar-transportista', $pedido), [
            'transportista_usuarioid' => $chofer->usuarioid,
            'vehiculoid' => $vehiculo->vehiculoid,
        ]);

        $this->actingAs($minorista);
        $response = $this->get(route('punto-venta.pedidos.show', $pedido));

        $response->assertOk();
        $response->assertSee('Confirmar envío', false);
        $response->assertSee('Confirme el envío entrante', false);
    }

    public function test_transportista_no_puede_registrar_condiciones_sin_confirmacion_minorista(): void
    {
        [$admin, $chofer, $vehiculo, , , , $pedido] = $this->escenarioPedidoConfirmado();

        $pedido->update([
            'envio_iniciado_mayorista' => true,
            'fecha_confirmacion_minorista' => null,
        ]);

        $this->actingAs($admin);
        $this->post(route('punto-venta.pedidos.designar-transportista', $pedido), [
            'transportista_usuarioid' => $chofer->usuarioid,
            'vehiculoid' => $vehiculo->vehiculoid,
        ]);

        $pedido->refresh();
        $ruta = $pedido->rutaDistribucion;
        $this->assertNotNull($ruta);

        $cierre = app(CierreEnvioDistribucionPdvService::class);
        $resumen = $cierre->resumenPasos($ruta);

        $this->assertTrue($resumen['pendiente_confirmacion_minorista']);
        $this->assertFalse($resumen['puede_registrar_condiciones']);
        $this->assertFalse($resumen['puede_empezar_ruta']);
        $this->assertSame('__espera_confirmacion_minorista__', $resumen['paso_actual']);

        CondicionTransporte::query()->firstOrCreate(
            ['codigo' => 'COND_PDV_MINORISTA_TEST'],
            ['titulo' => 'Luces', 'descripcion' => 'Test']
        );

        $this->actingAs($chofer);
        $this->post(route('punto-venta.rutas.cierre.condiciones', $ruta), [
            'perfectas_condiciones' => 1,
        ])->assertSessionHas('error');

        $pedido->update(['fecha_confirmacion_minorista' => now()]);
        $resumen = $cierre->resumenPasos($ruta->fresh());

        $this->assertFalse($resumen['pendiente_confirmacion_minorista']);
        $this->assertTrue($resumen['puede_registrar_condiciones']);

        $this->post(route('punto-venta.rutas.cierre.condiciones', $ruta), [
            'perfectas_condiciones' => 1,
        ])->assertRedirect();

        $resumen = $cierre->resumenPasos($ruta->fresh());
        $this->assertTrue($resumen['condiciones_vigentes']);
        $this->assertTrue($resumen['puede_empezar_ruta']);
    }

    private function registrarCondicionesRutaPdv(?\App\Models\RutaDistribucion $ruta, Usuario $usuario): void
    {
        if ($ruta === null) {
            $this->fail('Ruta de distribución no creada.');
        }

        CondicionTransporte::query()->firstOrCreate(
            ['codigo' => 'COND_PDV_TEST'],
            ['titulo' => 'Frenos', 'descripcion' => 'Test PDV']
        );

        app(CierreEnvioDistribucionPdvService::class)->registrarCondicionesVehiculo(
            $ruta->fresh(),
            $usuario,
            true,
        );
    }

}
