<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\DetallePedidoDistribucion;
use App\Models\Insumo;
use App\Models\InsumoPresentacion;
use App\Models\PedidoDistribucion;
use App\Models\PuntoVenta;
use App\Models\Usuario;
use App\Services\PuntoVentaAlmacenService;
use App\Services\RecepcionPuntoVentaService;
use App\Services\InventarioAlmacenProductoService;
use App\Support\AlmacenAmbito;
use App\Support\PedidoDistribucionCatalogo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Inventario completo en Mercado Alvaro usando el mismo flujo web (pedido en tránsito → recepción PDV).
 *
 * php artisan db:seed --class=PdvMercadoAlvaroCompletoSeeder
 */
class PdvMercadoAlvaroCompletoSeeder extends Seeder
{
    private const MARK = '[DEMO-PDV-ALVARO-COMPLETO]';

    private const PEDIDO = 'PDV-20260623-DEMO';

    /** @var list<array{producto: string, presentacion: string, unidades: int}> */
    private const LINEAS = [
        ['producto' => 'Zanahoria Imperator envasada', 'presentacion' => 'Bolsa 1 kg', 'unidades' => 15],
        ['producto' => 'Papas fritas clásicas', 'presentacion' => 'Bolsa 150 g', 'unidades' => 40],
        ['producto' => 'Salsa de tomate Perita', 'presentacion' => 'Frasco 340 g', 'unidades' => 24],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('pedido_distribucion') || ! Schema::hasTable('insumo')) {
            $this->command?->warn('Tablas de pedidos o inventario no disponibles.');

            return;
        }

        DB::transaction(function () {
            $punto = $this->resolverPuntoVenta();
            app(PuntoVentaAlmacenService::class)->crearAlmacenParaPuntoVenta($punto);
            $punto->refresh();

            $this->limpiarDemoAnterior($punto);

            $almacenMayorista = Almacen::query()
                ->where('activo', true)
                ->where('ambito', AlmacenAmbito::MAYORISTA)
                ->orderBy('almacenid')
                ->first();

            if ($almacenMayorista === null) {
                $this->command?->error('No hay almacén mayorista activo.');

                return;
            }

            $admin = Usuario::query()
                ->where('role', 'admin')
                ->where('activo', true)
                ->first();

            if ($admin === null) {
                $this->command?->error('No hay usuario administrador para registrar la recepción.');

                return;
            }

            $pedido = PedidoDistribucion::updateOrCreate(
                ['numero_solicitud' => self::PEDIDO],
                [
                    'puntoventaid' => $punto->puntoventaid,
                    'almacen_mayorista_origenid' => $almacenMayorista->almacenid,
                    'estado' => PedidoDistribucionCatalogo::ESTADO_EN_TRANSITO,
                    'fechapedido' => now()->subDays(2),
                    'fecha_aceptacion' => now()->subDay(),
                    'fecha_envio' => now()->subHours(6),
                    'creado_por_usuarioid' => $admin->usuarioid,
                    'aceptado_por_usuarioid' => $admin->usuarioid,
                    'observaciones' => 'Pedido demo PDV con recepción real — '.self::MARK,
                ]
            );

            DetallePedidoDistribucion::query()
                ->where('pedidodistribucionid', $pedido->pedidodistribucionid)
                ->delete();

            $lineasCreadas = 0;

            foreach (self::LINEAS as $linea) {
                $insumoMay = Insumo::query()
                    ->where('almacenid', $almacenMayorista->almacenid)
                    ->whereRaw('LOWER(TRIM(nombre)) = ?', [Str::lower(trim($linea['producto']))])
                    ->first();

                if ($insumoMay === null || (float) $insumoMay->stock <= 0) {
                    $this->command?->warn('Sin stock mayorista para «'.$linea['producto'].'» — omitido.');

                    continue;
                }

                $presentacion = InsumoPresentacion::query()
                    ->where('insumoid', $insumoMay->insumoid)
                    ->whereRaw('LOWER(TRIM(nombre)) = ?', [Str::lower(trim($linea['presentacion']))])
                    ->where('activo', true)
                    ->first();

                if ($presentacion === null) {
                    $this->command?->warn('Presentación «'.$linea['presentacion'].'» no encontrada para «'.$linea['producto'].'».');

                    continue;
                }

                $kgNecesarios = round($linea['unidades'] * $presentacion->pesoNetoKg(), 4);
                if ($kgNecesarios > (float) $insumoMay->stock + 0.0001) {
                    $unidades = (int) floor((float) $insumoMay->stock / $presentacion->pesoNetoKg());
                    if ($unidades <= 0) {
                        $this->command?->warn('Stock insuficiente en mayorista para «'.$linea['producto'].'».');

                        continue;
                    }
                    $linea['unidades'] = $unidades;
                }

                $productoNombre = $linea['producto'].' · '.$presentacion->nombre;

                DetallePedidoDistribucion::create([
                    'pedidodistribucionid' => $pedido->pedidodistribucionid,
                    'producto_nombre' => $productoNombre,
                    'insumoid' => $insumoMay->insumoid,
                    'insumo_presentacionid' => $presentacion->insumo_presentacionid,
                    'tipo_envase' => $presentacion->tipo_envase,
                    'cantidad' => $linea['unidades'],
                    'observaciones' => 'Recepción demo vía flujo web — '.self::MARK,
                ]);

                $lineasCreadas++;
            }

            if ($lineasCreadas === 0) {
                $this->command?->error('No se pudo armar ningún detalle. Ejecute agrofusion:asegurar-datos-demo primero.');

                return;
            }

            $pedido->refresh();
            app(RecepcionPuntoVentaService::class)->confirmar($pedido, $admin);

            $stockPdv = Insumo::query()
                ->where('almacenid', $punto->almacenid)
                ->where('stock', '>', 0)
                ->count();

            $this->command?->info('Mercado Alvaro: '.$stockPdv.' producto(s) en inventario tras recepción '.$pedido->numero_solicitud.'.');
        });
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

    private function limpiarDemoAnterior(PuntoVenta $punto): void
    {
        $almacenId = $punto->almacenid;
        if (! $almacenId) {
            return;
        }

        $pedidosDemo = PedidoDistribucion::query()
            ->where(function ($q) {
                $q->where('observaciones', 'like', '%'.self::MARK.'%')
                    ->orWhere('numero_solicitud', self::PEDIDO);
            })
            ->pluck('pedidodistribucionid');

        if ($pedidosDemo->isNotEmpty() && Schema::hasTable('detalle_pedido_distribucion')) {
            DetallePedidoDistribucion::query()
                ->whereIn('pedidodistribucionid', $pedidosDemo)
                ->delete();
        }

        PedidoDistribucion::query()
            ->whereIn('pedidodistribucionid', $pedidosDemo)
            ->delete();

        $almacen = Almacen::query()->find($almacenId);
        if ($almacen) {
            $svc = app(InventarioAlmacenProductoService::class);
            Insumo::query()
                ->where('almacenid', $almacenId)
                ->where(function ($q) {
                    $q->where('descripcion', 'like', '%'.self::MARK.'%')
                        ->orWhere('descripcion', 'like', '%'.self::PEDIDO.'%')
                        ->orWhere('descripcion', 'like', '%Producto recibido desde mayorista%');
                })
                ->get()
                ->each(fn (Insumo $insumo) => $svc->eliminarProducto($almacen, $insumo));
        }
    }
}
