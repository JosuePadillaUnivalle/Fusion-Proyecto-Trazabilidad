<?php

namespace Database\Seeders;

use App\Support\AlmacenDemoReferencia;
use App\Models\Almacen;
use App\Models\DetallePedido;
use App\Models\DocumentoEntrega;
use App\Models\EnvioAsignacionMultiple;
use App\Models\IncidenteEnvio;
use App\Models\Pedido;
use App\Models\RutaMultiEntrega;
use App\Models\RutaParada;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

/**
 * Demo logística: los “envíos” se representan con código único en numero_solicitud / externo_envio_id
 * (no existe tabla envío dedicada).
 */
class DemoEnviosAsignacionesRutasSeeder extends Seeder
{
    private const MARK_PED = '[DEMO-B6] Envío demo';

    private const EMAIL_PLANTA = 'planta@agrofusion.com';

    private const T_TRANSPORTISTA_CARLOS = 'transportista@agrofusion.com';

    private const T_TRANSPORTISTA_MIGUEL = 'miguel.rojas@agrofusion.com';

    private const T_TRANSPORTISTA_LUIS = 'luis.fernandez@agrofusion.com';

    public function run(): void
    {
        if (! Schema::hasTable('pedido')) {
            $this->command?->warn('Omitido BLOQUE 6: falta tabla pedido.');

            return;
        }

        $planta = Usuario::where('email', self::EMAIL_PLANTA)->first();
        $admin = Usuario::where('email', 'admin@agrofusion.com')->first();
        $asignadorId = $planta?->usuarioid ?? $admin?->usuarioid;

        $roleTransportista = Role::firstOrCreate(['name' => 'transportista', 'guard_name' => 'web']);

        $carlos = Usuario::where('email', self::T_TRANSPORTISTA_CARLOS)->first();
        $miguel = $this->ensureTransportista([
            'nombre' => 'Miguel',
            'apellido' => 'Rojas',
            'nombreusuario' => 'mrojas',
            'email' => self::T_TRANSPORTISTA_MIGUEL,
            'telefono' => '700000201',
        ], $roleTransportista);

        $luis = $this->ensureTransportista([
            'nombre' => 'Luis',
            'apellido' => 'Fernández',
            'nombreusuario' => 'lfernandez',
            'email' => self::T_TRANSPORTISTA_LUIS,
            'telefono' => '700000202',
        ], $roleTransportista);

        $almacenCentral = AlmacenDemoReferencia::central();
        $almacenNorte = AlmacenDemoReferencia::norte();
        $almacenPlanta = AlmacenDemoReferencia::planta();

        $enviosCatalogo = [
            [
                'codigo' => 'ENV-2026-0001',
                'destino' => 'Mercado Norte Santa Cruz',
                'origen_almacen' => 'Almacén Central Santa Cruz',
                'producto' => 'Tomate',
                'cantidad' => 800,
                'unidad' => 'kg',
                'fecha_prog' => '2026-04-28',
                'latitud' => -17.7692,
                'longitud' => -63.1440,
            ],
            [
                'codigo' => 'ENV-2026-0002',
                'destino' => 'Supermercado Central',
                'origen_almacen' => 'Almacén Central Santa Cruz',
                'producto' => 'Papa',
                'cantidad' => 1200,
                'unidad' => 'kg',
                'fecha_prog' => '2026-04-28',
                'latitud' => -17.7551,
                'longitud' => -63.1155,
            ],
            [
                'codigo' => 'ENV-2026-0003',
                'destino' => 'Restaurante Verde',
                'origen_almacen' => 'Almacén Planta Procesadora',
                'producto' => 'Lechuga',
                'cantidad' => 300,
                'unidad' => 'und',
                'fecha_prog' => '2026-04-29',
                'latitud' => -17.7720,
                'longitud' => -63.1500,
            ],
            [
                'codigo' => 'ENV-2026-0004',
                'destino' => 'Planta Procesadora Orgánica',
                'origen_almacen' => 'Almacén Norte',
                'producto' => 'Cebolla',
                'cantidad' => 600,
                'unidad' => 'kg',
                'fecha_prog' => '2026-04-27',
                'latitud' => -17.7833,
                'longitud' => -63.1821,
            ],
            [
                'codigo' => 'ENV-2026-0005',
                'destino' => 'Distribuidora Andina',
                'origen_almacen' => 'Almacén Central Santa Cruz',
                'producto' => 'Maíz',
                'cantidad' => 50,
                'unidad' => 'qq',
                'fecha_prog' => '2026-04-30',
                'latitud' => -17.7900,
                'longitud' => -63.2100,
            ],
        ];

        DB::transaction(function () use (
            $enviosCatalogo,
            $asignadorId,
            $carlos,
            $miguel,
            $luis,
            $almacenCentral,
            $almacenNorte,
            $almacenPlanta,
            $admin
        ) {
            $pedidoPorCodigo = [];

            foreach ($enviosCatalogo as $row) {
                $pedido = Pedido::updateOrCreate(
                    ['numero_solicitud' => $row['codigo']],
                    [
                        'nombre_planta' => $row['destino'],
                        'latitud' => $row['latitud'],
                        'longitud' => $row['longitud'],
                        'direccion_texto' => $row['destino'],
                        'estado' => 'pendiente',
                        'fechapedido' => $row['fecha_prog'].' 09:00:00',
                        'fechaEntregaDeseada' => $row['fecha_prog'],
                        'observaciones' => self::MARK_PED.' · Origen: '.$row['origen_almacen'],
                    ]
                );

                DetallePedido::updateOrCreate(
                    [
                        'pedidoid' => $pedido->pedidoid,
                        'cultivo_personalizado' => $row['producto'],
                    ],
                    [
                        'cantidad' => $row['cantidad'],
                        'observaciones' => 'Unidad: '.$row['unidad'].' · '.self::MARK_PED,
                    ]
                );

                $pedidoPorCodigo[$row['codigo']] = $pedido->pedidoid;
            }

            if (! Schema::hasTable('ruta_multi_entrega') || ! $carlos || ! $miguel || ! $luis || ! $asignadorId) {
                $this->command?->warn('BLOQUE 6: rutas/asignaciones omitidas (faltan usuarios transportistas o planta/admin).');
            } else {
            $r1 = RutaMultiEntrega::updateOrCreate(
                ['nombre' => 'Ruta Norte Comercial'],
                [
                    'creadopor_usuarioid' => $asignadorId,
                    'transportista_usuarioid' => $carlos->usuarioid,
                    'estado' => 'planificada',
                    'fecha_salida' => '2026-04-28 07:00:00',
                    'resumen' => ['demo_b6' => true, 'vehiculo_placa' => 'SCZ-1020'],
                ]
            );

            $r2 = RutaMultiEntrega::updateOrCreate(
                ['nombre' => 'Ruta Planta - Restaurantes'],
                [
                    'creadopor_usuarioid' => $asignadorId,
                    'transportista_usuarioid' => $miguel->usuarioid,
                    'estado' => 'en_ruta',
                    'fecha_salida' => '2026-04-29 06:30:00',
                    'resumen' => ['demo_b6' => true, 'vehiculo_placa' => 'SCZ-2040'],
                ]
            );

            $r3 = RutaMultiEntrega::updateOrCreate(
                ['nombre' => 'Ruta Oeste Distribución'],
                [
                    'creadopor_usuarioid' => $asignadorId,
                    'transportista_usuarioid' => $luis->usuarioid,
                    'estado' => 'planificada',
                    'fecha_salida' => '2026-04-30 08:00:00',
                    'resumen' => ['demo_b6' => true, 'vehiculo_placa' => 'SCZ-3090'],
                ]
            );

            if (Schema::hasTable('ruta_parada')) {
                $paradasR1 = [
                    ['orden' => 1, 'destino' => 'Almacén Central Santa Cruz', 'externo' => null],
                    ['orden' => 2, 'destino' => 'Mercado Norte Santa Cruz', 'externo' => 'ENV-2026-0001'],
                    ['orden' => 3, 'destino' => 'Supermercado Central', 'externo' => 'ENV-2026-0002'],
                ];
                foreach ($paradasR1 as $p) {
                    RutaParada::updateOrCreate(
                        ['rutamultientregaid' => $r1->rutamultientregaid, 'orden' => $p['orden']],
                        [
                            'pedidoid' => $p['externo'] ? ($pedidoPorCodigo[$p['externo']] ?? null) : null,
                            'externo_envio_id' => $p['externo'],
                            'destino' => $p['destino'],
                            'estado' => $p['orden'] === 1 ? 'pendiente' : ($p['orden'] === 2 ? 'en_ruta' : 'pendiente'),
                            'eta' => '2026-04-28 '.(9 + $p['orden']).':00:00',
                        ]
                    );
                }

                $paradasR2 = [
                    ['orden' => 1, 'destino' => 'Almacén Planta Procesadora', 'externo' => null],
                    ['orden' => 2, 'destino' => 'Restaurante Verde', 'externo' => 'ENV-2026-0003'],
                ];
                foreach ($paradasR2 as $p) {
                    RutaParada::updateOrCreate(
                        ['rutamultientregaid' => $r2->rutamultientregaid, 'orden' => $p['orden']],
                        [
                            'pedidoid' => $p['externo'] ? ($pedidoPorCodigo[$p['externo']] ?? null) : null,
                            'externo_envio_id' => $p['externo'],
                            'destino' => $p['destino'],
                            'estado' => $p['orden'] === 1 ? 'pendiente' : 'en_ruta',
                            'eta' => '2026-04-29 10:00:00',
                        ]
                    );
                }

                $paradasR3 = [
                    ['orden' => 1, 'destino' => 'Almacén Norte', 'externo' => null],
                    ['orden' => 2, 'destino' => 'Planta Procesadora Orgánica', 'externo' => 'ENV-2026-0004'],
                    ['orden' => 3, 'destino' => 'Distribuidora Andina', 'externo' => 'ENV-2026-0005'],
                ];
                foreach ($paradasR3 as $p) {
                    RutaParada::updateOrCreate(
                        ['rutamultientregaid' => $r3->rutamultientregaid, 'orden' => $p['orden']],
                        [
                            'pedidoid' => $p['externo'] ? ($pedidoPorCodigo[$p['externo']] ?? null) : null,
                            'externo_envio_id' => $p['externo'],
                            'destino' => $p['destino'],
                            'estado' => 'pendiente',
                            'eta' => '2026-04-30 11:00:00',
                        ]
                    );
                }
            }

            if (Schema::hasTable('envio_asignacion_multiple')) {

            $asignaciones = [
                ['codigo' => 'ENV-2026-0001', 'tid' => $carlos->usuarioid, 'rid' => $r1->rutamultientregaid, 'vehiculo' => 'Camión Volvo FH / SCZ-1020', 'estado' => 'pendiente', 'alm' => $almacenCentral?->almacenid],
                ['codigo' => 'ENV-2026-0002', 'tid' => $carlos->usuarioid, 'rid' => $r1->rutamultientregaid, 'vehiculo' => 'Camión Volvo FH / SCZ-1020', 'estado' => 'asignado', 'alm' => $almacenCentral?->almacenid],
                ['codigo' => 'ENV-2026-0003', 'tid' => $miguel->usuarioid, 'rid' => $r2->rutamultientregaid, 'vehiculo' => 'Camioneta Toyota Hilux / SCZ-2040', 'estado' => 'en_ruta', 'alm' => $almacenPlanta?->almacenid],
                ['codigo' => 'ENV-2026-0004', 'tid' => $luis->usuarioid, 'rid' => $r3->rutamultientregaid, 'vehiculo' => 'Camión Mercedes Atego / SCZ-3090', 'estado' => 'entregado', 'alm' => $almacenNorte?->almacenid],
                ['codigo' => 'ENV-2026-0005', 'tid' => $luis->usuarioid, 'rid' => $r3->rutamultientregaid, 'vehiculo' => 'Camión Mercedes Atego / SCZ-3090', 'estado' => 'pendiente', 'alm' => $almacenNorte?->almacenid],
            ];

            foreach ($asignaciones as $a) {
                EnvioAsignacionMultiple::updateOrCreate(
                    [
                        'externo_envio_id' => $a['codigo'],
                        'transportista_usuarioid' => $a['tid'],
                    ],
                    [
                        'pedidoid' => $pedidoPorCodigo[$a['codigo']] ?? null,
                        'asignadopor_usuarioid' => $asignadorId,
                        'rutamultientregaid' => $a['rid'],
                        'vehiculo_ref' => $a['vehiculo'],
                        'estado' => $a['estado'],
                        'fecha_asignacion' => now(),
                        'almacenid' => $a['alm'],
                    ]
                );
            }

            }

            }

            if (Schema::hasTable('documento_entrega') && $admin) {
                $docs = [
                    ['tipo' => 'guia_entrega', 'titulo' => 'Guía de entrega ENV-2026-0001', 'ext' => 'ENV-2026-0001', 'ped' => 'ENV-2026-0001'],
                    ['tipo' => 'nota_entrega', 'titulo' => 'Nota de entrega ENV-2026-0002', 'ext' => 'ENV-2026-0002', 'ped' => 'ENV-2026-0002'],
                    ['tipo' => 'confirmacion_entrega', 'titulo' => 'Confirmación de entrega ENV-2026-0004', 'ext' => 'ENV-2026-0004', 'ped' => 'ENV-2026-0004'],
                ];

                foreach ($docs as $d) {
                    DocumentoEntrega::updateOrCreate(
                        ['titulo' => $d['titulo']],
                        [
                            'externo_envio_id' => $d['ext'],
                            'pedidoid' => $pedidoPorCodigo[$d['ped']] ?? null,
                            'usuarioid' => $admin->usuarioid,
                            'tipo_documento' => $d['tipo'],
                            'archivo_path' => 'demo/seed/sin-archivo-'.$d['ext'].'.pdf',
                            'metadata' => ['demo_b6' => true, 'sin_archivo_real' => true],
                            'almacenid' => $almacenCentral?->almacenid,
                        ]
                    );
                }
            }

            if (Schema::hasTable('incidente_envio')) {
                IncidenteEnvio::firstOrCreate(
                    ['descripcion' => '[DEMO-B6] Retraso por tráfico en ruta hacia Restaurante Verde (ENV-2026-0003).'],
                    [
                        'externo_envio_id' => 'ENV-2026-0003',
                        'pedidoid' => $pedidoPorCodigo['ENV-2026-0003'] ?? null,
                        'reportadopor_usuarioid' => $miguel->usuarioid,
                        'tipo' => 'Retraso',
                        'estado' => 'abierto',
                        'almacenid' => $almacenPlanta?->almacenid,
                    ]
                );

                IncidenteEnvio::firstOrCreate(
                    ['descripcion' => '[DEMO-B6] Entrega reprogramada por disponibilidad del cliente (ENV-2026-0005).'],
                    [
                        'externo_envio_id' => 'ENV-2026-0005',
                        'pedidoid' => $pedidoPorCodigo['ENV-2026-0005'] ?? null,
                        'reportadopor_usuarioid' => $luis->usuarioid,
                        'tipo' => 'Reprogramación',
                        'estado' => 'pendiente',
                        'almacenid' => $almacenCentral?->almacenid,
                    ]
                );
            }
        });
    }

    private function ensureTransportista(array $data, Role $roleTransportista): ?Usuario
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

        $u->syncRoles([$roleTransportista->name]);

        return $u;
    }
}
