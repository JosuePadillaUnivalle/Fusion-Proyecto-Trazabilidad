<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\Insumo;
use App\Models\TipoInsumo;
use App\Models\TipoMovimientoAlmacen;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Support\AlmacenAmbito;
use App\Support\InsumoCatalogo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stock inicial en el almacén mayorista creado por el usuario (Almacen Pirai).
 * php artisan db:seed --class=AlmacenPiraiStockSeeder
 */
class AlmacenPiraiStockSeeder extends Seeder
{
    public const PRODUCTO_NOMBRE = 'Papas fritas';

    public const STOCK_KG = 100.0;

    public function run(): void
    {
        if (! Schema::hasTable('almacen') || ! Schema::hasTable('insumo')) {
            return;
        }

        $almacen = Almacen::query()
            ->where('ambito', AlmacenAmbito::MAYORISTA)
            ->whereRaw('LOWER(nombre) LIKE ?', ['%pirai%'])
            ->first();

        if (! $almacen) {
            $this->command?->warn('No se encontró un almacén mayorista con nombre "Pirai". Créelo primero.');

            return;
        }

        InsumoCatalogo::asegurarCatalogosBase();

        $kgId = UnidadMedida::query()
            ->whereRaw("LOWER(TRIM(COALESCE(abreviatura, ''))) = ?", ['kg'])
            ->orWhereRaw('LOWER(nombre) LIKE ?', ['%kilogramo%'])
            ->value('unidadmedidaid')
            ?? UnidadMedida::query()->value('unidadmedidaid');

        $tipoProducto = TipoInsumo::firstOrCreate(
            ['nombre' => 'Producto terminado'],
            ['nombre' => 'Producto terminado']
        );

        $usuario = Usuario::query()
            ->where('email', 'admin@agrofusion.com')
            ->orWhere('role', 'admin')
            ->first();

        $tipoIngreso = TipoMovimientoAlmacen::query()
            ->where('naturaleza', 'ingreso')
            ->where('activo', true)
            ->orderBy('tipo_movimiento_almacenid')
            ->first();

        DB::transaction(function () use ($almacen, $tipoProducto, $kgId, $usuario, $tipoIngreso) {
            $insumo = Insumo::updateOrCreate(
                [
                    'nombre' => self::PRODUCTO_NOMBRE,
                    'almacenid' => $almacen->almacenid,
                ],
                [
                    'tipoinsumoid' => $tipoProducto->tipoinsumoid,
                    'unidadmedidaid' => $kgId,
                    'stock' => self::STOCK_KG,
                    'stockminimo' => InsumoCatalogo::UMBRAL_ALERTA_STOCK,
                    'descripcion' => 'Producto terminado — stock para pedidos de distribución',
                ]
            );

            if ($tipoIngreso && $usuario) {
                AlmacenMovimiento::updateOrCreate(
                    [
                        'almacenid' => $almacen->almacenid,
                        'insumoid' => $insumo->insumoid,
                        'referencia' => 'STOCK-PIRAI-PAPAS',
                    ],
                    [
                        'tipo_movimiento_almacenid' => $tipoIngreso->tipo_movimiento_almacenid,
                        'usuarioid' => $usuario->usuarioid,
                        'fecha' => now()->toDateString(),
                        'cantidad' => self::STOCK_KG,
                        'observaciones' => 'Stock inicial papas fritas en '.$almacen->nombre,
                    ]
                );
            }
        });

        $this->command?->info(
            self::PRODUCTO_NOMBRE.' — '.self::STOCK_KG.' kg cargados en «'.$almacen->nombre.'».'
        );
    }
}
