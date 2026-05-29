<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\Clima;
use App\Models\DestinoProduccion;
use App\Models\DocumentoEntrega;
use App\Models\IncidenteEnvio;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\Produccion;
use App\Models\TipoMovimientoAlmacen;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DemoReportesPanelesFinalSeeder extends Seeder
{
    private const ALMACEN_CENTRAL = 'Almacén Central Santa Cruz';

    private const MARK = '[DEMO-B7]';

    public function run(): void
    {
        $this->seedClimaHistorico();
        $this->seedMovimientosAlmacenReforzados();
        $this->seedProduccionReportes();
        $this->seedVentasReportes();
        $this->seedPanelesRolesMinimos();
    }

    private function seedClimaHistorico(): void
    {
        if (! Schema::hasTable('clima')) {
            $this->command?->warn('BLOQUE 7: tabla clima no existe; historial omitido.');

            return;
        }

        $lote = Lote::where('nombre', 'Lote Norte A1')->first()
            ?? Lote::query()->orderBy('loteid')->first();

        if (! $lote) {
            $this->command?->warn('BLOQUE 7: sin lotes; clima omitido.');

            return;
        }

        $filas = [
            ['fecha' => '2026-04-21', 'temperatura' => 29, 'humedad' => 65, 'lluvia' => 0, 'viento' => 12, 'descripcion' => 'Parcialmente nublado'],
            ['fecha' => '2026-04-22', 'temperatura' => 31, 'humedad' => 60, 'lluvia' => 0, 'viento' => 10, 'descripcion' => 'Soleado'],
            ['fecha' => '2026-04-23', 'temperatura' => 28, 'humedad' => 72, 'lluvia' => 8, 'viento' => 14, 'descripcion' => 'Lluvia ligera'],
            ['fecha' => '2026-04-24', 'temperatura' => 30, 'humedad' => 68, 'lluvia' => 2, 'viento' => 11, 'descripcion' => 'Nublado'],
            ['fecha' => '2026-04-25', 'temperatura' => 32, 'humedad' => 58, 'lluvia' => 0, 'viento' => 9, 'descripcion' => 'Soleado'],
        ];

        foreach ($filas as $f) {
            Clima::firstOrCreate(
                [
                    'loteid' => $lote->loteid,
                    'observaciones' => self::MARK.' Historial '.$f['fecha'].' Santa Cruz',
                ],
                [
                    'fecha' => Carbon::parse($f['fecha'].' 12:00:00'),
                    'temperatura' => $f['temperatura'],
                    'humedad' => $f['humedad'],
                    'lluvia' => $f['lluvia'],
                    'viento' => $f['viento'],
                    'descripcion' => $f['descripcion'],
                    'icono' => 'demo',
                    'presion' => null,
                ]
            );
        }
    }

    private function seedMovimientosAlmacenReforzados(): void
    {
        if (! Schema::hasTable('almacen_movimiento') || ! Schema::hasTable('insumo')) {
            return;
        }

        $almacen = Almacen::where('nombre', self::ALMACEN_CENTRAL)->first();
        $usuario = Usuario::where('email', 'almacen@agronexus.com')->first();

        if (! $almacen || ! $usuario) {
            return;
        }

        $fecha = now()->toDateString();

        $ingresos = [
            ['producto' => 'Tomate', 'cantidad' => 1000, 'ref' => 'DEMO-B7-ING-TOMATE'],
            ['producto' => 'Papa', 'cantidad' => 1500, 'ref' => 'DEMO-B7-ING-PAPA'],
            ['producto' => 'Cebolla', 'cantidad' => 900, 'ref' => 'DEMO-B7-ING-CEBOLLA'],
        ];

        $salidas = [
            ['producto' => 'Tomate', 'cantidad' => 300, 'ref' => 'DEMO-B7-SAL-TOMATE'],
            ['producto' => 'Papa', 'cantidad' => 500, 'ref' => 'DEMO-B7-SAL-PAPA'],
            ['producto' => 'Maíz', 'cantidad' => 10, 'ref' => 'DEMO-B7-SAL-MAIZ'],
        ];

        DB::transaction(function () use ($almacen, $usuario, $fecha, $ingresos, $salidas) {
            foreach ($ingresos as $row) {
                $insumo = Insumo::where('nombre', $row['producto'])
                    ->where('almacenid', $almacen->almacenid)
                    ->first();

                $tipo = $this->resolveTipoMovimiento('ingreso', ['Compra', 'Ajuste positivo']);
                if (! $insumo || ! $tipo) {
                    continue;
                }

                $mov = AlmacenMovimiento::firstOrCreate(
                    ['referencia' => $row['ref']],
                    [
                        'almacenid' => $almacen->almacenid,
                        'insumoid' => $insumo->insumoid,
                        'tipo_movimiento_almacenid' => $tipo->tipo_movimiento_almacenid,
                        'usuarioid' => $usuario->usuarioid,
                        'fecha' => $fecha,
                        'cantidad' => $row['cantidad'],
                        'destino_motivo' => self::MARK.' Refuerzo reportes',
                        'observaciones' => 'DemoReportesPanelesFinalSeeder ingreso',
                    ]
                );

                if ($mov->wasRecentlyCreated) {
                    $insumo->refresh();
                    $insumo->incrementarStock((float) $row['cantidad']);
                }
            }

            foreach ($salidas as $row) {
                $insumo = Insumo::where('nombre', $row['producto'])
                    ->where('almacenid', $almacen->almacenid)
                    ->first();

                $tipo = $this->resolveTipoMovimiento('salida', ['Envío', 'Despacho', 'Consumo interno']);
                if (! $insumo || ! $tipo) {
                    continue;
                }

                $insumo->refresh();
                if ($insumo->stock < $row['cantidad']) {
                    $this->command?->warn("BLOQUE 7: stock insuficiente para salida {$row['producto']}; omitida.");

                    continue;
                }

                $mov = AlmacenMovimiento::firstOrCreate(
                    ['referencia' => $row['ref']],
                    [
                        'almacenid' => $almacen->almacenid,
                        'insumoid' => $insumo->insumoid,
                        'tipo_movimiento_almacenid' => $tipo->tipo_movimiento_almacenid,
                        'usuarioid' => $usuario->usuarioid,
                        'fecha' => $fecha,
                        'cantidad' => $row['cantidad'],
                        'destino_motivo' => self::MARK.' Refuerzo reportes',
                        'observaciones' => 'DemoReportesPanelesFinalSeeder salida',
                    ]
                );

                if ($mov->wasRecentlyCreated) {
                    $insumo->refresh();
                    $insumo->decrementarStock((float) $row['cantidad']);
                }
            }
        });
    }

    private function resolveTipoMovimiento(string $naturaleza, array $preferredNames): ?TipoMovimientoAlmacen
    {
        foreach ($preferredNames as $nombre) {
            $t = TipoMovimientoAlmacen::query()
                ->where('naturaleza', $naturaleza)
                ->where('nombre', $nombre)
                ->where('activo', true)
                ->first();

            if ($t) {
                return $t;
            }
        }

        return TipoMovimientoAlmacen::query()
            ->where('naturaleza', $naturaleza)
            ->where('activo', true)
            ->orderBy('tipo_movimiento_almacenid')
            ->first();
    }

    private function seedProduccionReportes(): void
    {
        if (! Schema::hasTable('produccion')) {
            return;
        }

        $destinoId = DestinoProduccion::whereRaw('LOWER(TRIM(nombre)) = ?', ['almacenamiento'])->value('destinoproduccionid');
        $kgId = UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['kilogramo'])->value('unidadmedidaid')
            ?? UnidadMedida::where('nombre', 'Kilogramo')->value('unidadmedidaid');

        if (! $kgId) {
            return;
        }

        $mapa = [
            ['cultivo' => 'Tomate', 'lote' => 'Lote Norte A1', 'cantidad' => 2200, 'fecha' => '2026-04-21'],
            ['cultivo' => 'Papa', 'lote' => 'Lote Este B2', 'cantidad' => 1850, 'fecha' => '2026-04-22'],
            ['cultivo' => 'Lechuga', 'lote' => 'Lote Sur C3', 'cantidad' => 950, 'fecha' => '2026-04-23'],
            ['cultivo' => 'Cebolla', 'lote' => 'Lote Central D4', 'cantidad' => 1200, 'fecha' => '2026-04-24'],
            ['cultivo' => 'Maíz', 'lote' => 'Lote Oeste E5', 'cantidad' => 1600, 'fecha' => '2026-04-25'],
        ];

        foreach ($mapa as $m) {
            $lote = Lote::where('nombre', $m['lote'])->first();
            if (! $lote) {
                continue;
            }

            Produccion::firstOrCreate(
                ['observaciones' => self::MARK.' Producción reporte '.$m['cultivo'].' '.$m['fecha']],
                [
                    'loteid' => $lote->loteid,
                    'cantidad' => $m['cantidad'],
                    'unidadmedidaid' => $kgId,
                    'cantidad_base' => $m['cantidad'],
                    'fechacosecha' => $m['fecha'],
                    'destinoproduccionid' => $destinoId,
                ]
            );
        }
    }

    private function seedVentasReportes(): void
    {
        if (! Schema::hasTable('venta')) {
            return;
        }

        $items = [
            ['marcador' => self::MARK.' VTA-TOM', 'cultivo' => 'Tomate', 'cantidad' => 400, 'unidad' => 'kg', 'precio' => 4.20, 'fecha' => '2026-04-21'],
            ['marcador' => self::MARK.' VTA-PAP', 'cultivo' => 'Papa', 'cantidad' => 600, 'unidad' => 'kg', 'precio' => 3.40, 'fecha' => '2026-04-22'],
            ['marcador' => self::MARK.' VTA-LECH', 'cultivo' => 'Lechuga', 'cantidad' => 250, 'unidad' => 'und', 'precio' => 2.80, 'fecha' => '2026-04-23'],
            ['marcador' => self::MARK.' VTA-CEB', 'cultivo' => 'Cebolla', 'cantidad' => 300, 'unidad' => 'kg', 'precio' => 3.00, 'fecha' => '2026-04-24'],
            ['marcador' => self::MARK.' VTA-MAIZ', 'cultivo' => 'Maíz', 'cantidad' => 15, 'unidad' => 'qq', 'precio' => 185, 'fecha' => '2026-04-25'],
        ];

        foreach ($items as $it) {
            if (Venta::where('observaciones', $it['marcador'])->exists()) {
                continue;
            }

            $produccionId = $this->resolverProduccionIdParaVenta($it['cultivo']);
            $unidadId = $this->unidadIdPorClave($it['unidad']);

            if (! $produccionId || ! $unidadId) {
                continue;
            }

            $total = round($it['cantidad'] * $it['precio'], 2);

            $attrs = [
                'produccionid' => $produccionId,
                'cliente' => 'Cliente demo reportes '.self::MARK,
                'cantidad' => $it['cantidad'],
                'unidadmedidaid' => $unidadId,
                'preciounitario' => $it['precio'],
                'fechaventa' => $it['fecha'],
                'observaciones' => $it['marcador'],
            ];

            if (Schema::hasColumn('venta', 'total')) {
                $attrs['total'] = $total;
            }

            Venta::insert([$attrs]);
        }
    }

    private function resolverProduccionIdParaVenta(string $cultivoNombre): ?int
    {
        $prefijos = [
            'Tomate' => ['[DEMO-B7] Producción reporte Tomate', '[DEMO-B4] PROD-001'],
            'Papa' => ['[DEMO-B7] Producción reporte Papa', '[DEMO-B4] PROD-002'],
            'Lechuga' => ['[DEMO-B7] Producción reporte Lechuga', '[DEMO-B4] PROD-003'],
            'Cebolla' => ['[DEMO-B7] Producción reporte Cebolla'],
            'Maíz' => ['[DEMO-B7] Producción reporte Maíz', '[DEMO-B5] Producción soporte venta demo Maíz'],
        ];

        $lista = $prefijos[$cultivoNombre] ?? [];

        foreach ($lista as $pref) {
            $id = Produccion::where('observaciones', 'like', $pref.'%')->value('produccionid');
            if ($id) {
                return (int) $id;
            }
        }

        $loteNombre = match ($cultivoNombre) {
            'Tomate' => 'Lote Norte A1',
            'Papa' => 'Lote Este B2',
            'Lechuga' => 'Lote Sur C3',
            'Cebolla' => 'Lote Central D4',
            'Maíz' => 'Lote Oeste E5',
            default => null,
        };

        if (! $loteNombre) {
            return null;
        }

        $lote = Lote::where('nombre', $loteNombre)->first();
        if (! $lote) {
            return null;
        }

        return Produccion::where('loteid', $lote->loteid)->orderByDesc('produccionid')->value('produccionid');
    }

    private function unidadIdPorClave(string $clave): ?int
    {
        return match (mb_strtolower($clave)) {
            'kg' => UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['kilogramo'])->value('unidadmedidaid')
                ?? UnidadMedida::where('nombre', 'Kilogramo')->value('unidadmedidaid'),
            'und' => UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['unidad'])->value('unidadmedidaid')
                ?? UnidadMedida::where('nombre', 'Unidad')->value('unidadmedidaid'),
            'qq' => UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', ['quintal'])->value('unidadmedidaid')
                ?? UnidadMedida::where('nombre', 'Quintal')->value('unidadmedidaid'),
            default => null,
        };
    }

    /**
     * Refuerzo mínimo para paneles (documentos / incidentes por rol) si faltaban filas demo.
     */
    private function seedPanelesRolesMinimos(): void
    {
        $carlos = Usuario::where('email', 'transportista@agronexus.com')->first();
        $almacen = Almacen::where('nombre', self::ALMACEN_CENTRAL)->first();
        $admin = Usuario::where('email', 'admin@agronexus.com')->first();

        if (Schema::hasTable('documento_entrega') && $carlos && $admin) {
            DocumentoEntrega::updateOrCreate(
                ['titulo' => self::MARK.' Acuse transportista Carlos ENV-2026-0001'],
                [
                    'externo_envio_id' => 'ENV-2026-0001',
                    'pedidoid' => null,
                    'usuarioid' => $carlos->usuarioid,
                    'tipo_documento' => 'confirmacion_entrega',
                    'archivo_path' => 'demo/seed/panel-transportista-documento.pdf',
                    'metadata' => ['demo_b7' => true],
                    'almacenid' => $almacen?->almacenid,
                ]
            );
        }

        if (Schema::hasTable('incidente_envio') && $carlos) {
            IncidenteEnvio::firstOrCreate(
                ['descripcion' => self::MARK.' Seguimiento ruta norte asignado a Carlos Mamani.'],
                [
                    'externo_envio_id' => 'ENV-2026-0001',
                    'pedidoid' => null,
                    'reportadopor_usuarioid' => $carlos->usuarioid,
                    'tipo' => 'seguimiento',
                    'estado' => 'abierto',
                    'almacenid' => $almacen?->almacenid,
                ]
            );
        }

        if (Schema::hasTable('documento_entrega') && $almacen && $admin) {
            DocumentoEntrega::updateOrCreate(
                ['titulo' => self::MARK.' Nota recepción panel almacén'],
                [
                    'externo_envio_id' => 'ENV-2026-0002',
                    'pedidoid' => null,
                    'usuarioid' => $admin->usuarioid,
                    'tipo_documento' => 'nota_entrega',
                    'archivo_path' => 'demo/seed/panel-almacen-nota.pdf',
                    'metadata' => ['demo_b7' => true, 'panel' => 'almacen'],
                    'almacenid' => $almacen->almacenid,
                ]
            );
        }
    }
}
