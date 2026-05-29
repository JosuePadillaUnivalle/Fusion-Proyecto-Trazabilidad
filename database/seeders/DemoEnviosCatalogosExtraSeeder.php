<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\DetallePedido;
use App\Models\DocumentoEntrega;
use App\Models\EnvioAsignacionMultiple;
use App\Models\EstadoLoteInsumo;
use App\Models\EstadoLoteTipo;
use App\Models\HistorialEstadoLote;
use App\Models\IncidenteEnvio;
use App\Models\Lote;
use App\Models\Pedido;
use App\Models\RutaMultiEntrega;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

/**
 * Demo adicional: envíos locales (pedido + asignaciones + rutas) y catálogos ligados a lotes/insumos.
 * Alimenta las tablas que lee App\Support\LocalOrgTrackFallback cuando OrgTrack no devuelve datos.
 */
class DemoEnviosCatalogosExtraSeeder extends Seeder
{
    private const MARK = '[DEMO-XTRA2]';

    public function run(): void
    {
        if (! Schema::hasTable('pedido')) {
            $this->command?->warn(self::MARK.' Omitido: no existe tabla pedido.');

            return;
        }

        $roleTransportista = Role::firstOrCreate(['name' => 'transportista', 'guard_name' => 'web']);

        $admin = Usuario::where('email', 'admin@agronexus.com')->first();
        $planta = Usuario::where('email', 'planta@agronexus.com')->first();
        $asignadorId = $planta?->usuarioid ?? $admin?->usuarioid;

        $carlos = $this->ensureTransportistaUsuario([
            'nombre' => 'Carlos',
            'apellido' => 'Mamani',
            'nombreusuario' => 'transportista',
            'email' => 'transportista@agronexus.com',
            'telefono' => '+59170001001',
            'ci' => '7894561',
            'licencia' => 'C',
            'estado_logistico' => 'Disponible',
        ], $roleTransportista);

        $miguel = $this->ensureTransportistaUsuario([
            'nombre' => 'Miguel',
            'apellido' => 'Rojas',
            'nombreusuario' => 'mrojas',
            'email' => 'miguel.rojas@agronexus.com',
            'telefono' => '+59170001002',
            'ci' => '6541239',
            'licencia' => 'B',
            'estado_logistico' => 'Disponible',
        ], $roleTransportista);

        $luis = $this->ensureTransportistaUsuario([
            'nombre' => 'Luis',
            'apellido' => 'Fernández',
            'nombreusuario' => 'lfernandez',
            'email' => 'luis.fernandez@agronexus.com',
            'telefono' => '+59170001003',
            'ci' => '9873215',
            'licencia' => 'C',
            'estado_logistico' => 'En ruta',
        ], $roleTransportista);

        $almacenCentral = Almacen::where('nombre', 'Almacén Central Santa Cruz')->first();
        $almacenNorte = Almacen::where('nombre', 'Almacén Norte')->first();
        $almacenPlanta = Almacen::where('nombre', 'Almacén Planta Procesadora')->first();

        $this->seedAlmacenCentralCoordsNote($almacenCentral);

        $enviosCatalogo = [
            [
                'codigo' => 'ENV-2026-0001',
                'destino' => 'Mercado Norte Santa Cruz',
                'direccion_texto' => 'Zona Norte, Santa Cruz',
                'origen_almacen' => 'Almacén Central Santa Cruz',
                'producto' => 'Tomate',
                'cantidad' => 800,
                'unidad' => 'kg',
                'fecha_prog' => '2026-04-28',
                'latitud' => -17.7300,
                'longitud' => -63.1750,
            ],
            [
                'codigo' => 'ENV-2026-0002',
                'destino' => 'Supermercado Central',
                'direccion_texto' => 'Av. Cañoto, Santa Cruz',
                'origen_almacen' => 'Almacén Central Santa Cruz',
                'producto' => 'Papa',
                'cantidad' => 1200,
                'unidad' => 'kg',
                'fecha_prog' => '2026-04-28',
                'latitud' => -17.7833,
                'longitud' => -63.1821,
            ],
            [
                'codigo' => 'ENV-2026-0003',
                'destino' => 'Restaurante Verde',
                'direccion_texto' => 'Equipetrol, Santa Cruz',
                'origen_almacen' => 'Almacén Planta Procesadora',
                'producto' => 'Lechuga',
                'cantidad' => 300,
                'unidad' => 'und',
                'fecha_prog' => '2026-04-29',
                'latitud' => -17.7580,
                'longitud' => -63.1980,
            ],
            [
                'codigo' => 'ENV-2026-0004',
                'destino' => 'Planta Procesadora Orgánica',
                'direccion_texto' => 'Parque Industrial, Santa Cruz',
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
                'direccion_texto' => 'Parque Industrial, Santa Cruz',
                'origen_almacen' => 'Almacén Central Santa Cruz',
                'producto' => 'Maíz',
                'cantidad' => 50,
                'unidad' => 'qq',
                'fecha_prog' => '2026-04-30',
                'latitud' => -17.7850,
                'longitud' => -63.1500,
            ],
            [
                'codigo' => 'ENV-2026-XTRA2-ASG',
                'destino' => 'Cliente demo asignaciones SCZ',
                'direccion_texto' => 'Av. Paraguá 100, Santa Cruz',
                'origen_almacen' => 'Almacén Central Santa Cruz',
                'producto' => 'Tomate',
                'cantidad' => 100,
                'unidad' => 'kg',
                'fecha_prog' => '2026-05-02',
                'latitud' => -17.7700,
                'longitud' => -63.1700,
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
                        'direccion_texto' => $row['direccion_texto'],
                        'estado' => 'pendiente',
                        'fechapedido' => $row['fecha_prog'].' 09:00:00',
                        'fechaEntregaDeseada' => $row['fecha_prog'],
                        'observaciones' => self::MARK.' Origen: '.$row['origen_almacen'],
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

                $pedidoPorCodigo[$row['codigo']] = $pedido->pedidoid;
            }

            if (! $carlos || ! $miguel || ! $luis || ! $asignadorId) {
                $this->command?->warn(self::MARK.' Rutas/asignaciones omitidas: faltan usuarios transportistas o planta/admin.');

                return;
            }

            if (! Schema::hasTable('ruta_multi_entrega')) {
                return;
            }

            $r1 = RutaMultiEntrega::updateOrCreate(
                ['nombre' => 'Ruta Norte Comercial'],
                [
                    'creadopor_usuarioid' => $asignadorId,
                    'transportista_usuarioid' => $carlos->usuarioid,
                    'estado' => 'planificada',
                    'fecha_salida' => '2026-04-28 07:00:00',
                    'resumen' => [
                        'demo_xtra2' => true,
                        'vehiculo_nombre' => 'Camión Volvo FH',
                        'vehiculo_placa' => 'SCZ-1020',
                        'capacidad_kg' => 10000,
                        'vehiculo_estado' => 'Activo',
                    ],
                ]
            );

            $r2 = RutaMultiEntrega::updateOrCreate(
                ['nombre' => 'Ruta Planta - Restaurantes'],
                [
                    'creadopor_usuarioid' => $asignadorId,
                    'transportista_usuarioid' => $miguel->usuarioid,
                    'estado' => 'en_ruta',
                    'fecha_salida' => '2026-04-29 06:30:00',
                    'resumen' => [
                        'demo_xtra2' => true,
                        'vehiculo_nombre' => 'Camioneta Toyota Hilux',
                        'vehiculo_placa' => 'SCZ-2040',
                        'capacidad_kg' => 1200,
                        'vehiculo_estado' => 'Activo',
                    ],
                ]
            );

            $r3 = RutaMultiEntrega::updateOrCreate(
                ['nombre' => 'Ruta Oeste Distribución'],
                [
                    'creadopor_usuarioid' => $asignadorId,
                    'transportista_usuarioid' => $luis->usuarioid,
                    'estado' => 'planificada',
                    'fecha_salida' => '2026-04-30 08:00:00',
                    'resumen' => [
                        'demo_xtra2' => true,
                        'vehiculo_nombre' => 'Camión Mercedes Atego',
                        'vehiculo_placa' => 'SCZ-3090',
                        'capacidad_kg' => 7000,
                        'vehiculo_estado' => 'En mantenimiento',
                    ],
                ]
            );

            if (Schema::hasTable('envio_asignacion_multiple')) {
                $asignaciones = [
                    ['codigo' => 'ENV-2026-0001', 'tid' => $carlos->usuarioid, 'rid' => $r1->rutamultientregaid, 'vehiculo' => 'Volvo FH SCZ-1020 · 10000 kg · Activo', 'estado' => 'pendiente', 'alm' => $almacenCentral?->almacenid],
                    ['codigo' => 'ENV-2026-0002', 'tid' => $carlos->usuarioid, 'rid' => $r1->rutamultientregaid, 'vehiculo' => 'Volvo FH SCZ-1020 · 10000 kg · Activo', 'estado' => 'entregado', 'alm' => $almacenCentral?->almacenid],
                    ['codigo' => 'ENV-2026-0003', 'tid' => $miguel->usuarioid, 'rid' => $r2->rutamultientregaid, 'vehiculo' => 'Hilux SCZ-2040 · 1200 kg · Activo', 'estado' => 'en_ruta', 'alm' => $almacenPlanta?->almacenid],
                    ['codigo' => 'ENV-2026-0004', 'tid' => $luis->usuarioid, 'rid' => $r3->rutamultientregaid, 'vehiculo' => 'Atego SCZ-3090 · 7000 kg · Mant.', 'estado' => 'entregado', 'alm' => $almacenNorte?->almacenid],
                    ['codigo' => 'ENV-2026-0005', 'tid' => $luis->usuarioid, 'rid' => $r3->rutamultientregaid, 'vehiculo' => 'Atego SCZ-3090 · 7000 kg · Mant.', 'estado' => 'pendiente', 'alm' => $almacenCentral?->almacenid],
                    ['codigo' => 'ENV-2026-XTRA2-ASG', 'tid' => $carlos->usuarioid, 'rid' => $r1->rutamultientregaid, 'vehiculo' => 'Volvo FH SCZ-1020 · 10000 kg · Activo', 'estado' => 'asignado', 'alm' => $almacenCentral?->almacenid],
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

            if (Schema::hasTable('documento_entrega') && $admin) {
                DocumentoEntrega::updateOrCreate(
                    ['titulo' => self::MARK.' Acuse digital ENV-2026-0002'],
                    [
                        'externo_envio_id' => 'ENV-2026-0002',
                        'pedidoid' => $pedidoPorCodigo['ENV-2026-0002'] ?? null,
                        'usuarioid' => $admin->usuarioid,
                        'tipo_documento' => 'acuse_entrega',
                        'archivo_path' => 'demo/seed/sin-archivo-ENV-2026-0002.pdf',
                        'metadata' => ['demo_xtra2' => true, 'sin_archivo_real' => true],
                        'almacenid' => $almacenCentral?->almacenid,
                    ]
                );

                DocumentoEntrega::updateOrCreate(
                    ['titulo' => self::MARK.' POD fotográfico ENV-2026-0004'],
                    [
                        'externo_envio_id' => 'ENV-2026-0004',
                        'pedidoid' => $pedidoPorCodigo['ENV-2026-0004'] ?? null,
                        'usuarioid' => $admin->usuarioid,
                        'tipo_documento' => 'pod_foto',
                        'archivo_path' => 'demo/seed/sin-archivo-ENV-2026-0004.pdf',
                        'metadata' => ['demo_xtra2' => true, 'sin_archivo_real' => true],
                        'almacenid' => $almacenNorte?->almacenid,
                    ]
                );
            }

            if (Schema::hasTable('incidente_envio')) {
                IncidenteEnvio::updateOrCreate(
                    ['descripcion' => self::MARK.' Incidente resuelto — revisión de temperatura en entrega fría.'],
                    [
                        'externo_envio_id' => 'ENV-2026-0004',
                        'pedidoid' => $pedidoPorCodigo['ENV-2026-0004'] ?? null,
                        'reportadopor_usuarioid' => $luis->usuarioid,
                        'tipo' => 'Calidad',
                        'estado' => 'resuelto',
                        'resueltopor_usuarioid' => $admin?->usuarioid,
                        'fecha_resolucion' => '2026-04-27 11:30:00',
                        'nota_resolucion' => 'Cadena de frío verificada; sin observaciones.',
                        'almacenid' => $almacenNorte?->almacenid,
                    ]
                );

                IncidenteEnvio::updateOrCreate(
                    ['descripcion' => self::MARK.' Demora en descarga — coordinación pendiente con receptor.'],
                    [
                        'externo_envio_id' => 'ENV-2026-XTRA2-ASG',
                        'pedidoid' => $pedidoPorCodigo['ENV-2026-XTRA2-ASG'] ?? null,
                        'reportadopor_usuarioid' => $carlos->usuarioid,
                        'tipo' => 'Operativo',
                        'estado' => 'abierto',
                        'almacenid' => $almacenCentral?->almacenid,
                    ]
                );
            }
        });

        $this->seedEstadosLoteInsumoDemo();
        $this->seedHistorialLotesDemo();
    }

    private function seedAlmacenCentralCoordsNote(?Almacen $almacenCentral): void
    {
        if (! $almacenCentral || ! Schema::hasColumn('almacen', 'descripcion')) {
            return;
        }

        $note = self::MARK.' Coordenadas demo: lat -17.7539, lng -63.1812';
        $desc = (string) ($almacenCentral->descripcion ?? '');
        if (str_contains($desc, self::MARK.' Coordenadas demo')) {
            return;
        }

        Almacen::where('almacenid', $almacenCentral->almacenid)->update([
            'descripcion' => trim($desc."\n".$note),
        ]);
    }

    private function ensureTransportistaUsuario(array $data, Role $roleTransportista): ?Usuario
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

        $u->nombre = $data['nombre'];
        $u->apellido = $data['apellido'];
        $u->nombreusuario = $data['nombreusuario'];
        $u->telefono = $data['telefono'];
        $u->role = 'transportista';
        $u->activo = true;
        $u->fechamodificacion = now();

        if (Schema::hasColumn('usuario', 'informacionadicional')) {
            $this->mergeInformacionAdicional($u, [
                'ci' => $data['ci'],
                'licencia' => $data['licencia'],
                'estado_logistico' => $data['estado_logistico'],
            ]);
        }

        $u->save();

        $u->syncRoles([$roleTransportista->name]);

        return $u;
    }

    private function mergeInformacionAdicional(Usuario $u, array $demoBlock): void
    {
        $existing = [];
        if ($u->informacionadicional) {
            $decoded = json_decode($u->informacionadicional, true);
            if (is_array($decoded)) {
                $existing = $decoded;
            }
        }
        $existing['demo_xtra2'] = array_merge($existing['demo_xtra2'] ?? [], $demoBlock);
        $u->informacionadicional = json_encode($existing, JSON_UNESCAPED_UNICODE);
    }

    private function seedEstadosLoteInsumoDemo(): void
    {
        if (! Schema::hasTable('estadoloteinsumo')) {
            $this->command?->info(self::MARK.' Sin tabla estadoloteinsumo; estados de aplicación omitidos.');

            return;
        }

        foreach (['Pendiente', 'Aplicado', 'Cancelado', 'Observado'] as $nombre) {
            EstadoLoteInsumo::firstOrCreate(['nombre' => $nombre], ['nombre' => $nombre]);
        }
    }

    private function seedHistorialLotesDemo(): void
    {
        if (! Schema::hasTable('historial_estados_lote')) {
            $this->command?->info(self::MARK.' Sin tabla historial_estados_lote; historial omitido.');

            return;
        }

        $actor = Usuario::where('email', 'agricultor@agronexus.com')->first()
            ?? Usuario::where('email', 'admin@agronexus.com')->first();

        $filas = [
            ['lote' => 'Lote Norte A1', 'estado' => 'Sembrado', 'fecha' => '2026-01-26 08:00:00'],
            ['lote' => 'Lote Norte A1', 'estado' => 'En producción', 'fecha' => '2026-03-01 09:00:00'],
            ['lote' => 'Lote Norte A1', 'estado' => 'Cosechado', 'fecha' => '2026-04-16 07:30:00'],
            ['lote' => 'Lote Este B2', 'estado' => 'Sembrado', 'fecha' => '2026-02-15 08:00:00'],
            ['lote' => 'Lote Este B2', 'estado' => 'En producción', 'fecha' => '2026-03-10 09:00:00'],
            ['lote' => 'Lote Sur C3', 'estado' => 'Sembrado', 'fecha' => '2026-03-12 08:00:00'],
            ['lote' => 'Lote Sur C3', 'estado' => 'Cosechado', 'fecha' => '2026-04-21 07:45:00'],
        ];

        foreach ($filas as $fila) {
            $lote = Lote::where('nombre', $fila['lote'])->first();
            if (! $lote) {
                $this->command?->warn(self::MARK." Lote «{$fila['lote']}» no encontrado; entrada de historial omitida.");

                continue;
            }

            $estadoNombre = $fila['estado'];
            $tipoId = EstadoLoteTipo::where('nombre', $estadoNombre)->value('estadolotetipoid');
            if (! $tipoId) {
                $this->command?->warn(self::MARK." Falta EstadoLoteTipo «{$estadoNombre}»; omitiendo fila.");

                continue;
            }

            $obs = self::MARK.' historial · '.$fila['lote'].' · '.$estadoNombre.' · '.$fila['fecha'];

            HistorialEstadoLote::updateOrCreate(
                ['observaciones' => $obs],
                [
                    'loteid' => $lote->loteid,
                    'estadolotetipoid' => $tipoId,
                    'fecha_cambio' => $fila['fecha'],
                    'usuarioid' => $actor?->usuarioid,
                ]
            );
        }
    }
}
