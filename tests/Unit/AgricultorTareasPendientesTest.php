<?php

namespace Tests\Unit;

use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\EstadoLoteTipo;
use App\Models\Lote;
use App\Models\Prioridad;
use App\Models\TipoActividad;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Support\AgricultorLoginNotificacion;
use Database\Seeders\CatalogosOperacionAgricolaSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AgricultorTareasPendientesTest extends TestCase
{
    use RefreshDatabase;

    private function crearAgricultor(): Usuario
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(CatalogosOperacionAgricolaSeeder::class);
        $role = Role::findOrCreate('agricultor', 'web');

        $user = Usuario::create([
            'nombre' => 'Luis',
            'apellido' => 'Guerrero',
            'email' => 'agricultor.tareas@test.local',
            'nombreusuario' => 'agricultor_tareas',
            'passwordhash' => Hash::make('secret123'),
            'role' => 'agricultor',
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);
        $user->syncRoles([$role->name]);

        return $user;
    }

    private function crearLotePlanificado(Usuario $agricultor, string $nombre): Lote
    {
        $planificado = EstadoLoteTipo::query()->firstOrCreate(
            ['nombre' => 'Planificación'],
            ['nombre' => 'Planificación']
        );
        $unidad = UnidadMedida::query()->firstOrCreate(
            ['abreviatura' => 'ha'],
            ['nombre' => 'Hectárea', 'categoria' => 'superficie']
        );
        $cultivo = Cultivo::query()->firstOrCreate(
            ['nombre' => 'Cebolla'],
            ['detalle' => 'Test']
        );

        return Lote::create([
            'usuarioid' => $agricultor->usuarioid,
            'nombre' => $nombre,
            'ubicacion' => 'Parcela test',
            'superficie' => 0.3,
            'unidadsuperficieid' => $unidad->unidadmedidaid,
            'cultivoid' => $cultivo->cultivoid,
            'estadolotetipoid' => $planificado->estadolotetipoid,
            'fechacreacion' => now(),
            'fechamodificacion' => now(),
        ]);
    }

    public function test_incluye_lotes_planificados_sin_actividad_asignada(): void
    {
        $agricultor = $this->crearAgricultor();
        $this->crearLotePlanificado($agricultor, 'Cebolla - Lote 002');

        $tareas = AgricultorLoginNotificacion::todasActividadesPendientes($agricultor);

        $this->assertCount(1, $tareas);
        $this->assertSame('Siembra', $tareas[0]['titulo']);
        $this->assertSame('Cebolla - Lote 002', $tareas[0]['lote']);
        $this->assertStringStartsWith('lote-planificado:', $tareas[0]['clave']);
    }

    public function test_no_duplica_lote_planificado_si_ya_tiene_actividad_pendiente(): void
    {
        $agricultor = $this->crearAgricultor();
        $lote = $this->crearLotePlanificado($agricultor, 'Lechuga Crespa - Lote 001');
        $tipoSiembra = TipoActividad::query()->firstOrCreate(
            ['nombre' => 'Siembra'],
            ['descripcion' => 'Siembra']
        );
        $prioridad = Prioridad::query()->firstOrCreate(['nombre' => 'Media'], ['nombre' => 'Media']);

        Actividad::create([
            'loteid' => $lote->loteid,
            'usuarioid' => $agricultor->usuarioid,
            'tipoactividadid' => $tipoSiembra->tipoactividadid,
            'prioridadid' => $prioridad->prioridadid,
            'descripcion' => 'Siembra',
            'fechainicio' => now(),
            'fechafin' => null,
        ]);

        $tareas = AgricultorLoginNotificacion::todasActividadesPendientes($agricultor);

        $this->assertCount(1, $tareas);
        $this->assertStringStartsWith('actividad:', $tareas[0]['clave']);
    }
}
