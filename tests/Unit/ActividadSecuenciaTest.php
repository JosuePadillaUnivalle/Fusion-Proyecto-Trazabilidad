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
use App\Support\ActividadPermisos;
use App\Support\ActividadSecuenciaService;
use Database\Seeders\CatalogosOperacionAgricolaSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActividadSecuenciaTest extends TestCase
{
    use RefreshDatabase;

    private function crearOperario(): Usuario
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(CatalogosOperacionAgricolaSeeder::class);
        $role = Role::findOrCreate('agricultor', 'web');

        $user = Usuario::create([
            'nombre' => 'Pepito',
            'apellido' => 'Operario',
            'email' => 'pepito.secuencia@test.local',
            'nombreusuario' => 'pepito_secuencia',
            'passwordhash' => Hash::make('secret123'),
            'role' => 'agricultor',
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);
        $user->syncRoles([$role->name]);

        return $user;
    }

    private function crearLote(Usuario $responsable): Lote
    {
        $estado = EstadoLoteTipo::query()->firstOrFail();
        $unidad = UnidadMedida::query()->firstOrCreate(
            ['abreviatura' => 'ha'],
            ['nombre' => 'Hectárea', 'categoria' => 'superficie']
        );
        $cultivo = Cultivo::query()->firstOrCreate(
            ['nombre' => 'Tomate'],
            ['detalle' => 'Test']
        );

        return Lote::create([
            'usuarioid' => $responsable->usuarioid,
            'nombre' => 'Lote secuencia',
            'ubicacion' => 'Parcela',
            'superficie' => 1,
            'unidadsuperficieid' => $unidad->unidadmedidaid,
            'cultivoid' => $cultivo->cultivoid,
            'estadolotetipoid' => $estado->estadolotetipoid,
            'fechacreacion' => now(),
            'fechamodificacion' => now(),
        ]);
    }

    private function crearActividad(Lote $lote, Usuario $operario, TipoActividad $tipo, int $orden): Actividad
    {
        $prioridad = Prioridad::query()->firstOrCreate(['nombre' => 'Media']);

        return Actividad::create([
            'loteid' => $lote->loteid,
            'usuarioid' => $operario->usuarioid,
            'descripcion' => $tipo->nombre,
            'fechainicio' => now()->addMinutes($orden),
            'fechafin' => null,
            'tipoactividadid' => $tipo->tipoactividadid,
            'prioridadid' => $prioridad->prioridadid,
            'orden_secuencia' => $orden,
        ]);
    }

    public function test_solo_la_primera_pendiente_esta_en_turno(): void
    {
        $pepito = $this->crearOperario();
        $lucia = Usuario::create([
            'nombre' => 'Lucia',
            'apellido' => 'Operaria',
            'email' => 'lucia.secuencia@test.local',
            'nombreusuario' => 'lucia_secuencia',
            'passwordhash' => Hash::make('secret123'),
            'role' => 'agricultor',
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);
        $lucia->syncRoles([Role::findOrCreate('agricultor', 'web')->name]);

        $lote = $this->crearLote($pepito);
        $fert = TipoActividad::query()->firstOrCreate(['nombre' => 'Fertilización'], ['descripcion' => 'Fertilización']);
        $riego = TipoActividad::query()->firstOrCreate(['nombre' => 'Riego'], ['descripcion' => 'Riego']);
        $plagas = TipoActividad::query()->firstOrCreate(['nombre' => 'Control de plagas'], ['descripcion' => 'Control de plagas']);

        $a1 = $this->crearActividad($lote, $pepito, $fert, 1);
        $a2 = $this->crearActividad($lote, $lucia, $riego, 2);
        $a3 = $this->crearActividad($lote, $pepito, $plagas, 3);

        $secuencia = app(ActividadSecuenciaService::class);

        $pendientes = $secuencia->pendientesOrdenadas($lote->fresh(), false);
        $this->assertCount(3, $pendientes);
        $this->assertSame((int) $a1->actividadid, (int) $pendientes->first()->actividadid);

        $this->assertTrue($secuencia->esSiguienteEnCola($a1->fresh(), false));
        $this->assertFalse($secuencia->esSiguienteEnCola($a2, false));
        $this->assertFalse(ActividadPermisos::puedeMarcarCompletada($lucia, $a2));
        $this->assertTrue(ActividadPermisos::puedeMarcarCompletada($pepito, $a1));

        $a1->fechafin = now();
        $a1->save();

        $this->assertTrue($secuencia->esSiguienteEnCola($a2->fresh(), false));
        $this->assertTrue(ActividadPermisos::puedeMarcarCompletada($lucia, $a2->fresh()));
        $this->assertFalse(ActividadPermisos::puedeMarcarCompletada($pepito, $a3->fresh()));
    }
}
