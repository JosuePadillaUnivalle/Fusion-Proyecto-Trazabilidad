<?php

namespace Database\Seeders;

use App\Models\Insumo;
use App\Models\PuntoVenta;
use App\Models\UnidadMedida;
use App\Services\PuntoVentaAlmacenService;
use App\Support\InsumoCatalogo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Restaura stock demo en puntos de venta sin inventario (no borra datos existentes).
 * php artisan db:seed --class=PdvInventarioDemoSeeder
 */
class PdvInventarioDemoSeeder extends Seeder
{
    private const PRODUCTO = 'Zanahoria Imperator envasada';

    private const STOCK_KG = 25.0;

    public function run(): void
    {
        if (! Schema::hasTable('punto_venta') || ! Schema::hasTable('insumo')) {
            return;
        }

        InsumoCatalogo::asegurarCatalogosBase();
        $tipoProdId = InsumoCatalogo::tipoProductoTerminadoId();
        $kgId = UnidadMedida::query()->where('abreviatura', 'kg')->value('unidadmedidaid');

        if ($tipoProdId === null || $kgId === null) {
            $this->command?->warn('Catálogo base incompleto. Ejecute DemoCatalogosBaseSeeder.');

            return;
        }

        $almacenService = app(PuntoVentaAlmacenService::class);
        $restaurados = 0;

        foreach (PuntoVenta::query()->where('activo', true)->get() as $punto) {
            $almacen = $punto->almacen;
            if ($almacen === null) {
                $almacen = $almacenService->crearAlmacenParaPuntoVenta($punto);
                $punto->refresh();
            }

            $tieneStock = Insumo::query()
                ->where('almacenid', $almacen->almacenid)
                ->where('stock', '>', 0)
                ->exists();

            if ($tieneStock) {
                continue;
            }

            Insumo::updateOrCreate(
                [
                    'nombre' => self::PRODUCTO,
                    'almacenid' => $almacen->almacenid,
                ],
                [
                    'tipoinsumoid' => $tipoProdId,
                    'unidadmedidaid' => $kgId,
                    'stock' => self::STOCK_KG,
                    'stockminimo' => 5,
                    'descripcion' => 'Stock demo PDV — producto procesado recibido del mayorista',
                ]
            );

            $restaurados++;
            $this->command?->info('PDV «'.$punto->nombre.'»: inventario demo restaurado.');
        }

        if ($restaurados === 0) {
            $this->command?->info('Todos los puntos de venta activos ya tienen inventario.');
        }
    }
}
