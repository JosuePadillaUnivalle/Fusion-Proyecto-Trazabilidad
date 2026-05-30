<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    private const DEMO_PASSWORD = '12345';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Usuario::updateOrCreate(
            ['email' => 'admin@agrofusion.com'],
            [
                'nombre' => 'Administrador',
                'apellido' => 'Sistema',
                'nombreusuario' => 'admin',
                'telefono' => '123456789',
                'passwordhash' => Hash::make(self::DEMO_PASSWORD),
                'role' => 'admin',
                'activo' => true,
                'fecharegistro' => now(),
                'fechamodificacion' => now(),
            ]
        );
        $admin->syncRoles(['admin']);

        $demoUsers = [
            ['email' => 'agricultor@agrofusion.com', 'nombre' => 'Usuario', 'apellido' => 'Agricultor', 'nombreusuario' => 'agricultor', 'telefono' => '700000001', 'role' => 'agricultor'],
            ['email' => 'planta@agrofusion.com', 'nombre' => 'Planta', 'apellido' => 'Principal', 'nombreusuario' => 'planta', 'telefono' => '700000103', 'role' => 'planta'],
            ['email' => 'transportista@agrofusion.com', 'nombre' => 'Carlos', 'apellido' => 'Mamani', 'nombreusuario' => 'transportista', 'telefono' => '700000104', 'role' => 'transportista'],
        ];

        foreach (['operador@agrofusion.com', 'almacen@agrofusion.com'] as $legacyEmail) {
            $legacy = Usuario::where('email', $legacyEmail)->first();
            if ($legacy) {
                $legacy->syncRoles(['agricultor']);
                $legacy->role = 'agricultor';
                $legacy->fechamodificacion = now();
                $legacy->save();
            }
        }

        foreach ($demoUsers as $entry) {
            $usuario = Usuario::updateOrCreate(
                ['email' => $entry['email']],
                [
                    'nombre' => $entry['nombre'],
                    'apellido' => $entry['apellido'],
                    'nombreusuario' => $entry['nombreusuario'],
                    'telefono' => $entry['telefono'],
                    'passwordhash' => Hash::make(self::DEMO_PASSWORD),
                    'role' => $entry['role'],
                    'activo' => true,
                    'fecharegistro' => now(),
                    'fechamodificacion' => now(),
                ]
            );
            $usuario->syncRoles([$entry['role']]);
        }
    }
}
