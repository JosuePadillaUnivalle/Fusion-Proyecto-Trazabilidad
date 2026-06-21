<?php

namespace Database\Seeders;

use App\Support\AlmacenDemoReferencia;
use App\Models\Almacen;
use App\Models\Produccion;
use App\Models\ProduccionAlmacenamiento;
use App\Models\UnidadMedida;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de demostración — módulo Ventas (listado, ingresos, stock en almacén para nueva venta).
 * Ejecutar: php artisan db:seed --class=VentasModuloSeeder
 */
class VentasModuloSeeder extends Seeder
{
    private const MARK = '[MOD-VENTAS]';

    private const MARK_PROD = '[MOD-PROD]';

    public function run(): void
    {
        if (! Schema::hasTable('venta')) {
            $this->command?->warn('Omitido: tabla venta no existe.');

            return;
        }

        $this->call(ProduccionModuloSeeder::class);

        if (Almacen::count() < 1) {
            $this->call(DemoUsuariosAlmacenesActoresSeeder::class);
        }

        if (ProduccionAlmacenamiento::where('observaciones', 'like', self::MARK.' stock%')->count() < 3) {
            $this->seedStockAlmacenParaVentas();
        }

        $ventasDefs = [
            [
                'codigo' => 'VTA-001',
                'produccion' => 'PROD-SUR-001',
                'cliente' => 'Restaurante Verde',
                'cantidad' => 300,
                'unidad' => 'und',
                'precio' => 2.50,
                'dias' => 10,
                'obs' => 'Lechuga hidropónica — pedido semanal.',
            ],
            [
                'codigo' => 'VTA-002',
                'produccion' => 'PROD-NORTE-002',
                'cliente' => 'Mercado Norte Santa Cruz',
                'cantidad' => 500,
                'unidad' => 'kg',
                'precio' => 4.00,
                'dias' => 8,
                'obs' => 'Tomate segunda cosecha — venta mayorista.',
            ],
            [
                'codigo' => 'VTA-003',
                'produccion' => 'PROD-ESTE-001',
                'cliente' => 'Supermercado Central',
                'cantidad' => 700,
                'unidad' => 'kg',
                'precio' => 3.20,
                'dias' => 5,
                'obs' => 'Papa blanca a granel.',
            ],
            [
                'codigo' => 'VTA-004',
                'produccion' => 'PROD-CENTRAL-001',
                'cliente' => 'Hotel Camino Real',
                'cantidad' => 400,
                'unidad' => 'kg',
                'precio' => 2.80,
                'dias' => 3,
                'obs' => 'Cebolla para cocina central.',
            ],
            [
                'codigo' => 'VTA-005',
                'produccion' => 'PROD-NORTE-001',
                'cliente' => 'Exportadora del Este',
                'cantidad' => 350,
                'unidad' => 'kg',
                'precio' => 4.50,
                'dias' => 2,
                'obs' => 'Tomate exportación grado A.',
            ],
            [
                'codigo' => 'VTA-006',
                'produccion' => 'PROD-OESTE-001',
                'cliente' => 'Distribuidora Andina',
                'cantidad' => 20,
                'unidad' => 'qq',
                'precio' => 180.00,
                'dias' => 1,
                'obs' => 'Maíz en quintales — venta regional.',
            ],
            [
                'codigo' => 'VTA-007',
                'produccion' => 'PROD-NORTE-002',
                'cliente' => 'Cooperativa Agrícola Sur',
                'cantidad' => 200,
                'unidad' => 'kg',
                'precio' => 3.90,
                'dias' => 0,
                'obs' => 'Venta del día — entrega inmediata.',
            ],
            [
                'codigo' => 'VTA-008',
                'produccion' => 'PROD-ESTE-001',
                'cliente' => 'Planta Procesadora Orgánica',
                'cantidad' => 450,
                'unidad' => 'kg',
                'precio' => 2.95,
                'dias' => 6,
                'obs' => 'Papa para procesamiento industrial.',
            ],
            [
                'codigo' => 'VTA-009',
                'produccion' => 'PROD-NORTE-001',
                'cliente' => 'Mercado Norte Santa Cruz',
                'cantidad' => 280,
                'unidad' => 'kg',
                'precio' => 4.20,
                'dias' => 4,
                'obs' => 'Tomate cherry — segunda entrega del mes.',
            ],
            [
                'codigo' => 'VTA-010',
                'produccion' => 'PROD-CENTRAL-001',
                'cliente' => 'Supermercado Central',
                'cantidad' => 150,
                'unidad' => 'kg',
                'precio' => 3.10,
                'dias' => 7,
                'obs' => 'Cebolla morada — promoción fin de semana.',
            ],
        ];

        DB::transaction(function () use ($ventasDefs) {
            foreach ($ventasDefs as $def) {
                $this->upsertVenta($def);
            }
        });

        $modCount = Venta::where('observaciones', 'like', self::MARK.'%')->count();
        $ingresos = Venta::where('observaciones', 'like', self::MARK.'%')
            ->get()
            ->sum(fn ($v) => ($v->cantidad ?? 0) * ($v->preciounitario ?? 0));

        $conStock = Produccion::query()
            ->whereHas('almacenamientos', fn ($q) => $q->where('cantidad', '>', 0))
            ->count();

        $this->command?->info(sprintf(
            '%s Listo: %d ventas módulo (%d total), ingresos demo Bs. %s, %d producciones con stock en almacén.',
            self::MARK,
            $modCount,
            Venta::count(),
            number_format($ingresos, 2, '.', ','),
            $conStock
        ));
    }

    private function seedStockAlmacenParaVentas(): void
    {
        if (! Schema::hasTable('produccionalmacenamiento')) {
            return;
        }

        $kgId = $this->unidadId('kg');
        $undId = $this->unidadId('und');
        $qqId = $this->unidadId('qq');

        if (! $kgId) {
            return;
        }

        $almCentral = AlmacenDemoReferencia::central();
        $almNorte = AlmacenDemoReferencia::norte();

        $stocks = [
            ['codigo' => 'PROD-NORTE-001', 'alm' => $almCentral, 'cant' => 1800, 'um' => $kgId],
            ['codigo' => 'PROD-NORTE-002', 'alm' => $almCentral, 'cant' => 1600, 'um' => $kgId],
            ['codigo' => 'PROD-ESTE-001', 'alm' => $almCentral, 'cant' => 2100, 'um' => $kgId],
            ['codigo' => 'PROD-SUR-001', 'alm' => $almNorte, 'cant' => 900, 'um' => $undId ?? $kgId],
            ['codigo' => 'PROD-CENTRAL-001', 'alm' => $almCentral, 'cant' => 1100, 'um' => $kgId],
            ['codigo' => 'PROD-OESTE-001', 'alm' => $almCentral, 'cant' => 35, 'um' => $qqId ?? $kgId],
        ];

        foreach ($stocks as $s) {
            if (! $s['alm']) {
                continue;
            }

            $prod = $this->produccionPorCodigo($s['codigo']);
            if (! $prod) {
                continue;
            }

            ProduccionAlmacenamiento::updateOrCreate(
                [
                    'produccionid' => $prod->produccionid,
                    'almacenid' => $s['alm']->almacenid,
                    'observaciones' => self::MARK.' stock|'.$s['codigo'],
                ],
                [
                    'cantidad' => $s['cant'],
                    'unidadmedidaid' => $s['um'],
                    'fechaentrada' => Carbon::now()->subDays(2),
                    'fechasalida' => null,
                ]
            );
        }
    }

    private function upsertVenta(array $def): void
    {
        $marker = self::MARK.' '.$def['codigo'];
        $prod = $this->produccionPorCodigo($def['produccion']);

        if (! $prod) {
            $this->command?->warn(self::MARK." Producción «{$def['produccion']}» no encontrada.");

            return;
        }

        $unidadId = $this->unidadId($def['unidad']);
        if (! $unidadId) {
            $this->command?->warn(self::MARK." Unidad «{$def['unidad']}» no encontrada.");

            return;
        }

        $total = round($def['cantidad'] * $def['precio'], 2);

        $attrs = [
            'produccionid' => $prod->produccionid,
            'cliente' => $def['cliente'],
            'cantidad' => $def['cantidad'],
            'unidadmedidaid' => $unidadId,
            'preciounitario' => $def['precio'],
            'fechaventa' => now()->subDays($def['dias'])->toDateString(),
            'observaciones' => $marker.' '.$def['obs'],
        ];

        if (Schema::hasColumn('venta', 'total')) {
            $attrs['total'] = $total;
        }

        Venta::unguarded(fn () => Venta::updateOrCreate(['observaciones' => $marker], $attrs));
    }

    private function produccionPorCodigo(string $codigo): ?Produccion
    {
        return Produccion::where('observaciones', 'like', self::MARK_PROD.' '.$codigo.'%')->first();
    }

    private function unidadId(string $clave): ?int
    {
        $clave = mb_strtolower(trim($clave));

        if ($clave === 'kg') {
            return UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['kilogramo'])->value('unidadmedidaid')
                ?? UnidadMedida::where('abreviatura', 'kg')->value('unidadmedidaid');
        }

        if ($clave === 'und') {
            return UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['unidad'])->value('unidadmedidaid')
                ?? UnidadMedida::where('abreviatura', 'und')->value('unidadmedidaid');
        }

        if ($clave === 'qq') {
            return UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['quintal'])->value('unidadmedidaid')
                ?? UnidadMedida::where('abreviatura', 'qq')->value('unidadmedidaid');
        }

        return null;
    }
}
