<?php

namespace Tests\Unit;

use App\Models\DetallePedido;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Pedido;
use App\Models\Usuario;
use App\Models\UsuarioNotificacion;
use App\Services\NotificacionUsuarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NotificacionEnvioListoRecogerTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifica_al_transportista_cuando_envio_listo_para_recoger(): void
    {
        $transportista = Usuario::create([
            'nombre' => 'Marco',
            'apellido' => 'Polo',
            'email' => 'marco.notif@test.local',
            'nombreusuario' => 'marco_notif',
            'passwordhash' => Hash::make('secret'),
            'role' => 'transportista',
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);

        $pedido = Pedido::create([
            'numero_solicitud' => 'PED-TEST-NOTIF-001',
            'origen_latitud' => -17.7953,
            'origen_longitud' => -63.16154,
            'latitud' => -17.81271,
            'longitud' => -63.17455,
            'estado' => 'confirmado',
            'fechapedido' => now(),
        ]);

        DetallePedido::create([
            'pedidoid' => $pedido->pedidoid,
            'cultivo_personalizado' => 'Lechuga Crespa',
            'cantidad' => 20,
        ]);

        $envio = EnvioAsignacionMultiple::create([
            'externo_envio_id' => $pedido->numero_solicitud,
            'pedidoid' => $pedido->pedidoid,
            'transportista_usuarioid' => $transportista->usuarioid,
            'estado' => 'asignado',
            'fecha_asignacion' => now(),
        ]);

        app(NotificacionUsuarioService::class)->envioListoParaRecoger($envio);

        $this->assertDatabaseHas('usuario_notificacion', [
            'usuarioid' => $transportista->usuarioid,
            'tipo' => 'envio_listo_recoger',
            'referencia_tipo' => 'envio_asignacion',
            'referencia_id' => $envio->envioasignacionmultipleid,
        ]);

        $notif = UsuarioNotificacion::query()->where('usuarioid', $transportista->usuarioid)->first();
        $this->assertStringContainsString('PED-TEST-NOTIF-001', $notif->mensaje);
        $this->assertStringContainsString('Lechuga Crespa', $notif->mensaje);
    }
}
