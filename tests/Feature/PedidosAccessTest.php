<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PedidosAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $roleName): Usuario
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findOrCreate($roleName, 'web');

        $user = Usuario::create([
            'nombre' => 'Test',
            'apellido' => ucfirst($roleName),
            'email' => $roleName . '.pedidos@test.local',
            'nombreusuario' => $roleName . '_pedidos',
            'passwordhash' => Hash::make('secret123'),
            'role' => $roleName,
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);

        $user->syncRoles([$role->name]);
        return $user;
    }

    public function test_admin_acceso_total_a_pedidos(): void
    {
        $admin = $this->createUser('admin');
        $this->actingAs($admin);

        $this->get(route('pedidos.index'))->assertOk();
        $this->get(route('pedidos.create'))->assertOk();
    }

    public function test_agricultor_ve_pedidos_sin_crear(): void
    {
        $agricultor = $this->createUser('agricultor');
        $this->actingAs($agricultor);

        $this->get(route('pedidos.index'))->assertOk();
        $this->get(route('pedidos.create'))->assertForbidden();
    }
}

