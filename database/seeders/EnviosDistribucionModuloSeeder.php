<?php

namespace Database\Seeders;

use App\Models\ActorAbastecimiento;
use App\Models\Almacen;
use App\Models\DetallePedido;
use App\Models\DistribucionDetalleIngreso;
use App\Models\DistribucionDetallePedidoAlmacen;
use App\Models\DistribucionDetalleSalida;
use App\Models\DistribucionIngreso;
use App\Models\DistribucionPedidoAlmacen;
use App\Models\DistribucionSalida;
use App\Models\DocumentoEntrega;
use App\Models\EnvioAsignacionMultiple;
use App\Models\IncidenteEnvio;
use App\Models\InventarioAlmacenEnvio;
use App\Models\Pedido;
use App\Models\ProductoDistribucion;
use App\Models\RutaMultiEntrega;
use App\Models\RutaParada;
use App\Models\SeguimientoEnvioGps;
use App\Models\TipoVehiculo;
use App\Models\Usuario;
use App\Models\Vehiculo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

/**
 * Datos de demostración — Envíos y distribución (asignaciones, rutas, inventario-envío, comprobantes).
 * Ejecutar: php artisan db:seed --class=EnviosDistribucionModuloSeeder
 */
class EnviosDistribucionModuloSeeder extends Seeder
{
    private const MARK = '[MOD-ENV]';

    private const MARK_DIST = '[MOD-ENV-DIST]';

    public function run(): void
    {
        if (! Schema::hasTable('pedido')) {
            $this->command?->warn('Omitido: falta tabla pedido.');

            return;
        }

        $this->call(InventarioModuloSeeder::class);

        if (Pedido::where('observaciones', 'like', '[MOD-PEDIDOS]%')->count() < 3) {
            $this->call(PedidosModuloSeeder::class);
        }

        $asignaciones = [];

        DB::transaction(function () use (&$asignaciones) {
            $ctx = $this->resolveContext();
            if (! $ctx) {
                return;
            }

            $this->seedVehiculos($ctx);
            $pedidoMap = $this->seedPedidosEnvio($ctx);
            $asignaciones = $this->seedRutasYAsignaciones($ctx, $pedidoMap);
            $this->seedDocumentosEIncidentes($ctx, $pedidoMap, $asignaciones);
            $this->seedInventarioAlmacenEnvio($ctx, $asignaciones);
            $this->seedDistribucionComprobantes($ctx, $pedidoMap, $asignaciones);
            $this->seedSeguimientoGps($asignaciones);
        });

        $modAsig = EnvioAsignacionMultiple::where('externo_envio_id', 'like', 'ENV-MOD-%')->count();
        $rutasAct = RutaMultiEntrega::whereIn('estado', ['planificada', 'en_ruta'])
            ->where('nombre', 'like', '%MOD%')
            ->count();

        $this->command?->info(sprintf(
            '%s Listo: %d asignaciones MOD, %d rutas activas MOD, %d líneas inventario-envío, ingresos dist: %d, salidas: %d, ped.almacén: %d (total asignaciones: %d).',
            self::MARK,
            $modAsig,
            $rutasAct,
            InventarioAlmacenEnvio::where('externo_envio_id', 'like', 'ENV-MOD-%')->count(),
            DistribucionIngreso::where('codigo_comprobante', 'like', 'ING-MOD-%')->count(),
            DistribucionSalida::where('codigo_comprobante', 'like', 'SAL-MOD-%')->count(),
            DistribucionPedidoAlmacen::where('codigo_comprobante', 'like', 'PED-ALM-MOD-%')->count(),
            EnvioAsignacionMultiple::count()
        ));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveContext(): ?array
    {
        $admin = Usuario::where('email', 'admin@agrofusion.com')->first();
        $operador = Usuario::where('email', 'operador@agrofusion.com')->first();
        $planta = Usuario::where('email', 'planta@agrofusion.com')->first();
        $asignadorId = $planta?->usuarioid ?? $operador?->usuarioid ?? $admin?->usuarioid;

        if (! $asignadorId || ! $admin) {
            $this->command?->error(self::MARK.' Falta usuario admin/planta/operador.');

            return null;
        }

        $roleTransportista = Role::firstOrCreate(['name' => 'transportista', 'guard_name' => 'web']);

        $carlos = $this->ensureTransportista([
            'nombre' => 'Carlos', 'apellido' => 'Mamani', 'nombreusuario' => 'transportista',
            'email' => 'transportista@agrofusion.com', 'telefono' => '700000101',
        ], $roleTransportista);

        $miguel = $this->ensureTransportista([
            'nombre' => 'Miguel', 'apellido' => 'Rojas', 'nombreusuario' => 'mrojas',
            'email' => 'miguel.rojas@agrofusion.com', 'telefono' => '700000201',
        ], $roleTransportista);

        $luis = $this->ensureTransportista([
            'nombre' => 'Luis', 'apellido' => 'Fernández', 'nombreusuario' => 'lfernandez',
            'email' => 'luis.fernandez@agrofusion.com', 'telefono' => '700000202',
        ], $roleTransportista);

        if (! $carlos || ! $miguel || ! $luis) {
            $this->command?->warn(self::MARK.' Transportistas incompletos; rutas omitidas.');

            return null;
        }

        return [
            'admin' => $admin,
            'asignador_id' => $asignadorId,
            'operador_id' => $operador?->usuarioid ?? $admin->usuarioid,
            'carlos' => $carlos,
            'miguel' => $miguel,
            'luis' => $luis,
            'alm_central' => Almacen::where('nombre', 'Almacén Central Santa Cruz')->first(),
            'alm_norte' => Almacen::where('nombre', 'Almacén Norte')->first(),
            'alm_planta' => Almacen::where('nombre', 'Almacén Planta Procesadora')->first(),
            'proveedor' => ActorAbastecimiento::where('nombre', 'Proveedor AgroInsumos SRL')->first()
                ?? ActorAbastecimiento::query()->first(),
        ];
    }

    /**
     * @param  array<string, mixed>  $ctx
     */
    private function seedVehiculos(array $ctx): void
    {
        if (! Schema::hasTable('vehiculo')) {
            return;
        }

        $tipoPorPlaca = [
            'SCZ-MOD-01' => 'CAMION_GR',
            'SCZ-MOD-02' => 'CAMIONETA',
            'SCZ-MOD-03' => 'CAMION_PQ',
        ];

        $tipoIds = TipoVehiculo::query()
            ->whereIn('codigo', array_values($tipoPorPlaca))
            ->pluck('tipovehiculoid', 'codigo');

        $defs = [
            ['placa' => 'SCZ-MOD-01', 'marca' => 'Volvo', 'modelo' => 'FH', 'anio' => 2022],
            ['placa' => 'SCZ-MOD-02', 'marca' => 'Toyota', 'modelo' => 'Hilux', 'anio' => 2021],
            ['placa' => 'SCZ-MOD-03', 'marca' => 'Mercedes', 'modelo' => 'Atego', 'anio' => 2020],
        ];

        foreach ($defs as $v) {
            $tipoCodigo = $tipoPorPlaca[$v['placa']] ?? null;

            Vehiculo::updateOrCreate(
                ['placa' => $v['placa']],
                [
                    'marca' => $v['marca'],
                    'modelo' => $v['modelo'],
                    'anio' => $v['anio'],
                    'color' => 'Blanco',
                    'activo' => true,
                    'tipovehiculoid' => $tipoCodigo ? ($tipoIds[$tipoCodigo] ?? null) : null,
                    'ambito_flota' => 'agricola',
                ]
            );
        }

        $ctx['vehiculo_camion'] = Vehiculo::where('placa', 'SCZ-MOD-01')->first();
        $ctx['vehiculo_hilux'] = Vehiculo::where('placa', 'SCZ-MOD-02')->first();
        $ctx['vehiculo_atego'] = Vehiculo::where('placa', 'SCZ-MOD-03')->first();
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @return array<string, int>
     */
    private function seedPedidosEnvio(array $ctx): array
    {
        $map = [];

        $envios = [
            [
                'codigo' => 'ENV-MOD-26-01',
                'pedido_solicitud' => 'MOD-PED-001',
                'destino' => 'Mercado Norte Santa Cruz',
                'producto' => 'Tomate', 'cantidad' => 800, 'unidad' => 'kg',
                'lat' => -17.7712, 'lng' => -63.1658,
                'origen' => 'Almacén Central Santa Cruz',
            ],
            [
                'codigo' => 'ENV-MOD-26-02',
                'pedido_solicitud' => 'MOD-PED-002',
                'destino' => 'Supermercado Central',
                'producto' => 'Papa', 'cantidad' => 1200, 'unidad' => 'kg',
                'lat' => -17.7891, 'lng' => -63.1924,
                'origen' => 'Almacén Central Santa Cruz',
            ],
            [
                'codigo' => 'ENV-MOD-26-03',
                'pedido_solicitud' => 'MOD-PED-003',
                'destino' => 'Restaurante Verde',
                'producto' => 'Lechuga', 'cantidad' => 300, 'unidad' => 'und',
                'lat' => -17.7568, 'lng' => -63.1742,
                'origen' => 'Almacén Planta Procesadora',
            ],
            [
                'codigo' => 'ENV-MOD-26-04',
                'pedido_solicitud' => 'MOD-PED-008',
                'destino' => 'Exportadora del Este',
                'producto' => 'Tomate', 'cantidad' => 2000, 'unidad' => 'kg',
                'lat' => -17.7488, 'lng' => -63.1421,
                'origen' => 'Almacén Central Santa Cruz',
            ],
            [
                'codigo' => 'ENV-MOD-26-05',
                'pedido_solicitud' => 'MOD-PED-005',
                'destino' => 'Distribuidora Andina',
                'producto' => 'Maíz', 'cantidad' => 500, 'unidad' => 'kg',
                'lat' => -17.8124, 'lng' => -63.1589,
                'origen' => 'Almacén Central Santa Cruz',
            ],
            [
                'codigo' => 'ENV-MOD-26-06',
                'pedido_solicitud' => 'MOD-PED-006',
                'destino' => 'Hotel Camino Real',
                'producto' => 'Tomate', 'cantidad' => 450, 'unidad' => 'kg',
                'lat' => -17.7789, 'lng' => -63.1811,
                'origen' => 'Almacén Norte',
            ],
        ];

        foreach ($envios as $row) {
            $pedido = Pedido::where('numero_solicitud', $row['pedido_solicitud'])->first();

            if (! $pedido) {
                $pedido = Pedido::updateOrCreate(
                    ['numero_solicitud' => $row['codigo']],
                    [
                        'nombre_planta' => $row['destino'],
                        'latitud' => $row['lat'],
                        'longitud' => $row['lng'],
                        'direccion_texto' => $row['destino'],
                        'estado' => 'pendiente',
                        'fechapedido' => now()->subDays(2),
                        'observaciones' => self::MARK.' Origen: '.$row['origen'],
                    ]
                );

                DetallePedido::updateOrCreate(
                    [
                        'pedidoid' => $pedido->pedidoid,
                        'cultivo_personalizado' => $row['producto'],
                    ],
                    [
                        'cantidad' => $row['cantidad'],
                        'observaciones' => 'Unidad: '.$row['unidad'].' · '.self::MARK,
                    ]
                );
            } else {
                $pedido->update([
                    'observaciones' => trim(($pedido->observaciones ?? '').' '.self::MARK.' Envío '.$row['codigo']),
                ]);
            }

            $map[$row['codigo']] = $pedido->pedidoid;
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @param  array<string, int>  $pedidoMap
     * @return array<string, EnvioAsignacionMultiple>
     */
    private function seedRutasYAsignaciones(array $ctx, array $pedidoMap): array
    {
        $out = [];

        if (! Schema::hasTable('envio_asignacion_multiple') || ! Schema::hasTable('ruta_multi_entrega')) {
            return $out;
        }

        $r1 = RutaMultiEntrega::updateOrCreate(
            ['nombre' => 'Ruta MOD Centro Comercial'],
            [
                'creadopor_usuarioid' => $ctx['asignador_id'],
                'transportista_usuarioid' => $ctx['carlos']->usuarioid,
                'estado' => 'planificada',
                'fecha_salida' => now()->addDay()->setTime(7, 0),
                'resumen' => [
                    'mod_env' => true,
                    'vehiculo_placa' => 'SCZ-MOD-01',
                    'vehiculo_nombre' => 'Camión Volvo FH',
                    'capacidad_kg' => 10000,
                    'vehiculo_estado' => 'Activo',
                ],
            ]
        );

        $r2 = RutaMultiEntrega::updateOrCreate(
            ['nombre' => 'Ruta MOD Planta y Horeca'],
            [
                'creadopor_usuarioid' => $ctx['asignador_id'],
                'transportista_usuarioid' => $ctx['miguel']->usuarioid,
                'estado' => 'en_ruta',
                'fecha_salida' => now()->setTime(6, 30),
                'resumen' => [
                    'mod_env' => true,
                    'vehiculo_placa' => 'SCZ-MOD-02',
                    'vehiculo_nombre' => 'Camioneta Toyota Hilux',
                    'capacidad_kg' => 1200,
                    'vehiculo_estado' => 'Activo',
                ],
            ]
        );

        $r3 = RutaMultiEntrega::updateOrCreate(
            ['nombre' => 'Ruta MOD Exportación Oeste'],
            [
                'creadopor_usuarioid' => $ctx['asignador_id'],
                'transportista_usuarioid' => $ctx['luis']->usuarioid,
                'estado' => 'planificada',
                'fecha_salida' => now()->addDays(2)->setTime(8, 0),
                'resumen' => [
                    'mod_env' => true,
                    'vehiculo_placa' => 'SCZ-MOD-03',
                    'vehiculo_nombre' => 'Camión Mercedes Atego',
                    'capacidad_kg' => 7000,
                    'vehiculo_estado' => 'Activo',
                ],
            ]
        );

        if (Schema::hasTable('ruta_parada')) {
            $this->seedParadas($r1, [
                ['orden' => 1, 'destino' => 'Almacén Central Santa Cruz', 'ext' => null],
                ['orden' => 2, 'destino' => 'Mercado Norte Santa Cruz', 'ext' => 'ENV-MOD-26-01'],
                ['orden' => 3, 'destino' => 'Supermercado Central', 'ext' => 'ENV-MOD-26-02'],
            ], $pedidoMap);

            $this->seedParadas($r2, [
                ['orden' => 1, 'destino' => 'Almacén Planta Procesadora', 'ext' => null],
                ['orden' => 2, 'destino' => 'Restaurante Verde', 'ext' => 'ENV-MOD-26-03'],
                ['orden' => 3, 'destino' => 'Hotel Camino Real', 'ext' => 'ENV-MOD-26-06'],
            ], $pedidoMap);

            $this->seedParadas($r3, [
                ['orden' => 1, 'destino' => 'Almacén Central Santa Cruz', 'ext' => null],
                ['orden' => 2, 'destino' => 'Exportadora del Este', 'ext' => 'ENV-MOD-26-04'],
                ['orden' => 3, 'destino' => 'Distribuidora Andina', 'ext' => 'ENV-MOD-26-05'],
            ], $pedidoMap);
        }

        $productoMap = [
            'Tomate' => 'PROD-TOM-01',
            'Papa' => 'PROD-PAP-01',
            'Lechuga' => 'PROD-LEC-01',
            'Maíz' => 'PROD-MAI-01',
        ];

        $asignacionesDefs = [
            ['codigo' => 'ENV-MOD-26-01', 'tid' => $ctx['carlos'], 'rid' => $r1, 'estado' => 'pendiente', 'alm' => $ctx['alm_central'], 'veh' => 'Volvo FH / SCZ-MOD-01', 'prod' => 'Tomate', 'cant' => 800],
            ['codigo' => 'ENV-MOD-26-02', 'tid' => $ctx['carlos'], 'rid' => $r1, 'estado' => 'asignado', 'alm' => $ctx['alm_central'], 'veh' => 'Volvo FH / SCZ-MOD-01', 'prod' => 'Papa', 'cant' => 1200],
            ['codigo' => 'ENV-MOD-26-03', 'tid' => $ctx['miguel'], 'rid' => $r2, 'estado' => 'en_ruta', 'alm' => $ctx['alm_planta'], 'veh' => 'Hilux / SCZ-MOD-02', 'prod' => 'Lechuga', 'cant' => 300],
            ['codigo' => 'ENV-MOD-26-04', 'tid' => $ctx['luis'], 'rid' => $r3, 'estado' => 'entregado', 'alm' => $ctx['alm_central'], 'veh' => 'Atego / SCZ-MOD-03', 'prod' => 'Tomate', 'cant' => 2000],
            ['codigo' => 'ENV-MOD-26-05', 'tid' => $ctx['luis'], 'rid' => $r3, 'estado' => 'pendiente', 'alm' => $ctx['alm_central'], 'veh' => 'Atego / SCZ-MOD-03', 'prod' => 'Maíz', 'cant' => 500],
            ['codigo' => 'ENV-MOD-26-06', 'tid' => $ctx['miguel'], 'rid' => $r2, 'estado' => 'asignado', 'alm' => $ctx['alm_norte'], 'veh' => 'Hilux / SCZ-MOD-02', 'prod' => 'Tomate', 'cant' => 450],
        ];

        foreach ($asignacionesDefs as $a) {
            $detallesProductos = null;
            if (Schema::hasColumn('envio_asignacion_multiple', 'detalles_productos')) {
                $detallesProductos = [[
                    'codigo_producto' => $productoMap[$a['prod']] ?? null,
                    'producto' => $a['prod'],
                    'cantidad' => $a['cant'],
                    'unidad' => $a['prod'] === 'Lechuga' ? 'und' : 'kg',
                ]];
            }

            $asig = EnvioAsignacionMultiple::updateOrCreate(
                ['externo_envio_id' => $a['codigo']],
                [
                    'pedidoid' => $pedidoMap[$a['codigo']] ?? null,
                    'transportista_usuarioid' => $a['tid']->usuarioid,
                    'asignadopor_usuarioid' => $ctx['asignador_id'],
                    'rutamultientregaid' => $a['rid']->rutamultientregaid,
                    'vehiculo_ref' => $a['veh'],
                    'estado' => $a['estado'],
                    'fecha_asignacion' => now()->subDays(1),
                    'almacenid' => $a['alm']?->almacenid,
                    'detalles_productos' => $detallesProductos,
                ]
            );

            $out[$a['codigo']] = $asig;
        }

        return $out;
    }

    /**
     * @param  array<string, int>  $pedidoMap
     * @param  array<string, EnvioAsignacionMultiple>  $asignaciones
     */
    private function seedParadas(RutaMultiEntrega $ruta, array $paradas, array $pedidoMap): void
    {
        foreach ($paradas as $p) {
            RutaParada::updateOrCreate(
                ['rutamultientregaid' => $ruta->rutamultientregaid, 'orden' => $p['orden']],
                [
                    'pedidoid' => $p['ext'] ? ($pedidoMap[$p['ext']] ?? null) : null,
                    'externo_envio_id' => $p['ext'],
                    'destino' => $p['destino'],
                    'estado' => $p['ext'] ? 'pendiente' : 'pendiente',
                    'eta' => now()->addHours(8 + $p['orden']),
                ]
            );
        }
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @param  array<string, int>  $pedidoMap
     * @param  array<string, EnvioAsignacionMultiple>  $asignaciones
     */
    private function seedDocumentosEIncidentes(array $ctx, array $pedidoMap, array $asignaciones): void
    {
        if (Schema::hasTable('documento_entrega')) {
            foreach (
                [
                    ['titulo' => 'Guía de transporte ENV-MOD-26-01', 'ext' => 'ENV-MOD-26-01', 'tipo' => 'guia_entrega'],
                    ['titulo' => 'Nota de entrega ENV-MOD-26-03', 'ext' => 'ENV-MOD-26-03', 'tipo' => 'nota_entrega'],
                    ['titulo' => 'Confirmación de entrega ENV-MOD-26-04', 'ext' => 'ENV-MOD-26-04', 'tipo' => 'confirmacion_entrega'],
                ] as $d
            ) {
                DocumentoEntrega::updateOrCreate(
                    ['titulo' => $d['titulo']],
                    [
                        'externo_envio_id' => $d['ext'],
                        'pedidoid' => $pedidoMap[$d['ext']] ?? null,
                        'usuarioid' => $ctx['admin']->usuarioid,
                        'tipo_documento' => $d['tipo'],
                        'archivo_path' => 'demo/mod-env/'.$d['ext'].'.pdf',
                        'metadata' => ['mod_env' => true],
                        'almacenid' => $ctx['alm_central']?->almacenid,
                    ]
                );
            }
        }

        if (Schema::hasTable('incidente_envio') && isset($asignaciones['ENV-MOD-26-03'])) {
            IncidenteEnvio::updateOrCreate(
                ['descripcion' => 'Retraso por congestión vial hacia Restaurante Verde.'],
                [
                    'externo_envio_id' => 'ENV-MOD-26-03',
                    'pedidoid' => $pedidoMap['ENV-MOD-26-03'] ?? null,
                    'reportadopor_usuarioid' => $ctx['miguel']->usuarioid,
                    'tipo' => 'Retraso',
                    'estado' => 'abierto',
                    'almacenid' => $ctx['alm_planta']?->almacenid,
                ]
            );
        }
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @param  array<string, EnvioAsignacionMultiple>  $asignaciones
     */
    private function seedInventarioAlmacenEnvio(array $ctx, array $asignaciones): void
    {
        if (! Schema::hasTable('inventario_almacen_envio')) {
            return;
        }

        $prodByCodigo = ProductoDistribucion::whereIn('codigo', [
            'PROD-TOM-01', 'PROD-PAP-01', 'PROD-LEC-01', 'PROD-MAI-01',
        ])->pluck('productodistribucionid', 'codigo');

        $lineas = [
            ['ext' => 'ENV-MOD-26-01', 'cod' => 'PROD-TOM-01', 'cant' => 800, 'peso' => 800, 'alm' => 'alm_central', 'estado' => 'reservado'],
            ['ext' => 'ENV-MOD-26-02', 'cod' => 'PROD-PAP-01', 'cant' => 1200, 'peso' => 1200, 'alm' => 'alm_central', 'estado' => 'reservado'],
            ['ext' => 'ENV-MOD-26-03', 'cod' => 'PROD-LEC-01', 'cant' => 300, 'peso' => 90, 'alm' => 'alm_planta', 'estado' => 'en_transito'],
            ['ext' => 'ENV-MOD-26-04', 'cod' => 'PROD-TOM-01', 'cant' => 2000, 'peso' => 2000, 'alm' => 'alm_central', 'estado' => 'entregado'],
            ['ext' => 'ENV-MOD-26-05', 'cod' => 'PROD-MAI-01', 'cant' => 500, 'peso' => 500, 'alm' => 'alm_central', 'estado' => 'disponible'],
            ['ext' => 'ENV-MOD-26-06', 'cod' => 'PROD-TOM-01', 'cant' => 450, 'peso' => 450, 'alm' => 'alm_norte', 'estado' => 'reservado'],
        ];

        foreach ($lineas as $l) {
            $asig = $asignaciones[$l['ext']] ?? null;
            $prodId = $prodByCodigo[$l['cod']] ?? null;
            $alm = $ctx[$l['alm']] ?? null;

            if (! $asig || ! $prodId || ! $alm) {
                continue;
            }

            InventarioAlmacenEnvio::updateOrCreate(
                [
                    'externo_envio_id' => $l['ext'],
                    'productodistribucionid' => $prodId,
                    'almacenid' => $alm->almacenid,
                ],
                [
                    'envioasignacionmultipleid' => $asig->envioasignacionmultipleid,
                    'cantidad' => $l['cant'],
                    'peso_total' => $l['peso'],
                    'fecha_ingreso' => now()->subHours(12),
                    'estado' => $l['estado'],
                ]
            );
        }
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @param  array<string, int>  $pedidoMap
     * @param  array<string, EnvioAsignacionMultiple>  $asignaciones
     */
    private function seedDistribucionComprobantes(array $ctx, array $pedidoMap, array $asignaciones): void
    {
        $tipoIngresoId = Schema::hasTable('distribucion_tipo_ingreso')
            ? (int) DB::table('distribucion_tipo_ingreso')->where('nombre', 'Compra')->value('distribuciontipoingresoid')
            : 0;

        $tipoSalidaId = Schema::hasTable('distribucion_tipo_salida')
            ? (int) DB::table('distribucion_tipo_salida')->where('nombre', 'Despacho')->value('distribuciontiposalidaid')
            : 0;

        $vehiculoId = Schema::hasTable('vehiculo')
            ? (int) Vehiculo::where('placa', 'SCZ-MOD-01')->value('vehiculoid')
            : 0;

        $prodTom = ProductoDistribucion::where('codigo', 'PROD-TOM-01')->first();
        $prodPap = ProductoDistribucion::where('codigo', 'PROD-PAP-01')->first();
        $prodLec = ProductoDistribucion::where('codigo', 'PROD-LEC-01')->first();
        $prodCeb = ProductoDistribucion::where('codigo', 'PROD-CEB-01')->first();

        if (
            Schema::hasTable('distribucion_ingreso')
            && $tipoIngresoId
            && $ctx['alm_central']
            && $ctx['proveedor']
            && $prodTom
        ) {
            $ing = DistribucionIngreso::updateOrCreate(
                ['codigo_comprobante' => 'ING-MOD-001'],
                [
                    'fecha' => now()->subDays(3)->toDateString(),
                    'estado' => 1,
                    'almacenid' => $ctx['alm_central']->almacenid,
                    'operador_usuarioid' => $ctx['operador_id'],
                    'transportista_usuarioid' => $ctx['carlos']->usuarioid,
                    'proveedor_actorid' => $ctx['proveedor']->actorid,
                    'pedidoid' => $pedidoMap['ENV-MOD-26-01'] ?? null,
                    'distribuciontipoingresoid' => $tipoIngresoId,
                    'vehiculoid' => $vehiculoId ?: null,
                    'administrador_usuarioid' => $ctx['admin']->usuarioid,
                ]
            );

            if (Schema::hasTable('distribucion_detalle_ingreso')) {
                DistribucionDetalleIngreso::updateOrCreate(
                    [
                        'distribucioningresoid' => $ing->distribucioningresoid,
                        'productodistribucionid' => $prodTom->productodistribucionid,
                    ],
                    ['cant_ingreso' => 1500, 'precio' => 4.50]
                );
            }
        }

        if (
            Schema::hasTable('distribucion_salida')
            && $tipoSalidaId
            && $vehiculoId
            && $ctx['alm_central']
            && $prodPap
        ) {
            $sal = DistribucionSalida::updateOrCreate(
                ['codigo_comprobante' => 'SAL-MOD-001'],
                [
                    'fecha' => now()->subDay()->toDateString(),
                    'estado' => 1,
                    'almacenid' => $ctx['alm_central']->almacenid,
                    'operador_usuarioid' => $ctx['operador_id'],
                    'transportista_usuarioid' => $ctx['carlos']->usuarioid,
                    'distribuciontiposalidaid' => $tipoSalidaId,
                    'vehiculoid' => $vehiculoId,
                    'administrador_usuarioid' => $ctx['admin']->usuarioid,
                ]
            );

            if (Schema::hasTable('distribucion_detalle_salida')) {
                DistribucionDetalleSalida::updateOrCreate(
                    [
                        'distribucionsalidaid' => $sal->distribucionsalidaid,
                        'productodistribucionid' => $prodPap->productodistribucionid,
                    ],
                    ['cant_salida' => 1200, 'precio' => 3.20]
                );
            }
        }

        if (Schema::hasTable('distribucion_pedido_almacen') && $ctx['alm_planta'] && $prodLec && $prodCeb) {
            $pedAlm = DistribucionPedidoAlmacen::updateOrCreate(
                ['codigo_comprobante' => 'PED-ALM-MOD-001'],
                [
                    'fecha' => now()->toDateString(),
                    'estado' => 1,
                    'almacenid' => $ctx['alm_planta']->almacenid,
                    'operador_usuarioid' => $ctx['operador_id'],
                    'transportista_usuarioid' => $ctx['miguel']->usuarioid,
                    'proveedor_actorid' => $ctx['proveedor']?->actorid,
                    'administrador_usuarioid' => $ctx['admin']->usuarioid,
                ]
            );

            if (Schema::hasTable('distribucion_detalle_pedido_almacen')) {
                DistribucionDetallePedidoAlmacen::updateOrCreate(
                    [
                        'distribucionpedidoid' => $pedAlm->distribucionpedidoid,
                        'productodistribucionid' => $prodLec->productodistribucionid,
                    ],
                    ['cantidad' => 300]
                );
                DistribucionDetallePedidoAlmacen::updateOrCreate(
                    [
                        'distribucionpedidoid' => $pedAlm->distribucionpedidoid,
                        'productodistribucionid' => $prodCeb->productodistribucionid,
                    ],
                    ['cantidad' => 200]
                );
            }
        }
    }

    /**
     * @param  array<string, EnvioAsignacionMultiple>  $asignaciones
     */
    private function seedSeguimientoGps(array $asignaciones): void
    {
        if (! Schema::hasTable('seguimiento_envio_gps')) {
            return;
        }

        $asig = $asignaciones['ENV-MOD-26-03'] ?? null;
        if (! $asig) {
            return;
        }

        $puntos = [
            ['lat' => -17.7833, 'lng' => -63.1821, 'h' => 3],
            ['lat' => -17.7700, 'lng' => -63.1700, 'h' => 2],
            ['lat' => -17.7568, 'lng' => -63.1742, 'h' => 1],
        ];

        foreach ($puntos as $i => $p) {
            SeguimientoEnvioGps::updateOrCreate(
                [
                    'envioasignacionmultipleid' => $asig->envioasignacionmultipleid,
                    'registrado_en' => now()->subHours($p['h']),
                ],
                [
                    'externo_envio_id' => 'ENV-MOD-26-03',
                    'latitud' => $p['lat'],
                    'longitud' => $p['lng'],
                    'velocidad' => 35 + $i * 5,
                ]
            );
        }
    }

    private function ensureTransportista(array $data, Role $role): ?Usuario
    {
        if (! Schema::hasTable('usuario')) {
            return null;
        }

        $u = Usuario::firstOrCreate(
            ['email' => $data['email']],
            [
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'nombreusuario' => $data['nombreusuario'],
                'telefono' => $data['telefono'],
                'passwordhash' => Hash::make('password'),
                'role' => 'transportista',
                'activo' => true,
                'fecharegistro' => now(),
                'fechamodificacion' => now(),
            ]
        );

        $u->syncRoles([$role->name]);

        return $u;
    }
}
