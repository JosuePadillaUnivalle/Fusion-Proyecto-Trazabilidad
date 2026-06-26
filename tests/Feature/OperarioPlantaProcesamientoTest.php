<?php

namespace Tests\Feature;

use App\Models\AsignacionEtapaPlanta;
use App\Models\LoteProduccionPedido;
use App\Models\MaquinaPlanta;
use App\Models\Pedido;
use App\Models\ProcesoPlanta;
use App\Models\Usuario;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OperarioPlantaProcesamientoTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $roleName, string $suffix = ''): Usuario
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findOrCreate($roleName, 'web');
        $slug = $roleName.$suffix;

        $user = Usuario::create([
            'nombre' => 'Test',
            'apellido' => ucfirst($roleName).$suffix,
            'email' => $slug.'_op_planta@test.local',
            'nombreusuario' => $slug.'_op_planta',
            'passwordhash' => Hash::make('secret123'),
            'role' => $roleName,
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);

        $user->syncRoles([$role->name]);

        return $user;
    }

    public function test_operario_solo_ve_lotes_con_etapas_asignadas(): void
    {
        $jefe = $this->createUser('jefe_planta', 'J');
        $operario = $this->createUser('planta', 'A');
        $otroOperario = $this->createUser('planta', 'B');

        $proceso = ProcesoPlanta::create(['nombre' => 'Preparación', 'activo' => true]);
        $maquina = MaquinaPlanta::create(['nombre' => 'Máquina test', 'codigo' => 'MQ-1', 'activo' => true]);

        $loteAsignado = $this->crearLote('LP-OP-001', 'Lote asignado');
        $loteAjeno = $this->crearLote('LP-OP-002', 'Lote ajeno');

        AsignacionEtapaPlanta::create([
            'loteproduccionpedidoid' => $loteAsignado->loteproduccionpedidoid,
            'procesoplantaid' => $proceso->procesoplantaid,
            'maquinaplantaid' => $maquina->maquinaplantaid,
            'operador_usuarioid' => $operario->usuarioid,
            'asignado_por_usuarioid' => $jefe->usuarioid,
            'orden' => 1,
            'estado' => AsignacionEtapaPlanta::ESTADO_PENDIENTE,
            'creado_en' => now(),
        ]);
        AsignacionEtapaPlanta::create([
            'loteproduccionpedidoid' => $loteAjeno->loteproduccionpedidoid,
            'procesoplantaid' => $proceso->procesoplantaid,
            'maquinaplantaid' => $maquina->maquinaplantaid,
            'operador_usuarioid' => $otroOperario->usuarioid,
            'asignado_por_usuarioid' => $jefe->usuarioid,
            'orden' => 1,
            'estado' => AsignacionEtapaPlanta::ESTADO_PENDIENTE,
            'creado_en' => now(),
        ]);

        $this->actingAs($operario)
            ->get(route('procesamiento.index'))
            ->assertOk()
            ->assertSee('LP-OP-001')
            ->assertDontSee('LP-OP-002');

        $this->actingAs($operario)
            ->get(route('procesamiento.show', $loteAsignado))
            ->assertOk();

        $this->actingAs($operario)
            ->get(route('procesamiento.show', $loteAjeno))
            ->assertForbidden();
    }

    public function test_operario_no_accede_certificaciones_planta(): void
    {
        $operario = $this->createUser('planta');

        $this->actingAs($operario)
            ->get(route('certificaciones-planta.index'))
            ->assertForbidden();
    }

    private function crearLote(string $codigo, string $nombre): LoteProduccionPedido
    {
        $pedido = Pedido::create([
            'numero_solicitud' => $codigo.'-PED',
            'nombre_planta' => 'Planta test',
            'latitud' => -12.0,
            'longitud' => -77.0,
            'estado' => 'en produccion',
        ]);

        return LoteProduccionPedido::create([
            'pedidoid' => $pedido->pedidoid,
            'codigo_lote' => $codigo,
            'nombre' => $nombre,
            'producto' => 'Producto test',
            'fecha_creacion' => now()->toDateString(),
        ]);
    }
}
