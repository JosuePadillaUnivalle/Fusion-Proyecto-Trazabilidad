<?php

namespace Database\Seeders;

use App\Models\Actividad;
use App\Models\ActorAbastecimiento;
use App\Models\Cultivo;
use App\Models\EstadoLoteTipo;
use App\Models\HistorialEstadoLote;
use App\Models\Lote;
use App\Models\Prioridad;
use App\Models\TipoActividad;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de demostración para el módulo «Lotes y actividades» (listado, mapa, calendario).
 * Ejecutar: php artisan db:seed --class=LotesActividadesModuloSeeder
 */
class LotesActividadesModuloSeeder extends Seeder
{
    private const MARK = '[MOD-LOTES]';

    private const AGRICULTOR_EMAIL = 'agricultor@agrofusion.com';

    public function run(): void
    {
        if (! Schema::hasTable('lote')) {
            $this->command?->warn('Omitido: tabla lote no existe.');

            return;
        }

        $this->call(DemoCatalogosBaseSeeder::class);

        $haId = UnidadMedida::whereRaw('LOWER(nombre) = ?', ['hectárea'])->value('unidadmedidaid')
            ?? UnidadMedida::where('nombre', 'Hectárea')->value('unidadmedidaid');

        if (! $haId) {
            $this->command?->error('Faltan unidades de medida (Hectárea).');

            return;
        }

        $agricultor = Usuario::where('email', self::AGRICULTOR_EMAIL)->first();
        if (! $agricultor) {
            $this->command?->error('No existe usuario '.self::AGRICULTOR_EMAIL.'. Ejecute DatosPruebaSeeder primero.');

            return;
        }

        $prioridadMediaId = Prioridad::whereRaw('LOWER(nombre) = ?', ['media'])->value('prioridadid')
            ?? Prioridad::query()->orderBy('prioridadid')->value('prioridadid');

        if (! $prioridadMediaId) {
            $this->command?->error('No hay prioridades en catálogo.');

            return;
        }

        $actorId = ActorAbastecimiento::where('nombre', 'Cooperativa Verde Sur')->value('actorid')
            ?? ActorAbastecimiento::query()->value('actorid');

        $lotesDefs = [
            [
                'nombre' => 'Lote Norte A1',
                'codigo_trazabilidad' => 'TRAZ-LOTE-0001',
                'cultivo' => 'Tomate',
                'superficie' => 12.5,
                'ubicacion' => 'Zona Norte - Parcela A1',
                'estado' => 'en producción',
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
                'estado' => 'en producción',
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
                'estado' => 'cosechado',
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
                'estado' => 'sembrado',
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
                'estado' => 'en descanso',
                'fechasiembra' => '2026-01-10',
                'latitud' => -17.7900,
                'longitud' => -63.2100,
            ],
        ];

        $actividadesDefs = [
            ['lote' => 'Lote Norte A1', 'tipo' => 'Siembra', 'dias' => -45, 'horas' => 4, 'pendiente' => false],
            ['lote' => 'Lote Norte A1', 'tipo' => 'Riego', 'dias' => -20, 'horas' => 3, 'pendiente' => false],
            ['lote' => 'Lote Norte A1', 'tipo' => 'Fertilización', 'dias' => -10, 'horas' => 4, 'pendiente' => false],
            ['lote' => 'Lote Norte A1', 'tipo' => 'Control de plagas', 'dias' => 2, 'horas' => null, 'pendiente' => true],
            ['lote' => 'Lote Este B2', 'tipo' => 'Siembra', 'dias' => -40, 'horas' => 4, 'pendiente' => false],
            ['lote' => 'Lote Este B2', 'tipo' => 'Riego', 'dias' => -5, 'horas' => 3, 'pendiente' => false],
            ['lote' => 'Lote Este B2', 'tipo' => 'Fertilización', 'dias' => 5, 'horas' => null, 'pendiente' => true],
            ['lote' => 'Lote Sur C3', 'tipo' => 'Siembra', 'dias' => -35, 'horas' => 4, 'pendiente' => false],
            ['lote' => 'Lote Sur C3', 'tipo' => 'Cosecha', 'dias' => -8, 'horas' => 6, 'pendiente' => false],
            ['lote' => 'Lote Central D4', 'tipo' => 'Siembra', 'dias' => -15, 'horas' => 4, 'pendiente' => false],
            ['lote' => 'Lote Central D4', 'tipo' => 'Riego', 'dias' => 0, 'horas' => null, 'pendiente' => true],
            ['lote' => 'Lote Oeste E5', 'tipo' => 'Siembra', 'dias' => -60, 'horas' => 4, 'pendiente' => false],
            ['lote' => 'Lote Norte A1', 'tipo' => 'Riego', 'dias' => 7, 'horas' => null, 'pendiente' => true],
            ['lote' => 'Lote Este B2', 'tipo' => 'Control de plagas', 'dias' => 14, 'horas' => null, 'pendiente' => true],
        ];

        $historialDefs = [
            ['lote' => 'Lote Norte A1', 'estado' => 'Sembrado', 'fecha' => '2026-01-26 08:00:00'],
            ['lote' => 'Lote Norte A1', 'estado' => 'En producción', 'fecha' => '2026-03-01 09:00:00'],
            ['lote' => 'Lote Este B2', 'estado' => 'Sembrado', 'fecha' => '2026-02-15 08:00:00'],
            ['lote' => 'Lote Este B2', 'estado' => 'En producción', 'fecha' => '2026-03-10 09:00:00'],
            ['lote' => 'Lote Sur C3', 'estado' => 'Sembrado', 'fecha' => '2026-03-12 08:00:00'],
            ['lote' => 'Lote Sur C3', 'estado' => 'Cosechado', 'fecha' => '2026-04-21 07:45:00'],
            ['lote' => 'Lote Central D4', 'estado' => 'Sembrado', 'fecha' => '2026-04-05 08:00:00'],
            ['lote' => 'Lote Oeste E5', 'estado' => 'En descanso', 'fecha' => '2026-01-10 08:00:00'],
        ];

        DB::transaction(function () use (
            $lotesDefs,
            $actividadesDefs,
            $historialDefs,
            $agricultor,
            $actorId,
            $haId,
            $prioridadMediaId
        ) {
            foreach ($lotesDefs as $def) {
                $cultivoId = Cultivo::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(trim($def['cultivo']))])->value('cultivoid');
                $estadoId = $this->resolverEstadoLoteTipoId($def['estado']);
                if (! $cultivoId || ! $estadoId) {
                    $this->command?->warn(self::MARK." Omitido lote {$def['nombre']}: cultivo o estado no encontrado.");

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

                Lote::updateOrCreate(['nombre' => $def['nombre']], $payload);
            }

            if (Schema::hasTable('actividad')) {
                foreach ($actividadesDefs as $i => $act) {
                    $lote = Lote::where('nombre', $act['lote'])->first();
                    $tipo = $this->resolverTipoActividad($act['tipo']);
                    if (! $lote || ! $tipo) {
                        continue;
                    }

                    $marcador = self::MARK.' '.$act['lote'].'|'.$act['tipo'].'|'.$i;
                    $inicio = now()->startOfDay()->addDays($act['dias'])->setTime(9, 0, 0);
                    $fin = null;
                    if (empty($act['pendiente']) && $act['horas']) {
                        $fin = $inicio->copy()->addHours($act['horas']);
                    }

                    Actividad::updateOrCreate(
                        ['observaciones' => $marcador],
                        [
                            'loteid' => $lote->loteid,
                            'usuarioid' => $agricultor->usuarioid,
                            'descripcion' => 'Actividad programada: '.$act['tipo'].' en '.$act['lote'].'.',
                            'fechainicio' => $inicio,
                            'fechafin' => $fin,
                            'tipoactividadid' => $tipo->tipoactividadid,
                            'prioridadid' => $prioridadMediaId,
                        ]
                    );
                }
            }

            if (Schema::hasTable('historial_estados_lote')) {
                foreach ($historialDefs as $fila) {
                    $lote = Lote::where('nombre', $fila['lote'])->first();
                    $tipoId = $this->resolverEstadoLoteTipoId($fila['estado']);
                    if (! $lote || ! $tipoId) {
                        continue;
                    }

                    $obs = self::MARK.' historial · '.$fila['lote'].' · '.$fila['estado'];
                    HistorialEstadoLote::updateOrCreate(
                        ['observaciones' => $obs],
                        [
                            'loteid' => $lote->loteid,
                            'estadolotetipoid' => $tipoId,
                            'fecha_cambio' => $fila['fecha'],
                            'usuarioid' => $agricultor->usuarioid,
                        ]
                    );
                }
            }
        });

        $this->command?->info(sprintf(
            '%s Listo: %d lotes, %d actividades (%s), %d historial.',
            self::MARK,
            Lote::count(),
            Actividad::where('observaciones', 'like', self::MARK.'%')->count(),
            Actividad::where('observaciones', 'like', self::MARK.'%')
                ->whereMonth('fechainicio', now()->month)
                ->whereYear('fechainicio', now()->year)
                ->count().' este mes',
            Schema::hasTable('historial_estados_lote')
                ? DB::table('historial_estados_lote')->where('observaciones', 'like', self::MARK.'%')->count()
                : 0
        ));
    }

    private function resolverEstadoLoteTipoId(string $nombre): ?int
    {
        $slug = mb_strtolower(trim($nombre));

        $id = EstadoLoteTipo::whereRaw('LOWER(TRIM(nombre)) = ?', [$slug])->value('estadolotetipoid');

        return $id ? (int) $id : null;
    }

    private function resolverTipoActividad(string $nombreHumano): ?TipoActividad
    {
        $slug = mb_strtolower(trim($nombreHumano));

        return TipoActividad::whereRaw('LOWER(TRIM(nombre)) = ?', [$slug])->first();
    }
}
