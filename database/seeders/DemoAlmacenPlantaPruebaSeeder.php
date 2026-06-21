<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\Insumo;
use App\Models\TipoAlmacen;
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
 * Almacén de planta y producto terminado para pruebas de distribución PDV.
 * php artisan db:seed --class=DemoAlmacenPlantaPruebaSeeder
 */
class DemoAlmacenPlantaPruebaSeeder extends Seeder
{
    public const ALMACEN_NOMBRE = 'Almacén Remanso';

    public const PRODUCTO_NOMBRE = 'Zanahoria Imperator';

    public const STOCK_INICIAL = 150.0;

    public function run(): void
    {
        if (! Schema::hasTable('almacen') || ! Schema::hasTable('insumo')) {
            return;
        }

        InsumoCatalogo::asegurarCatalogosBase();

        $tipoPlanta = TipoAlmacen::firstOrCreate(
            ['nombre' => 'Planta'],
            ['descripcion' => 'Almacén de planta procesadora']
        );

        $kgId = UnidadMedida::query()
            ->whereRaw("LOWER(TRIM(COALESCE(abreviatura, ''))) = ?", ['kg'])
            ->orWhereRaw('LOWER(nombre) LIKE ?', ['%kilogramo%'])
            ->value('unidadmedidaid')
            ?? UnidadMedida::query()->value('unidadmedidaid');

        $almacen = Almacen::updateOrCreate(
            ['nombre' => self::ALMACEN_NOMBRE],
            [
                'descripcion' => 'Almacén de planta — producto terminado para distribución a puntos de venta',
                'ubicacion' => 'Av. Cristo Redentor km 8, Santa Cruz de la Sierra',
                'capacidad' => 10000,
                'unidadmedidaid' => $kgId,
                'tipoalmacenid' => $tipoPlanta->tipoalmacenid,
                'ambito' => AlmacenAmbito::PLANTA,
                'activo' => true,
            ]
        );

        $tipoProducto = TipoInsumo::firstOrCreate(
            ['nombre' => 'Producto terminado'],
            ['nombre' => 'Producto terminado']
        );

        $usuarioId = Usuario::query()->where('role', 'planta')->value('usuarioid')
            ?? Usuario::query()->where('role', 'admin')->value('usuarioid');

        $tipoIngreso = TipoMovimientoAlmacen::query()
            ->where('naturaleza', 'ingreso')
            ->where('activo', true)
            ->orderBy('tipo_movimiento_almacenid')
            ->first();

        DB::transaction(function () use ($almacen, $tipoProducto, $kgId, $usuarioId, $tipoIngreso) {
            $insumo = Insumo::updateOrCreate(
                [
                    'nombre' => self::PRODUCTO_NOMBRE,
                    'almacenid' => $almacen->almacenid,
                ],
                [
                    'tipoinsumoid' => $tipoProducto->tipoinsumoid,
                    'unidadmedidaid' => $kgId,
                    'stock' => self::STOCK_INICIAL,
                    'stockminimo' => 10,
                    'descripcion' => 'Producto terminado de planta — prueba de pedidos de distribución PDV.',
                    'preciounitario' => 4.50,
                ]
            );

            if ($tipoIngreso && $usuarioId && Schema::hasTable('almacen_movimiento')) {
                $ref = 'DEMO-PLANTA-'.$insumo->insumoid;
                if (! AlmacenMovimiento::query()->where('referencia', $ref)->exists()) {
                    AlmacenMovimiento::create([
                        'almacenid' => $almacen->almacenid,
                        'insumoid' => $insumo->insumoid,
                        'tipo_movimiento_almacenid' => $tipoIngreso->tipo_movimiento_almacenid,
                        'usuarioid' => $usuarioId,
                        'fecha' => now()->toDateString(),
                        'cantidad' => self::STOCK_INICIAL,
                        'referencia' => $ref,
                        'destino_motivo' => $almacen->nombre,
                        'observaciones' => 'Stock inicial de prueba — '.self::PRODUCTO_NOMBRE,
                    ]);
                }
            }
        });

        $this->command?->info(
            'Datos de prueba listos: «'.self::ALMACEN_NOMBRE.'» con «'.self::PRODUCTO_NOMBRE.'» ('.self::STOCK_INICIAL.' kg).'
        );
    }
}
