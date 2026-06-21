<?php



namespace Tests\Feature;



use App\Models\Almacen;

use App\Models\Insumo;

use App\Models\PerfilTransportista;

use App\Models\RutaDistribucion;

use App\Models\TipoInsumo;

use App\Models\UnidadMedida;

use App\Models\Usuario;

use App\Models\Vehiculo;

use App\Services\SimulacionRutaService;

use App\Services\TrasladoPlantaMayoristaService;

use App\Support\AlmacenAmbito;

use App\Support\RutaDistribucionCatalogo;

use App\Support\SimulacionRutaCatalogo;

use App\Support\TransportistaFlotaCatalogo;

use Database\Seeders\RolePermissionSeeder;

use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Support\Facades\Hash;

use Spatie\Permission\Models\Role;

use Tests\TestCase;



class TrasladoPlantaMayoristaTest extends TestCase

{

    use RefreshDatabase;



    private function admin(): Usuario

    {

        $this->seed(RolePermissionSeeder::class);

        Role::findOrCreate('admin', 'web');



        $user = Usuario::create([

            'nombre' => 'Admin',

            'apellido' => 'Traslado',

            'email' => 'admin.traslado@test.local',

            'nombreusuario' => 'admin_traslado',

            'passwordhash' => Hash::make('secret'),

            'role' => 'admin',

            'fecharegistro' => now(),

            'activo' => true,

        ]);

        $user->assignRole('admin');



        return $user;

    }



    private function almacen(string $nombre, string $ambito, string $ubicacion): Almacen

    {

        $unidad = UnidadMedida::create([

            'nombre' => 'Kilogramo',

            'abreviatura' => 'kg',

            'activo' => true,

        ]);



        return Almacen::create([

            'nombre' => $nombre,

            'ubicacion' => $ubicacion,

            'ambito' => $ambito,

            'capacidad' => 10000,

            'unidadmedidaid' => $unidad->unidadmedidaid,

            'activo' => true,

        ]);

    }



    /** @return array{transportista: Usuario, vehiculo: Vehiculo} */

    private function flotaPlanta(): array

    {

        $transportista = Usuario::create([

            'nombre' => 'Chofer',

            'apellido' => 'Planta',

            'email' => 'chofer.traslado@test.local',

            'nombreusuario' => 'chofer_traslado',

            'passwordhash' => Hash::make('Password'),

            'role' => 'transportista',

            'fecharegistro' => now(),

            'activo' => true,

        ]);



        $vehiculo = Vehiculo::create([

            'placa' => 'SCZ-TPM-01',

            'marca' => 'Toyota',

            'modelo' => 'Hilux',

            'anio' => 2022,

            'activo' => true,

            'ambito_flota' => TransportistaFlotaCatalogo::PLANTA,

        ]);



        PerfilTransportista::create([

            'usuarioid' => $transportista->usuarioid,

            'ambito_flota' => TransportistaFlotaCatalogo::PLANTA,

            'vehiculoid' => $vehiculo->vehiculoid,

        ]);



        return ['transportista' => $transportista, 'vehiculo' => $vehiculo];

    }



    private function insumoPlanta(Almacen $planta, float $stock = 500): Insumo

    {

        $tipo = TipoInsumo::query()->firstOrCreate(['nombre' => 'Producto terminado']);

        $unidad = UnidadMedida::first() ?? UnidadMedida::create([

            'nombre' => 'Kilogramo',

            'abreviatura' => 'kg',

            'activo' => true,

        ]);



        return Insumo::create([

            'nombre' => 'Zanahoria procesada',

            'tipoinsumoid' => $tipo->tipoinsumoid,

            'unidadmedidaid' => $unidad->unidadmedidaid,

            'stock' => $stock,

            'stockminimo' => 10,

            'almacenid' => $planta->almacenid,

        ]);

    }



    public function test_crea_traslado_y_aparece_en_tiempo_real_al_marcar_en_ruta(): void

    {

        $admin = $this->admin();

        $planta = $this->almacen('Planta Central', AlmacenAmbito::PLANTA, 'GPS -17.78,-63.18');

        $mayorista = $this->almacen('Almacen Pirai Test', AlmacenAmbito::MAYORISTA, 'GPS -17.79,-63.19');

        $insumo = $this->insumoPlanta($planta);

        $flota = $this->flotaPlanta();



        $ruta = app(TrasladoPlantaMayoristaService::class)->crear(

            $planta,

            $mayorista,

            (int) $flota['transportista']->usuarioid,

            (int) $flota['vehiculo']->vehiculoid,

            (int) $admin->usuarioid,

            [['insumoid' => $insumo->insumoid, 'cantidad' => 100]],

            null,

            50.0

        );



        $this->assertTrue($ruta->esTrasladoPlantaMayorista());

        $this->assertSame(RutaDistribucionCatalogo::ESTADO_PENDIENTE_APROBACION, $ruta->estado);

        $this->assertCount(1, $ruta->detallesTraslado);



        app(TrasladoPlantaMayoristaService::class)->aceptar($ruta->fresh(), $admin);

        $ruta->refresh();

        $this->assertSame(RutaDistribucionCatalogo::ESTADO_PLANIFICADA, $ruta->estado);



        app(SimulacionRutaService::class)->empezarDistribucion($ruta->fresh());

        $ruta->refresh();



        $this->assertSame(RutaDistribucionCatalogo::ESTADO_EN_RUTA, $ruta->estado);

        $this->assertNotNull($ruta->simulacion_inicio_at);



        $activas = app(SimulacionRutaService::class)->listarActivas();

        $item = $activas->first(fn ($i) => ($i['tipo'] ?? '') === SimulacionRutaCatalogo::TIPO_PLANTA_MAYORISTA);



        $this->assertNotNull($item);

        $this->assertSame($ruta->rutadistribucionid, $item['id']);

        $this->assertStringContainsString('Planta Central', $item['origen']);

        $this->assertStringContainsString('Pirai', $item['destino']);

    }



    public function test_admin_puede_crear_traslado_desde_formulario(): void

    {

        $admin = $this->admin();

        $planta = $this->almacen('Planta Norte', AlmacenAmbito::PLANTA, 'GPS -17.77,-63.17');

        $mayorista = $this->almacen('Mayorista Sur', AlmacenAmbito::MAYORISTA, 'GPS -17.80,-63.20');

        $insumo = $this->insumoPlanta($planta);

        $flota = $this->flotaPlanta();



        $response = $this->actingAs($admin)->post(route('logistica.traslados-planta.store'), [

            'almacen_planta_origenid' => $planta->almacenid,

            'almacen_mayorista_destinoid' => $mayorista->almacenid,

            'transportista_usuarioid' => $flota['transportista']->usuarioid,

            'vehiculoid' => $flota['vehiculo']->vehiculoid,

            'costo_bs' => 50,

            'detalles' => [

                ['insumoid' => $insumo->insumoid, 'cantidad' => 80],

            ],

        ]);



        $ruta = RutaDistribucion::query()->first();

        $this->assertNotNull($ruta);

        $response->assertRedirect(route('logistica.traslados-planta.show', $ruta));

        $this->assertCount(1, $ruta->detallesTraslado);

    }



    public function test_traslado_calcula_kg_desde_presentacion_comercial(): void

    {

        $admin = $this->admin();

        $planta = $this->almacen('Planta Presentacion', AlmacenAmbito::PLANTA, 'GPS -17.77,-63.17');

        $mayorista = $this->almacen('Mayorista Presentacion', AlmacenAmbito::MAYORISTA, 'GPS -17.80,-63.20');

        $insumo = $this->insumoPlanta($planta, 500);

        $presentacion = \App\Models\InsumoPresentacion::create([

            'insumoid' => $insumo->insumoid,

            'nombre' => 'Lata 400 g',

            'tipo_envase' => 'lata',

            'peso_neto_kg' => 0.4,

            'orden' => 1,

            'activo' => true,

        ]);

        $inventario = \App\Models\InventarioPresentacionLote::create([

            'almacenid' => $planta->almacenid,

            'insumoid' => $insumo->insumoid,

            'insumo_presentacionid' => $presentacion->insumo_presentacionid,

            'referencia_lote' => 'LOTE-PRES-A',

            'cantidad_unidades' => 500,

            'cantidad_kg' => 200,

        ]);

        $flota = $this->flotaPlanta();



        $ruta = app(TrasladoPlantaMayoristaService::class)->crear(

            $planta,

            $mayorista,

            (int) $flota['transportista']->usuarioid,

            (int) $flota['vehiculo']->vehiculoid,

            (int) $admin->usuarioid,

            [[

                'insumoid' => $insumo->insumoid,

                'insumo_presentacionid' => $presentacion->insumo_presentacionid,

                'inventario_presentacion_loteid' => $inventario->inventario_presentacion_loteid,

                'cantidad_unidades' => 100,

            ]],

            null,

            50.0

        );



        $detalle = $ruta->detallesTraslado->first();

        $this->assertNotNull($detalle);

        $this->assertSame('Lata 400 g', $detalle->presentacion_nombre);

        $this->assertEquals(100.0, (float) $detalle->cantidad_unidades);

        $this->assertEquals(40.0, (float) $detalle->cantidad);

    }



    public function test_traslado_descuenta_inventario_por_lote_presentacion(): void

    {

        $admin = $this->admin();

        $planta = $this->almacen('Planta Lote', AlmacenAmbito::PLANTA, 'GPS -17.77,-63.17');

        $mayorista = $this->almacen('Mayorista Lote', AlmacenAmbito::MAYORISTA, 'GPS -17.80,-63.20');

        $insumo = $this->insumoPlanta($planta, 200);

        $presentacion = \App\Models\InsumoPresentacion::create([

            'insumoid' => $insumo->insumoid,

            'nombre' => 'Lata 400 g',

            'tipo_envase' => 'lata',

            'peso_neto_kg' => 0.4,

            'orden' => 1,

            'activo' => true,

        ]);

        $inventario = \App\Models\InventarioPresentacionLote::create([

            'almacenid' => $planta->almacenid,

            'insumoid' => $insumo->insumoid,

            'insumo_presentacionid' => $presentacion->insumo_presentacionid,

            'referencia_lote' => 'LOTE-TEST-A',

            'cantidad_unidades' => 300,

            'cantidad_kg' => 120,

        ]);

        $flota = $this->flotaPlanta();

        $service = app(TrasladoPlantaMayoristaService::class);

        $ruta = $service->crear(

            $planta,

            $mayorista,

            (int) $flota['transportista']->usuarioid,

            (int) $flota['vehiculo']->vehiculoid,

            (int) $admin->usuarioid,

            [[

                'insumoid' => $insumo->insumoid,

                'insumo_presentacionid' => $presentacion->insumo_presentacionid,

                'inventario_presentacion_loteid' => $inventario->inventario_presentacion_loteid,

                'cantidad_unidades' => 50,

            ]],

            null,

            50.0

        );

        $ruta->update(['estado' => RutaDistribucionCatalogo::ESTADO_PLANIFICADA]);

        $service->transferirInventarioAlCompletar($ruta, $admin);

        $inventario->refresh();

        $this->assertEquals(250.0, (float) $inventario->cantidad_unidades);

        $this->assertEquals(100.0, (float) $inventario->cantidad_kg);

    }



    public function test_rechaza_traslado_si_supera_capacidad_vehiculo(): void

    {

        $admin = $this->admin();

        $planta = $this->almacen('Planta Cap', AlmacenAmbito::PLANTA, 'GPS -17.77,-63.17');

        $mayorista = $this->almacen('Mayorista Cap', AlmacenAmbito::MAYORISTA, 'GPS -17.80,-63.20');

        $insumo = $this->insumoPlanta($planta, 5000);

        $flota = $this->flotaPlanta();

        $tipoVehiculo = \App\Models\TipoVehiculo::query()->create([

            'nombre' => 'Camión test',

            'capacidad_kg' => 100,

            'capacidad_m3' => 10,

            'activo' => true,

        ]);

        $flota['vehiculo']->update(['tipovehiculoid' => $tipoVehiculo->tipovehiculoid]);

        $this->expectException(\InvalidArgumentException::class);

        app(TrasladoPlantaMayoristaService::class)->crear(

            $planta,

            $mayorista,

            (int) $flota['transportista']->usuarioid,

            (int) $flota['vehiculo']->vehiculoid,

            (int) $admin->usuarioid,

            [[

                'insumoid' => $insumo->insumoid,

                'cantidad' => 500,

            ]],

            null,

            50.0

        );

    }



    public function test_no_permite_marcar_en_ruta_sin_aprobacion_mayorista(): void

    {

        $admin = $this->admin();

        $planta = $this->almacen('Planta Bloqueo', AlmacenAmbito::PLANTA, 'GPS -17.78,-63.18');

        $mayorista = $this->almacen('Mayorista Bloqueo', AlmacenAmbito::MAYORISTA, 'GPS -17.79,-63.19');

        $insumo = $this->insumoPlanta($planta);

        $flota = $this->flotaPlanta();



        $ruta = app(TrasladoPlantaMayoristaService::class)->crear(

            $planta,

            $mayorista,

            (int) $flota['transportista']->usuarioid,

            (int) $flota['vehiculo']->vehiculoid,

            (int) $admin->usuarioid,

            [['insumoid' => $insumo->insumoid, 'cantidad' => 50]],

            null,

            40.0

        );



        $response = $this->actingAs($admin)->patch(route('logistica.traslados-planta.empezar-ruta', $ruta));

        $response->assertRedirect();

        $response->assertSessionHas('error');

        $this->assertSame(RutaDistribucionCatalogo::ESTADO_PENDIENTE_APROBACION, $ruta->fresh()->estado);

    }

}
