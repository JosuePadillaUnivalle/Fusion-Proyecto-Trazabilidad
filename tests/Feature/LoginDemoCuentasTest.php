<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Support\CuentaEstado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginDemoCuentasTest extends TestCase
{
    use RefreshDatabase;

    public function test_luis_guerrero_entra_con_email_mayusculas_y_12345(): void
    {
        $usuario = Usuario::create([
            'nombre' => 'Luis',
            'apellido' => 'Guerrero',
            'email' => 'LuisGuerrero123@gmail.com',
            'nombreusuario' => 'luis_guerrero',
            'passwordhash' => bcrypt('12345'),
            'activo' => true,
            'estado_cuenta' => CuentaEstado::APROBADO,
            'role' => 'agricultor',
            'fecharegistro' => now(),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'LuisGuerrero123@gmail.com',
            'password' => '12345',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_login_no_depende_de_mayusculas_en_email(): void
    {
        Usuario::create([
            'nombre' => 'Luis',
            'apellido' => 'Guerrero',
            'email' => 'LuisGuerrero123@gmail.com',
            'nombreusuario' => 'luis_guerrero',
            'passwordhash' => bcrypt('12345'),
            'activo' => true,
            'estado_cuenta' => CuentaEstado::APROBADO,
            'role' => 'agricultor',
            'fecharegistro' => now(),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'luisguerrero123@gmail.com',
            'password' => '12345',
        ]);

        $response->assertRedirect(route('dashboard'));
    }
}
