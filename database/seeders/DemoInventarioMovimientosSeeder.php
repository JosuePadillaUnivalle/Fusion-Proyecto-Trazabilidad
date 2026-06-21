<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\Insumo;
use App\Models\TipoInsumo;
use App\Models\TipoMovimientoAlmacen;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DemoInventarioMovimientosSeeder extends Seeder
{
    private const ALMACEN_NOMBRE = 'Almacén Central Santa Cruz';

    private const USUARIO_ALMACEN_EMAIL = 'almacen@agrofusion.com';

    public function run(): void
    {
        // Obsoleto: los productos cosechados ya no se registran como insumos.
        return;

        if (! Schema::hasTable('insumo')
            || ! Schema::hasTable('almacen_movimiento')
            || ! Schema::hasTable('tipo_movimiento_almacen')) {
            return;
        }

        $almacen = Almacen::where('nombre', self::ALMACEN_NOMBRE)->first();
        $usuario = Usuario::where('email', self::USUARIO_ALMACEN_EMAIL)->first();

        if (! $almacen || ! $usuario) {
            return;
        }

        $tipoInsumoId = TipoInsumo::query()->where('nombre', 'Producto agrícola')->value('tipoinsumoid')
            ?? TipoInsumo::query()->orderBy('tipoinsumoid')->value('tipoinsumoid');

        if (! $tipoInsumoId) {
            return;
        }

        $unidades = [
            'kg' => UnidadMedida::where('nombre', 'Kilogramo')->value('unidadmedidaid'),
            'und' => UnidadMedida::where('nombre', 'Unidad')->value('unidadmedidaid'),
            'qq' => UnidadMedida::where('nombre', 'Quintal')->value('unidadmedidaid'),
        ];

        if (in_array(null, $unidades, true)) {
            return;
        }

        $defs = [
            [
                'nombre' => 'Tomate',
                'unidad_key' => 'kg',
                'stock_inicial' => 5000,
                'ingreso' => ['cantidad' => 2000, 'tipos' => ['Compra']],
                'salida' => ['cantidad' => 500, 'tipos' => ['Envío', 'Despacho']],
            ],
            [
                'nombre' => 'Papa',
                'unidad_key' => 'kg',
                'stock_inicial' => 8000,
                'ingreso' => ['cantidad' => 3000, 'tipos' => ['Producción recibida']],
                'salida' => ['cantidad' => 1000, 'tipos' => ['Consumo interno']],
            ],
            [
                'nombre' => 'Lechuga',
                'unidad_key' => 'und',
                'stock_inicial' => 2000,
                'ingreso' => ['cantidad' => 1000, 'tipos' => ['Compra']],
                'salida' => null,
            ],
            [
                'nombre' => 'Cebolla',
                'unidad_key' => 'kg',
                'stock_inicial' => 6000,
                'ingreso' => null,
                'salida' => ['cantidad' => 800, 'tipos' => ['Envío', 'Despacho']],
            ],
            [
                'nombre' => 'Maíz',
                'unidad_key' => 'qq',
                'stock_inicial' => 3000,
                'ingreso' => null,
                'salida' => null,
            ],
        ];

        $fecha = now()->toDateString();

        DB::transaction(function () use ($defs, $almacen, $usuario, $tipoInsumoId, $unidades, $fecha) {
            foreach ($defs as $def) {
                $umId = $unidades[$def['unidad_key']];

                $insumo = Insumo::firstOrCreate(
                    [
                        'nombre' => $def['nombre'],
                        'almacenid' => $almacen->almacenid,
                    ],
                    [
                        'tipoinsumoid' => $tipoInsumoId,
                        'unidadmedidaid' => $umId,
                        'stock' => $def['stock_inicial'],
                        'stockminimo' => 50,
                        'proveedor' => null,
                        'actorid' => null,
                        'preciounitario' => null,
                        'descripcion' => null,
                    ]
                );

                $insumo->refresh();

                if (! empty($def['ingreso'])) {
                    $tipo = $this->resolveTipoMovimiento('ingreso', $def['ingreso']['tipos']);
                    if ($tipo) {
                        $ref = 'DEMO-B3-ING-' . Str::upper(Str::slug($def['nombre'], '_'));
                        $mov = AlmacenMovimiento::firstOrCreate(
                            ['referencia' => $ref],
                            [
                                'almacenid' => $almacen->almacenid,
                                'insumoid' => $insumo->insumoid,
                                'tipo_movimiento_almacenid' => $tipo->tipo_movimiento_almacenid,
                                'usuarioid' => $usuario->usuarioid,
                                'fecha' => $fecha,
                                'cantidad' => $def['ingreso']['cantidad'],
                                'destino_motivo' => 'Demo BLOQUE 3',
                                'observaciones' => 'Seeder DemoInventarioMovimientosSeeder',
                            ]
                        );

                        if ($mov->wasRecentlyCreated) {
                            $insumo->refresh();
                            $insumo->incrementarStock((float) $def['ingreso']['cantidad']);
                        }
                    }
                }

                if (! empty($def['salida'])) {
                    $tipo = $this->resolveTipoMovimiento('salida', $def['salida']['tipos']);
                    if ($tipo) {
                        $ref = 'DEMO-B3-SAL-' . Str::upper(Str::slug($def['nombre'], '_'));
                        $mov = AlmacenMovimiento::firstOrCreate(
                            ['referencia' => $ref],
                            [
                                'almacenid' => $almacen->almacenid,
                                'insumoid' => $insumo->insumoid,
                                'tipo_movimiento_almacenid' => $tipo->tipo_movimiento_almacenid,
                                'usuarioid' => $usuario->usuarioid,
                                'fecha' => $fecha,
                                'cantidad' => $def['salida']['cantidad'],
                                'destino_motivo' => 'Demo BLOQUE 3',
                                'observaciones' => 'Seeder DemoInventarioMovimientosSeeder',
                            ]
                        );

                        if ($mov->wasRecentlyCreated) {
                            $insumo->refresh();
                            $insumo->decrementarStock((float) $def['salida']['cantidad']);
                        }
                    }
                }
            }
        });
    }

    /**
     * @param  array<int, string>  $preferredNames
     */
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
}
