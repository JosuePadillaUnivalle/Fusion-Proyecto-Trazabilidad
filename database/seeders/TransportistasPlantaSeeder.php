<?php

namespace Database\Seeders;

use App\Models\PerfilTransportista;
use App\Models\TipoVehiculo;
use App\Models\Usuario;
use App\Models\Vehiculo;
use App\Support\CuentaEstado;
use App\Support\TelefonoBolivia;
use App\Support\TransportistaFlotaCatalogo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

/**
 * Choferes de flota planta para rutas de distribución a minoristas.
 * Ejecutar: php artisan db:seed --class=TransportistasPlantaSeeder
 */
class TransportistasPlantaSeeder extends Seeder
{
    private const PASSWORD = 'Password';

    public function run(): void
    {
        Role::findOrCreate('transportista', 'web');

        $tipoCamioneta = Schema::hasTable('tipo_vehiculo')
            ? TipoVehiculo::where('codigo', 'CAMIONETA')->value('tipovehiculoid')
            : null;

        $choferes = [
            [
                'email' => 'Mario@gmail.com',
                'nombre' => 'Mario',
                'apellido' => 'Chofer Planta',
                'nombreusuario' => 'mario_planta',
                'telefono' => '+591 71234001',
                'vehiculo' => [
                    'placa' => 'SCZ-PLT-01',
                    'marca' => 'Toyota',
                    'modelo' => 'Hilux',
                    'anio' => 2022,
                ],
            ],
            [
                'email' => 'Daniel@gmail.com',
                'nombre' => 'Daniel',
                'apellido' => 'Chofer Planta',
                'nombreusuario' => 'daniel_planta',
                'telefono' => '+591 71234002',
                'vehiculo' => [
                    'placa' => 'SCZ-PLT-02',
                    'marca' => 'Nissan',
                    'modelo' => 'Frontier',
                    'anio' => 2021,
                ],
            ],
            [
                'email' => 'Nahuel@gmail.com',
                'nombre' => 'Nahuel',
                'apellido' => 'Chofer Planta',
                'nombreusuario' => 'nahuel_planta',
                'telefono' => '+591 71234003',
                'vehiculo' => [
                    'placa' => 'SCZ-PLT-03',
                    'marca' => 'Mercedes',
                    'modelo' => 'Sprinter',
                    'anio' => 2020,
                ],
            ],
        ];

        foreach ($choferes as $entry) {
            $usuario = Usuario::updateOrCreate(
                ['email' => $entry['email']],
                [
                    'nombre' => $entry['nombre'],
                    'apellido' => $entry['apellido'],
                    'nombreusuario' => $entry['nombreusuario'],
                    'telefono' => $entry['telefono'] ?? null,
                    'passwordhash' => Hash::make(self::PASSWORD),
                    'role' => 'transportista',
                    'activo' => true,
                    'estado_cuenta' => CuentaEstado::APROBADO,
                    'fecharegistro' => now(),
                ]
            );

            $usuario->syncRoles(['transportista']);

            $vehiculoId = null;
            if (Schema::hasTable('vehiculo')) {
                $vehiculo = Vehiculo::updateOrCreate(
                    ['placa' => $entry['vehiculo']['placa']],
                    [
                        'marca' => $entry['vehiculo']['marca'],
                        'modelo' => $entry['vehiculo']['modelo'],
                        'anio' => $entry['vehiculo']['anio'],
                        'color' => 'Blanco',
                        'activo' => true,
                        'tipovehiculoid' => $tipoCamioneta,
                        'ambito_flota' => TransportistaFlotaCatalogo::PLANTA,
                    ]
                );
                $vehiculoId = $vehiculo->vehiculoid;
            }

            PerfilTransportista::updateOrCreate(
                ['usuarioid' => $usuario->usuarioid],
                [
                    'ambito_flota' => TransportistaFlotaCatalogo::PLANTA,
                    'vehiculoid' => $vehiculoId,
                    'disponible' => true,
                ]
            );
        }

        $extras = [
            ['placa' => 'SCZ-PLT-04', 'marca' => 'Ford', 'modelo' => 'Ranger', 'anio' => 2023],
            ['placa' => 'SCZ-PLT-05', 'marca' => 'Chevrolet', 'modelo' => 'D-Max', 'anio' => 2022],
            ['placa' => 'SCZ-PLT-06', 'marca' => 'Isuzu', 'modelo' => 'NPR', 'anio' => 2021],
        ];

        if (Schema::hasTable('vehiculo')) {
            foreach ($extras as $v) {
                Vehiculo::updateOrCreate(
                    ['placa' => $v['placa']],
                    [
                        'marca' => $v['marca'],
                        'modelo' => $v['modelo'],
                        'anio' => $v['anio'],
                        'color' => 'Blanco',
                        'activo' => true,
                        'tipovehiculoid' => $tipoCamioneta,
                        'ambito_flota' => TransportistaFlotaCatalogo::PLANTA,
                    ]
                );
            }
        }

        Usuario::query()
            ->where('role', 'transportista')
            ->whereNotNull('telefono')
            ->each(function (Usuario $usuario) {
                $normalizado = TelefonoBolivia::normalizar($usuario->telefono);
                if ($normalizado !== null && $normalizado !== $usuario->telefono) {
                    $usuario->update(['telefono' => $normalizado]);
                }
            });

        $this->command?->info('Transportistas de planta listos: Mario, Daniel y Nahuel (Password). Flota planta: SCZ-PLT-01 a 06. Teléfonos en formato +591.');
    }
}
