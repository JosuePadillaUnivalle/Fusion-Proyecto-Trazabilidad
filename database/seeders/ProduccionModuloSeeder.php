<?php

namespace Database\Seeders;

use App\Models\Clima;
use App\Models\DestinoProduccion;
use App\Models\Lote;
use App\Models\MaquinaPlanta;
use App\Models\ProcesoPlanta;
use App\Support\ProcesoPlantaCatalogo;
use App\Models\Produccion;
use App\Models\UnidadMedida;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de demostración: Producción (cosechas), Clima, Procesos y Máquinas de planta.
 * Ejecutar: php artisan db:seed --class=ProduccionModuloSeeder
 */
class ProduccionModuloSeeder extends Seeder
{
    private const MARK = '[MOD-PROD]';

    public function run(): void
    {
        $this->call(DemoCatalogosBaseSeeder::class);

        if (Lote::count() === 0) {
            $this->call(LotesActividadesModuloSeeder::class);
        }

        $kgId = UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['kilogramo'])->value('unidadmedidaid')
            ?? UnidadMedida::where('nombre', 'Kilogramo')->value('unidadmedidaid');

        $qqId = UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['quintal'])->value('unidadmedidaid')
            ?? UnidadMedida::where('nombre', 'Quintal')->value('unidadmedidaid');

        if (! $kgId) {
            $this->command?->error('Falta unidad Kilogramo.');

            return;
        }

        $procesos = $this->seedProcesosPlanta();
        $maquinas = $this->seedMaquinasPlanta();

        $produccionesDefs = [
            ['codigo' => 'PROD-NORTE-001', 'lote' => 'Lote Norte A1', 'cantidad' => 2450, 'unidad' => 'kg', 'destino' => 'almacenamiento', 'dias' => 14, 'obs' => 'Cosecha principal de tomate.'],
            ['codigo' => 'PROD-ESTE-001', 'lote' => 'Lote Este B2', 'cantidad' => 1980, 'unidad' => 'kg', 'destino' => 'almacenamiento', 'dias' => 12, 'obs' => 'Producción de papa para inventario.'],
            ['codigo' => 'PROD-SUR-001', 'lote' => 'Lote Sur C3', 'cantidad' => 1325, 'unidad' => 'kg', 'destino' => 'venta', 'dias' => 10, 'obs' => 'Lechuga para pedidos locales.'],
            ['codigo' => 'PROD-NORTE-002', 'lote' => 'Lote Norte A1', 'cantidad' => 2400, 'unidad' => 'kg', 'destino' => 'venta', 'dias' => 7, 'obs' => 'Segunda cosecha parcial de tomate.'],
            ['codigo' => 'PROD-ESTE-002', 'lote' => 'Lote Este B2', 'cantidad' => 1975, 'unidad' => 'kg', 'destino' => 'procesamiento', 'dias' => 5, 'obs' => 'Papa destinada a planta procesadora.'],
            ['codigo' => 'PROD-CENTRAL-001', 'lote' => 'Lote Central D4', 'cantidad' => 1200, 'unidad' => 'kg', 'destino' => 'almacenamiento', 'dias' => 3, 'obs' => 'Primera cosecha de cebolla.'],
            ['codigo' => 'PROD-OESTE-001', 'lote' => 'Lote Oeste E5', 'cantidad' => 50, 'unidad' => 'qq', 'destino' => 'almacenamiento', 'dias' => 1, 'obs' => 'Cosecha de maíz en quintales.'],
        ];

        $procesoMaquinaMap = [
            'Tomate' => ['proceso' => 'Preparación de Materias Primas', 'maquina' => 'Lavadora Industrial L-100'],
            'Papa' => ['proceso' => 'Control de Calidad', 'maquina' => 'Banda Clasificadora BC-20'],
            'Lechuga' => ['proceso' => 'Empaquetado', 'maquina' => 'Selladora de Empaque SE-10'],
            'Cebolla' => ['proceso' => 'Control de Calidad', 'maquina' => 'Balanza Digital BD-500'],
            'Maíz' => ['proceso' => 'Control de Calidad', 'maquina' => 'Banda Clasificadora BC-20'],
        ];

        DB::transaction(function () use ($produccionesDefs, $procesos, $maquinas, $procesoMaquinaMap, $kgId, $qqId) {
            if (Schema::hasTable('produccion')) {
                foreach ($produccionesDefs as $def) {
                    $lote = Lote::where('nombre', $def['lote'])->with('cultivo')->first();
                    if (! $lote) {
                        continue;
                    }

                    $destinoId = $this->resolverDestinoId($def['destino']);
                    $unidadId = ($def['unidad'] === 'qq' && $qqId) ? $qqId : $kgId;

                    $marcador = self::MARK.' '.$def['codigo'];
                    $payload = [
                        'loteid' => $lote->loteid,
                        'cantidad' => $def['cantidad'],
                        'unidadmedidaid' => $unidadId,
                        'cantidad_base' => $def['cantidad'],
                        'fechacosecha' => now()->subDays($def['dias'])->toDateString(),
                        'destinoproduccionid' => $destinoId,
                        'observaciones' => $marcador.' '.$def['obs'],
                    ];

                    $cultivoNombre = $lote->cultivo->nombre ?? null;
                    if ($cultivoNombre && isset($procesoMaquinaMap[$cultivoNombre])) {
                        $refs = $procesoMaquinaMap[$cultivoNombre];
                        if (isset($procesos[$refs['proceso']])) {
                            $payload['procesoplantaid'] = $procesos[$refs['proceso']]->procesoplantaid;
                        }
                        if (isset($maquinas[$refs['maquina']])) {
                            $payload['maquinaplantaid'] = $maquinas[$refs['maquina']]->maquinaplantaid;
                        }
                    }

                    Produccion::updateOrCreate(
                        ['observaciones' => $marcador],
                        $payload
                    );
                }
            }

            if (Schema::hasTable('clima')) {
                $this->seedClimaPorLotes();
            }
        });

        $modProd = Produccion::where('observaciones', 'like', self::MARK.'%')->count();
        $modClima = Clima::where('observaciones', 'like', self::MARK.'%')->count();

        $this->command?->info(sprintf(
            '%s Listo: %d producciones módulo (%d total), %d registros clima módulo, %d procesos, %d máquinas.',
            self::MARK,
            $modProd,
            Produccion::count(),
            $modClima,
            ProcesoPlanta::count(),
            MaquinaPlanta::count()
        ));
    }

    /**
     * @return array<string, ProcesoPlanta>
     */
    private function seedProcesosPlanta(): array
    {
        $out = [];
        if (! Schema::hasTable('proceso_planta')) {
            return $out;
        }

        foreach (ProcesoPlantaCatalogo::CANONICOS as $nombre) {
            $p = ProcesoPlanta::firstOrCreate(
                ['nombre' => $nombre],
                ['descripcion' => 'Proceso de planta · '.self::MARK, 'activo' => true]
            );
            $out[$nombre] = $p;
        }

        return $out;
    }

    /**
     * @return array<string, MaquinaPlanta>
     */
    private function seedMaquinasPlanta(): array
    {
        $out = [];
        if (! Schema::hasTable('maquina_planta')) {
            return $out;
        }

        $descripciones = MaquinaPlanta::descripcionesPorCodigo();

        foreach (
            [
                ['nombre' => 'Lavadora Industrial L-100', 'codigo' => 'L-100', 'activo' => true],
                ['nombre' => 'Banda Clasificadora BC-20', 'codigo' => 'BC-20', 'activo' => true],
                ['nombre' => 'Selladora de Empaque SE-10', 'codigo' => 'SE-10', 'activo' => false],
                ['nombre' => 'Balanza Digital BD-500', 'codigo' => 'BD-500', 'activo' => true],
            ] as $d
        ) {
            $m = MaquinaPlanta::updateOrCreate(
                ['codigo' => $d['codigo']],
                [
                    'nombre' => $d['nombre'],
                    'descripcion' => $descripciones[$d['codigo']] ?? 'Equipo de planta · '.self::MARK,
                    'activo' => $d['activo'],
                ]
            );
            $out[$d['nombre']] = $m;
        }

        return $out;
    }

    private function seedClimaPorLotes(): void
    {
        $lotes = Lote::orderBy('loteid')->get();
        if ($lotes->isEmpty()) {
            return;
        }

        foreach ($lotes as $lote) {
            for ($d = 0; $d < 5; $d++) {
                $fecha = now()->subDays($d * 3)->setTime(8, 0, 0);
                $marcador = self::MARK.' clima|'.$lote->loteid.'|'.$fecha->format('Y-m-d');

                Clima::updateOrCreate(
                    ['observaciones' => $marcador],
                    [
                        'loteid' => $lote->loteid,
                        'fecha' => $fecha,
                        'temperatura' => round(22 + ($lote->loteid % 5), 1),
                        'humedad' => 55 + ($d * 3),
                        'lluvia' => $d === 0 ? 2.5 : 0,
                        'viento' => 8.5 + $d,
                        'presion' => 1012 + $d,
                        'descripcion' => 'Parcialmente nublado',
                        'icono' => '02d',
                    ]
                );
            }
        }
    }

    private function resolverDestinoId(string $clave): ?int
    {
        $slug = mb_strtolower(trim($clave));

        $id = DestinoProduccion::whereRaw('LOWER(TRIM(nombre)) = ?', [$slug])->value('destinoproduccionid');

        return $id ? (int) $id : null;
    }
}
