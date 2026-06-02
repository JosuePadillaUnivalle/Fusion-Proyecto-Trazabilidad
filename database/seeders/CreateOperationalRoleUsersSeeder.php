<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Support\CuentaEstado;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateOperationalRoleUsersSeeder extends Seeder
{
    private const DEMO_PASSWORD = '12345';

    public function run(): void
    {
        $users = [
            [
                'email' => 'admin@agrofusion.com',
                'nombre' => 'Administrador',
                'apellido' => 'Sistema',
                'nombreusuario' => 'admin',
                'telefono' => '123456789',
                'role' => 'admin',
                'password' => self::DEMO_PASSWORD,
            ],
            [
                'email' => 'agricultor@agrofusion.com',
                'nombre' => 'Usuario',
                'apellido' => 'Agricultor',
                'nombreusuario' => 'agricultor',
                'telefono' => '700000001',
                'role' => 'agricultor',
                'password' => self::DEMO_PASSWORD,
            ],
            [
                'email' => 'planta@agrofusion.com',
                'nombre' => 'Usuario',
                'apellido' => 'Planta',
                'nombreusuario' => 'planta',
                'telefono' => '700000003',
                'role' => 'planta',
                'password' => self::DEMO_PASSWORD,
            ],
            [
                'email' => 'transportista@agrofusion.com',
                'nombre' => 'Usuario',
                'apellido' => 'Transportista',
                'nombreusuario' => 'transportista',
                'telefono' => '700000004',
                'role' => 'transportista',
                'password' => self::DEMO_PASSWORD,
            ],
        ];

        foreach ($users as $entry) {
            $roleName = $entry['role'];
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            $usuario = Usuario::updateOrCreate(
                ['email' => $entry['email']],
                [
                    'nombre' => $entry['nombre'],
                    'apellido' => $entry['apellido'],
                    'nombreusuario' => $entry['nombreusuario'],
                    'telefono' => $entry['telefono'],
                    'passwordhash' => Hash::make($entry['password']),
                    'role' => $roleName,
                    'activo' => true,
                    'estado_cuenta' => CuentaEstado::APROBADO,
                    'fecharegistro' => now(),
                    'fechamodificacion' => now(),
                ]
            );

            $usuario->syncRoles([$roleName]);
        }
    }
}
