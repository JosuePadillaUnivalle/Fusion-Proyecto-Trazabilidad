<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Usuario::updateOrCreate(
            ['email' => 'admin@agronexus.com'],
            [
                'nombre' => 'Administrador',
                'apellido' => 'Sistema',
                'nombreusuario' => 'admin',
                'telefono' => '123456789',
                'passwordhash' => Hash::make('123456'),
                'role' => 'admin',
                'activo' => true,
                'fecharegistro' => now(),
                'fechamodificacion' => now(),
            ]
        );

        // Assign Spatie Role
        $admin->syncRoles(['admin']);

        $agricultor = Usuario::updateOrCreate(
            ['email' => 'agricultor@agronexus.com'],
            [
                'nombre' => 'Usuario',
                'apellido' => 'Agricultor',
                'nombreusuario' => 'agricultor',
                'telefono' => '700000001',
                'passwordhash' => Hash::make('123456'),
                'role' => 'agricultor',
                'activo' => true,
                'fecharegistro' => now(),
                'fechamodificacion' => now(),
            ]
        );
        $agricultor->syncRoles(['agricultor']);

        $operador = Usuario::updateOrCreate(
            ['email' => 'operador@agronexus.com'],
            [
                'nombre' => 'Usuario',
                'apellido' => 'Operador',
                'nombreusuario' => 'operador',
                'telefono' => '700000002',
                'passwordhash' => Hash::make('123456'),
                'role' => 'operador',
                'activo' => true,
                'fecharegistro' => now(),
                'fechamodificacion' => now(),
            ]
        );
        $operador->syncRoles(['operador']);

    }
}
