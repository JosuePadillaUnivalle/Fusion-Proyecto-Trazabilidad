<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Support\CuentaEstado;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SolicitudUsuarioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_no_puede_editar_solicitud_pendiente(): void
    {
        $admin = $this->crearUsuarioConRol('admin');
        $pendiente = $this->crearSolicitudPendiente();

        $response = $this->actingAs($admin)->get(route('gestion.edit', $pendiente));

        $response->assertRedirect(route('gestion.show', $pendiente));
        $response->assertSessionHas('error');
    }

    public function test_admin_no_puede_eliminar_solicitud_pendiente(): void
    {
        $admin = $this->crearUsuarioConRol('admin');
        $pendiente = $this->crearSolicitudPendiente();

        $response = $this->actingAs($admin)->delete(route('gestion.usuario.destroy', $pendiente));

        $response->assertForbidden();
        $this->assertDatabaseHas('usuario', ['usuarioid' => $pendiente->usuarioid]);
    }

    public function test_rechazar_solicitud_elimina_usuario(): void
    {
        $admin = $this->crearUsuarioConRol('admin');
        $pendiente = $this->crearSolicitudPendiente();
        $id = $pendiente->usuarioid;

        $response = $this->actingAs($admin)->post(route('gestion.solicitud.rechazar', $pendiente));

        $response->assertRedirect(route('gestion.index'));
        $this->assertDatabaseMissing('usuario', ['usuarioid' => $id]);
    }

    public function test_aprobar_genera_nombre_usuario_y_bienvenida_pendiente(): void
    {
        $admin = $this->crearUsuarioConRol('admin');
        $pendiente = $this->crearSolicitudPendiente([
            'nombre' => 'Jose',
            'apellido' => 'Mamani',
            'nombreusuario' => 'sol_temporal123',
            'rol_solicitado' => 'agricultor',
        ]);

        $response = $this->actingAs($admin)->post(route('gestion.solicitud.aprobar', $pendiente));

        $response->assertRedirect(route('gestion.show', $pendiente));
        $pendiente->refresh();

        $this->assertSame(CuentaEstado::APROBADO, $pendiente->estado_cuenta);
        $this->assertFalse($pendiente->bienvenida_vista);
        $this->assertFalse($pendiente->nombreusuario_editado);
        $this->assertNotSame('sol_temporal123', $pendiente->nombreusuario);
        $this->assertMatchesRegularExpression('/^jmamani\d+$/', $pendiente->nombreusuario);
    }

    public function test_eliminar_usuario_con_relaciones_no_falla(): void
    {
        $admin = $this->crearUsuarioConRol('admin');
        $extra = Usuario::create([
            'nombre' => 'Demo',
            'apellido' => 'Eliminar',
            'email' => 'demo.eliminar@test.local',
            'nombreusuario' => 'demo_eliminar',
            'telefono' => '70000099',
            'passwordhash' => Hash::make('secret123'),
            'role' => 'agricultor',
            'estado_cuenta' => CuentaEstado::APROBADO,
            'fecharegistro' => now(),
            'activo' => true,
        ]);
        $extra->syncRoles(['agricultor']);

        $response = $this->actingAs($admin)->delete(route('gestion.usuario.destroy', $extra));

        $response->assertRedirect(route('gestion.index'));
        $this->assertDatabaseMissing('usuario', ['usuarioid' => $extra->usuarioid]);
    }

    public function test_no_se_puede_eliminar_usuario_esencial(): void
    {
        $admin = $this->crearUsuarioConRol('admin');
        $esencial = Usuario::create([
            'nombre' => 'Admin',
            'apellido' => 'Esencial',
            'email' => 'admin@agrofusion.com',
            'nombreusuario' => 'admin_esencial_test',
            'telefono' => '70000001',
            'passwordhash' => Hash::make('secret123'),
            'role' => 'admin',
            'estado_cuenta' => CuentaEstado::APROBADO,
            'fecharegistro' => now(),
            'activo' => true,
        ]);
        $esencial->syncRoles(['admin']);

        $response = $this->actingAs($admin)->delete(route('gestion.usuario.destroy', $esencial));

        $response->assertRedirect(route('gestion.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('usuario', ['email' => 'admin@agrofusion.com']);
    }

    public function test_usuario_solo_puede_editar_foto_y_nombre_usuario_una_vez(): void
    {
        $usuario = $this->crearUsuarioConRol('agricultor');
        $usuario->update([
            'nombreusuario_editado' => false,
            'bienvenida_vista' => true,
        ]);

        $response = $this->actingAs($usuario)->put(route('profile.update'), [
            'nombreusuario' => 'nuevoagricultor',
        ]);

        $response->assertRedirect(route('profile.show'));
        $usuario->refresh();
        $this->assertSame('nuevoagricultor', $usuario->nombreusuario);
        $this->assertTrue($usuario->nombreusuario_editado);

        $response2 = $this->actingAs($usuario)->put(route('profile.update'), [
            'nombreusuario' => 'otrointento',
        ]);

        $response2->assertRedirect(route('profile.show'));
        $usuario->refresh();
        $this->assertSame('nuevoagricultor', $usuario->nombreusuario);
    }

    private function crearUsuarioConRol(string $roleName): Usuario
    {
        $role = Role::findOrCreate($roleName, 'web');

        $user = Usuario::create([
            'nombre' => 'Test',
            'apellido' => ucfirst($roleName),
            'email' => $roleName.'.solicitud@test.local',
            'nombreusuario' => $roleName.'_solicitud',
            'passwordhash' => Hash::make('secret123'),
            'role' => $roleName,
            'estado_cuenta' => CuentaEstado::APROBADO,
            'fecharegistro' => now(),
            'fechamodificacion' => now(),
            'activo' => true,
            'bienvenida_vista' => true,
        ]);

        $user->syncRoles([$role->name]);

        return $user;
    }

    private function crearSolicitudPendiente(array $overrides = []): Usuario
    {
        return Usuario::create(array_merge([
            'nombre' => 'Solicitante',
            'apellido' => 'Prueba',
            'email' => 'solicitud'.uniqid().'@test.local',
            'nombreusuario' => 'sol_'.uniqid(),
            'telefono' => '70000000',
            'ci_nit' => (string) random_int(1000000, 9999999),
            'passwordhash' => bcrypt('secret123'),
            'carta_motivacion' => str_repeat('Quiero unirme a AgroFusion. ', 3),
            'rol_solicitado' => 'agricultor',
            'estado_cuenta' => CuentaEstado::PENDIENTE,
            'activo' => true,
            'fecharegistro' => now(),
        ], $overrides));
    }
}
