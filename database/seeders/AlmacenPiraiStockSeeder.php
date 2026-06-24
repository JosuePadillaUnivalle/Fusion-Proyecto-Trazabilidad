<?php

namespace Database\Seeders;

use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\Insumo;
use App\Models\InsumoPresentacion;
use App\Models\TipoEmpaque;
use App\Models\TipoMovimientoAlmacen;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use App\Services\InventarioPresentacionService;
use App\Support\AlmacenAmbito;
use App\Support\InsumoCatalogo;
use App\Support\TipoEmpaqueAmbito;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stock demo en almacén mayorista (catálogo PDV).
 * php artisan db:seed --class=AlmacenPiraiStockSeeder
 */
class AlmacenPiraiStockSeeder extends Seeder
{
    /** @var list<array{nombre: string, stock: float, descripcion: string, precio: float, presentaciones: list<array{nombre: string, tipo_envase: string, tipo_empaque: string, peso_neto_kg: float, unidades_por_caja?: int}>}> */
    private const PRODUCTOS = [
        [
            'nombre' => 'Papas fritas clásicas',
            'stock' => 120.0,
            'descripcion' => 'Papas fritas de papa — producto terminado de planta.',
            'precio' => 5.50,
            'presentaciones' => [
                ['nombre' => 'Bolsa 150 g', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 0.15],
                ['nombre' => 'Bolsa 250 g', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 0.25],
            ],
        ],
        [
            'nombre' => 'Chips de papa laminados',
            'stock' => 180.0,
            'descripcion' => 'Papa deshidratada en láminas — producto terminado de planta.',
            'precio' => 5.20,
            'presentaciones' => [
                ['nombre' => 'Bolsa 80 g', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 0.08],
                ['nombre' => 'Bolsa 150 g', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 0.15],
            ],
        ],
        [
            'nombre' => 'Zanahoria Imperator envasada',
            'stock' => 150.0,
            'descripcion' => 'Zanahoria procesada y envasada al vacío.',
            'precio' => 4.50,
            'presentaciones' => [
                ['nombre' => 'Bolsa 500 g', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 0.5],
                ['nombre' => 'Bolsa 1 kg', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 1.0],
            ],
        ],
        [
            'nombre' => 'Puré de zanahoria pasteurizado',
            'stock' => 240.0,
            'descripcion' => 'Puré industrial pasteurizado — producto terminado de planta.',
            'precio' => 6.80,
            'presentaciones' => [
                ['nombre' => 'Lata 400 g', 'tipo_envase' => 'lata', 'tipo_empaque' => 'Lata', 'peso_neto_kg' => 0.4],
                ['nombre' => 'Pouch 1 kg', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Pouch', 'peso_neto_kg' => 1.0],
            ],
        ],
        [
            'nombre' => 'Salsa de tomate Perita',
            'stock' => 320.0,
            'descripcion' => 'Salsa base de tomate Perita — producto terminado de planta.',
            'precio' => 3.90,
            'presentaciones' => [
                ['nombre' => 'Frasco 340 g', 'tipo_envase' => 'frasco', 'tipo_empaque' => 'Frasco', 'peso_neto_kg' => 0.34],
                ['nombre' => 'Bidón 5 kg', 'tipo_envase' => 'bidon', 'tipo_empaque' => 'Bidón', 'peso_neto_kg' => 5.0],
            ],
        ],
        [
            'nombre' => 'Puré de tomate natural',
            'stock' => 200.0,
            'descripcion' => 'Puré de tomate para distribución mayorista.',
            'precio' => 4.20,
            'presentaciones' => [
                ['nombre' => 'Lata 400 g', 'tipo_envase' => 'lata', 'tipo_empaque' => 'Lata', 'peso_neto_kg' => 0.4],
                ['nombre' => 'Bolsa 1 kg', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 1.0],
            ],
        ],
        [
            'nombre' => 'Mix vegetal IQF congelado',
            'stock' => 95.0,
            'descripcion' => 'Mezcla de vegetales IQF (zanahoria, choclo, arveja).',
            'precio' => 7.40,
            'presentaciones' => [
                ['nombre' => 'Bolsa 1 kg', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 1.0],
                ['nombre' => 'Bolsa 2,5 kg', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 2.5],
            ],
        ],
        [
            'nombre' => 'Cebolla deshidratada en polvo',
            'stock' => 80.0,
            'descripcion' => 'Cebolla procesada en polvo — producto terminado de planta.',
            'precio' => 8.10,
            'presentaciones' => [
                ['nombre' => 'Bolsa 250 g', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 0.25],
                ['nombre' => 'Bolsa 1 kg', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 1.0],
            ],
        ],
        [
            'nombre' => 'Tomate triturado en conserva',
            'stock' => 160.0,
            'descripcion' => 'Tomate triturado envasado — producto terminado de planta.',
            'precio' => 3.60,
            'presentaciones' => [
                ['nombre' => 'Lata 400 g', 'tipo_envase' => 'lata', 'tipo_empaque' => 'Lata', 'peso_neto_kg' => 0.4],
                ['nombre' => 'Frasco 720 g', 'tipo_envase' => 'frasco', 'tipo_empaque' => 'Frasco', 'peso_neto_kg' => 0.72],
            ],
        ],
        [
            'nombre' => 'Snack de zanahoria deshidratada',
            'stock' => 70.0,
            'descripcion' => 'Zanahoria en chips deshidratados — producto terminado de planta.',
            'precio' => 6.50,
            'presentaciones' => [
                ['nombre' => 'Bolsa 100 g', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 0.1],
                ['nombre' => 'Bolsa 200 g', 'tipo_envase' => 'bolsa', 'tipo_empaque' => 'Bolsa plástica', 'peso_neto_kg' => 0.2],
            ],
        ],
    ];

    /** @var list<array{nombre: string, descripcion: string, largo_cm: float, ancho_cm: float, alto_cm: float, tara_kg: float, capacidad_unidades: int, unidades_por_pallet: int}> */
    private const TIPOS_EMPAQUE = [
        ['nombre' => 'Lata', 'descripcion' => 'Lata metálica', 'largo_cm' => 8, 'ancho_cm' => 8, 'alto_cm' => 11, 'tara_kg' => 0.045, 'capacidad_unidades' => 1, 'unidades_por_pallet' => 120],
        ['nombre' => 'Frasco', 'descripcion' => 'Frasco de vidrio', 'largo_cm' => 7, 'ancho_cm' => 7, 'alto_cm' => 12, 'tara_kg' => 0.18, 'capacidad_unidades' => 1, 'unidades_por_pallet' => 96],
        ['nombre' => 'Bidón', 'descripcion' => 'Bidón plástico', 'largo_cm' => 30, 'ancho_cm' => 30, 'alto_cm' => 40, 'tara_kg' => 1.2, 'capacidad_unidades' => 1, 'unidades_por_pallet' => 36],
        ['nombre' => 'Pouch', 'descripcion' => 'Bolsa doypack', 'largo_cm' => 12, 'ancho_cm' => 8, 'alto_cm' => 18, 'tara_kg' => 0.025, 'capacidad_unidades' => 1, 'unidades_por_pallet' => 200],
        ['nombre' => 'Bolsa plástica', 'descripcion' => 'Bolsa comercial', 'largo_cm' => 25, 'ancho_cm' => 15, 'alto_cm' => 8, 'tara_kg' => 0.05, 'capacidad_unidades' => 1, 'unidades_por_pallet' => 80],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('almacen') || ! Schema::hasTable('insumo')) {
            return;
        }

        $almacenes = AlmacenAmbito::scope(
            Almacen::query()->where('activo', true),
            AlmacenAmbito::MAYORISTA
        )->orderBy('almacenid')->get();

        if ($almacenes->isEmpty()) {
            $this->command?->warn('No se encontró almacén mayorista. Créelo primero.');

            return;
        }

        InsumoCatalogo::asegurarCatalogosBase();
        $this->seedTiposEmpaque();

        $tipoProductoId = InsumoCatalogo::tipoProductoTerminadoId();
        if ($tipoProductoId === null) {
            $this->command?->warn('Tipo «Producto terminado» no configurado. Ejecute DemoCatalogosBaseSeeder.');

            return;
        }

        $tiposEmpaque = $this->mapaTiposEmpaque();
        $kgId = $this->unidadId('kg');

        $usuario = Usuario::query()
            ->where('email', 'admin@agrofusion.com')
            ->orWhere('role', 'admin')
            ->first();

        $tipoIngreso = TipoMovimientoAlmacen::query()
            ->where('naturaleza', 'ingreso')
            ->where('activo', true)
            ->orderBy('tipo_movimiento_almacenid')
            ->first();

        $creados = 0;
        $presentacionesCreadas = 0;

        foreach ($almacenes as $almacen) {
            DB::transaction(function () use ($almacen, $tipoProductoId, $kgId, $usuario, $tipoIngreso, $tiposEmpaque, &$creados, &$presentacionesCreadas) {
                $inventarioService = app(InventarioPresentacionService::class);

                foreach (self::PRODUCTOS as $def) {
                    $insumoExistente = Insumo::query()
                        ->where('nombre', $def['nombre'])
                        ->where('almacenid', $almacen->almacenid)
                        ->first();

                    if ($insumoExistente && (float) $insumoExistente->stock > 0) {
                        $creados++;

                        continue;
                    }

                    $insumo = Insumo::updateOrCreate(
                        [
                            'nombre' => $def['nombre'],
                            'almacenid' => $almacen->almacenid,
                        ],
                        [
                            'tipoinsumoid' => $tipoProductoId,
                            'unidadmedidaid' => $kgId,
                            'stock' => $def['stock'],
                            'stockminimo' => InsumoCatalogo::UMBRAL_ALERTA_STOCK,
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
                            $sku = strtoupper(substr(preg_replace('/[^a-z0-9]/i', '', $def['nombre']), 0, 6))
                                .'-M'.str_pad((string) ($orden + 1), 2, '0', STR_PAD_LEFT);
                            $presentacion = InsumoPresentacion::create([
                                'insumoid' => $insumo->insumoid,
                                'tipoempaqueid' => $tiposEmpaque[$pres['tipo_empaque']] ?? null,
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
                                    'referencia_lote' => 'MAY-'.str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT),
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

                    if ($tipoIngreso && $usuario && Schema::hasTable('almacen_movimiento')) {
                        $ref = 'STOCK-MAY-'.$almacen->almacenid.'-'.$insumo->insumoid;
                        AlmacenMovimiento::updateOrCreate(
                            ['referencia' => $ref],
                            [
                                'almacenid' => $almacen->almacenid,
                                'insumoid' => $insumo->insumoid,
                                'tipo_movimiento_almacenid' => $tipoIngreso->tipo_movimiento_almacenid,
                                'usuarioid' => $usuario->usuarioid,
                                'fecha' => now()->toDateString(),
                                'cantidad' => $def['stock'],
                                'destino_motivo' => $almacen->nombre,
                                'observaciones' => 'Stock demo mayorista — catálogo PDV',
                            ]
                        );
                    }

                    $creados++;
                }
            });

            $this->command?->info(
                '«'.$almacen->nombre.'»: '.count(self::PRODUCTOS).' productos terminados con presentaciones.'
            );
        }

        $this->command?->info(
            "Listo: {$creados} productos y {$presentacionesCreadas} presentaciones en {$almacenes->count()} almacén(es) mayorista."
        );
    }

    private function seedTiposEmpaque(): void
    {
        if (! Schema::hasTable('tipo_empaque')) {
            return;
        }

        foreach (self::TIPOS_EMPAQUE as $row) {
            TipoEmpaque::query()->updateOrCreate(
                ['nombre' => $row['nombre']],
                array_merge($row, ['activo' => true, 'ambito' => TipoEmpaqueAmbito::PLANTA])
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
