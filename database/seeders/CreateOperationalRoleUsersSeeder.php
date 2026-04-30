<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateOperationalRoleUsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email' => 'admin@agronexus.com',
                'nombre' => 'Administrador',
                'apellido' => 'Sistema',
                'nombreusuario' => 'admin',
                'telefono' => '123456789',
                'role' => 'admin',
                'password' => '123456',
            ],
            [
                'email' => 'agricultor@agronexus.com',
                'nombre' => 'Usuario',
                'apellido' => 'Agricultor',
                'nombreusuario' => 'agricultor',
                'telefono' => '700000001',
                'role' => 'agricultor',
                'password' => 'password',
            ],
            [
                'email' => 'operador@agronexus.com',
                'nombre' => 'Usuario',
                'apellido' => 'Operador',
                'nombreusuario' => 'operador',
                'telefono' => '700000002',
                'role' => 'operador',
                'password' => 'password',
            ],
            [
                'email' => 'planta@agronexus.com',
                'nombre' => 'Usuario',
                'apellido' => 'Planta',
                'nombreusuario' => 'planta',
                'telefono' => '700000003',
                'role' => 'planta',
                'password' => 'password',
            ],
            [
                'email' => 'transportista@agronexus.com',
                'nombre' => 'Usuario',
                'apellido' => 'Transportista',
                'nombreusuario' => 'transportista',
                'telefono' => '700000004',
                'role' => 'transportista',
                'password' => 'password',
            ],
            [
                'email' => 'almacen@agronexus.com',
                'nombre' => 'Usuario',
                'apellido' => 'Almacen',
                'nombreusuario' => 'almacen',
                'telefono' => '700000005',
                'role' => 'almacen',
                'password' => 'password',
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
                    'fecharegistro' => now(),
                    'fechamodificacion' => now(),
                ]
            );

            $usuario->syncRoles([$roleName]);

            if ($roleName === 'almacen') {
                $almacen = Almacen::query()->orderBy('almacenid')->first();
                if ($almacen) {
                    $usuario->almacenid = $almacen->almacenid;
                    $usuario->save();
                }
            }
        }
    }
}

