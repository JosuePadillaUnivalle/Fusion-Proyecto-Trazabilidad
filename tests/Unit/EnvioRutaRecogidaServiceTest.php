<?php

namespace Tests\Unit;

use App\Models\EnvioAsignacionMultiple;
use App\Models\Pedido;
use App\Models\RutaMultiEntrega;
use App\Models\RutaParada;
use App\Models\Usuario;
use App\Support\EnvioAsignacionEstadoCatalogo;
use App\Support\EnvioPedidoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EnvioRutaRecogidaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_crea_ruta_con_varias_recogidas_y_vincula_envio(): void
    {
        $creador = Usuario::create([
            'nombre' => 'Admin',
            'apellido' => 'Test',
            'email' => 'admin.ruta@test.local',
            'nombreusuario' => 'admin_ruta',
            'passwordhash' => Hash::make('secret'),
            'role' => 'admin',
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);

        $transportista = Usuario::create([
            'nombre' => 'Chofer',
            'apellido' => 'Test',
            'email' => 'chofer.ruta@test.local',
            'nombreusuario' => 'chofer_ruta',
            'passwordhash' => Hash::make('secret'),
            'role' => 'transportista',
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);

        $pedido = Pedido::create([
            'numero_solicitud' => 'PED-TEST-RUTA-001',
            'origen_latitud' => -17.7833,
            'origen_longitud' => -63.1821,
            'origen_direccion' => 'Almacén agrícola A',
            'latitud' => -17.7942,
            'longitud' => -63.1615,
            'direccion_texto' => 'Almacén planta B',
            'estado' => 'sin asignacion',
            'fechapedido' => now(),
        ]);

        $envio = EnvioAsignacionMultiple::create(
            EnvioAsignacionEstadoCatalogo::applyToAttributes([
                'externo_envio_id' => $pedido->numero_solicitud,
                'pedidoid' => $pedido->pedidoid,
                'transportista_usuarioid' => $transportista->usuarioid,
                'estado' => 'pendiente',
            ])
        );

        $recogidasExtra = [
            [
                'latitud' => -17.7516,
                'longitud' => -63.2367,
                'direccion' => 'Almacén agrícola C',
            ],
        ];

        $ruta = EnvioPedidoService::crearRutaRecogidasMultiples(
            $pedido,
            $envio,
            $recogidasExtra,
            $transportista->usuarioid,
            $creador->usuarioid
        );

        $this->assertInstanceOf(RutaMultiEntrega::class, $ruta);
        $this->assertSame($transportista->usuarioid, $ruta->transportista_usuarioid);

        $paradas = RutaParada::query()
            ->where('rutamultientregaid', $ruta->rutamultientregaid)
            ->orderBy('orden')
            ->get();

        $this->assertCount(3, $paradas);
        $this->assertStringStartsWith('Recogida:', $paradas[0]->destino);
        $this->assertStringStartsWith('Recogida:', $paradas[1]->destino);
        $this->assertStringStartsWith('Entrega:', $paradas[2]->destino);
        $this->assertSame($pedido->origen_latitud, $paradas[0]->latitud);
        $this->assertSame(-17.7516, $paradas[1]->latitud);
        $this->assertSame($pedido->latitud, $paradas[2]->latitud);

        $envio->refresh();
        $this->assertSame($ruta->rutamultientregaid, $envio->rutamultientregaid);

        $envio->load('ruta.paradas', 'pedido');
        $this->assertSame(
            'Almacén agrícola A → Almacén agrícola C a Almacén planta B',
            EnvioPedidoService::trayectoTexto($envio)
        );
        $this->assertCount(3, EnvioPedidoService::paradasMapaEnvio($envio));
    }

    public function test_trayecto_simple_desde_pedido_sin_ruta_multi(): void
    {
        $pedido = Pedido::create([
            'numero_solicitud' => 'PED-TEST-RUTA-003',
            'origen_latitud' => -17.78,
            'origen_longitud' => -63.18,
            'origen_direccion' => 'Almacén Norte',
            'latitud' => -17.79,
            'longitud' => -63.16,
            'direccion_texto' => 'Planta Sur',
            'estado' => 'confirmado',
            'fechapedido' => now(),
        ]);

        $envio = EnvioAsignacionMultiple::create(
            EnvioAsignacionEstadoCatalogo::applyToAttributes([
                'externo_envio_id' => $pedido->numero_solicitud,
                'pedidoid' => $pedido->pedidoid,
                'estado' => 'asignado',
            ])
        );

        $envio->setRelation('pedido', $pedido);

        $this->assertSame('Almacén Norte a Planta Sur', EnvioPedidoService::trayectoTexto($envio));
        $this->assertCount(2, EnvioPedidoService::paradasMapaEnvio($envio));
    }

    public function test_trayecto_omite_coordenadas_gps(): void
    {
        $pedido = Pedido::create([
            'numero_solicitud' => 'PED-TEST-RUTA-004',
            'origen_latitud' => -17.7953,
            'origen_longitud' => -63.16154,
            'origen_direccion' => 'Almacen Sur Oeste · GPS -17.79530, -63.16154',
            'latitud' => -17.71591,
            'longitud' => -63.16893,
            'direccion_texto' => 'Almacen Remanso · GPS -17.71591, -63.16893',
            'estado' => 'confirmado',
            'fechapedido' => now(),
        ]);

        $envio = EnvioAsignacionMultiple::create(
            EnvioAsignacionEstadoCatalogo::applyToAttributes([
                'externo_envio_id' => $pedido->numero_solicitud,
                'pedidoid' => $pedido->pedidoid,
                'estado' => 'asignado',
            ])
        );

        $envio->setRelation('pedido', $pedido);

        $this->assertSame('Almacen Sur Oeste a Almacen Remanso', EnvioPedidoService::trayectoTexto($envio));

        $partes = EnvioPedidoService::trayectoPartes($envio);
        $this->assertSame(['Almacen Sur Oeste'], $partes['recogidas']);
        $this->assertSame('Almacen Remanso', $partes['destino']);
    }

    public function test_etiqueta_planta_destino_lista_usa_nombre_corto_almacen(): void
    {
        $pedido = Pedido::create([
            'numero_solicitud' => 'PED-TEST-LISTA-001',
            'origen_latitud' => -17.7953,
            'origen_longitud' => -63.16154,
            'origen_direccion' => 'GPS -17.79530, -63.16154',
            'latitud' => -17.81271,
            'longitud' => -63.17455,
            'direccion_texto' => 'GPS -17.81271, -63.17455',
            'estado' => 'sin asignacion',
            'fechapedido' => now(),
        ]);

        $lista = EnvioPedidoService::etiquetaPlantaDestinoLista($pedido);
        $this->assertNotNull($lista);
        $this->assertStringNotContainsString('GPS', $lista);
        $this->assertLessThanOrEqual(48, mb_strlen($lista));

        $trayectoLista = EnvioPedidoService::trayectoPartesListaPedido($pedido);
        $this->assertNotNull($trayectoLista);
        $this->assertStringNotContainsString('GPS', $trayectoLista['destino'] ?? '');
    }

    public function test_trayecto_con_solo_gps_resuelve_nombre_almacen(): void
    {
        $pedido = Pedido::create([
            'numero_solicitud' => 'PED-TEST-RUTA-005',
            'origen_latitud' => -17.7953,
            'origen_longitud' => -63.16154,
            'origen_direccion' => 'GPS -17.79530, -63.16154',
            'latitud' => -17.81271,
            'longitud' => -63.17455,
            'direccion_texto' => 'GPS -17.81271, -63.17455',
            'estado' => 'confirmado',
            'fechapedido' => now(),
        ]);

        $envio = EnvioAsignacionMultiple::create(
            EnvioAsignacionEstadoCatalogo::applyToAttributes([
                'externo_envio_id' => $pedido->numero_solicitud,
                'pedidoid' => $pedido->pedidoid,
                'estado' => 'pendiente',
            ])
        );

        $envio->setRelation('pedido', $pedido);

        $texto = EnvioPedidoService::trayectoTexto($envio);
        $this->assertNotNull($texto);
        $this->assertStringNotContainsString('—', $texto);
        $this->assertStringContainsString(' a ', $texto);
    }

    public function test_sin_recogidas_extra_no_crea_ruta(): void
    {
        $pedido = Pedido::create([
            'numero_solicitud' => 'PED-TEST-RUTA-002',
            'origen_latitud' => -17.78,
            'origen_longitud' => -63.18,
            'latitud' => -17.79,
            'longitud' => -63.16,
            'estado' => 'sin asignacion',
            'fechapedido' => now(),
        ]);

        $envio = EnvioAsignacionMultiple::create(
            EnvioAsignacionEstadoCatalogo::applyToAttributes([
                'externo_envio_id' => $pedido->numero_solicitud,
                'pedidoid' => $pedido->pedidoid,
                'estado' => 'pendiente',
            ])
        );

        $ruta = EnvioPedidoService::crearRutaRecogidasMultiples(
            $pedido,
            $envio,
            [],
            null,
            1
        );

        $this->assertNull($ruta);
        $this->assertNull($envio->fresh()->rutamultientregaid);
    }
}
