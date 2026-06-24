<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReporteCentroFiltrosTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Usuario
    {
        $this->seed(RolePermissionSeeder::class);
        Role::findOrCreate('admin', 'web');

        $user = Usuario::create([
            'nombre' => 'Admin',
            'apellido' => 'Reportes',
            'email' => 'admin.reportes@test.local',
            'nombreusuario' => 'admin_reportes',
            'passwordhash' => Hash::make('secret'),
            'role' => 'admin',
            'fecharegistro' => now(),
            'activo' => true,
        ]);
        $user->assignRole('admin');

        return $user;
    }

    public function test_reportes_con_filtros_responden_ok(): void
    {
        $admin = $this->admin();

        $cases = [
            ['reportes.envios-estado', ['fecha_desde' => '2026-01-01', 'fecha_hasta' => '2026-12-31']],
            ['reportes.envios-estado', ['fecha_desde' => '2026-01-01', 'fecha_hasta' => '2026-12-31', 'estado_envio' => 'Pendiente']],
            ['reportes.stock-ambito', ['ambito' => 'planta']],
            ['reportes.stock-ambito', ['solo_criticos' => '1']],
            ['reportes.transportistas', ['fecha_desde' => '2026-01-01', 'fecha_hasta' => '2026-12-31']],
            ['reportes.traslados-planta-mayorista', ['fecha_desde' => '2026-01-01', 'fecha_hasta' => '2026-12-31', 'estado' => 'completada']],
            ['reportes.pedidos-pdv', ['fecha_desde' => '2026-01-01', 'fecha_hasta' => '2026-12-31']],
            ['reportes.productos-terminados', ['ambito' => 'planta']],
        ];

        foreach ($cases as [$route, $query]) {
            $response = $this->actingAs($admin)->get(route($route, $query));
            $response->assertOk("Fallo en ruta {$route}: ".$response->status());
            $response->assertSee('rpt-kpi', false);
        }
    }
}
