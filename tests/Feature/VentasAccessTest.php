<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VentasAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $roleName): Usuario
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findOrCreate($roleName, 'web');

        $user = Usuario::create([
            'nombre' => 'Test',
            'apellido' => ucfirst($roleName),
            'email' => $roleName . '.ventas@test.local',
            'nombreusuario' => $roleName . '_ventas',
            'passwordhash' => Hash::make('secret123'),
            'role' => $roleName,
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);

        $user->syncRoles([$role->name]);
        return $user;
    }

    public function test_admin_acceso_total_a_ventas(): void
    {
        $admin = $this->createUser('admin');
        $this->actingAs($admin);

        $this->get(route('ventas.index'))->assertOk();
        $this->get(route('ventas.create'))->assertOk();
    }

    public function test_operador_puede_ver_y_no_crear_ventas(): void
    {
        $operador = $this->createUser('operador');
        $this->actingAs($operador);

        $this->get(route('ventas.index'))->assertOk();
        $this->get(route('ventas.create'))->assertForbidden();
    }

    public function test_agricultor_no_accede_a_ventas(): void
    {
        $agricultor = $this->createUser('agricultor');
        $this->actingAs($agricultor);

        $this->get(route('ventas.index'))->assertForbidden();
    }
}

