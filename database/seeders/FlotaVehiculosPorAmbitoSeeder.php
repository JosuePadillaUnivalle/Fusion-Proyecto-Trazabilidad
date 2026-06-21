<?php



namespace Database\Seeders;



use App\Models\TipoTransporte;

use App\Models\TipoVehiculo;

use App\Models\Vehiculo;

use App\Support\EstadoVehiculoCatalogo;

use App\Support\TransportistaFlotaCatalogo;

use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;



/**

 * Flota demo: 3 tipos de vehículo (camioneta, camión PQ, camión GR)

 * por cada ámbito (agrícola, planta, mayorista) con tipo de transporte asignado.

 *

 * Ejecutar: php artisan db:seed --class=FlotaVehiculosPorAmbitoSeeder

 */

class FlotaVehiculosPorAmbitoSeeder extends Seeder

{

    /** @var array<string, string> */

    private const TRANSPORTE_POR_TIPO_VEHICULO = [

        'CAMIONETA' => 'CARGA_GENERAL',

        'CAMION_PQ' => 'REFRIGERADO',

        'CAMION_GR' => 'MULTITEMPERATURA',

    ];



    /** @var list<string> */

    private const PLACAS_OBSOLETAS = ['SCZ-MOD-04', 'SCZ-PLT-04', 'SCZ-MAY-04'];



    public function run(): void

    {

        if (! Schema::hasTable('vehiculo') || ! Schema::hasTable('tipo_vehiculo')) {

            $this->command?->warn('Tablas de vehículo no disponibles.');



            return;

        }



        $this->eliminarUnidadesObsoletas();

        $this->syncTransporteCatalogoTiposVehiculo();

        $this->seedFlota();

    }



    private function eliminarUnidadesObsoletas(): void

    {

        foreach (self::PLACAS_OBSOLETAS as $placa) {

            $vehiculo = Vehiculo::query()->where('placa', $placa)->first();

            if (! $vehiculo) {

                continue;

            }



            if (Schema::hasTable('vehiculo_tipo_transporte')) {

                $vehiculo->tiposTransporte()->detach();

            }



            $vehiculo->delete();

        }

    }



    private function syncTransporteCatalogoTiposVehiculo(): void

    {

        if (! Schema::hasTable('tipo_vehiculo_tipo_transporte')) {

            return;

        }



        $transporteIds = TipoTransporte::query()

            ->whereNotNull('codigo')

            ->pluck('tipotransporteid', 'codigo');



        foreach (self::TRANSPORTE_POR_TIPO_VEHICULO as $codigoVehiculo => $codigoTransporte) {

            $tipoVehiculoId = TipoVehiculo::query()->where('codigo', $codigoVehiculo)->value('tipovehiculoid');

            $tipoTransporteId = $transporteIds[$codigoTransporte] ?? null;



            if (! $tipoVehiculoId || ! $tipoTransporteId) {

                continue;

            }



            DB::table('tipo_vehiculo_tipo_transporte')->where('tipovehiculoid', $tipoVehiculoId)->delete();

            DB::table('tipo_vehiculo_tipo_transporte')->insert([

                'tipovehiculoid' => $tipoVehiculoId,

                'tipotransporteid' => $tipoTransporteId,

            ]);

        }

    }



    private function seedFlota(): void

    {

        $tipoIds = TipoVehiculo::query()

            ->whereIn('codigo', array_keys(self::TRANSPORTE_POR_TIPO_VEHICULO))

            ->pluck('tipovehiculoid', 'codigo');



        if ($tipoIds->count() < 3) {

            $this->command?->warn('Faltan tipos de vehículo en catálogo. Ejecute LogisticaCatalogosVerdurasSeeder primero.');



            return;

        }



        $estadoOperativo = EstadoVehiculoCatalogo::idOperativo();



        /** @var array<string, list<array{placa: string, marca: string, modelo: string, anio: int, tipo: string, color?: string}>> $flota */

        $flota = [

            TransportistaFlotaCatalogo::AGRICOLA => [

                ['placa' => 'SCZ-MOD-01', 'marca' => 'Volvo', 'modelo' => 'FH', 'anio' => 2022, 'tipo' => 'CAMION_GR'],

                ['placa' => 'SCZ-MOD-02', 'marca' => 'Toyota', 'modelo' => 'Hilux', 'anio' => 2021, 'tipo' => 'CAMIONETA'],

                ['placa' => 'SCZ-MOD-03', 'marca' => 'Mercedes', 'modelo' => 'Atego', 'anio' => 2020, 'tipo' => 'CAMION_PQ'],

            ],

            TransportistaFlotaCatalogo::PLANTA => [

                ['placa' => 'SCZ-PLT-01', 'marca' => 'Volvo', 'modelo' => 'FM', 'anio' => 2022, 'tipo' => 'CAMION_GR'],

                ['placa' => 'SCZ-PLT-02', 'marca' => 'Mercedes', 'modelo' => 'Accelo', 'anio' => 2021, 'tipo' => 'CAMION_PQ'],

                ['placa' => 'SCZ-PLT-03', 'marca' => 'Toyota', 'modelo' => 'Hilux', 'anio' => 2022, 'tipo' => 'CAMIONETA'],

            ],

            TransportistaFlotaCatalogo::MAYORISTA => [

                ['placa' => 'SCZ-MAY-01', 'marca' => 'Iveco', 'modelo' => 'Daily', 'anio' => 2022, 'tipo' => 'CAMION_GR'],

                ['placa' => 'SCZ-MAY-02', 'marca' => 'Mercedes', 'modelo' => 'Atego', 'anio' => 2021, 'tipo' => 'CAMION_PQ'],

                ['placa' => 'SCZ-MAY-03', 'marca' => 'Nissan', 'modelo' => 'Frontier', 'anio' => 2020, 'tipo' => 'CAMIONETA'],

            ],

        ];



        $creados = 0;



        foreach ($flota as $ambito => $unidades) {

            foreach ($unidades as $def) {

                $codigoTipo = $def['tipo'];

                $tipoVehiculoId = $tipoIds[$codigoTipo] ?? null;



                if (! $tipoVehiculoId) {

                    continue;

                }



                $vehiculo = Vehiculo::query()->updateOrCreate(

                    ['placa' => $def['placa']],

                    [

                        'marca' => $def['marca'],

                        'modelo' => $def['modelo'],

                        'anio' => $def['anio'],

                        'color' => $def['color'] ?? 'Blanco',

                        'activo' => true,

                        'tipovehiculoid' => $tipoVehiculoId,

                        'estadovehiculoid' => $estadoOperativo,

                        'ambito_flota' => $ambito,

                        'capacidad_kg_override' => null,

                        'capacidad_m3_override' => null,

                        'largo_m_override' => null,

                        'ancho_m_override' => null,

                        'alto_m_override' => null,

                    ]

                );



                $this->syncTransporteUnidad($vehiculo, $codigoTipo);

                $creados++;

            }

        }



        $this->command?->info("Flota actualizada: {$creados} vehículos (3 tipos × 3 ámbitos).");

        $this->command?->info('Transporte: camioneta→carga general, camión PQ→refrigerado, camión GR→multitemperatura.');

    }



    private function syncTransporteUnidad(Vehiculo $vehiculo, string $codigoTipoVehiculo): void

    {

        if (! Schema::hasTable('vehiculo_tipo_transporte')) {

            return;

        }



        $codigoTransporte = self::TRANSPORTE_POR_TIPO_VEHICULO[$codigoTipoVehiculo] ?? null;

        if (! $codigoTransporte) {

            return;

        }



        $tipoTransporteId = TipoTransporte::query()->where('codigo', $codigoTransporte)->value('tipotransporteid');

        if ($tipoTransporteId) {

            $vehiculo->tiposTransporte()->sync([$tipoTransporteId]);

        }

    }

}


