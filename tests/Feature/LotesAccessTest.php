<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LotesAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $roleName): Usuario
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findOrCreate($roleName, 'web');

        $user = Usuario::create([
            'nombre' => 'Test',
            'apellido' => ucfirst($roleName),
            'email' => $roleName . '.lotes@test.local',
            'nombreusuario' => $roleName . '_lotes',
            'passwordhash' => Hash::make('secret123'),
            'role' => $roleName,
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);

        $user->syncRoles([$role->name]);
        return $user;
    }

    public function test_admin_puede_ver_y_crear_lotes(): void
    {
        $admin = $this->createUser('admin');
        $this->actingAs($admin);

        $this->get(route('lotes.index'))->assertOk();
        $this->get(route('lotes.create'))->assertOk();
    }

    public function test_operador_solo_lectura_en_lotes(): void
    {
        $operador = $this->createUser('operador');
        $this->actingAs($operador);

        $this->get(route('lotes.index'))->assertOk();
        $this->get(route('lotes.create'))->assertForbidden();
    }

    public function test_agricultor_no_accede_a_lotes(): void
    {
        $agricultor = $this->createUser('agricultor');
        $this->actingAs($agricultor);

        $this->get(route('lotes.index'))->assertForbidden();
    }

    public function test_admin_y_operador_acceden_api_lotes(): void
    {
        $admin = $this->createUser('admin');
        Sanctum::actingAs($admin);
        $this->getJson('/api/lotes')->assertOk();

        $operador = $this->createUser('operador');
        Sanctum::actingAs($operador);
        $this->getJson('/api/lotes')->assertOk();
    }

    public function test_agricultor_no_accede_api_lotes(): void
    {
        $agricultor = $this->createUser('agricultor');
        Sanctum::actingAs($agricultor);
        $this->getJson('/api/lotes')->assertForbidden();
    }
}

