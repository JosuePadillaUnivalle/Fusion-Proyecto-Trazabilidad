<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\Insumo;
use App\Models\InsumoPresentacion;
use App\Models\TipoAlmacen;
use App\Models\TipoEmpaque;
use App\Models\TipoMovimientoAlmacen;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Services\InventarioPresentacionService;
use App\Support\AlmacenAmbito;
use App\Support\InsumoCatalogo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Productos terminados con stock en almacén de planta (traslado planta → mayorista).
 * php artisan db:seed --class=ProductosTerminadosPlantaMayoristaSeeder
 */
class ProductosTerminadosPlantaMayoristaSeeder extends Seeder
{
    /** @var list<array{nombre: string, stock: float, unidad: string, descripcion: string, precio: float, presentaciones: list<array{nombre: string, tipo_envase: string, tipo_empaque: string, peso_neto_kg: float, unidades_por_caja?: int}>}> */
    private const PRODUCTOS = [
        [
            'nombre' => 'Zanahoria Imperator envasada',
            'stock' => 150.0,
            'unidad' => 'kg',
            'descripcion' => 'Zanahoria procesada y envasada al vacío — lista para distribución mayorista.',
            'precio' => 4.50,
            'presentaciones' => [
                ['nombre' => 'Bolsa 500 g', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 0.5],
                ['nombre' => 'Bolsa 1 kg', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 1.0],
            ],
        ],
        [
            'nombre' => 'Puré de zanahoria pasteurizado',
            'stock' => 240.0,
            'unidad' => 'kg',
            'descripcion' => 'Puré industrial pasteurizado, brix 12° — producto terminado de planta.',
            'precio' => 6.80,
            'presentaciones' => [
                ['nombre' => 'Lata 400 g', 'tipo_envase' => 'lata', 'tipo_empaque' => 'Lata', 'peso_neto_kg' => 0.4],
                ['nombre' => 'Pouch 1 kg', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Pouch', 'peso_neto_kg' => 1.0],
                ['nombre' => 'Bidón 20 kg', 'tipo_envase' => 'bidon', 'tipo_empaque' => 'Bidón', 'peso_neto_kg' => 20.0],
            ],
        ],
        [
            'nombre' => 'Chips de papa laminados',
            'stock' => 180.0,
            'unidad' => 'kg',
            'descripcion' => 'Papa deshidratada en láminas — insumo terminado para empaque mayorista.',
            'precio' => 5.20,
            'presentaciones' => [
                ['nombre' => 'Bolsa 80 g', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 0.08],
                ['nombre' => 'Bolsa 150 g', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 0.15],
                ['nombre' => 'Caja 12×150 g', 'tipo_envase' => 'caja', 'tipo_empaque' => 'Caja de cartón', 'peso_neto_kg' => 1.8, 'unidades_por_caja' => 12],
            ],
        ],
        [
            'nombre' => 'Salsa de tomate Perita',
            'stock' => 320.0,
            'unidad' => 'kg',
            'descripcion' => 'Salsa base de tomate Perita — producto terminado en bidones industriales.',
            'precio' => 3.90,
            'presentaciones' => [
                ['nombre' => 'Frasco 340 g', 'tipo_envase' => 'frasco', 'tipo_empaque' => 'Frasco', 'peso_neto_kg' => 0.34],
                ['nombre' => 'Bidón 5 kg', 'tipo_envase' => 'bidon', 'tipo_empaque' => 'Bidón', 'peso_neto_kg' => 5.0],
            ],
        ],
        [
            'nombre' => 'Mix vegetal IQF congelado',
            'stock' => 95.0,
            'unidad' => 'kg',
            'descripcion' => 'Mezcla de vegetales IQF (zanahoria, choclo, arveja) — congelado industrial.',
            'precio' => 7.40,
            'presentaciones' => [
                ['nombre' => 'Bolsa 1 kg', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 1.0],
                ['nombre' => 'Bolsa 2,5 kg', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 2.5],
            ],
        ],
    ];

    /** @var list<array{nombre: string, descripcion: string, largo_cm: float, ancho_cm: float, alto_cm: float, tara_kg: float, capacidad_unidades: int}> */
    private const TIPOS_EMPAQUE_PLANTA = [
        ['nombre' => 'Lata', 'descripcion' => 'Lata metálica para purés y conservas', 'largo_cm' => 8, 'ancho_cm' => 8, 'alto_cm' => 11, 'tara_kg' => 0.045, 'capacidad_unidades' => 1],
        ['nombre' => 'Frasco', 'descripcion' => 'Frasco de vidrio para salsas', 'largo_cm' => 7, 'ancho_cm' => 7, 'alto_cm' => 12, 'tara_kg' => 0.18, 'capacidad_unidades' => 1],
        ['nombre' => 'Bidón', 'descripcion' => 'Bidón plástico industrial', 'largo_cm' => 30, 'ancho_cm' => 30, 'alto_cm' => 40, 'tara_kg' => 1.2, 'capacidad_unidades' => 1],
        ['nombre' => 'Pouch', 'descripcion' => 'Bolsa doypack para purés', 'largo_cm' => 12, 'ancho_cm' => 8, 'alto_cm' => 18, 'tara_kg' => 0.025, 'capacidad_unidades' => 1],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('almacen') || ! Schema::hasTable('insumo')) {
            return;
        }

        InsumoCatalogo::asegurarCatalogosBase();
        $this->seedTiposEmpaquePlanta();

        $tipoProductoId = InsumoCatalogo::tipoProductoTerminadoId();
        $tiposEmpaque = $this->mapaTiposEmpaque();

        $almacen = Almacen::query()
            ->where('activo', true)
            ->where('ambito', AlmacenAmbito::PLANTA)
            ->orderBy('almacenid')
            ->first();

        if ($almacen === null) {
            $tipoPlanta = TipoAlmacen::firstOrCreate(
                ['nombre' => 'Planta'],
                ['descripcion' => 'Almacén de planta procesadora']
            );

            $kgId = $this->unidadId('kg');

            $almacen = Almacen::create([
                'nombre' => DemoAlmacenPlantaPruebaSeeder::ALMACEN_NOMBRE,
                'descripcion' => 'Almacén de planta — productos terminados para mayorista',
                'ubicacion' => 'Av. Cristo Redentor km 8, Santa Cruz de la Sierra',
                'capacidad' => 10000,
                'unidadmedidaid' => $kgId,
                'tipoalmacenid' => $tipoPlanta->tipoalmacenid,
                'ambito' => AlmacenAmbito::PLANTA,
                'activo' => true,
            ]);
        }

        $usuarioId = Usuario::query()->where('role', 'planta')->value('usuarioid')
            ?? Usuario::query()->where('role', 'admin')->value('usuarioid');

        $tipoIngreso = TipoMovimientoAlmacen::query()
            ->where('naturaleza', 'ingreso')
            ->where('activo', true)
            ->orderBy('tipo_movimiento_almacenid')
            ->first();

        $creados = 0;
        $presentacionesCreadas = 0;

        DB::transaction(function () use ($almacen, $tipoProductoId, $usuarioId, $tipoIngreso, $tiposEmpaque, &$creados, &$presentacionesCreadas) {
            $inventarioService = app(InventarioPresentacionService::class);

            foreach (self::PRODUCTOS as $def) {
                $unidadId = $this->unidadId($def['unidad']);

                $insumo = Insumo::updateOrCreate(
                    [
                        'nombre' => $def['nombre'],
                        'almacenid' => $almacen->almacenid,
                    ],
                    [
                        'tipoinsumoid' => $tipoProductoId,
                        'unidadmedidaid' => $unidadId,
                        'stock' => $def['stock'],
                        'stockminimo' => 10,
                        'descripcion' => $def['descripcion'],
                        'preciounitario' => $def['precio'],
                    ]
                );

                if (Schema::hasTable('insumo_presentacion')) {
                    InsumoPresentacion::query()
                        ->where('insumoid', $insumo->insumoid)
                        ->delete();

                    $presentacionesCreadasProducto = [];
                    foreach ($def['presentaciones'] as $orden => $pres) {
                        $tipoEmpaqueId = $tiposEmpaque[$pres['tipo_empaque']] ?? null;
                        $sku = strtoupper(substr(preg_replace('/[^a-z0-9]/i', '', $def['nombre']), 0, 6))
                            .'-'.str_pad((string) ($orden + 1), 2, '0', STR_PAD_LEFT);
                        $presentacion = InsumoPresentacion::create([
                            'insumoid' => $insumo->insumoid,
                            'tipoempaqueid' => $tipoEmpaqueId,
                            'nombre' => $pres['nombre'],
                            'tipo_envase' => $pres['tipo_envase'],
                            'peso_neto_kg' => $pres['peso_neto_kg'],
                            'unidades_por_caja' => $pres['unidades_por_caja'] ?? null,
                            'sku' => $sku,
                            'orden' => $orden + 1,
                            'activo' => true,
                        ]);
                        $presentacionesCreadasProducto[] = $presentacion;
                        $presentacionesCreadas++;
                    }

                    if (Schema::hasTable('inventario_presentacion_lote') && $presentacionesCreadasProducto !== []) {
                        $shareKg = $def['stock'] / count($presentacionesCreadasProducto);
                        $distribucion = [];
                        foreach ($presentacionesCreadasProducto as $idx => $presentacion) {
                            $unidades = (int) floor($shareKg / $presentacion->pesoNetoKg());
                            if ($unidades <= 0) {
                                continue;
                            }
                            $distribucion[] = [
                                'presentacion' => $presentacion,
                                'unidades' => (float) $unidades,
                                'referencia_lote' => 'DEMO-'.str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT),
                                'partes' => 2,
                            ];
                        }
                        if ($distribucion !== []) {
                            $inventarioService->bootstrapDesdeStockAgregado(
                                (int) $almacen->almacenid,
                                $insumo,
                                $distribucion
                            );
                        }
                    }
                }

                if ($tipoIngreso && $usuarioId && Schema::hasTable('almacen_movimiento')) {
                    $ref = 'DEMO-PT-PLANTA-'.$insumo->insumoid;
                    if (! AlmacenMovimiento::query()->where('referencia', $ref)->exists()) {
                        AlmacenMovimiento::create([
                            'almacenid' => $almacen->almacenid,
                            'insumoid' => $insumo->insumoid,
                            'tipo_movimiento_almacenid' => $tipoIngreso->tipo_movimiento_almacenid,
                            'usuarioid' => $usuarioId,
                            'fecha' => now()->toDateString(),
                            'cantidad' => $def['stock'],
                            'referencia' => $ref,
                            'destino_motivo' => $almacen->nombre,
                            'observaciones' => 'Stock demo — producto terminado planta → mayorista',
                        ]);
                    }
                }

                $creados++;
            }
        });

        $this->command?->info(
            "Listo: {$creados} productos terminados y {$presentacionesCreadas} presentaciones en «{$almacen->nombre}» (almacenid {$almacen->almacenid})."
        );
    }

    private function seedTiposEmpaquePlanta(): void
    {
        if (! Schema::hasTable('tipo_empaque')) {
            return;
        }

        foreach (self::TIPOS_EMPAQUE_PLANTA as $row) {
            TipoEmpaque::query()->updateOrCreate(
                ['nombre' => $row['nombre']],
                array_merge($row, ['activo' => true])
            );
        }
    }

    /** @return array<string, int> */
    private function mapaTiposEmpaque(): array
    {
        if (! Schema::hasTable('tipo_empaque')) {
            return [];
        }

        return TipoEmpaque::query()
            ->pluck('tipoempaqueid', 'nombre')
            ->mapWithKeys(fn ($id, $nombre) => [(string) $nombre => (int) $id])
            ->all();
    }

    private function unidadId(string $abreviatura): int
    {
        $id = UnidadMedida::query()
            ->whereRaw("LOWER(TRIM(COALESCE(abreviatura, ''))) = ?", [strtolower($abreviatura)])
            ->value('unidadmedidaid');

        if ($id !== null) {
            return (int) $id;
        }

        return (int) (UnidadMedida::query()->value('unidadmedidaid') ?? 1);
    }
}
