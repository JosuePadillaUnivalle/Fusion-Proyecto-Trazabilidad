<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InsumosAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $roleName): Usuario
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findOrCreate($roleName, 'web');

        $user = Usuario::create([
            'nombre' => 'Test',
            'apellido' => ucfirst($roleName),
            'email' => $roleName . '.insumos@test.local',
            'nombreusuario' => $roleName . '_insumos',
            'passwordhash' => Hash::make('secret123'),
            'role' => $roleName,
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);

        $user->syncRoles([$role->name]);
        return $user;
    }

    public function test_admin_y_operador_ven_api_insumos(): void
    {
        $admin = $this->createUser('admin');
        Sanctum::actingAs($admin);
        $this->getJson('/api/insumos')->assertOk();

        $operador = $this->createUser('operador');
        Sanctum::actingAs($operador);
        $this->getJson('/api/insumos')->assertOk();
    }

    public function test_agricultor_puede_ver_api_insumos(): void
    {
        $agricultor = $this->createUser('agricultor');
        Sanctum::actingAs($agricultor);
        $this->getJson('/api/insumos')->assertOk();
    }
}

