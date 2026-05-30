<?php

namespace Database\Seeders;

use App\Models\CertificacionLote;
use App\Models\DestinoProduccion;
use App\Models\DetallePedido;
use App\Models\Lote;
use App\Models\Pedido;
use App\Models\Produccion;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoPedidosVentasCertificacionesSeeder extends Seeder
{
    private const EMAIL_ADMIN = 'admin@agrofusion.com';

    private const MARK_PED_OBS = '[DEMO-B5] Pedido demo';

    /**
     * Estados solicitados → valores permitidos en columna pedido.estado
     * ('pendiente'|'confirmado'|'en produccion'|'rechazado').
     */
    private function mapEstadoPedido(string $etiqueta): string
    {
        $t = mb_strtolower(trim($etiqueta));

        return match ($t) {
            'pendiente' => 'pendiente',
            'en proceso', 'en_proceso' => 'en produccion',
            'entregado', 'entregada' => 'confirmado',
            default => 'pendiente',
        };
    }

    public function run(): void
    {
        $pedidosDefs = [
            [
                'numero_solicitud' => 'DEMO-B5-PED-001',
                'nombre_planta' => 'Mercado Norte Santa Cruz',
                'cultivo' => 'Tomate',
                'cantidad' => 800,
                'detalle_obs' => 'Unidad: kg',
                'estado' => 'pendiente',
                'fecha' => '2026-04-22',
            ],
            [
                'numero_solicitud' => 'DEMO-B5-PED-002',
                'nombre_planta' => 'Supermercado Central',
                'cultivo' => 'Papa',
                'cantidad' => 1200,
                'detalle_obs' => 'Unidad: kg',
                'estado' => 'en proceso',
                'fecha' => '2026-04-23',
            ],
            [
                'numero_solicitud' => 'DEMO-B5-PED-003',
                'nombre_planta' => 'Restaurante Verde',
                'cultivo' => 'Lechuga',
                'cantidad' => 300,
                'detalle_obs' => 'Unidad: und',
                'estado' => 'entregado',
                'fecha' => '2026-04-24',
            ],
            [
                'numero_solicitud' => 'DEMO-B5-PED-004',
                'nombre_planta' => 'Planta Procesadora Orgánica',
                'cultivo' => 'Cebolla',
                'cantidad' => 600,
                'detalle_obs' => 'Unidad: kg',
                'estado' => 'pendiente',
                'fecha' => '2026-04-25',
            ],
            [
                'numero_solicitud' => 'DEMO-B5-PED-005',
                'nombre_planta' => 'Distribuidora Andina',
                'cultivo' => 'Maíz',
                'cantidad' => 50,
                'detalle_obs' => 'Unidad: qq',
                'estado' => 'en proceso',
                'fecha' => '2026-04-26',
            ],
        ];

        $ventasDefs = [
            [
                'marcador' => '[DEMO-B5] VTA-001',
                'produccion_obs_prefix' => '[DEMO-B4] PROD-003',
                'cliente' => 'Restaurante Verde',
                'cantidad' => 300,
                'unidad' => 'und',
                'preciounitario' => 2.50,
                'fecha' => '2026-04-24',
            ],
            [
                'marcador' => '[DEMO-B5] VTA-002',
                'produccion_obs_prefix' => '[DEMO-B4] PROD-001',
                'cliente' => 'Mercado Norte Santa Cruz',
                'cantidad' => 500,
                'unidad' => 'kg',
                'preciounitario' => 4.00,
                'fecha' => '2026-04-25',
            ],
            [
                'marcador' => '[DEMO-B5] VTA-003',
                'produccion_obs_prefix' => '[DEMO-B4] PROD-002',
                'cliente' => 'Supermercado Central',
                'cantidad' => 700,
                'unidad' => 'kg',
                'preciounitario' => 3.20,
                'fecha' => '2026-04-26',
            ],
            [
                'marcador' => '[DEMO-B5] VTA-004',
                'produccion_obs_prefix' => null,
                'cliente' => 'Distribuidora Andina',
                'cantidad' => 20,
                'unidad' => 'qq',
                'preciounitario' => 180,
                'fecha' => '2026-04-27',
            ],
        ];

        $certDefs = [
            [
                'codigo_certificado' => 'CERT-LOTE-A1-2026',
                'lote_nombre' => 'Lote Norte A1',
                'observaciones' => 'Lote certificado para producción agrícola trazable.',
                'fecha' => '2026-04-20',
            ],
            [
                'codigo_certificado' => 'CERT-LOTE-B2-2026',
                'lote_nombre' => 'Lote Este B2',
                'observaciones' => 'Certificación emitida para cosecha de papa.',
                'fecha' => '2026-04-22',
            ],
            [
                'codigo_certificado' => 'CERT-LOTE-C3-2026',
                'lote_nombre' => 'Lote Sur C3',
                'observaciones' => 'Certificación para producción de lechuga destinada a pedidos locales.',
                'fecha' => '2026-04-24',
            ],
        ];

        DB::transaction(function () use ($pedidosDefs, $ventasDefs, $certDefs) {
            if (Schema::hasTable('pedido') && Schema::hasTable('detallepedido')) {
                foreach ($pedidosDefs as $def) {
                    $estado = $this->mapEstadoPedido($def['estado']);

                    $pedido = Pedido::updateOrCreate(
                        ['numero_solicitud' => $def['numero_solicitud']],
                        [
                            'nombre_planta' => $def['nombre_planta'],
                            'latitud' => -17.7833,
                            'longitud' => -63.1821,
                            'direccion_texto' => $def['nombre_planta'],
                            'estado' => $estado,
                            'fechapedido' => $def['fecha'].' 10:00:00',
                            'observaciones' => self::MARK_PED_OBS,
                        ]
                    );

                    DetallePedido::updateOrCreate(
                        [
                            'pedidoid' => $pedido->pedidoid,
                            'cultivo_personalizado' => $def['cultivo'],
                        ],
                        [
                            'cantidad' => $def['cantidad'],
                            'observaciones' => $def['detalle_obs'].' · '.self::MARK_PED_OBS,
                        ]
                    );
                }
            }

            if (Schema::hasTable('venta')) {
                $destinoAlmId = DestinoProduccion::whereRaw('LOWER(TRIM(nombre)) = ?', ['almacenamiento'])->value('destinoproduccionid');

                $produccionMaiz = $this->ensureProduccionMaizDemo($destinoAlmId);

                foreach ($ventasDefs as $vdef) {
                    if (Venta::where('observaciones', $vdef['marcador'])->exists()) {
                        continue;
                    }

                    $produccionId = null;
                    if ($vdef['produccion_obs_prefix']) {
                        $produccionId = Produccion::where('observaciones', 'like', $vdef['produccion_obs_prefix'].'%')->value('produccionid');
                    } else {
                        $produccionId = $produccionMaiz?->produccionid;
                    }

                    if (! $produccionId) {
                        continue;
                    }

                    $unidadMedidaId = $this->unidadMedidaIdPorClave($vdef['unidad']);
                    if (! $unidadMedidaId) {
                        continue;
                    }

                    $total = round((float) $vdef['cantidad'] * (float) $vdef['preciounitario'], 2);

                    $attrs = [
                        'produccionid' => (int) $produccionId,
                        'cliente' => $vdef['cliente'],
                        'cantidad' => (float) $vdef['cantidad'],
                        'unidadmedidaid' => (int) $unidadMedidaId,
                        'preciounitario' => (float) $vdef['preciounitario'],
                        'fechaventa' => $vdef['fecha'],
                        'observaciones' => $vdef['marcador'],
                    ];

                    if (Schema::hasColumn('venta', 'total')) {
                        $attrs['total'] = $total;
                    }

                    Venta::insert([$attrs]);
                }
            }

            if (Schema::hasTable('certificacion_lote')) {
                $admin = Usuario::where('email', self::EMAIL_ADMIN)->first();
                if ($admin) {
                    foreach ($certDefs as $cdef) {
                        $lote = Lote::where('nombre', $cdef['lote_nombre'])->first();
                        if (! $lote) {
                            continue;
                        }

                        CertificacionLote::updateOrCreate(
                            ['codigo_certificado' => $cdef['codigo_certificado']],
                            [
                                'loteid' => $lote->loteid,
                                'usuarioid' => $admin->usuarioid,
                                'observaciones' => $cdef['observaciones'].' [DEMO-B5]',
                                'fecha_certificacion' => $cdef['fecha'].' 12:00:00',
                            ]
                        );
                    }
                }
            }
        });
    }

    private function unidadMedidaIdPorClave(string $clave): ?int
    {
        $clave = mb_strtolower(trim($clave));

        if ($clave === 'kg') {
            return UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['kilogramo'])->value('unidadmedidaid')
                ?? UnidadMedida::where('nombre', 'Kilogramo')->value('unidadmedidaid');
        }

        if ($clave === 'und') {
            return UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['unidad'])->value('unidadmedidaid')
                ?? UnidadMedida::where('nombre', 'Unidad')->value('unidadmedidaid');
        }

        if ($clave === 'qq') {
            return UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['quintal'])->value('unidadmedidaid')
                ?? UnidadMedida::where('nombre', 'Quintal')->value('unidadmedidaid');
        }

        return null;
    }

    /**
     * Producción mínima de Maíz para poder enlazar la venta VTA-004 (no existía en BLOQUE 4).
     */
    private function ensureProduccionMaizDemo(?int $destinoAlmId): ?Produccion
    {
        $marker = '[DEMO-B5] Producción soporte venta demo Maíz';

        $lote = Lote::where('nombre', 'Lote Oeste E5')->first();
        $kgId = UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['kilogramo'])->value('unidadmedidaid')
            ?? UnidadMedida::where('nombre', 'Kilogramo')->value('unidadmedidaid');

        if (! $lote || ! $kgId) {
            return null;
        }

        return Produccion::firstOrCreate(
            ['observaciones' => $marker],
            [
                'loteid' => $lote->loteid,
                'cantidad' => 2300,
                'unidadmedidaid' => $kgId,
                'cantidad_base' => 2300,
                'fechacosecha' => '2026-04-18',
                'destinoproduccionid' => $destinoAlmId,
            ]
        );
    }
}
