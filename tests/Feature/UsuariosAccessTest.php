<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UsuariosAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $roleName): Usuario
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findOrCreate($roleName, 'web');

        $user = Usuario::create([
            'nombre' => 'Test',
            'apellido' => ucfirst($roleName),
            'email' => $roleName . '.usuarios@test.local',
            'nombreusuario' => $roleName . '_usuarios',
            'passwordhash' => Hash::make('secret123'),
            'role' => $roleName,
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);

        $user->syncRoles([$role->name]);
        return $user;
    }

    public function test_admin_puede_gestionar_usuarios(): void
    {
        $admin = $this->createUser('admin');
        $this->actingAs($admin);

        $this->get(route('gestion.index'))->assertOk();
    }

    public function test_agricultor_no_puede_ver_gestion_usuarios(): void
    {
        $agricultor = $this->createUser('agricultor');
        $this->actingAs($agricultor);

        $this->get(route('gestion.index'))->assertForbidden();
    }
}

