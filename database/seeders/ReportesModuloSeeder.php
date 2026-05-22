<?php

namespace Database\Seeders;

use App\Models\Actividad;
use App\Models\Almacen;
use App\Models\Clima;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\LoteInsumo;
use App\Models\DestinoProduccion;
use App\Models\EstadoLoteInsumo;
use App\Models\Prioridad;
use App\Models\Produccion;
use App\Models\TipoActividad;
use App\Models\TipoInsumo;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Datos de demostración — módulo Reportes (centro, ventas, inventario, producción, clima, actividades, distribución).
 * Ejecutar: php artisan db:seed --class=ReportesModuloSeeder --force
 */
class ReportesModuloSeeder extends Seeder
{
    private const MARK = '[MOD-REP]';

    private const MARK_PROD = '[MOD-PROD]';

    public function run(): void
    {
        $this->ensureDependencies();

        DB::transaction(function () {
            $this->seedMetricasMesActual();
            $this->seedVentasHistoricoSeisMeses();
            $this->seedClimaUltimosDias();
            $this->seedInsumosStockCritico();
            $this->seedActividadesParaReporte();
            $this->seedConsumoInsumoReciente();
        });

        $ventasMes = Venta::whereMonth('fechaventa', now()->month)
            ->whereYear('fechaventa', now()->year)
            ->count();
        $prodMes = Produccion::whereMonth('fechacosecha', now()->month)
            ->whereYear('fechacosecha', now()->year)
            ->count();
        $criticos = Insumo::whereRaw('stock <= COALESCE(stockminimo, 10)')->count();
        $pendientes = Actividad::whereNull('fechafin')->count();
        $clima7 = Clima::where('fecha', '>=', now()->subDays(7))->count();

        $this->command?->info(sprintf(
            '%s Listo: ventas mes %d, producción mes %d, insumos críticos %d, actividades pendientes %d, clima 7d %d.',
            self::MARK,
            $ventasMes,
            $prodMes,
            $criticos,
            $pendientes,
            $clima7
        ));
        $this->command?->info('  Rutas: /reportes · /reportes/ventas · /reportes/inventario · /envios/reportes-distribucion');
        $this->command?->info('  Usuario sugerido: admin@agronexus.com / 123456');
    }

    private function ensureDependencies(): void
    {
        if (Usuario::where('email', 'admin@agronexus.com')->doesntExist()) {
            $this->call(DatosPruebaSeeder::class);
        }

        if (Lote::count() < 3) {
            $this->call(LotesActividadesModuloSeeder::class);
        }

        if (Produccion::where('observaciones', 'like', self::MARK_PROD.'%')->count() < 3) {
            $this->call(ProduccionModuloSeeder::class);
        }

        if (Venta::where('observaciones', 'like', '[MOD-VENTAS]%')->count() < 3) {
            $this->call(VentasModuloSeeder::class);
        }

        if (Insumo::count() < 5) {
            $this->call(InventarioModuloSeeder::class);
        }

        if (Schema::hasTable('envio_asignacion_multiple')
            && DB::table('envio_asignacion_multiple')->count() < 5) {
            $this->call(EnviosDistribucionModuloSeeder::class);
        }
    }

    private function seedMetricasMesActual(): void
    {
        $kgId = $this->unidadId('kg');
        if (! $kgId) {
            return;
        }

        $lote = Lote::where('nombre', 'Lote Norte A1')->first();
        $destinoId = DestinoProduccion::whereRaw('LOWER(TRIM(nombre)) = ?', ['almacenamiento'])->value('destinoproduccionid');
        if ($lote && Schema::hasTable('produccion') && $destinoId) {
            Produccion::updateOrCreate(
                ['observaciones' => self::MARK.' PROD-MES-ACTUAL'],
                [
                    'loteid' => $lote->loteid,
                    'cantidad' => 1850,
                    'unidadmedidaid' => $kgId,
                    'cantidad_base' => 1850,
                    'fechacosecha' => now()->startOfMonth()->addDays(3)->toDateString(),
                    'destinoproduccionid' => $destinoId,
                ]
            );
        }

        if (! Schema::hasTable('venta')) {
            return;
        }

        $ventasMes = [
            ['cod' => 'REP-VTA-MES-01', 'prod' => 'PROD-NORTE-001', 'cliente' => 'Cadena Retail Andina', 'cant' => 420, 'precio' => 4.35, 'dia' => 2],
            ['cod' => 'REP-VTA-MES-02', 'prod' => 'PROD-ESTE-001', 'cliente' => 'Distribuidora El Trópico', 'cant' => 550, 'precio' => 3.25, 'dia' => 5],
            ['cod' => 'REP-VTA-MES-03', 'prod' => 'PROD-CENTRAL-001', 'cliente' => 'Mercado Municipal Sur', 'cant' => 280, 'precio' => 2.95, 'dia' => 8],
        ];

        foreach ($ventasMes as $v) {
            $this->upsertVentaReporte($v);
        }
    }

    private function seedVentasHistoricoSeisMeses(): void
    {
        if (! Schema::hasTable('venta')) {
            return;
        }

        for ($m = 5; $m >= 0; $m--) {
            $fecha = now()->subMonths($m)->startOfMonth()->addDays(10);
            $cod = 'REP-VTA-HIST-'.$fecha->format('Ym');
            $prod = Produccion::where('observaciones', 'like', self::MARK_PROD.' PROD-NORTE-001%')->first()
                ?? Produccion::query()->orderByDesc('produccionid')->first();

            if (! $prod) {
                continue;
            }

            $kgId = $this->unidadId('kg');
            if (! $kgId) {
                continue;
            }

            $cant = 180 + ($m * 40);
            $precio = 3.8 + ($m * 0.05);
            $total = round($cant * $precio, 2);
            $marker = self::MARK.' '.$cod;

            $attrs = [
                'produccionid' => $prod->produccionid,
                'cliente' => 'Cliente histórico reportes '.$fecha->format('m/Y'),
                'cantidad' => $cant,
                'unidadmedidaid' => $kgId,
                'preciounitario' => $precio,
                'fechaventa' => $fecha->toDateString(),
                'observaciones' => $marker,
            ];

            if (Schema::hasColumn('venta', 'total')) {
                $attrs['total'] = $total;
            }

            Venta::unguarded(fn () => Venta::updateOrCreate(['observaciones' => $marker], $attrs));
        }
    }

    private function seedClimaUltimosDias(): void
    {
        if (! Schema::hasTable('clima')) {
            return;
        }

        $lote = Lote::where('nombre', 'Lote Norte A1')->first()
            ?? Lote::query()->orderBy('loteid')->first();

        if (! $lote) {
            return;
        }

        $temps = [27, 29, 31, 28, 26, 30, 32, 29, 28, 27, 30, 31, 29, 28];

        foreach ($temps as $i => $temp) {
            $fecha = now()->subDays(count($temps) - 1 - $i)->setTime(7, 30, 0);
            $marcador = self::MARK.' clima-dia|'.$fecha->format('Y-m-d');

            Clima::updateOrCreate(
                ['observaciones' => $marcador],
                [
                    'loteid' => $lote->loteid,
                    'fecha' => $fecha,
                    'temperatura' => $temp,
                    'humedad' => 58 + ($i % 12),
                    'lluvia' => $i % 4 === 0 ? 4.5 : 0,
                    'viento' => 9 + ($i % 5),
                    'presion' => 1010 + ($i % 3),
                    'descripcion' => $i % 3 === 0 ? 'Lluvia ligera' : 'Parcialmente nublado',
                    'icono' => '02d',
                ]
            );
        }
    }

    private function seedInsumosStockCritico(): void
    {
        if (! Schema::hasTable('insumo')) {
            return;
        }

        $almacen = Almacen::where('nombre', 'Almacén Central Santa Cruz')->first()
            ?? Almacen::query()->first();

        if (! $almacen) {
            return;
        }

        $kgId = $this->unidadId('kg');
        $lId = $this->unidadId('litro');

        $tipoHerramienta = TipoInsumo::whereRaw('LOWER(nombre) LIKE ?', ['%herramienta%'])->first()
            ?? TipoInsumo::firstOrCreate(['nombre' => 'Herramienta'], ['nombre' => 'Herramienta']);

        $criticos = [
            ['nombre' => 'Kit riego por goteo', 'stock' => 3, 'min' => 25, 'um' => $lId ?? $kgId],
            ['nombre' => 'Cinta de embalaje agrícola', 'stock' => 5, 'min' => 40, 'um' => $kgId],
            ['nombre' => 'Guantes nitrilo (caja)', 'stock' => 2, 'min' => 15, 'um' => $kgId],
        ];

        foreach ($criticos as $c) {
            if (! $c['um']) {
                continue;
            }

            Insumo::updateOrCreate(
                [
                    'nombre' => $c['nombre'],
                    'almacenid' => $almacen->almacenid,
                ],
                [
                    'tipoinsumoid' => $tipoHerramienta->tipoinsumoid,
                    'stock' => $c['stock'],
                    'stockminimo' => $c['min'],
                    'unidadmedidaid' => $c['um'],
                    'preciounitario' => 12.5,
                    'descripcion' => self::MARK.' Insumo en stock crítico para alertas de reporte.',
                ]
            );
        }

        $maiz = Insumo::where('nombre', 'Maíz')->where('almacenid', $almacen->almacenid)->first();
        if ($maiz) {
            $maiz->update([
                'stock' => 8,
                'stockminimo' => 50,
                'descripcion' => trim(($maiz->descripcion ?? '').' '.self::MARK.' Ajuste crítico demo.'),
            ]);
        }
    }

    private function seedActividadesParaReporte(): void
    {
        if (! Schema::hasTable('actividad')) {
            return;
        }

        $agricultor = Usuario::where('email', 'agricultor@agronexus.com')->first();
        $prioridadAlta = Prioridad::whereRaw('LOWER(nombre) = ?', ['alta'])->value('prioridadid')
            ?? Prioridad::query()->orderByDesc('prioridadid')->value('prioridadid');

        if (! $agricultor || ! $prioridadAlta) {
            return;
        }

        $defs = [
            ['lote' => 'Lote Norte A1', 'tipo' => 'Riego', 'pendiente' => true, 'dias' => -1, 'horas' => null],
            ['lote' => 'Lote Este B2', 'tipo' => 'Fertilización', 'pendiente' => true, 'dias' => 0, 'horas' => null],
            ['lote' => 'Lote Central D4', 'tipo' => 'Control de plagas', 'pendiente' => true, 'dias' => 1, 'horas' => null],
            ['lote' => 'Lote Sur C3', 'tipo' => 'Cosecha', 'pendiente' => false, 'dias' => -5, 'horas' => 6],
            ['lote' => 'Lote Oeste E5', 'tipo' => 'Siembra', 'pendiente' => false, 'dias' => -12, 'horas' => 8],
        ];

        foreach ($defs as $i => $act) {
            $lote = Lote::where('nombre', $act['lote'])->first();
            $tipo = TipoActividad::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($act['tipo'])])->first();
            if (! $lote || ! $tipo) {
                continue;
            }

            $marcador = self::MARK.' act|'.$act['lote'].'|'.$i;
            $inicio = now()->startOfDay()->addDays($act['dias'])->setTime(8, 0, 0);
            $fin = null;
            if (! $act['pendiente'] && $act['horas']) {
                $fin = $inicio->copy()->addHours($act['horas']);
            }

            Actividad::updateOrCreate(
                ['observaciones' => $marcador],
                [
                    'loteid' => $lote->loteid,
                    'usuarioid' => $agricultor->usuarioid,
                    'descripcion' => 'Actividad demo reportes: '.$act['tipo'].' en '.$act['lote'].'.',
                    'fechainicio' => $inicio,
                    'fechafin' => $fin,
                    'tipoactividadid' => $tipo->tipoactividadid,
                    'prioridadid' => $prioridadAlta,
                ]
            );
        }
    }

    private function seedConsumoInsumoReciente(): void
    {
        if (! Schema::hasTable('loteinsumo')) {
            return;
        }

        $agricultor = Usuario::where('email', 'agricultor@agronexus.com')->first();
        $lote = Lote::where('nombre', 'Lote Norte A1')->first();
        $insumo = Insumo::where('nombre', 'Fertilizante NPK 15-15-15')->first();

        if (! $agricultor || ! $lote || ! $insumo) {
            return;
        }

        $estadoId = EstadoLoteInsumo::whereRaw('LOWER(nombre) = ?', ['aplicado'])->value('estadoloteinsumoid')
            ?? EstadoLoteInsumo::firstOrCreate(['nombre' => 'Aplicado'], ['nombre' => 'Aplicado'])->estadoloteinsumoid;

        foreach ([1, 3, 6, 10, 14] as $dias) {
            $marcador = self::MARK.' consumo|'.$dias.'d';
            LoteInsumo::updateOrCreate(
                ['observaciones' => $marcador],
                [
                    'loteid' => $lote->loteid,
                    'insumoid' => $insumo->insumoid,
                    'usuarioid' => $agricultor->usuarioid,
                    'cantidadusada' => 4 + ($dias % 3),
                    'fechauo' => Carbon::now()->subDays($dias),
                    'costototal' => round((4 + ($dias % 3)) * (float) ($insumo->preciounitario ?? 38), 2),
                    'estadoloteinsumoid' => $estadoId,
                ]
            );
        }
    }

    private function upsertVentaReporte(array $v): void
    {
        $marker = self::MARK.' '.$v['cod'];
        $prod = Produccion::where('observaciones', 'like', self::MARK_PROD.' '.$v['prod'].'%')->first();

        if (! $prod) {
            return;
        }

        $unidadId = $this->unidadId('kg');
        if (! $unidadId) {
            return;
        }

        $fecha = now()->startOfMonth()->addDays(min($v['dia'], now()->daysInMonth - 1));
        $total = round($v['cant'] * $v['precio'], 2);

        $attrs = [
            'produccionid' => $prod->produccionid,
            'cliente' => $v['cliente'],
            'cantidad' => $v['cant'],
            'unidadmedidaid' => $unidadId,
            'preciounitario' => $v['precio'],
            'fechaventa' => $fecha->toDateString(),
            'observaciones' => $marker,
        ];

        if (Schema::hasColumn('venta', 'total')) {
            $attrs['total'] = $total;
        }

        Venta::unguarded(fn () => Venta::updateOrCreate(['observaciones' => $marker], $attrs));
    }

    private function unidadId(string $clave): ?int
    {
        $clave = mb_strtolower(trim($clave));

        return match ($clave) {
            'kg' => UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['kilogramo'])->value('unidadmedidaid'),
            'l', 'litro' => UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['litro'])->value('unidadmedidaid'),
            'und' => UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['unidad'])->value('unidadmedidaid'),
            default => null,
        };
    }
}
