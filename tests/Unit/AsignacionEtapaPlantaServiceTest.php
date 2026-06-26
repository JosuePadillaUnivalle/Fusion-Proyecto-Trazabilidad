<?php

namespace Tests\Unit;

use App\Models\AsignacionEtapaPlanta;
use App\Models\LoteProduccionPedido;
use App\Models\MaquinaPlanta;
use App\Models\Pedido;
use App\Models\ProcesoPlanta;
use App\Models\Usuario;
use App\Support\AsignacionEtapaPlantaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsignacionEtapaPlantaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_asignacion_estados_pendiente_y_completada(): void
    {
        $asignacion = new AsignacionEtapaPlanta(['estado' => AsignacionEtapaPlanta::ESTADO_PENDIENTE]);
        $this->assertTrue($asignacion->estaPendiente());

        $asignacion->estado = AsignacionEtapaPlanta::ESTADO_COMPLETADA;
        $this->assertFalse($asignacion->estaPendiente());

        $asignacion->estado = AsignacionEtapaPlanta::ESTADO_PROGRAMADA;
        $this->assertTrue($asignacion->estaProgramada());
        $this->assertFalse($asignacion->estaPendiente());
    }

    public function test_sincronizar_promocion_cola_activa_siguiente_etapa_programada(): void
    {
        $lote = $this->crearLote();
        $proceso = ProcesoPlanta::create(['nombre' => 'Preparación', 'activo' => true]);
        $maquina = MaquinaPlanta::create(['nombre' => 'Máquina', 'codigo' => 'M-1', 'activo' => true]);
        $operario = Usuario::create([
            'nombre' => 'Op',
            'apellido' => 'Test',
            'email' => 'op_promo@test.local',
            'nombreusuario' => 'op_promo',
            'passwordhash' => 'hash',
            'fecharegistro' => now(),
            'activo' => true,
        ]);

        AsignacionEtapaPlanta::create([
            'loteproduccionpedidoid' => $lote->loteproduccionpedidoid,
            'procesoplantaid' => $proceso->procesoplantaid,
            'maquinaplantaid' => $maquina->maquinaplantaid,
            'operador_usuarioid' => $operario->usuarioid,
            'asignado_por_usuarioid' => $operario->usuarioid,
            'orden' => 1,
            'estado' => AsignacionEtapaPlanta::ESTADO_COMPLETADA,
            'completada_en' => now(),
            'creado_en' => now(),
        ]);

        $siguiente = AsignacionEtapaPlanta::create([
            'loteproduccionpedidoid' => $lote->loteproduccionpedidoid,
            'procesoplantaid' => $proceso->procesoplantaid,
            'maquinaplantaid' => $maquina->maquinaplantaid,
            'operador_usuarioid' => $operario->usuarioid,
            'asignado_por_usuarioid' => $operario->usuarioid,
            'orden' => 2,
            'estado' => AsignacionEtapaPlanta::ESTADO_PROGRAMADA,
            'creado_en' => now(),
        ]);

        app(AsignacionEtapaPlantaService::class)->sincronizarPromocionCola($lote->fresh());

        $this->assertSame(
            AsignacionEtapaPlanta::ESTADO_PENDIENTE,
            $siguiente->fresh()->estado
        );
    }

    private function crearLote(): LoteProduccionPedido
    {
        $pedido = Pedido::create([
            'numero_solicitud' => 'TEST-PROMO',
            'nombre_planta' => 'Planta',
            'latitud' => -12.0,
            'longitud' => -77.0,
            'estado' => 'en produccion',
        ]);

        return LoteProduccionPedido::create([
            'pedidoid' => $pedido->pedidoid,
            'codigo_lote' => 'LOTE-PROMO',
            'nombre' => 'Lote promo',
            'producto' => 'Test',
            'fecha_creacion' => now()->toDateString(),
        ]);
    }
}

