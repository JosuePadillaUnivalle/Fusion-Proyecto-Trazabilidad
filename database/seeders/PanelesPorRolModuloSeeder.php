<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\AlmacenProducto;
use App\Models\DetallePedido;
use App\Models\DocumentoEntrega;
use App\Models\EnvioAsignacionMultiple;
use App\Models\IncidenteEnvio;
use App\Models\Insumo;
use App\Models\InventarioAlmacenEnvio;
use App\Models\Pedido;
use App\Models\ProductoDistribucion;
use App\Models\RutaMultiEntrega;
use App\Models\RutaParada;
use App\Models\TipoMovimientoAlmacen;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

/**
 * Datos de demostración — paneles por rol (Planta, Transportista, Almacén).
 * Ejecutar: php artisan db:seed --class=PanelesPorRolModuloSeeder
 */
class PanelesPorRolModuloSeeder extends Seeder
{
    private const MARK = '[MOD-PANEL]';

    public function run(): void
    {
        if (EnvioAsignacionMultiple::where('externo_envio_id', 'like', 'ENV-MOD-%')->count() < 3) {
            $this->call(LogisticaOperativaModuloSeeder::class);
        } elseif (IncidenteEnvio::where('descripcion', 'like', '[MOD-LOG]%')->count() < 1) {
            $this->call(LogisticaOperativaModuloSeeder::class);
        }

        if (Insumo::where('almacenid', Almacen::where('nombre', 'Almacén Central Santa Cruz')->value('almacenid'))->count() < 2) {
            $this->call(InventarioModuloSeeder::class);
        }

        DB::transaction(function () {
            $ctx = $this->resolveContext();
            if (! $ctx) {
                return;
            }

            $this->asegurarUsuariosRoles($ctx);
            $this->seedPanelTransportista($ctx);
            $this->seedPanelAlmacen($ctx);
            $this->seedPanelPlanta($ctx);
        });

        $this->imprimirResumen();
    }

    private function imprimirResumen(): void
    {
        $planta = Usuario::where('email', 'planta@agrofusion.com')->first();
        $transportista = Usuario::where('email', 'transportista@agrofusion.com')->first();
        $almacenUser = Usuario::where('email', 'almacen@agrofusion.com')->first();

        $lineas = [self::MARK.' Métricas estimadas por rol:'];

        if ($planta) {
            $lineas[] = sprintf(
                '  Planta → ped:%d asig:%d rutas_act:%d inc:%d docs:%d',
                Pedido::count(),
                EnvioAsignacionMultiple::count(),
                RutaMultiEntrega::whereIn('estado', ['planificada', 'en_ruta'])->count(),
                IncidenteEnvio::where('estado', 'abierto')->count(),
                DocumentoEntrega::count()
            );
        }

        if ($transportista) {
            $uid = $transportista->usuarioid;
            $asig = EnvioAsignacionMultiple::where('transportista_usuarioid', $uid);
            $lineas[] = sprintf(
                '  Transportista → asignados:%d por_recoger:%d en_ruta:%d entregados_hoy:%d docs:%d inc_abiertos:%d',
                (clone $asig)->count(),
                (clone $asig)->where('estado', 'asignado')->count(),
                (clone $asig)->where('estado', 'en_ruta')->count(),
                (clone $asig)->where('estado', 'entregado')->whereDate('updated_at', now())->count(),
                DocumentoEntrega::where('usuarioid', $uid)->count(),
                IncidenteEnvio::where('reportadopor_usuarioid', $uid)->where('estado', 'abierto')->count()
            );
        }

        if ($almacenUser && $almacenUser->almacenid) {
            $aid = (int) $almacenUser->almacenid;
            $asigAlm = EnvioAsignacionMultiple::where('almacenid', $aid);
            $mov = AlmacenMovimiento::where('almacenid', $aid);
            $lineas[] = sprintf(
                '  Almacén → recibidos:%d por_recibir:%d stock_ins:%.0f stock_dist:%.0f líneas_inv_env:%d ing_mes:%d sal_mes:%d',
                (clone $asigAlm)->where('estado', 'entregado')->count(),
                (clone $asigAlm)->whereIn('estado', ['asignado', 'en_ruta'])->count(),
                (float) Insumo::where('almacenid', $aid)->sum('stock'),
                (float) AlmacenProducto::where('almacenid', $aid)->sum('stock'),
                InventarioAlmacenEnvio::where('almacenid', $aid)->count(),
                (clone $mov)->whereMonth('fecha', now()->month)->whereHas('tipo', fn ($q) => $q->where('naturaleza', 'ingreso'))->count(),
                (clone $mov)->whereMonth('fecha', now()->month)->whereHas('tipo', fn ($q) => $q->where('naturaleza', 'salida'))->count()
            );
        }

        $this->command?->info(implode("\n", $lineas));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveContext(): ?array
    {
        $admin = Usuario::where('email', 'admin@agrofusion.com')->first();
        $planta = Usuario::where('email', 'planta@agrofusion.com')->first();
        $operador = Usuario::where('email', 'operador@agrofusion.com')->first();
        $carlos = Usuario::where('email', 'transportista@agrofusion.com')->first();
        $miguel = Usuario::where('email', 'miguel.rojas@agrofusion.com')->first();
        $almacenUser = Usuario::where('email', 'almacen@agrofusion.com')->first();

        if (! $admin || ! $planta || ! $carlos || ! $almacenUser) {
            $this->command?->error(self::MARK.' Faltan usuarios demo (admin, planta, transportista o almacen). Ejecute DemoUsuariosAlmacenesActoresSeeder.');

            return null;
        }

        return [
            'admin' => $admin,
            'planta' => $planta,
            'operador' => $operador ?? $planta,
            'carlos' => $carlos,
            'miguel' => $miguel,
            'almacen_user' => $almacenUser,
            'alm_central' => Almacen::where('nombre', 'Almacén Central Santa Cruz')->first(),
            'alm_planta' => Almacen::where('nombre', 'Almacén Planta Procesadora')->first(),
            'alm_norte' => Almacen::where('nombre', 'Almacén Norte')->first(),
        ];
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function asegurarUsuariosRoles(array $ctx): void
    {
        $map = [
            'planta@agrofusion.com' => 'planta',
            'transportista@agrofusion.com' => 'transportista',
            'miguel.rojas@agrofusion.com' => 'transportista',
            'almacen@agrofusion.com' => 'almacen',
        ];

        foreach ($map as $email => $roleName) {
            $user = Usuario::where('email', $email)->first();
            if (! $user) {
                continue;
            }
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $user->syncRoles([$role->name]);
        }

        if ($ctx['alm_central']) {
            $ctx['almacen_user']->update(['almacenid' => $ctx['alm_central']->almacenid]);
        }
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function seedPanelTransportista(array $ctx): void
    {
        $carlos = $ctx['carlos'];
        $miguel = $ctx['miguel'];
        $almCentral = $ctx['alm_central'];
        $almPlanta = $ctx['alm_planta'];

        $rutaCarlos = RutaMultiEntrega::updateOrCreate(
            ['nombre' => 'Ruta MOD Panel Carlos'],
            [
                'creadopor_usuarioid' => $ctx['planta']->usuarioid,
                'transportista_usuarioid' => $carlos->usuarioid,
                'estado' => 'en_ruta',
                'fecha_salida' => now()->subHours(2),
                'resumen' => ['mod_panel' => true],
            ]
        );

        $enviosCarlos = [
            ['codigo' => 'MOD-PANEL-ENV-01', 'estado' => 'asignado', 'destino' => 'Mercado Norte Santa Cruz', 'prod' => 'Tomate', 'cant' => 400, 'alm' => $almCentral],
            ['codigo' => 'MOD-PANEL-ENV-02', 'estado' => 'en_ruta', 'destino' => 'Supermercado Central', 'prod' => 'Papa', 'cant' => 600, 'alm' => $almCentral],
            ['codigo' => 'MOD-PANEL-ENV-03', 'estado' => 'entregado', 'destino' => 'Restaurante Verde', 'prod' => 'Lechuga', 'cant' => 250, 'alm' => $almPlanta, 'hoy' => true],
            ['codigo' => 'MOD-PANEL-ENV-04', 'estado' => 'pendiente', 'destino' => 'Hotel Camino Real', 'prod' => 'Tomate', 'cant' => 300, 'alm' => $almCentral],
        ];

        foreach ($enviosCarlos as $i => $e) {
            $this->upsertEnvioPanel($e, $carlos, $ctx['planta'], $rutaCarlos, $e['alm'], $i + 1);
        }

        if ($miguel) {
            $envioMiguel = [
                'codigo' => 'MOD-PANEL-ENV-MIGUEL',
                'estado' => 'en_ruta',
                'destino' => 'Distribuidora Andina',
                'prod' => 'Maíz',
                'cant' => 120,
                'alm' => $almCentral,
            ];
            $this->upsertEnvioPanel($envioMiguel, $miguel, $ctx['planta'], null, $almCentral, 1);
        }

        DocumentoEntrega::updateOrCreate(
            ['titulo' => self::MARK.' Guía Carlos MOD-PANEL-ENV-01'],
            [
                'externo_envio_id' => 'MOD-PANEL-ENV-01',
                'usuarioid' => $carlos->usuarioid,
                'tipo_documento' => 'guia_entrega',
                'archivo_path' => 'demo/mod-panel/guia-carlos.pdf',
                'metadata' => ['mod_panel' => true],
                'almacenid' => $almCentral?->almacenid,
            ]
        );

        DocumentoEntrega::updateOrCreate(
            ['titulo' => self::MARK.' Confirmación Carlos MOD-PANEL-ENV-03'],
            [
                'externo_envio_id' => 'MOD-PANEL-ENV-03',
                'usuarioid' => $carlos->usuarioid,
                'tipo_documento' => 'confirmacion_entrega',
                'archivo_path' => 'demo/mod-panel/conf-carlos.pdf',
                'metadata' => ['mod_panel' => true],
                'almacenid' => $almPlanta?->almacenid,
            ]
        );

        IncidenteEnvio::updateOrCreate(
            ['descripcion' => self::MARK.' Carlos: demora en acceso a zona Equipetrol (MOD-PANEL-ENV-02).'],
            [
                'externo_envio_id' => 'MOD-PANEL-ENV-02',
                'reportadopor_usuarioid' => $carlos->usuarioid,
                'tipo' => 'Retraso',
                'estado' => 'abierto',
                'almacenid' => $almCentral?->almacenid,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function seedPanelAlmacen(array $ctx): void
    {
        $alm = $ctx['alm_central'];
        $almacenUser = $ctx['almacen_user'];
        if (! $alm) {
            return;
        }

        $transportista = $ctx['carlos'];

        $enviosAlmacen = [
            ['codigo' => 'MOD-PANEL-ALM-01', 'estado' => 'entregado', 'destino' => 'Recepción Central', 'prod' => 'Tomate', 'cant' => 500, 'hoy' => true],
            ['codigo' => 'MOD-PANEL-ALM-02', 'estado' => 'entregado', 'destino' => 'Recepción Central', 'prod' => 'Papa', 'cant' => 800],
            ['codigo' => 'MOD-PANEL-ALM-03', 'estado' => 'asignado', 'destino' => 'Despacho pendiente', 'prod' => 'Cebolla', 'cant' => 350],
            ['codigo' => 'MOD-PANEL-ALM-04', 'estado' => 'en_ruta', 'destino' => 'En tránsito a cliente', 'prod' => 'Tomate', 'cant' => 200],
        ];

        foreach ($enviosAlmacen as $i => $e) {
            $this->upsertEnvioPanel($e, $transportista, $ctx['planta'], null, $alm, $i + 10);
        }

        DocumentoEntrega::updateOrCreate(
            ['titulo' => self::MARK.' Nota entrega almacén central'],
            [
                'externo_envio_id' => 'MOD-PANEL-ALM-01',
                'usuarioid' => $almacenUser->usuarioid,
                'tipo_documento' => 'nota_entrega',
                'archivo_path' => 'demo/mod-panel/nota-almacen.pdf',
                'metadata' => ['mod_panel' => true],
                'almacenid' => $alm->almacenid,
            ]
        );

        IncidenteEnvio::updateOrCreate(
            ['descripcion' => self::MARK.' Almacén: diferencia de peso en recepción MOD-PANEL-ALM-02.'],
            [
                'externo_envio_id' => 'MOD-PANEL-ALM-02',
                'reportadopor_usuarioid' => $almacenUser->usuarioid,
                'tipo' => 'Calidad',
                'estado' => 'abierto',
                'almacenid' => $alm->almacenid,
            ]
        );

        if (Schema::hasTable('inventario_almacen_envio')) {
            $prodTom = ProductoDistribucion::where('codigo', 'PROD-TOM-01')->first();
            $asig = EnvioAsignacionMultiple::where('externo_envio_id', 'MOD-PANEL-ALM-03')->first();
            if ($prodTom && $asig) {
                InventarioAlmacenEnvio::updateOrCreate(
                    [
                        'externo_envio_id' => 'MOD-PANEL-ALM-03',
                        'productodistribucionid' => $prodTom->productodistribucionid,
                        'almacenid' => $alm->almacenid,
                    ],
                    [
                        'envioasignacionmultipleid' => $asig->envioasignacionmultipleid,
                        'cantidad' => 350,
                        'peso_total' => 350,
                        'fecha_ingreso' => now()->subHours(4),
                        'estado' => 'reservado',
                    ]
                );
            }
        }

        $this->seedMovimientosMesAlmacen($alm, $almacenUser);
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function seedPanelPlanta(array $ctx): void
    {
        RutaMultiEntrega::updateOrCreate(
            ['nombre' => 'Ruta MOD Panel Planta Sur'],
            [
                'creadopor_usuarioid' => $ctx['planta']->usuarioid,
                'transportista_usuarioid' => $ctx['miguel']?->usuarioid ?? $ctx['carlos']->usuarioid,
                'estado' => 'planificada',
                'fecha_salida' => now()->addDay(),
                'resumen' => ['mod_panel' => true],
            ]
        );

        Pedido::updateOrCreate(
            ['numero_solicitud' => 'MOD-PANEL-PED-PLANTA'],
            [
                'nombre_planta' => 'Cliente Panel Planta Demo',
                'latitud' => -17.78,
                'longitud' => -63.18,
                'direccion_texto' => 'Zona industrial demo',
                'estado' => 'pendiente',
                'fechapedido' => now(),
                'observaciones' => self::MARK.' Pedido visible en panel planta.',
            ]
        );

        IncidenteEnvio::updateOrCreate(
            ['descripcion' => self::MARK.' Planta: coordinación de carga pendiente en central.'],
            [
                'externo_envio_id' => 'MOD-PANEL-ALM-03',
                'reportadopor_usuarioid' => $ctx['planta']->usuarioid,
                'tipo' => 'Operación',
                'estado' => 'abierto',
                'almacenid' => $ctx['alm_central']?->almacenid,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $envio
     */
    private function upsertEnvioPanel(
        array $envio,
        Usuario $transportista,
        Usuario $asignador,
        ?RutaMultiEntrega $ruta,
        ?Almacen $almacen,
        int $ordenParada
    ): void {
        $pedido = Pedido::updateOrCreate(
            ['numero_solicitud' => $envio['codigo']],
            [
                'nombre_planta' => $envio['destino'],
                'latitud' => -17.78,
                'longitud' => -63.18,
                'direccion_texto' => $envio['destino'],
                'estado' => 'pendiente',
                'fechapedido' => now()->subDays(2),
                'observaciones' => self::MARK,
            ]
        );

        DetallePedido::updateOrCreate(
            [
                'pedidoid' => $pedido->pedidoid,
                'cultivo_personalizado' => $envio['prod'],
            ],
            [
                'cantidad' => $envio['cant'],
                'observaciones' => self::MARK,
            ]
        );

        $asig = EnvioAsignacionMultiple::updateOrCreate(
            [
                'externo_envio_id' => $envio['codigo'],
                'transportista_usuarioid' => $transportista->usuarioid,
            ],
            [
                'pedidoid' => $pedido->pedidoid,
                'asignadopor_usuarioid' => $asignador->usuarioid,
                'rutamultientregaid' => $ruta?->rutamultientregaid,
                'vehiculo_ref' => 'SCZ-MOD-01',
                'estado' => $envio['estado'],
                'fecha_asignacion' => now()->subDay(),
                'almacenid' => $almacen?->almacenid,
            ]
        );

        if (! empty($envio['hoy']) && $envio['estado'] === 'entregado') {
            DB::table('envio_asignacion_multiple')
                ->where('envioasignacionmultipleid', $asig->envioasignacionmultipleid)
                ->update(['updated_at' => now()]);
        }

        if ($ruta && Schema::hasTable('ruta_parada')) {
            RutaParada::updateOrCreate(
                [
                    'rutamultientregaid' => $ruta->rutamultientregaid,
                    'orden' => $ordenParada,
                ],
                [
                    'pedidoid' => $pedido->pedidoid,
                    'externo_envio_id' => $envio['codigo'],
                    'destino' => $envio['destino'],
                    'estado' => $envio['estado'] === 'entregado' ? 'entregado' : 'pendiente',
                    'eta' => now()->addHours($ordenParada + 2),
                ]
            );
        }
    }

    private function seedMovimientosMesAlmacen(Almacen $almacen, Usuario $usuario): void
    {
        if (! Schema::hasTable('almacen_movimiento')) {
            return;
        }

        $fecha = now()->toDateString();
        $movs = [
            ['n' => 'ingreso', 'ref' => 'MOD-PANEL-ING-01', 'prod' => 'Tomate', 'cant' => 600],
            ['n' => 'ingreso', 'ref' => 'MOD-PANEL-ING-02', 'prod' => 'Papa', 'cant' => 900],
            ['n' => 'salida', 'ref' => 'MOD-PANEL-SAL-01', 'prod' => 'Tomate', 'cant' => 250],
            ['n' => 'salida', 'ref' => 'MOD-PANEL-SAL-02', 'prod' => 'Papa', 'cant' => 400],
        ];

        foreach ($movs as $m) {
            $insumo = Insumo::where('nombre', $m['prod'])->where('almacenid', $almacen->almacenid)->first();
            $tipo = TipoMovimientoAlmacen::where('naturaleza', $m['n'])->where('activo', true)->first();
            if (! $insumo || ! $tipo) {
                continue;
            }

            AlmacenMovimiento::updateOrCreate(
                ['referencia' => $m['ref']],
                [
                    'almacenid' => $almacen->almacenid,
                    'insumoid' => $insumo->insumoid,
                    'tipo_movimiento_almacenid' => $tipo->tipo_movimiento_almacenid,
                    'usuarioid' => $usuario->usuarioid,
                    'fecha' => $fecha,
                    'cantidad' => $m['cant'],
                    'observaciones' => self::MARK,
                ]
            );
        }
    }
}
