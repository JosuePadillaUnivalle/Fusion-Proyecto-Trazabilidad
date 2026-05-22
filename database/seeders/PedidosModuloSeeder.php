<?php

namespace Database\Seeders;

use App\Models\ClienteComercial;
use App\Models\DetallePedido;
use App\Models\Pedido;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de demostración — módulo Pedidos (listado, estados, detalle, mapa).
 * Ejecutar: php artisan db:seed --class=PedidosModuloSeeder
 */
class PedidosModuloSeeder extends Seeder
{
    private const MARK = '[MOD-PEDIDOS]';

    public function run(): void
    {
        if (! Schema::hasTable('pedido') || ! Schema::hasTable('detallepedido')) {
            $this->command?->warn('Omitido: tablas pedido/detallepedido no existen.');

            return;
        }

        $clientes = $this->seedClientesComerciales();

        $pedidos = [
            [
                'numero_solicitud' => 'MOD-PED-001',
                'nombre_planta' => 'Mercado Norte Santa Cruz',
                'cliente_key' => 'mercado_norte',
                'latitud' => -17.7712,
                'longitud' => -63.1658,
                'direccion_texto' => 'Av. Beni esq. 2do Anillo, Mercado Norte',
                'estado' => 'pendiente',
                'dias_pedido' => 2,
                'dias_entrega' => 7,
                'observaciones' => 'Entrega en horario matutino (06:00–10:00).',
                'detalles' => [
                    ['cultivo' => 'Tomate', 'cantidad' => 800, 'obs' => 'Variedad cherry, calibre medio'],
                ],
            ],
            [
                'numero_solicitud' => 'MOD-PED-002',
                'nombre_planta' => 'Supermercado Central',
                'cliente_key' => 'super_central',
                'latitud' => -17.7891,
                'longitud' => -63.1924,
                'direccion_texto' => 'Av. San Martín 450, Centro',
                'estado' => 'en produccion',
                'dias_pedido' => 5,
                'dias_entrega' => 3,
                'observaciones' => 'Pedido mixto para reposición de góndola.',
                'detalles' => [
                    ['cultivo' => 'Papa', 'cantidad' => 1200, 'obs' => 'Papa blanca, bolsa 25 kg'],
                    ['cultivo' => 'Cebolla', 'cantidad' => 300, 'obs' => 'Cebolla morada'],
                ],
            ],
            [
                'numero_solicitud' => 'MOD-PED-003',
                'nombre_planta' => 'Restaurante Verde',
                'cliente_key' => 'rest_verde',
                'latitud' => -17.7568,
                'longitud' => -63.1742,
                'direccion_texto' => 'Equipetrol Norte, Calle 8 Oeste',
                'estado' => 'confirmado',
                'dias_pedido' => 8,
                'dias_entrega' => 1,
                'observaciones' => 'Cliente frecuente — prioridad alta.',
                'detalles' => [
                    ['cultivo' => 'Lechuga', 'cantidad' => 300, 'obs' => 'Hidropónica, hoja entera'],
                ],
            ],
            [
                'numero_solicitud' => 'MOD-PED-004',
                'nombre_planta' => 'Planta Procesadora Orgánica',
                'cliente_key' => null,
                'latitud' => -17.8015,
                'longitud' => -63.2103,
                'direccion_texto' => 'Parque Industrial El Trompillo, Km 12',
                'estado' => 'pendiente',
                'dias_pedido' => 1,
                'dias_entrega' => 10,
                'observaciones' => 'Requiere certificación de lote adjunta.',
                'detalles' => [
                    ['cultivo' => 'Cebolla', 'cantidad' => 600, 'obs' => 'Destino procesamiento industrial'],
                ],
            ],
            [
                'numero_solicitud' => 'MOD-PED-005',
                'nombre_planta' => 'Distribuidora Andina',
                'cliente_key' => 'dist_andina',
                'latitud' => -17.8124,
                'longitud' => -63.1589,
                'direccion_texto' => 'Zona Sur, Av. Santos Dumont',
                'estado' => 'en produccion',
                'dias_pedido' => 4,
                'dias_entrega' => 5,
                'observaciones' => 'Despacho parcial permitido.',
                'detalles' => [
                    ['cultivo' => 'Maíz', 'cantidad' => 500, 'obs' => 'Grano seco, humedad máx. 14%'],
                ],
            ],
            [
                'numero_solicitud' => 'MOD-PED-006',
                'nombre_planta' => 'Hotel Camino Real',
                'cliente_key' => null,
                'latitud' => -17.7789,
                'longitud' => -63.1811,
                'direccion_texto' => 'Av. San Martín 310, Centro',
                'estado' => 'confirmado',
                'dias_pedido' => 10,
                'dias_entrega' => null,
                'observaciones' => 'Abastecimiento semanal cocina central.',
                'detalles' => [
                    ['cultivo' => 'Tomate', 'cantidad' => 450, 'obs' => 'Tomate pera'],
                    ['cultivo' => 'Lechuga', 'cantidad' => 200, 'obs' => 'Mix verdes'],
                ],
            ],
            [
                'numero_solicitud' => 'MOD-PED-007',
                'nombre_planta' => 'Cooperativa Agrícola Sur',
                'cliente_key' => null,
                'latitud' => -17.8356,
                'longitud' => -63.2241,
                'direccion_texto' => 'Carretera a Cotoca, Km 8',
                'estado' => 'rechazado',
                'dias_pedido' => 12,
                'dias_entrega' => null,
                'observaciones' => 'Rechazado: stock insuficiente en almacén central.',
                'detalles' => [
                    ['cultivo' => 'Papa', 'cantidad' => 1000, 'obs' => 'Solicitud fuera de temporada'],
                ],
            ],
            [
                'numero_solicitud' => 'MOD-PED-008',
                'nombre_planta' => 'Exportadora del Este',
                'cliente_key' => 'export_este',
                'latitud' => -17.7488,
                'longitud' => -63.1421,
                'direccion_texto' => 'Zona Franca El Alto, Santa Cruz',
                'estado' => 'pendiente',
                'dias_pedido' => 0,
                'dias_entrega' => 14,
                'observaciones' => 'Pedido exportación — documentación aduanera pendiente.',
                'detalles' => [
                    ['cultivo' => 'Tomate', 'cantidad' => 2000, 'obs' => 'Export grade A'],
                    ['cultivo' => 'Papa', 'cantidad' => 1500, 'obs' => 'Papa roja, calibre uniforme'],
                ],
            ],
        ];

        DB::transaction(function () use ($pedidos, $clientes) {
            foreach ($pedidos as $def) {
                $fechaPedido = now()->subDays($def['dias_pedido'])->setTime(9, 30, 0);
                $fechaEntrega = isset($def['dias_entrega']) && $def['dias_entrega'] !== null
                    ? now()->addDays($def['dias_entrega'])->toDateString()
                    : null;

                $payload = [
                    'nombre_planta' => $def['nombre_planta'],
                    'latitud' => $def['latitud'],
                    'longitud' => $def['longitud'],
                    'direccion_texto' => $def['direccion_texto'],
                    'estado' => $def['estado'],
                    'fechapedido' => $fechaPedido,
                    'fechaEntregaDeseada' => $fechaEntrega,
                    'observaciones' => self::MARK.' '.$def['observaciones'],
                ];

                if (Schema::hasColumn('pedido', 'clientecomercialid') && $def['cliente_key']) {
                    $payload['clientecomercialid'] = $clientes[$def['cliente_key']] ?? null;
                }

                $pedido = Pedido::updateOrCreate(
                    ['numero_solicitud' => $def['numero_solicitud']],
                    $payload
                );

                foreach ($def['detalles'] as $det) {
                    DetallePedido::updateOrCreate(
                        [
                            'pedidoid' => $pedido->pedidoid,
                            'cultivo_personalizado' => $det['cultivo'],
                        ],
                        [
                            'cantidad' => $det['cantidad'],
                            'observaciones' => $det['obs'].' · '.self::MARK,
                        ]
                    );
                }
            }
        });

        $modCount = Pedido::where('observaciones', 'like', self::MARK.'%')->count();
        $porEstado = Pedido::query()
            ->where('observaciones', 'like', self::MARK.'%')
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado')
            ->all();

        $this->command?->info(sprintf(
            '%s Listo: %d pedidos módulo (%d total). Estados: %s',
            self::MARK,
            $modCount,
            Pedido::count(),
            collect($porEstado)->map(fn ($n, $e) => "$e=$n")->implode(', ')
        ));
    }

    /**
     * @return array<string, int>
     */
    private function seedClientesComerciales(): array
    {
        $out = [];

        if (! Schema::hasTable('cliente_comercial')) {
            return $out;
        }

        $defs = [
            'mercado_norte' => [
                'razon_social' => 'Mercado Norte S.R.L.',
                'nombre_comercial' => 'Mercado Norte Santa Cruz',
                'nit' => '102030405',
                'email' => 'compras@mercadonorte.bo',
            ],
            'super_central' => [
                'razon_social' => 'Supermercado Central S.A.',
                'nombre_comercial' => 'Supermercado Central',
                'nit' => '203040506',
                'email' => 'abastecimiento@supercentral.bo',
            ],
            'rest_verde' => [
                'razon_social' => 'Restaurante Verde Ltda.',
                'nombre_comercial' => 'Restaurante Verde',
                'nit' => '304050607',
                'email' => 'chef@restauranteverde.bo',
            ],
            'dist_andina' => [
                'razon_social' => 'Distribuidora Andina S.R.L.',
                'nombre_comercial' => 'Distribuidora Andina',
                'nit' => '405060708',
                'email' => 'logistica@distandina.bo',
            ],
            'export_este' => [
                'razon_social' => 'Exportadora del Este S.A.',
                'nombre_comercial' => 'Exportadora del Este',
                'nit' => '506070809',
                'email' => 'export@exportadoraeste.bo',
            ],
        ];

        foreach ($defs as $key => $d) {
            $cliente = ClienteComercial::updateOrCreate(
                ['nit' => $d['nit']],
                [
                    'razon_social' => $d['razon_social'],
                    'nombre_comercial' => $d['nombre_comercial'],
                    'direccion' => 'Santa Cruz de la Sierra, Bolivia',
                    'telefono' => '+591 3 3'.substr($d['nit'], -6),
                    'email' => $d['email'],
                    'contacto' => 'Compras · '.self::MARK,
                    'activo' => true,
                ]
            );
            $out[$key] = (int) $cliente->clientecomercialid;
        }

        return $out;
    }
}
