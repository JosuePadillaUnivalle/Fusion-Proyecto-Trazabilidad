<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\DetallePedido;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Pedido;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

/**
 * Tres envíos de ejemplo con rutas reales de Bolivia para la pantalla de seguimiento.
 * Ejecutar: php artisan db:seed --class=EnviosSeguimientoBoliviaSeeder
 */
class EnviosSeguimientoBoliviaSeeder extends Seeder
{
    private const MARK = '[ENV-BO]';

    public function run(): void
    {
        $admin = Usuario::where('email', 'admin@agrofusion.com')->first();
        $transportista = Usuario::where('email', 'transportista@agrofusion.com')->first()
            ?? Usuario::where('role', 'transportista')->where('activo', true)->first();

        if (! $admin || ! $transportista) {
            $this->command?->error(self::MARK.' Requiere usuarios admin y transportista (ejecute AdminUserSeeder).');

            return;
        }

        $almCentral = Almacen::where('nombre', 'Almacén Central Santa Cruz')->first();
        $almNorte = Almacen::where('nombre', 'Almacén Norte')->first();

        $envios = [
            [
                'codigo' => 'ENV-BO-SCZ-2026-01',
                'solicitud' => 'PED-SCZ-MERCADO-001',
                'estado' => 'en_ruta',
                'origen_almacen' => $almCentral,
                'origen_texto' => 'Parque Industrial El Trompillo, Km 12, Santa Cruz de la Sierra',
                'destino_nombre' => 'Mercado Abasto La Ramada',
                'destino_dir' => 'Av. Grigotá esq. 3er Anillo Interno, Santa Cruz de la Sierra',
                'lat' => -17.7892,
                'lng' => -63.1751,
                'producto' => 'Tomate',
                'cantidad' => 850,
                'remitente' => 'Cooperativa Agrícola Valle Verde — Santa Cruz',
            ],
            [
                'codigo' => 'ENV-BO-CBB-2026-02',
                'solicitud' => 'PED-CBB-QUILLACOLLO-002',
                'estado' => 'asignado',
                'origen_almacen' => $almNorte,
                'origen_texto' => 'Zona Franca Santísima Trinidad, Cochabamba',
                'destino_nombre' => 'Centro de Acopio Quillacollo',
                'destino_dir' => 'Av. Blanco Galindo Km 8, Quillacollo, Cochabamba',
                'lat' => -17.3923,
                'lng' => -66.2784,
                'producto' => 'Papa',
                'cantidad' => 1200,
                'remitente' => 'Productores del Valle — Cochabamba',
            ],
            [
                'codigo' => 'ENV-BO-LPZ-2026-03',
                'solicitud' => 'PED-LPZ-ELALTO-003',
                'estado' => 'entregado',
                'origen_almacen' => $almCentral,
                'origen_texto' => 'Carretera a Copacabana Km 4, Achocalla, La Paz',
                'destino_nombre' => 'CEA El Alto — Abastecimiento',
                'destino_dir' => 'Av. Juan Pablo II, Zona Río Seco, El Alto, La Paz',
                'lat' => -16.5047,
                'lng' => -68.1632,
                'producto' => 'Lechuga',
                'cantidad' => 420,
                'remitente' => 'Asociación Andina de Hortalizas — La Paz',
            ],
        ];

        foreach ($envios as $row) {
            $pedido = Pedido::updateOrCreate(
                ['numero_solicitud' => $row['solicitud']],
                [
                    'nombre_planta' => $row['destino_nombre'],
                    'latitud' => $row['lat'],
                    'longitud' => $row['lng'],
                    'direccion_texto' => $row['destino_dir'],
                    'estado' => 'pendiente',
                    'fechapedido' => now()->subDays(1),
                    'observaciones' => self::MARK.' Origen: '.$row['origen_texto'].' · Remitente: '.$row['remitente'],
                ]
            );

            DetallePedido::updateOrCreate(
                [
                    'pedidoid' => $pedido->pedidoid,
                    'cultivo_personalizado' => $row['producto'],
                ],
                [
                    'cantidad' => $row['cantidad'],
                    'observaciones' => self::MARK.' Unidad: kg',
                ]
            );

            EnvioAsignacionMultiple::updateOrCreate(
                ['externo_envio_id' => $row['codigo']],
                [
                    'pedidoid' => $pedido->pedidoid,
                    'transportista_usuarioid' => $transportista->usuarioid,
                    'asignadopor_usuarioid' => $admin->usuarioid,
                    'vehiculo_ref' => 'BO-'.$row['codigo'],
                    'estado' => $row['estado'],
                    'fecha_asignacion' => now()->subHours(6),
                    'almacenid' => $row['origen_almacen']?->almacenid,
                    'detalles_productos' => [
                        'remitente' => $row['remitente'],
                        'origen' => $row['origen_texto'],
                        'destino' => $row['destino_dir'],
                    ],
                ]
            );
        }

        $this->command?->info(sprintf(
            '%s Listo: %d envíos Bolivia visibles en seguimiento.',
            self::MARK,
            EnvioAsignacionMultiple::where('externo_envio_id', 'like', 'ENV-BO-%')->count()
        ));
    }
}
