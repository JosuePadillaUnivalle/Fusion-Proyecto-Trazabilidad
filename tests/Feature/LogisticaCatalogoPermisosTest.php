<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LogisticaCatalogoPermisosTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $roleName): Usuario
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findOrCreate($roleName, 'web');

        $user = Usuario::create([
            'nombre' => 'Test',
            'apellido' => ucfirst($roleName),
            'email' => $roleName.'.catalogo@test.local',
            'nombreusuario' => $roleName.'_catalogo',
            'passwordhash' => Hash::make('secret123'),
            'role' => $roleName,
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);

        $user->syncRoles([$role->name]);

        return $user;
    }

    public function test_jefe_planta_puede_ver_tipos_empaque_pero_no_crear(): void
    {
        $user = $this->createUser('jefe_planta');

        $this->actingAs($user)->get(route('envios.catalogos.index', 'tipos-empaque'))->assertOk();
        $this->actingAs($user)->get(route('envios.catalogos.create', 'tipos-empaque'))->assertForbidden();
    }

    public function test_jefe_agricultor_puede_crear_tamano_conteo_pero_no_tipos_empaque(): void
    {
        $user = $this->createUser('jefe_agricultor');

        $this->actingAs($user)->get(route('envios.catalogos.create', 'tipos-empaque'))->assertForbidden();
        $this->actingAs($user)->get(route('envios.catalogos.create', 'tamano-conteo'))->assertOk();
    }

    public function test_mayorista_puede_crear_tipos_vehiculo(): void
    {
        $user = $this->createUser('mayorista');

        $this->actingAs($user)->get(route('envios.catalogos.create', 'tipos-vehiculo'))->assertOk();
        $this->actingAs($user)->get(route('envios.catalogos.create', 'tamano-conteo'))->assertForbidden();
    }
}
