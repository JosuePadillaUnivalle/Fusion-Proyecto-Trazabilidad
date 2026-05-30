<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AlmacenMovimientosAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $roleName): Usuario
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findOrCreate($roleName, 'web');

        $user = Usuario::create([
            'nombre' => 'Test',
            'apellido' => ucfirst($roleName),
            'email' => $roleName . '.mov@test.local',
            'nombreusuario' => $roleName . '_mov',
            'passwordhash' => Hash::make('secret123'),
            'role' => $roleName,
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);

        $user->syncRoles([$role->name]);

        return $user;
    }

    public function test_admin_puede_ver_y_crear_movimientos(): void
    {
        $admin = $this->createUser('admin');
        $this->actingAs($admin);

        $this->get(route('almacen-movimientos.index'))->assertOk();
        $this->get(route('almacen-movimientos.create', ['naturaleza' => 'ingreso']))->assertOk();
    }

    public function test_agricultor_puede_ver_y_crear_movimientos(): void
    {
        $agricultor = $this->createUser('agricultor');
        $this->actingAs($agricultor);

        $this->get(route('almacen-movimientos.index'))->assertOk();
        $this->get(route('almacen-movimientos.create', ['naturaleza' => 'salida']))->assertOk();
    }

    public function test_transportista_no_accede_a_movimientos_internos(): void
    {
        $transportista = $this->createUser('transportista');
        $this->actingAs($transportista);

        $this->get(route('almacen-movimientos.index'))->assertForbidden();
    }
}
