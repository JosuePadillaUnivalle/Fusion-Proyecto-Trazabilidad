<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\ChecklistCondicionLogistica;
use App\Models\ChecklistCondicionLogisticaDetalle;
use App\Models\DireccionLogistica;
use App\Models\DocumentoEntrega;
use App\Models\EnvioAsignacionMultiple;
use App\Models\HistorialEstadoEnvio;
use App\Models\IncidenteEnvio;
use App\Models\Insumo;
use App\Models\PerfilTransportista;
use App\Models\RutaMultiEntrega;
use App\Models\RutaParada;
use App\Models\TipoMovimientoAlmacen;
use App\Models\Usuario;
use App\Models\Vehiculo;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de demostración — Operación logística (asignaciones, rutas, documentos, incidentes, paneles por rol).
 * Ejecutar: php artisan db:seed --class=LogisticaOperativaModuloSeeder
 */
class LogisticaOperativaModuloSeeder extends Seeder
{
    private const MARK = '[MOD-LOG]';

    public function run(): void
    {
        if (EnvioAsignacionMultiple::where('externo_envio_id', 'like', 'ENV-MOD-%')->count() < 4) {
            $this->call(EnviosDistribucionModuloSeeder::class);
        }

        DB::transaction(function () {
            $ctx = $this->resolveContext();
            if (! $ctx) {
                return;
            }

            $this->seedDireccionesLogisticas($ctx);
            $this->seedPerfilesTransportista($ctx);
            $this->seedHistorialEstados($ctx);
            $this->seedChecklistsRecepcion($ctx);
            $this->seedIncidentesOperativos($ctx);
            $this->seedDocumentosOperativos($ctx);
            $this->seedRutaOperadorCircuito($ctx);
            $this->seedMovimientosPanelAlmacen($ctx);
            $this->touchEntregadosParaPanelAlmacen($ctx);
        });

        $abiertos = IncidenteEnvio::where('estado', 'abierto')->count();
        $resueltos = IncidenteEnvio::where('estado', 'resuelto')->where('descripcion', 'like', self::MARK.'%')->count();

        $this->command?->info(sprintf(
            '%s Listo: %d direcciones, %d historiales estado, %d checklists, %d inc. módulo (abiertos tot: %d, resueltos MOD: %d), %d docs MOD, rutas MOD: %d.',
            self::MARK,
            DireccionLogistica::where('nombre', 'like', self::MARK.'%')->count(),
            HistorialEstadoEnvio::whereHas('asignacion', fn ($q) => $q->where('externo_envio_id', 'like', 'ENV-MOD-%'))->count(),
            ChecklistCondicionLogistica::where('observaciones', 'like', self::MARK.'%')->count(),
            IncidenteEnvio::where('descripcion', 'like', self::MARK.'%')->count(),
            $abiertos,
            $resueltos,
            DocumentoEntrega::where('titulo', 'like', self::MARK.'%')->count(),
            RutaMultiEntrega::where('nombre', 'like', '%MOD%')->count()
        ));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveContext(): ?array
    {
        $admin = Usuario::where('email', 'admin@agronexus.com')->first();
        $operador = Usuario::where('email', 'operador@agronexus.com')->first();
        $planta = Usuario::where('email', 'planta@agronexus.com')->first();
        $almacenUser = Usuario::where('email', 'almacen@agronexus.com')->first();
        $transportista = Usuario::where('email', 'transportista@agronexus.com')->first();
        $miguel = Usuario::where('email', 'miguel.rojas@agronexus.com')->first();

        if (! $admin) {
            $this->command?->error(self::MARK.' Falta usuario admin.');

            return null;
        }

        return [
            'admin' => $admin,
            'operador' => $operador ?? $admin,
            'planta' => $planta ?? $admin,
            'almacen_user' => $almacenUser,
            'transportista' => $transportista,
            'miguel' => $miguel,
            'alm_central' => Almacen::where('nombre', 'Almacén Central Santa Cruz')->first(),
            'alm_norte' => Almacen::where('nombre', 'Almacén Norte')->first(),
            'alm_planta' => Almacen::where('nombre', 'Almacén Planta Procesadora')->first(),
        ];
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function seedDireccionesLogisticas(array $ctx): void
    {
        if (! Schema::hasTable('direccion_logistica')) {
            return;
        }

        $defs = [
            [
                'key' => 'central',
                'nombre' => self::MARK.' Hub Central Santa Cruz',
                'dir' => 'Parque Industrial El Trompillo, Km 12',
                'ciudad' => 'Santa Cruz de la Sierra',
                'lat' => -17.8015,
                'lng' => -63.2103,
                'alm' => $ctx['alm_central'],
            ],
            [
                'key' => 'norte',
                'nombre' => self::MARK.' Punto Norte',
                'dir' => 'Av. Beni esq. 2do Anillo',
                'ciudad' => 'Santa Cruz de la Sierra',
                'lat' => -17.7712,
                'lng' => -63.1658,
                'alm' => $ctx['alm_norte'],
            ],
            [
                'key' => 'planta',
                'nombre' => self::MARK.' Planta Procesadora',
                'dir' => 'Zona Industrial Warnes',
                'ciudad' => 'Warnes',
                'lat' => -17.5120,
                'lng' => -63.1680,
                'alm' => $ctx['alm_planta'],
            ],
        ];

        foreach ($defs as $d) {
            $dir = DireccionLogistica::updateOrCreate(
                ['nombre' => $d['nombre']],
                [
                    'direccion_completa' => $d['dir'],
                    'ciudad' => $d['ciudad'],
                    'departamento' => 'Santa Cruz',
                    'pais' => 'Bolivia',
                    'latitud' => $d['lat'],
                    'longitud' => $d['lng'],
                    'referencia' => 'Punto logístico demo operación',
                    'activo' => true,
                ]
            );

            if ($d['alm'] && Schema::hasColumn('almacen', 'direccionlogisticaid')) {
                $d['alm']->update(['direccionlogisticaid' => $dir->direccionlogisticaid]);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function seedPerfilesTransportista(array $ctx): void
    {
        if (! Schema::hasTable('perfil_transportista')) {
            return;
        }

        $estadoDisponibleId = Schema::hasTable('estado_transportista')
            ? (int) (DB::table('estado_transportista')->where('nombre', 'disponible')->value('estadotransportistaid')
                ?? DB::table('estado_transportista')->orderBy('estadotransportistaid')->value('estadotransportistaid'))
            : null;

        $estadoEnRutaId = Schema::hasTable('estado_transportista')
            ? (int) (DB::table('estado_transportista')->where('nombre', 'en_ruta')->value('estadotransportistaid') ?? $estadoDisponibleId)
            : $estadoDisponibleId;

        $vehiculos = [
            'transportista@agronexus.com' => Vehiculo::where('placa', 'SCZ-MOD-01')->value('vehiculoid'),
            'miguel.rojas@agronexus.com' => Vehiculo::where('placa', 'SCZ-MOD-02')->value('vehiculoid'),
            'luis.fernandez@agronexus.com' => Vehiculo::where('placa', 'SCZ-MOD-03')->value('vehiculoid'),
        ];

        $defs = [
            ['email' => 'transportista@agronexus.com', 'lic' => 'B-4521987', 'tipo' => 'C', 'estado' => $estadoDisponibleId, 'disp' => true],
            ['email' => 'miguel.rojas@agronexus.com', 'lic' => 'B-7788123', 'tipo' => 'B', 'estado' => $estadoEnRutaId, 'disp' => true],
            ['email' => 'luis.fernandez@agronexus.com', 'lic' => 'C-9912345', 'tipo' => 'C', 'estado' => $estadoDisponibleId, 'disp' => false],
        ];

        foreach ($defs as $d) {
            $user = Usuario::where('email', $d['email'])->first();
            if (! $user) {
                continue;
            }

            PerfilTransportista::updateOrCreate(
                ['usuarioid' => $user->usuarioid],
                [
                    'estadotransportistaid' => $d['estado'] ?: null,
                    'vehiculoid' => $vehiculos[$d['email']] ?? null,
                    'licencia' => $d['lic'],
                    'tipo_licencia' => $d['tipo'],
                    'fecha_vencimiento_licencia' => now()->addYear()->toDateString(),
                    'disponible' => $d['disp'],
                ]
            );
        }
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function seedHistorialEstados(array $ctx): void
    {
        if (! Schema::hasTable('historial_estado_envio') || ! Schema::hasTable('estado_envio_catalogo')) {
            return;
        }

        $catalogo = DB::table('estado_envio_catalogo')->pluck('estadoenviocatalogoid', 'nombre');

        $flujos = [
            'ENV-MOD-26-04' => ['pendiente', 'asignado', 'en_transito', 'entregado'],
            'ENV-MOD-26-03' => ['pendiente', 'asignado', 'en_transito'],
            'ENV-MOD-26-02' => ['pendiente', 'asignado'],
        ];

        foreach ($flujos as $codigo => $estados) {
            $asig = EnvioAsignacionMultiple::where('externo_envio_id', $codigo)->first();
            if (! $asig) {
                continue;
            }

            foreach ($estados as $i => $nombreEstado) {
                $catId = $catalogo[$nombreEstado] ?? null;
                if (! $catId) {
                    continue;
                }

                HistorialEstadoEnvio::updateOrCreate(
                    [
                        'envioasignacionmultipleid' => $asig->envioasignacionmultipleid,
                        'estadoenviocatalogoid' => $catId,
                        'fecha' => now()->subDays(3 - $i)->setTime(8 + $i, 0),
                    ],
                    [
                        'externo_envio_id' => $codigo,
                    ]
                );
            }
        }
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function seedChecklistsRecepcion(array $ctx): void
    {
        if (! Schema::hasTable('checklist_condicion_logistica')) {
            return;
        }

        $checks = [
            ['codigo' => 'ENV-MOD-26-04', 'alm' => 'alm_central', 'estado' => 'aprobado'],
            ['codigo' => 'ENV-MOD-26-02', 'alm' => 'alm_central', 'estado' => 'observado'],
            ['codigo' => 'ENV-MOD-26-06', 'alm' => 'alm_norte', 'estado' => 'aprobado'],
        ];

        foreach ($checks as $c) {
            $asig = EnvioAsignacionMultiple::where('externo_envio_id', $c['codigo'])->first();
            $alm = $ctx[$c['alm']] ?? null;
            if (! $asig || ! $alm) {
                continue;
            }

            $chk = ChecklistCondicionLogistica::updateOrCreate(
                ['envioasignacionmultipleid' => $asig->envioasignacionmultipleid],
                [
                    'almacenid' => $alm->almacenid,
                    'revisado_por_usuarioid' => $ctx['almacen_user']?->usuarioid ?? $ctx['operador']->usuarioid,
                    'estado_general' => $c['estado'],
                    'productos_completos' => true,
                    'empaque_intacto' => $c['estado'] === 'aprobado',
                    'temperatura_adecuada' => true,
                    'sin_danos_visibles' => $c['estado'] === 'aprobado',
                    'documentacion_completa' => true,
                    'observaciones' => self::MARK.' Revisión de condiciones en almacén.',
                    'fecha_revision' => now()->subHours(6),
                    'created_at' => now()->subHours(6),
                ]
            );

            if (Schema::hasTable('checklist_condicion_logistica_detalle') && Schema::hasTable('condicion_transporte')) {
                $cond = DB::table('condicion_transporte')->orderBy('condiciontransporteid')->limit(3)->get();
                foreach ($cond as $i => $row) {
                    ChecklistCondicionLogisticaDetalle::updateOrCreate(
                        [
                            'checklistcondicionid' => $chk->checklistcondicionid,
                            'condiciontransporteid' => $row->condiciontransporteid,
                        ],
                        [
                            'valor' => $i < 2,
                            'comentario' => self::MARK,
                        ]
                    );
                }
            }
        }
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function seedIncidentesOperativos(array $ctx): void
    {
        if (! Schema::hasTable('incidente_envio')) {
            return;
        }

        $defs = [
            [
                'desc' => self::MARK.' Faltante de 2 bultos en recepción (ENV-MOD-26-02).',
                'ext' => 'ENV-MOD-26-02',
                'tipo' => 'Faltante',
                'estado' => 'pendiente',
                'reporta' => 'operador',
                'alm' => 'alm_central',
            ],
            [
                'desc' => self::MARK.' Daño menor en empaque — resuelto con nota de crédito (ENV-MOD-26-05).',
                'ext' => 'ENV-MOD-26-05',
                'tipo' => 'Daño producto',
                'estado' => 'resuelto',
                'reporta' => 'transportista',
                'alm' => 'alm_central',
                'nota' => 'Cliente aceptó entrega parcial. Reposición programada.',
            ],
            [
                'desc' => self::MARK.' Cliente no disponible en primera visita (ENV-MOD-26-06).',
                'ext' => 'ENV-MOD-26-06',
                'tipo' => 'Reprogramación',
                'estado' => 'abierto',
                'reporta' => 'miguel',
                'alm' => 'alm_norte',
            ],
        ];

        foreach ($defs as $d) {
            $reportador = match ($d['reporta']) {
                'operador' => $ctx['operador'],
                'miguel' => $ctx['miguel'] ?? $ctx['transportista'],
                default => $ctx['transportista'],
            };

            $payload = [
                'externo_envio_id' => $d['ext'],
                'pedidoid' => EnvioAsignacionMultiple::where('externo_envio_id', $d['ext'])->value('pedidoid'),
                'reportadopor_usuarioid' => $reportador?->usuarioid,
                'tipo' => $d['tipo'],
                'estado' => $d['estado'],
                'almacenid' => $ctx[$d['alm']]?->almacenid,
            ];

            if ($d['estado'] === 'resuelto') {
                $payload['resueltopor_usuarioid'] = $ctx['admin']->usuarioid;
                $payload['fecha_resolucion'] = now()->subDay();
                $payload['nota_resolucion'] = $d['nota'] ?? 'Resuelto en operación logística.';
            }

            IncidenteEnvio::updateOrCreate(
                ['descripcion' => $d['desc']],
                $payload
            );
        }
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function seedDocumentosOperativos(array $ctx): void
    {
        if (! Schema::hasTable('documento_entrega')) {
            return;
        }

        $docs = [
            ['titulo' => self::MARK.' Nota entrega almacén ENV-MOD-26-04', 'ext' => 'ENV-MOD-26-04', 'tipo' => 'nota_entrega', 'user' => 'almacen_user', 'alm' => 'alm_central'],
            ['titulo' => self::MARK.' Nota entrega planta ENV-MOD-26-03', 'ext' => 'ENV-MOD-26-03', 'tipo' => 'nota_entrega', 'user' => 'planta', 'alm' => 'alm_planta'],
            ['titulo' => self::MARK.' Guía transportista ENV-MOD-26-01', 'ext' => 'ENV-MOD-26-01', 'tipo' => 'guia_entrega', 'user' => 'transportista', 'alm' => 'alm_central'],
            ['titulo' => self::MARK.' Confirmación ENV-MOD-26-06', 'ext' => 'ENV-MOD-26-06', 'tipo' => 'confirmacion_entrega', 'user' => 'miguel', 'alm' => 'alm_norte'],
        ];

        foreach ($docs as $d) {
            $usuario = $ctx[$d['user']] ?? $ctx['admin'];

            DocumentoEntrega::updateOrCreate(
                ['titulo' => $d['titulo']],
                [
                    'externo_envio_id' => $d['ext'],
                    'usuarioid' => $usuario->usuarioid,
                    'tipo_documento' => $d['tipo'],
                    'archivo_path' => 'demo/mod-log/'.$d['ext'].'.pdf',
                    'metadata' => ['mod_log' => true],
                    'almacenid' => $ctx[$d['alm']]?->almacenid,
                ]
            );
        }
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function seedRutaOperadorCircuito(array $ctx): void
    {
        if (! Schema::hasTable('ruta_multi_entrega')) {
            return;
        }

        $carlos = $ctx['transportista'];
        if (! $carlos) {
            return;
        }

        $ruta = RutaMultiEntrega::updateOrCreate(
            ['nombre' => 'Ruta MOD Circuito Operador'],
            [
                'creadopor_usuarioid' => $ctx['operador']->usuarioid,
                'transportista_usuarioid' => $carlos->usuarioid,
                'estado' => 'planificada',
                'fecha_salida' => now()->addDays(3)->setTime(7, 30),
                'resumen' => ['mod_log' => true, 'vehiculo_placa' => 'SCZ-MOD-01'],
            ]
        );

        if (! Schema::hasTable('ruta_parada')) {
            return;
        }

        $paradas = [
            ['orden' => 1, 'destino' => 'Almacén Central Santa Cruz', 'ext' => null],
            ['orden' => 2, 'destino' => 'Mercado Norte Santa Cruz', 'ext' => 'ENV-MOD-26-01'],
            ['orden' => 3, 'destino' => 'Hotel Camino Real', 'ext' => 'ENV-MOD-26-06'],
        ];

        foreach ($paradas as $p) {
            $pedidoId = $p['ext']
                ? EnvioAsignacionMultiple::where('externo_envio_id', $p['ext'])->value('pedidoid')
                : null;

            RutaParada::updateOrCreate(
                ['rutamultientregaid' => $ruta->rutamultientregaid, 'orden' => $p['orden']],
                [
                    'pedidoid' => $pedidoId,
                    'externo_envio_id' => $p['ext'],
                    'destino' => $p['destino'],
                    'estado' => 'pendiente',
                    'eta' => now()->addDays(3)->setTime(9 + $p['orden'], 0),
                ]
            );
        }

        EnvioAsignacionMultiple::where('externo_envio_id', 'ENV-MOD-26-01')
            ->update([
                'rutamultientregaid' => $ruta->rutamultientregaid,
                'asignadopor_usuarioid' => $ctx['operador']->usuarioid,
            ]);
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function seedMovimientosPanelAlmacen(array $ctx): void
    {
        if (! Schema::hasTable('almacen_movimiento') || ! $ctx['alm_central'] || ! $ctx['almacen_user']) {
            return;
        }

        $fecha = now()->toDateString();
        $movs = [
            ['naturaleza' => 'ingreso', 'producto' => 'Tomate', 'cant' => 500, 'ref' => 'MOD-LOG-ING-TOM'],
            ['naturaleza' => 'ingreso', 'producto' => 'Papa', 'cant' => 800, 'ref' => 'MOD-LOG-ING-PAP'],
            ['naturaleza' => 'salida', 'producto' => 'Tomate', 'cant' => 200, 'ref' => 'MOD-LOG-SAL-TOM'],
        ];

        foreach ($movs as $m) {
            $insumo = Insumo::where('nombre', $m['producto'])
                ->where('almacenid', $ctx['alm_central']->almacenid)
                ->first();

            $tipo = TipoMovimientoAlmacen::query()
                ->where('naturaleza', $m['naturaleza'])
                ->where('activo', true)
                ->first();

            if (! $insumo || ! $tipo) {
                continue;
            }

            AlmacenMovimiento::updateOrCreate(
                ['referencia' => $m['ref']],
                [
                    'almacenid' => $ctx['alm_central']->almacenid,
                    'insumoid' => $insumo->insumoid,
                    'tipo_movimiento_almacenid' => $tipo->tipo_movimiento_almacenid,
                    'usuarioid' => $ctx['almacen_user']->usuarioid,
                    'fecha' => $fecha,
                    'cantidad' => $m['cant'],
                    'observaciones' => self::MARK.' Movimiento panel almacén',
                ]
            );
        }
    }

    /**
     * Marca entregas recientes para métricas del panel almacén (recibidos_hoy).
     *
     * @param  array<string, mixed>  $ctx
     */
    private function touchEntregadosParaPanelAlmacen(array $ctx): void
    {
        if (! $ctx['alm_central']) {
            return;
        }

        EnvioAsignacionMultiple::query()
            ->where('externo_envio_id', 'ENV-MOD-26-04')
            ->where('almacenid', $ctx['alm_central']->almacenid)
            ->update([
                'estado' => 'entregado',
                'updated_at' => now(),
            ]);
    }
}
