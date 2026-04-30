<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CatalogosAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $roleName): Usuario
    {
        $this->seed(RolePermissionSeeder::class);
        $role = Role::findOrCreate($roleName, 'web');

        $user = Usuario::create([
            'nombre' => 'Test',
            'apellido' => ucfirst($roleName),
            'email' => $roleName . '.catalogos@test.local',
            'nombreusuario' => $roleName . '_catalogos',
            'passwordhash' => Hash::make('secret123'),
            'role' => $roleName,
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);

        $user->syncRoles([$role->name]);
        return $user;
    }

    public function test_operador_puede_ver_catalogos_api(): void
    {
        $operador = $this->createUser('operador');
        Sanctum::actingAs($operador);

        $this->getJson('/api/cultivos')->assertOk();
    }

    public function test_agricultor_puede_ver_catalogos_api(): void
    {
        $agricultor = $this->createUser('agricultor');
        Sanctum::actingAs($agricultor);

        $this->getJson('/api/tipoinsumos')->assertOk();
    }
}

