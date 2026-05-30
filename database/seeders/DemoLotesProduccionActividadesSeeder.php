<?php

namespace Database\Seeders;

use App\Models\ActorAbastecimiento;
use App\Models\Actividad;
use App\Models\Cultivo;
use App\Models\DestinoProduccion;
use App\Models\EstadoLoteTipo;
use App\Models\Lote;
use App\Models\Prioridad;
use App\Models\Produccion;
use App\Models\TipoActividad;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoLotesProduccionActividadesSeeder extends Seeder
{
    private const USUARIO_AGRICULTOR_EMAIL = 'agricultor@agrofusion.com';

    private const ACTOR_PRODUCTOR_NOMBRE = 'Productor Valle Verde';

    public function run(): void
    {
        if (! Schema::hasTable('lote')) {
            $this->command?->warn('Omitido: tabla lote no existe.');

            return;
        }

        $haId = UnidadMedida::whereRaw('LOWER(nombre) = ?', ['hectárea'])->value('unidadmedidaid')
            ?? UnidadMedida::where('nombre', 'Hectárea')->value('unidadmedidaid');

        $kgId = UnidadMedida::whereRaw('LOWER(nombre) = ?', ['kilogramo'])->value('unidadmedidaid')
            ?? UnidadMedida::where('nombre', 'Kilogramo')->value('unidadmedidaid');

        if (! $haId || ! $kgId) {
            $this->command?->warn('Omitido: faltan unidades Hectárea o Kilogramo en unidadmedida.');

            return;
        }

        $agricultor = Usuario::where('email', self::USUARIO_AGRICULTOR_EMAIL)->first();
        if (! $agricultor) {
            $this->command?->warn('Omitido: no existe usuario '.self::USUARIO_AGRICULTOR_EMAIL);

            return;
        }

        $actorId = ActorAbastecimiento::where('nombre', self::ACTOR_PRODUCTOR_NOMBRE)->value('actorid');

        $prioridadMediaId = Schema::hasTable('prioridad')
            ? (Prioridad::whereRaw('LOWER(nombre) = ?', ['media'])->value('prioridadid')
                ?? Prioridad::query()->orderBy('prioridadid')->value('prioridadid'))
            : null;

        if (! $prioridadMediaId && Schema::hasTable('prioridad')) {
            $this->command?->warn('Actividades omitidas: no hay filas en prioridad.');
        }

        $lotesDefs = [
            [
                'nombre' => 'Lote Norte A1',
                'codigo_trazabilidad' => 'TRAZ-LOTE-0001',
                'cultivo' => 'Tomate',
                'superficie' => 12.5,
                'ubicacion' => 'Zona Norte - Parcela A1',
                'estado_label' => 'en producción',
                'fechasiembra' => '2026-01-26',
                'latitud' => -17.7692,
                'longitud' => -63.1440,
            ],
            [
                'nombre' => 'Lote Este B2',
                'codigo_trazabilidad' => 'TRAZ-LOTE-0002',
                'cultivo' => 'Papa',
                'superficie' => 9.0,
                'ubicacion' => 'Zona Este - Parcela B2',
                'estado_label' => 'en producción',
                'fechasiembra' => '2026-02-15',
                'latitud' => -17.7551,
                'longitud' => -63.1155,
            ],
            [
                'nombre' => 'Lote Sur C3',
                'codigo_trazabilidad' => 'TRAZ-LOTE-0003',
                'cultivo' => 'Lechuga',
                'superficie' => 7.4,
                'ubicacion' => 'Zona Sur - Parcela C3',
                'estado_label' => 'cosechado',
                'fechasiembra' => '2026-03-12',
                'latitud' => -17.7692,
                'longitud' => -63.1440,
            ],
            [
                'nombre' => 'Lote Central D4',
                'codigo_trazabilidad' => 'TRAZ-LOTE-0004',
                'cultivo' => 'Cebolla',
                'superficie' => 6.8,
                'ubicacion' => 'Zona Central - Parcela D4',
                'estado_label' => 'sembrado',
                'fechasiembra' => '2026-04-05',
                'latitud' => -17.7833,
                'longitud' => -63.1821,
            ],
            [
                'nombre' => 'Lote Oeste E5',
                'codigo_trazabilidad' => 'TRAZ-LOTE-0005',
                'cultivo' => 'Maíz',
                'superficie' => 15.0,
                'ubicacion' => 'Zona Oeste - Parcela E5',
                'estado_label' => 'en descanso',
                'fechasiembra' => '2026-01-10',
                'latitud' => -17.7900,
                'longitud' => -63.2100,
            ],
        ];

        $actividadesPorLote = [
            'Lote Norte A1' => [
                ['tipo' => 'Siembra', 'pendiente' => false],
                ['tipo' => 'Riego', 'pendiente' => false],
                ['tipo' => 'Fertilización', 'pendiente' => false],
                ['tipo' => 'Control de plagas', 'pendiente' => true],
            ],
            'Lote Este B2' => [
                ['tipo' => 'Siembra', 'pendiente' => false],
                ['tipo' => 'Riego', 'pendiente' => false],
                ['tipo' => 'Fertilización', 'pendiente' => false],
            ],
            'Lote Sur C3' => [
                ['tipo' => 'Siembra', 'pendiente' => false],
                ['tipo' => 'Cosecha', 'pendiente' => false],
            ],
            'Lote Central D4' => [
                ['tipo' => 'Siembra', 'pendiente' => false],
                ['tipo' => 'Riego', 'pendiente' => false],
            ],
            'Lote Oeste E5' => [
                ['tipo' => 'Siembra', 'pendiente' => false],
            ],
        ];

        $produccionesDefs = [
            [
                'marcador' => '[DEMO-B4] PROD-001',
                'lote_nombre' => 'Lote Norte A1',
                'fechacosecha' => '2026-04-16',
                'cantidad' => 2450,
                'destino_key' => 'almacenamiento',
                'observaciones' => 'Cosecha principal de tomate para almacén central.',
            ],
            [
                'marcador' => '[DEMO-B4] PROD-002',
                'lote_nombre' => 'Lote Este B2',
                'fechacosecha' => '2026-04-19',
                'cantidad' => 1980,
                'destino_key' => 'almacenamiento',
                'observaciones' => 'Producción de papa enviada a inventario.',
            ],
            [
                'marcador' => '[DEMO-B4] PROD-003',
                'lote_nombre' => 'Lote Sur C3',
                'fechacosecha' => '2026-04-21',
                'cantidad' => 1325,
                'destino_key' => 'almacenamiento',
                'observaciones' => 'Cosecha de lechuga para pedidos locales.',
            ],
            [
                'marcador' => '[DEMO-B4] PROD-004',
                'lote_nombre' => 'Lote Norte A1',
                'fechacosecha' => '2026-04-24',
                'cantidad' => 2400,
                'destino_key' => 'venta',
                'observaciones' => 'Segunda cosecha parcial de tomate.',
            ],
            [
                'marcador' => '[DEMO-B4] PROD-005',
                'lote_nombre' => 'Lote Este B2',
                'fechacosecha' => '2026-04-25',
                'cantidad' => 1975,
                'destino_key' => 'procesamiento',
                'observaciones' => 'Producción destinada a planta procesadora.',
            ],
        ];

        DB::transaction(function () use (
            $lotesDefs,
            $actividadesPorLote,
            $produccionesDefs,
            $agricultor,
            $actorId,
            $haId,
            $kgId,
            $prioridadMediaId
        ) {
            foreach ($lotesDefs as $def) {
                $cultivoId = Cultivo::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(trim($def['cultivo']))])->value('cultivoid')
                    ?? Cultivo::where('nombre', $def['cultivo'])->value('cultivoid');
                if (! $cultivoId) {
                    continue;
                }

                $estadoId = $this->resolverEstadoLoteTipoId($def['estado_label']);
                if (! $estadoId) {
                    continue;
                }

                $payload = [
                    'usuarioid' => $agricultor->usuarioid,
                    'ubicacion' => $def['ubicacion'],
                    'superficie' => $def['superficie'],
                    'unidadsuperficieid' => $haId,
                    'cultivoid' => $cultivoId,
                    'codigo_trazabilidad' => $def['codigo_trazabilidad'],
                    'fechasiembra' => $def['fechasiembra'],
                    'estadolotetipoid' => $estadoId,
                    'latitud' => $def['latitud'],
                    'longitud' => $def['longitud'],
                    'fechacreacion' => now(),
                    'fechamodificacion' => now(),
                ];

                if ($actorId) {
                    $payload['actorid'] = $actorId;
                }

                Lote::updateOrCreate(
                    ['nombre' => $def['nombre']],
                    $payload
                );
            }

            if (Schema::hasTable('actividad') && $prioridadMediaId) {
                foreach ($actividadesPorLote as $nombreLote => $acts) {
                    $lote = Lote::where('nombre', $nombreLote)->first();
                    if (! $lote) {
                        continue;
                    }

                    $base = Carbon::parse('2026-04-01 09:00:00');
                    $idx = 0;
                    foreach ($acts as $act) {
                        $tipoAct = $this->resolverTipoActividad($act['tipo']);
                        if (! $tipoAct) {
                            continue;
                        }

                        $marcadorObs = '[DEMO-B4] '.$nombreLote.'|'.$act['tipo'];
                        $inicio = $base->copy()->addDays($idx)->setTime(9, 0, 0);
                        $fin = null;
                        if (empty($act['pendiente'])) {
                            $fin = $inicio->copy()->addHours(4);
                        }

                        Actividad::firstOrCreate(
                            ['observaciones' => $marcadorObs],
                            [
                                'loteid' => $lote->loteid,
                                'usuarioid' => $agricultor->usuarioid,
                                'descripcion' => 'Actividad agrícola demo BLOQUE 4: '.$act['tipo'].'.',
                                'fechainicio' => $inicio,
                                'fechafin' => $fin,
                                'tipoactividadid' => $tipoAct->tipoactividadid,
                                'prioridadid' => $prioridadMediaId,
                            ]
                        );
                        $idx++;
                    }
                }
            }

            foreach ($produccionesDefs as $pdef) {
                $lote = Lote::where('nombre', $pdef['lote_nombre'])->first();
                if (! $lote) {
                    continue;
                }

                $destinoId = $this->resolverDestinoProduccionId($pdef['destino_key']);
                $obsCompleta = $pdef['marcador'].' '.$pdef['observaciones'];

                Produccion::firstOrCreate(
                    ['observaciones' => $obsCompleta],
                    [
                        'loteid' => $lote->loteid,
                        'cantidad' => $pdef['cantidad'],
                        'unidadmedidaid' => $kgId,
                        'cantidad_base' => $pdef['cantidad'],
                        'fechacosecha' => $pdef['fechacosecha'],
                        'destinoproduccionid' => $destinoId,
                    ]
                );
            }
        });
    }

    private function resolverEstadoLoteTipoId(string $normalizado): ?int
    {
        $slug = mb_strtolower(trim($normalizado));

        $id = EstadoLoteTipo::whereRaw('LOWER(nombre) = ?', [$slug])->value('estadolotetipoid');

        return $id ? (int) $id : null;
    }

    private function resolverTipoActividad(string $nombreHumano): ?TipoActividad
    {
        $slug = mb_strtolower(trim($nombreHumano));

        return TipoActividad::whereRaw('LOWER(nombre) = ?', [$slug])->first();
    }

    private function resolverDestinoProduccionId(string $clave): ?int
    {
        $slug = mb_strtolower(trim($clave));

        $id = DestinoProduccion::whereRaw('LOWER(nombre) = ?', [$slug])->value('destinoproduccionid');

        return $id ? (int) $id : null;
    }
}
