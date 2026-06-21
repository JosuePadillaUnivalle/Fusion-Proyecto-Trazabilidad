<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AlmacenDashboardScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_agricultor_usa_dashboard_principal(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $agricultor = Usuario::create([
            'nombre' => 'Campo',
            'apellido' => 'Principal',
            'email' => 'agri@test.local',
            'nombreusuario' => 'agri',
            'passwordhash' => Hash::make('secret123'),
            'role' => 'agricultor',
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);
        $agricultor->syncRoles(['agricultor']);

        $this->actingAs($agricultor);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertViewIs('dashboard.inicio.agricultor');
    }
}
