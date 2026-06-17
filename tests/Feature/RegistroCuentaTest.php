<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Support\CuentaEstado;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistroCuentaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_registro_publico_crea_solicitud_pendiente(): void
    {
        $response = $this->post(route('register.post'), [
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan@test.local',
            'telefono' => '+591 71111111',
            'ci_nit' => '1234567',
            'rol_solicitado' => 'jefe_agricultor',
            'carta_motivacion' => str_repeat('Quiero formar parte de AgroFusion para mejorar la trazabilidad. ', 3),
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('register.enviado'));
        $this->assertDatabaseHas('usuario', [
            'email' => 'juan@test.local',
            'estado_cuenta' => CuentaEstado::PENDIENTE,
            'rol_solicitado' => 'jefe_agricultor',
        ]);
    }

    public function test_usuario_pendiente_no_puede_iniciar_sesion(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Pendiente',
            'apellido' => 'Test',
            'email' => 'pendiente@test.local',
            'nombreusuario' => 'pendiente_test',
            'telefono' => '70000000',
            'ci_nit' => '9999999',
            'passwordhash' => bcrypt('secret123'),
            'estado_cuenta' => CuentaEstado::PENDIENTE,
            'activo' => true,
            'fecharegistro' => now(),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => $usuario->email,
            'password' => 'secret123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_transportista_debe_indicar_licencias(): void
    {
        $base = [
            'nombre' => 'Pedro',
            'apellido' => 'Choque',
            'email' => 'pedro.transportista@test.local',
            'telefono' => '+591 71112222',
            'ci_nit' => '5555555',
            'rol_solicitado' => 'transportista',
            'carta_motivacion' => str_repeat('Quiero trabajar como transportista en AgroFusion. ', 2),
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $this->post(route('register.post'), $base)->assertSessionHasErrors('licencias');

        $response = $this->post(route('register.post'), $base + ['licencias' => ['C']]);
        $response->assertRedirect(route('register.enviado'));
        $this->assertDatabaseHas('usuario', [
            'email' => 'pedro.transportista@test.local',
            'tipo_licencia' => 'C',
        ]);
    }

    public function test_carta_motivacion_requiere_minimo_30_caracteres(): void
    {
        $response = $this->post(route('register.post'), [
            'nombre' => 'Ana',
            'apellido' => 'López',
            'email' => 'ana@test.local',
            'telefono' => '+591 71111111',
            'ci_nit' => '7654321',
            'rol_solicitado' => 'jefe_agricultor',
            'carta_motivacion' => 'Texto demasiado corto',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertSessionHasErrors('carta_motivacion');
    }

    public function test_registro_rechaza_nombre_con_numeros(): void
    {
        $payload = [
            'nombre' => 'Juan123',
            'apellido' => 'Pérez',
            'email' => 'invalido-nombre@test.local',
            'telefono' => '+591 71111111',
            'ci_nit' => '1234567',
            'rol_solicitado' => 'jefe_agricultor',
            'carta_motivacion' => str_repeat('Quiero formar parte de AgroFusion para mejorar la trazabilidad. ', 3),
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $this->post(route('register.post'), $payload)->assertSessionHasErrors('nombre');
    }

    public function test_registro_rechaza_telefono_con_letras(): void
    {
        $payload = [
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'invalido-telefono@test.local',
            'telefono' => '+591 abcdef',
            'ci_nit' => '1234567',
            'rol_solicitado' => 'jefe_agricultor',
            'carta_motivacion' => str_repeat('Quiero formar parte de AgroFusion para mejorar la trazabilidad. ', 3),
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $this->post(route('register.post'), $payload)->assertSessionHasErrors('telefono');
    }

    public function test_registro_rechaza_ci_nit_con_caracteres_especiales(): void
    {
        $payload = [
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'invalido-ci@test.local',
            'telefono' => '+591 71111111',
            'ci_nit' => '1234567@LP',
            'rol_solicitado' => 'jefe_agricultor',
            'carta_motivacion' => str_repeat('Quiero formar parte de AgroFusion para mejorar la trazabilidad. ', 3),
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $this->post(route('register.post'), $payload)->assertSessionHasErrors('ci_nit');
    }

    public function test_registro_acepta_ci_nit_con_letras_y_numeros(): void
    {
        $response = $this->post(route('register.post'), [
            'nombre' => 'María',
            'apellido' => 'Choque',
            'email' => 'maria.ci@test.local',
            'telefono' => '+591 70001234',
            'ci_nit' => '1234567 LP',
            'rol_solicitado' => 'jefe_agricultor',
            'carta_motivacion' => str_repeat('Quiero formar parte de AgroFusion para mejorar la trazabilidad. ', 3),
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('register.enviado'));
        $this->assertDatabaseHas('usuario', ['ci_nit' => '1234567 LP']);
    }

    public function test_registro_minorista_crea_solicitud_pendiente(): void
    {
        $response = $this->post(route('register.post'), [
            'nombre' => 'María',
            'apellido' => 'López',
            'email' => 'maria.minorista@test.local',
            'telefono' => '+591 72223333',
            'ci_nit' => '8765432',
            'rol_solicitado' => 'minorista',
            'carta_motivacion' => str_repeat('Deseo comercializar productos trazables de AgroFusion. ', 2),
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('register.enviado'));
        $this->assertDatabaseHas('usuario', [
            'email' => 'maria.minorista@test.local',
            'estado_cuenta' => CuentaEstado::PENDIENTE,
            'rol_solicitado' => 'minorista',
        ]);
    }
}
