<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrgTrackAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithRole(string $roleName): Usuario
    {
        $role = Role::findOrCreate($roleName, 'web');

        $permissionMap = [
            'admin' => ['envios.view', 'envios.create', 'envios.admin.view', 'vehiculos.view', 'transportistas.view', 'direcciones.view', 'reportes.view'],
            'operador' => ['envios.view', 'envios.create', 'vehiculos.view', 'transportistas.view', 'direcciones.view', 'reportes.view'],
            'agricultor' => ['reportes.view'],
        ];

        foreach ($permissionMap[$roleName] ?? [] as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        if (isset($permissionMap[$roleName])) {
            $role->syncPermissions($permissionMap[$roleName]);
        }

        $user = Usuario::create([
            'nombre' => 'Test',
            'apellido' => ucfirst($roleName),
            'email' => $roleName . '@test.local',
            'nombreusuario' => $roleName . '_user',
            'passwordhash' => Hash::make('secret123'),
            'role' => $roleName,
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
        ]);

        $user->assignRole($roleName);

        return $user;
    }

    public function test_operador_puede_acceder_modulos_envios_vehiculos_reportes(): void
    {
        $operador = $this->createUserWithRole('operador');
        $this->actingAs($operador);

        $this->get(route('envios.seguimiento'))->assertOk();
        $this->get(route('envios.vehiculos'))->assertOk();
        $this->get(route('envios.reportes-distribucion'))->assertOk();
    }

    public function test_operador_no_puede_acceder_dashboard_admin_logistico(): void
    {
        $operador = $this->createUserWithRole('operador');
        $this->actingAs($operador);

        $this->get(route('envios.admin'))->assertForbidden();
    }

    public function test_admin_puede_acceder_dashboard_admin_logistico(): void
    {
        $admin = $this->createUserWithRole('admin');
        $this->actingAs($admin);

        $this->get(route('envios.admin'))->assertOk();
    }

    public function test_agricultor_solo_accede_a_reportes_de_distribucion(): void
    {
        $agricultor = $this->createUserWithRole('agricultor');
        $this->actingAs($agricultor);

        $this->get(route('envios.reportes-distribucion'))->assertOk();
        $this->get(route('envios.vehiculos'))->assertForbidden();
        $this->get(route('envios.seguimiento'))->assertForbidden();
    }

    public function test_proxy_envia_bearer_token_a_orgtrack(): void
    {
        $operador = $this->createUserWithRole('operador');
        $this->actingAs($operador);

        config()->set('services.orgtrack.url', 'https://orgtrack.example');
        config()->set('services.orgtrack.token', 'token-prueba');

        Http::fake([
            'https://orgtrack.example/*' => Http::response(['ok' => true], 200),
        ]);

        $this->get(route('envios.api.vehiculos'))->assertOk();

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer token-prueba')
                && $request->url() === 'https://orgtrack.example/api/vehiculos';
        });
    }

    public function test_orgtrack_sin_url_no_llama_http_y_devuelve_payload_local(): void
    {
        $operador = $this->createUserWithRole('operador');
        $this->actingAs($operador);

        Http::fake();

        config()->set('services.orgtrack.url', '');

        $this->get(route('envios.api.envios'))
            ->assertOk()
            ->assertJsonStructure(['data']);

        Http::assertNothingSent();
    }
}

