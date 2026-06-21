<?php

namespace Database\Seeders;

use App\Support\AlmacenDemoReferencia;
use App\Models\Actividad;
use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\DetallePedidoDistribucion;
use App\Models\HistorialEstadoLote;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\PedidoDistribucion;
use App\Models\Produccion;
use App\Models\ProduccionAlmacenamiento;
use App\Models\PuntoVenta;
use App\Models\TipoInsumo;
use App\Models\TipoMovimientoAlmacen;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Services\PuntoVentaAlmacenService;
use App\Support\AlmacenAmbito;
use App\Support\PedidoDistribucionCatalogo;
use App\Support\TrazabilidadProductoPdvService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Inventario PDV «Mercado Alvaro» — Zanahoria del lote real «Lote de Zanahoria» (Almacén Norte).
 * Elimina el ejemplo demo anterior y enlaza el QR al lote con fotos reales cargadas en campo.
 *
 * php artisan db:seed --class=DemoMercadoAlvaroZanahoriaQrSeeder
 */
class DemoMercadoAlvaroZanahoriaQrSeeder extends Seeder
{
    private const MARK_DEMO = '[DEMO-MERCADO-ALVARO-ZAN]';

    private const LOTE_CODIGO = 'TRAZ-20260605-98F808';

    private const LOTE_NOMBRE = 'Lote de Zanahoria';

    private const PRODUCTO = 'Zanahoria';

    private const PEDIDO = 'PDV-20260612-ZAN';

    private const STOCK_PDV = 25.00;

    public function run(): void
    {
        if (! Schema::hasTable('insumo') || ! Schema::hasTable('lote')) {
            $this->command?->warn('Tablas insumo/lote no disponibles.');

            return;
        }

        DB::transaction(function () {
            $this->limpiarDemoAnterior();

            $lote = Lote::query()
                ->where('codigo_trazabilidad', self::LOTE_CODIGO)
                ->first();

            if ($lote === null) {
                $this->command?->error('No se encontró el lote real «'.self::LOTE_NOMBRE.'» ('.self::LOTE_CODIGO.').');

                return;
            }

            $punto = $this->resolverPuntoVenta();
            $almacenPdv = app(PuntoVentaAlmacenService::class)->crearAlmacenParaPuntoVenta($punto);
            $punto->refresh();

            $admin = Usuario::query()->where('role', 'admin')->first()
                ?? Usuario::query()->where('activo', true)->first();
            $kgId = $this->unidadKgId();

            $almacenPlanta = AlmacenDemoReferencia::planta()
                ?? AlmacenAmbito::scope(Almacen::query()->where('activo', true), AlmacenAmbito::PLANTA)->first();

            $tipoProd = TipoInsumo::query()
                ->whereRaw('LOWER(nombre) LIKE ?', ['%producto%'])
                ->first()
                ?? TipoInsumo::query()->first();

            $insumoPlanta = null;
            if ($almacenPlanta) {
                $insumoPlanta = Insumo::updateOrCreate(
                    ['nombre' => self::PRODUCTO, 'almacenid' => $almacenPlanta->almacenid],
                    [
                        'tipoinsumoid' => $tipoProd?->tipoinsumoid,
                        'unidadmedidaid' => $kgId,
                        'stock' => 180,
                        'stockminimo' => 20,
                        'descripcion' => 'Cosecha de '.self::LOTE_NOMBRE.' ('.self::LOTE_CODIGO.') — Almacén Norte.',
                    ]
                );
            }

            $this->seedPedidoDistribucion($punto, $almacenPlanta, $insumoPlanta, $admin);

            $insumoPdv = Insumo::updateOrCreate(
                [
                    'nombre' => self::PRODUCTO,
                    'almacenid' => $almacenPdv->almacenid,
                ],
                [
                    'tipoinsumoid' => $tipoProd?->tipoinsumoid,
                    'unidadmedidaid' => $kgId,
                    'stock' => self::STOCK_PDV,
                    'stockminimo' => 5,
                    'descripcion' => 'Lote agrícola '.self::LOTE_CODIGO.' — ingreso desde Almacén Norte — '.self::PEDIDO,
                ]
            );

            $urlQr = app(TrazabilidadProductoPdvService::class)->urlPublica($insumoPdv);

            $this->command?->info('Listo: inventario PDV con lote real.');
            $this->command?->info('  Punto de venta: '.$punto->nombre);
            $this->command?->info('  Producto PDV: '.self::PRODUCTO.' (stock '.self::STOCK_PDV.' kg)');
            $this->command?->info('  Lote agrícola: '.self::LOTE_NOMBRE.' ('.self::LOTE_CODIGO.')');
            $this->command?->info('  Almacén origen cosecha: Almacén Norte');
            $this->command?->info('  QR público: '.$urlQr);
        });
    }

    private function limpiarDemoAnterior(): void
    {
        $pedidoDemo = PedidoDistribucion::query()
            ->where('numero_solicitud', 'PDV-20260609-ZAN')
            ->orWhere('observaciones', 'like', '%'.self::MARK_DEMO.'%')
            ->first();

        if ($pedidoDemo) {
            if (Schema::hasTable('detalle_pedido_distribucion')) {
                DetallePedidoDistribucion::query()
                    ->where('pedidodistribucionid', $pedidoDemo->pedidodistribucionid)
                    ->delete();
            }
            AlmacenMovimiento::query()
                ->where('referencia', $pedidoDemo->numero_solicitud)
                ->delete();
            $pedidoDemo->delete();
        }

        Insumo::query()
            ->where('codigo_trazabilidad', 'TRZ-PDV-20260609-ZAN001')
            ->orWhere('nombre', 'Zanahoria fresca Valle')
            ->orWhere('descripcion', 'like', '%'.self::MARK_DEMO.'%')
            ->delete();

        Actividad::query()
            ->where('observaciones', 'like', '%'.self::MARK_DEMO.'%')
            ->delete();

        $produccionesDemo = Produccion::query()
            ->where('observaciones', 'like', '%'.self::MARK_DEMO.'%')
            ->pluck('produccionid');

        if ($produccionesDemo->isNotEmpty() && Schema::hasTable('produccion_almacenamiento')) {
            ProduccionAlmacenamiento::query()
                ->whereIn('produccionid', $produccionesDemo)
                ->delete();
        }

        Produccion::query()
            ->where('observaciones', 'like', '%'.self::MARK_DEMO.'%')
            ->delete();

        HistorialEstadoLote::query()
            ->where('observaciones', 'like', '%'.self::MARK_DEMO.'%')
            ->delete();

        $lotesDemo = Lote::query()
            ->where('observaciones', 'like', '%'.self::MARK_DEMO.'%')
            ->orWhere('nombre', 'Lote Zanahoria Imperator — Valle')
            ->pluck('loteid');

        if ($lotesDemo->isNotEmpty()) {
            Actividad::query()->whereIn('loteid', $lotesDemo)->delete();
            Produccion::query()->whereIn('loteid', $lotesDemo)->where('observaciones', 'like', '%'.self::MARK_DEMO.'%')->delete();
            HistorialEstadoLote::query()->whereIn('loteid', $lotesDemo)->where('observaciones', 'like', '%'.self::MARK_DEMO.'%')->delete();
            Lote::query()->whereIn('loteid', $lotesDemo)->delete();
        }

        if (Storage::disk('public')->exists('evidencias_demo/zanahoria')) {
            Storage::disk('public')->deleteDirectory('evidencias_demo/zanahoria');
        }

        $this->command?->info('Demo anterior eliminado.');
    }

    private function resolverPuntoVenta(): PuntoVenta
    {
        $punto = PuntoVenta::query()
            ->whereRaw('LOWER(nombre) LIKE ?', ['%alvaro%'])
            ->first();

        if ($punto) {
            return $punto;
        }

        $minorista = Usuario::query()
            ->where('email', 'minorista@agrofusion.com')
            ->orWhere('role', 'minorista')
            ->first();

        return PuntoVenta::create([
            'usuarioid' => $minorista?->usuarioid,
            'nombre' => 'Mercado Alvaro',
            'direccion' => 'Av. Grigotá esq. Calle Ñuflo de Chávez, Santa Cruz',
            'latitud' => -17.7892,
            'longitud' => -63.1811,
            'activo' => true,
            'fechacreacion' => now(),
        ]);
    }

    private function seedPedidoDistribucion(
        PuntoVenta $punto,
        ?Almacen $almacenPlanta,
        ?Insumo $insumoPlanta,
        ?Usuario $admin
    ): void {
        if (! Schema::hasTable('pedido_distribucion') || ! $almacenPlanta || ! $insumoPlanta || ! $admin) {
            return;
        }

        $pedido = PedidoDistribucion::updateOrCreate(
            ['numero_solicitud' => self::PEDIDO],
            [
                'puntoventaid' => $punto->puntoventaid,
                'almacen_planta_origenid' => $almacenPlanta->almacenid,
                'estado' => PedidoDistribucionCatalogo::ESTADO_RECIBIDO,
                'fechapedido' => now()->subDays(4),
                'fecha_aceptacion' => now()->subDays(3),
                'fecha_envio' => now()->subDays(2),
                'fecha_recepcion' => now()->subDay(),
                'creado_por_usuarioid' => $admin->usuarioid,
                'aceptado_por_usuarioid' => $admin->usuarioid,
                'observaciones' => 'Distribución zanahoria desde Almacén Norte ('.self::LOTE_CODIGO.')',
            ]
        );

        DetallePedidoDistribucion::updateOrCreate(
            [
                'pedidodistribucionid' => $pedido->pedidodistribucionid,
                'producto_nombre' => self::PRODUCTO,
            ],
            [
                'insumoid' => $insumoPlanta->insumoid,
                'cantidad' => self::STOCK_PDV,
                'observaciones' => 'Lote '.self::LOTE_NOMBRE,
            ]
        );

        if (! Schema::hasTable('almacen_movimiento')) {
            return;
        }

        $tipoIngreso = TipoMovimientoAlmacen::activosPorNaturaleza('ingreso')->first();
        if (! $tipoIngreso) {
            return;
        }

        $almacenPdv = $punto->almacen;
        if (! $almacenPdv) {
            return;
        }

        $insumoPdv = Insumo::query()
            ->where('almacenid', $almacenPdv->almacenid)
            ->where('nombre', self::PRODUCTO)
            ->first();

        if (! $insumoPdv) {
            return;
        }

        AlmacenMovimiento::updateOrCreate(
            ['referencia' => self::PEDIDO, 'insumoid' => $insumoPdv->insumoid],
            [
                'almacenid' => $almacenPdv->almacenid,
                'tipo_movimiento_almacenid' => $tipoIngreso->tipo_movimiento_almacenid,
                'usuarioid' => $admin->usuarioid,
                'fecha' => now()->subDay()->toDateString(),
                'cantidad' => self::STOCK_PDV,
                'observaciones' => '[Recepción PDV] '.self::PEDIDO,
            ]
        );
    }

    private function unidadKgId(): ?int
    {
        return UnidadMedida::query()
            ->whereRaw('LOWER(TRIM(COALESCE(abreviatura, nombre))) IN (?, ?)', ['kg', 'kilogramo'])
            ->value('unidadmedidaid')
            ?? UnidadMedida::query()->value('unidadmedidaid');
    }
}
