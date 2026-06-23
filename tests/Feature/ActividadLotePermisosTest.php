<?php

namespace Tests\Feature;

use App\Models\Cultivo;
use App\Models\EstadoLoteTipo;
use App\Models\Lote;
use App\Models\TipoActividad;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use Database\Seeders\CatalogosOperacionAgricolaSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ActividadLotePermisosTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $roleName, array $overrides = []): Usuario
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(CatalogosOperacionAgricolaSeeder::class);
        $role = Role::findOrCreate($roleName, 'web');

        $user = Usuario::create(array_merge([
            'nombre' => 'Test',
            'apellido' => ucfirst($roleName),
            'email' => $roleName.'.actividad@test.local',
            'nombreusuario' => $roleName.'_actividad',
            'passwordhash' => Hash::make('secret123'),
            'role' => $roleName,
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ], $overrides));

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
            'nombre' => 'Lote prueba '.$responsable->usuarioid,
            'ubicacion' => 'Parcela test',
            'superficie' => 1,
            'unidadsuperficieid' => $unidad->unidadmedidaid,
            'cultivoid' => $cultivo->cultivoid,
            'estadolotetipoid' => $estado->estadolotetipoid,
            'fechacreacion' => now(),
            'fechamodificacion' => now(),
        ]);
    }

    public function test_agricultor_puede_abrir_asignar_actividad_de_su_lote(): void
    {
        TipoActividad::create(['nombre' => 'Riego']);
        $agricultor = $this->createUser('agricultor');
        $lote = $this->crearLote($agricultor);

        $this->actingAs($agricultor)
            ->get(route('actividades.create', [
                'loteid' => $lote->loteid,
                'tipo' => 'Riego',
                'return' => route('lotes.trazabilidad', $lote, absolute: false),
            ]))
            ->assertOk();
    }

    public function test_agricultor_no_puede_asignar_actividad_en_lote_ajeno(): void
    {
        TipoActividad::create(['nombre' => 'Riego']);
        $agricultor = $this->createUser('agricultor');
        $otro = $this->createUser('agricultor', [
            'email' => 'otro.agricultor@test.local',
            'nombreusuario' => 'otro_agricultor',
        ]);
        $lote = $this->crearLote($otro);

        $this->actingAs($agricultor)
            ->get(route('actividades.create', [
                'loteid' => $lote->loteid,
                'tipo' => 'Riego',
            ]))
            ->assertForbidden();
    }

    public function test_agricultor_puede_acceder_ruta_siembra_de_su_lote_sin_403(): void
    {
        TipoActividad::create(['nombre' => 'Siembra']);
        $agricultor = $this->createUser('agricultor');
        $lote = $this->crearLote($agricultor);

        $this->actingAs($agricultor)
            ->get(route('lotes.siembra.create', [
                'lote' => $lote,
                'return' => route('lotes.trazabilidad', $lote, absolute: false),
            ]))
            ->assertOk();
    }

    public function test_admin_puede_asignar_siembra_desde_trazabilidad(): void
    {
        $admin = $this->createUser('admin');
        $agricultor = $this->createUser('agricultor', [
            'email' => 'agri.siembra@test.local',
            'nombreusuario' => 'agri_siembra',
        ]);
        $estado = EstadoLoteTipo::query()->whereRaw('LOWER(nombre) LIKE ?', ['%planif%'])->first()
            ?? EstadoLoteTipo::query()->firstOrFail();
        $lote = Lote::create([
            'usuarioid' => $agricultor->usuarioid,
            'nombre' => 'Lote siembra test',
            'ubicacion' => 'Parcela test',
            'superficie' => 1,
            'unidadsuperficieid' => UnidadMedida::query()->firstOrCreate(
                ['abreviatura' => 'ha'],
                ['nombre' => 'Hectárea', 'categoria' => 'superficie']
            )->unidadmedidaid,
            'cultivoid' => Cultivo::query()->firstOrCreate(['nombre' => 'Tomate'], ['detalle' => 'Test'])->cultivoid,
            'estadolotetipoid' => $estado->estadolotetipoid,
            'fechacreacion' => now(),
            'fechamodificacion' => now(),
        ]);

        $this->actingAs($admin)
            ->post(route('lotes.siembra.asignar', $lote), [
                'usuarioid' => $agricultor->usuarioid,
            ])
            ->assertRedirect(route('lotes.trazabilidad', $lote));
    }
}
