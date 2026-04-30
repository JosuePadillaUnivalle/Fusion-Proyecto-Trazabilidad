<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $roleName): Usuario
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findOrCreate($roleName, 'web');

        $user = Usuario::create([
            'nombre' => 'Api',
            'apellido' => ucfirst($roleName),
            'email' => $roleName . '.api@test.local',
            'nombreusuario' => $roleName . '_api',
            'passwordhash' => Hash::make('secret123'),
            'role' => $roleName,
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);
        $user->syncRoles([$role->name]);

        return $user;
    }

    public function test_admin_tiene_acceso_total_api(): void
    {
        $admin = $this->createUserWithRole('admin');
        Sanctum::actingAs($admin);

        $this->getJson('/api/pedidos')->assertOk();
        $this->getJson('/api/ventas')->assertOk();
        $this->getJson('/api/certificaciones')->assertOk();
    }

    public function test_operador_accede_api_pedidos_y_ventas_pero_no_certificaciones(): void
    {
        $operador = $this->createUserWithRole('operador');
        Sanctum::actingAs($operador);

        $this->getJson('/api/pedidos')->assertOk();
        $this->getJson('/api/ventas')->assertOk();
        $this->getJson('/api/certificaciones')->assertForbidden();
    }

    public function test_agricultor_solo_accede_api_certificaciones(): void
    {
        $agricultor = $this->createUserWithRole('agricultor');
        Sanctum::actingAs($agricultor);

        $this->getJson('/api/certificaciones')->assertOk();
        $this->getJson('/api/pedidos')->assertForbidden();
        $this->getJson('/api/ventas')->assertForbidden();
    }
}

