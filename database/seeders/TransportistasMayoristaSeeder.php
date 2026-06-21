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

 * Choferes de flota mayorista para rutas hacia puntos de venta.

 * Ejecutar: php artisan db:seed --class=TransportistasMayoristaSeeder

 */

class TransportistasMayoristaSeeder extends Seeder

{

    private const PASSWORD = 'Password';



    public function run(): void

    {

        Role::findOrCreate('transportista', 'web');



        $tipoCamioneta = Schema::hasTable('tipo_vehiculo')

            ? TipoVehiculo::where('codigo', 'CAMIONETA')->value('tipovehiculoid')

            : null;



        $vehiculos = [

            [

                'placa' => 'SCZ-MAY-01',

                'marca' => 'Toyota',

                'modelo' => 'Hilux',

                'anio' => 2022,

            ],

            [

                'placa' => 'SCZ-MAY-02',

                'marca' => 'Nissan',

                'modelo' => 'Frontier',

                'anio' => 2021,

            ],

            [

                'placa' => 'SCZ-MAY-03',

                'marca' => 'Mitsubishi',

                'modelo' => 'L200',

                'anio' => 2020,

            ],

            [

                'placa' => 'SCZ-MAY-04',

                'marca' => 'Chevrolet',

                'modelo' => 'D-Max',

                'anio' => 2023,

            ],

        ];



        $vehiculoIds = [];

        foreach ($vehiculos as $entry) {

            if (! Schema::hasTable('vehiculo')) {

                break;

            }

            $vehiculo = Vehiculo::updateOrCreate(

                ['placa' => $entry['placa']],

                [

                    'marca' => $entry['marca'],

                    'modelo' => $entry['modelo'],

                    'anio' => $entry['anio'],

                    'color' => 'Blanco',

                    'activo' => true,

                    'tipovehiculoid' => $tipoCamioneta,

                    'ambito_flota' => TransportistaFlotaCatalogo::MAYORISTA,

                ]

            );

            $vehiculoIds[$entry['placa']] = $vehiculo->vehiculoid;

        }



        $choferes = [

            [

                'email' => 'Pedro@gmail.com',

                'nombre' => 'Pedro',

                'apellido' => 'Chofer Mayorista',

                'nombreusuario' => 'pedro_mayorista',

                'telefono' => '+591 71235001',

                'vehiculo_placa' => 'SCZ-MAY-01',

                'licencia' => 'C-4521001',

                'tipo_licencia' => 'C',

            ],

            [

                'email' => 'Lucia@gmail.com',

                'nombre' => 'Lucía',

                'apellido' => 'Chofer Mayorista',

                'nombreusuario' => 'lucia_mayorista',

                'telefono' => '+591 71235002',

                'vehiculo_placa' => 'SCZ-MAY-02',

                'licencia' => 'C-4521002',

                'tipo_licencia' => 'C',

            ],

            [

                'email' => 'carlos.mayorista@gmail.com',

                'nombre' => 'Carlos',

                'apellido' => 'Chofer Mayorista',

                'nombreusuario' => 'carlos_mayorista',

                'telefono' => '+591 71235003',

                'vehiculo_placa' => 'SCZ-MAY-03',

                'licencia' => 'C-4521003',

                'tipo_licencia' => 'C',

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

                    'tipo_licencia' => $entry['tipo_licencia'] ?? null,

                ]

            );



            $usuario->syncRoles(['transportista']);



            $vehiculoId = $vehiculoIds[$entry['vehiculo_placa']] ?? null;



            $perfilData = [

                'ambito_flota' => TransportistaFlotaCatalogo::MAYORISTA,

                'vehiculoid' => $vehiculoId,

                'disponible' => true,

                'licencia' => $entry['licencia'] ?? null,

                'tipo_licencia' => $entry['tipo_licencia'] ?? null,

                'fecha_vencimiento_licencia' => now()->addYear()->toDateString(),

            ];

            if (Schema::hasColumn('perfil_transportista', 'licencias_json')) {

                $perfilData['licencias_json'] = [$entry['tipo_licencia'] ?? 'C'];

            }



            PerfilTransportista::updateOrCreate(

                ['usuarioid' => $usuario->usuarioid],

                $perfilData

            );

        }



        if (Schema::hasTable('punto_venta')) {

            \App\Models\PuntoVenta::query()

                ->where(function ($q) {

                    $q->whereNull('direccion')

                        ->orWhereRaw('LOWER(TRIM(direccion)) = ?', ['pdv'])

                        ->orWhereRaw('LOWER(TRIM(direccion)) LIKE ?', ['pdv gps%']);

                })

                ->whereNotNull('latitud')

                ->whereNotNull('longitud')

                ->each(function (\App\Models\PuntoVenta $pv) {

                    $ref = \App\Support\UbicacionGpsParser::referenciaPuntoVenta(

                        $pv->direccion,

                        $pv->latitud,

                        $pv->longitud,

                        (int) $pv->puntoventaid,

                        $pv->nombre

                    );

                    if ($ref['direccion'] !== 'Sin calle de referencia registrada') {

                        $pv->update(['direccion' => $ref['direccion']]);

                    }

                });

        }



        $this->command?->info('Transportistas mayorista: Pedro, Lucía y Carlos (Password). Vehículos: SCZ-MAY-01 a SCZ-MAY-04.');

    }

}


