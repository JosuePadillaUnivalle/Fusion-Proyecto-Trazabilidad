<?php

namespace Database\Seeders;

use App\Models\ActorAbastecimiento;
use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\AlmacenProducto;
use App\Models\CategoriaProducto;
use App\Models\EstadoLoteInsumo;
use App\Models\Insumo;
use App\Models\Lote;
use App\Models\LoteInsumo;
use App\Models\ProductoDistribucion;
use App\Models\TipoInsumo;
use App\Models\TipoMovimientoAlmacen;
use App\Models\UnidadMedida;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Datos de demostración — módulo Inventario (insumos, actores, lotes-insumo, almacenes, movimientos, stock distribución).
 * Ejecutar: php artisan db:seed --class=InventarioModuloSeeder
 */
class InventarioModuloSeeder extends Seeder
{
    private const MARK = '[MOD-INV]';

    private const ALMACEN_CENTRAL = 'Almacén Central Santa Cruz';

    private const EMAIL_ALMACEN = 'almacen@agronexus.com';

    private const EMAIL_AGRICULTOR = 'agricultor@agronexus.com';

    public function run(): void
    {
        $this->call(DemoCatalogosBaseSeeder::class);
        $this->call(DemoUsuariosAlmacenesActoresSeeder::class);

        if (Lote::count() === 0) {
            $this->call(LotesActividadesModuloSeeder::class);
        }

        $almacenCentral = Almacen::where('nombre', self::ALMACEN_CENTRAL)->first();
        $almacenNorte = Almacen::where('nombre', 'Almacén Norte')->first();
        $almacenPlanta = Almacen::where('nombre', 'Almacén Planta Procesadora')->first();

        if (! $almacenCentral) {
            $this->command?->error('No existe '.self::ALMACEN_CENTRAL.'. Revise DemoUsuariosAlmacenesActoresSeeder.');

            return;
        }

        $usuarioAlmacen = Usuario::where('email', self::EMAIL_ALMACEN)->first();
        $agricultor = Usuario::where('email', self::EMAIL_AGRICULTOR)->first();

        DB::transaction(function () use ($almacenCentral, $almacenNorte, $almacenPlanta, $usuarioAlmacen, $agricultor) {
            $this->seedActoresExtra();
            $insumosAgro = $this->seedInsumosAgricolas($almacenCentral);
            $this->seedInsumosProductoYMovimientos($almacenCentral, $usuarioAlmacen);
            $this->seedLoteInsumos($insumosAgro, $agricultor);
            $this->seedProductosDistribucionStock($almacenCentral, $almacenNorte, $almacenPlanta);
        });

        $this->command?->info(sprintf(
            '%s Listo: %d insumos, %d actores, %d lote-insumos, %d movimientos, %d productos distribución, %d stock almacén.',
            self::MARK,
            Insumo::count(),
            ActorAbastecimiento::count(),
            LoteInsumo::where('observaciones', 'like', self::MARK.'%')->count(),
            AlmacenMovimiento::where('referencia', 'like', 'MOD-INV-%')->count(),
            ProductoDistribucion::where('codigo', 'like', 'PROD-%')->count(),
            AlmacenProducto::count()
        ));
    }

    private function seedActoresExtra(): void
    {
        if (! Schema::hasTable('actor_abastecimiento')) {
            return;
        }

        foreach (
            [
                ['nombre' => 'Distribuidora AgroSur', 'tipo_actor' => 'proveedor', 'email' => 'ventas@agrosur.test', 'telefono' => '700200001'],
                ['nombre' => 'Cooperativa El Trópico', 'tipo_actor' => 'productor', 'email' => 'info@eltropico.test', 'telefono' => '700200002'],
            ] as $row
        ) {
            ActorAbastecimiento::updateOrCreate(
                ['nombre' => $row['nombre']],
                [
                    'tipo_actor' => $row['tipo_actor'],
                    'email' => $row['email'],
                    'telefono' => $row['telefono'],
                    'activo' => true,
                ]
            );
        }
    }

    /**
     * @return array<string, Insumo>
     */
    private function seedInsumosAgricolas(Almacen $almacen): array
    {
        $out = [];
        if (! Schema::hasTable('insumo')) {
            return $out;
        }

        $kgId = $this->unidadId('kilogramo');
        $lId = $this->unidadId('litro');
        $tipoFert = TipoInsumo::whereRaw('LOWER(nombre) LIKE ?', ['%fertilizante%'])->first()
            ?? TipoInsumo::firstOrCreate(['nombre' => 'Fertilizante'], ['nombre' => 'Fertilizante']);
        $tipoSemilla = TipoInsumo::whereRaw('LOWER(nombre) LIKE ?', ['%semilla%'])->first()
            ?? TipoInsumo::firstOrCreate(['nombre' => 'Semilla'], ['nombre' => 'Semilla']);
        $tipoBio = TipoInsumo::whereRaw('LOWER(nombre) LIKE ?', ['%bio%'])->first()
            ?? TipoInsumo::firstOrCreate(['nombre' => 'Bioinsumo'], ['nombre' => 'Bioinsumo']);

        $proveedor = ActorAbastecimiento::where('nombre', 'Proveedor AgroInsumos SRL')->first()
            ?? ActorAbastecimiento::query()->where('tipo_actor', 'proveedor')->first();

        $defs = [
            ['key' => 'npk', 'nombre' => 'Fertilizante NPK 15-15-15', 'tipo' => $tipoFert, 'um' => $kgId, 'stock' => 180, 'min' => 30, 'precio' => 38.5],
            ['key' => 'semilla_tomate', 'nombre' => 'Semilla Certificada Tomate', 'tipo' => $tipoSemilla, 'um' => $kgId, 'stock' => 60, 'min' => 15, 'precio' => 52.9],
            ['key' => 'bio_foliar', 'nombre' => 'Bioestimulante Foliar', 'tipo' => $tipoBio, 'um' => $lId ?? $kgId, 'stock' => 95, 'min' => 20, 'precio' => 44.0],
            ['key' => 'herbicida', 'nombre' => 'Herbicida Orgánico EcoWeed', 'tipo' => $tipoBio, 'um' => $lId ?? $kgId, 'stock' => 42, 'min' => 10, 'precio' => 28.0],
            ['key' => 'fungicida', 'nombre' => 'Fungicida Cobre Plus', 'tipo' => $tipoBio, 'um' => $kgId, 'stock' => 35, 'min' => 8, 'precio' => 31.5],
        ];

        foreach ($defs as $d) {
            if (! $d['um']) {
                continue;
            }

            $insumo = Insumo::updateOrCreate(
                [
                    'nombre' => $d['nombre'],
                    'almacenid' => $almacen->almacenid,
                ],
                [
                    'tipoinsumoid' => $d['tipo']->tipoinsumoid,
                    'unidadmedidaid' => $d['um'],
                    'stock' => $d['stock'],
                    'stockminimo' => $d['min'],
                    'proveedor' => $proveedor?->nombre,
                    'actorid' => $proveedor?->actorid,
                    'preciounitario' => $d['precio'],
                    'descripcion' => self::MARK.' Insumo agrícola de demostración.',
                ]
            );
            $out[$d['key']] = $insumo;
        }

        return $out;
    }

    private function seedInsumosProductoYMovimientos(Almacen $almacen, ?Usuario $usuario): void
    {
        if (! Schema::hasTable('insumo') || ! Schema::hasTable('almacen_movimiento') || ! $usuario) {
            return;
        }

        $tipoProd = TipoInsumo::whereRaw('LOWER(nombre) LIKE ?', ['%producto%'])->first()
            ?? TipoInsumo::firstOrCreate(['nombre' => 'Producto agrícola'], ['nombre' => 'Producto agrícola']);

        $unidades = [
            'kg' => $this->unidadId('kilogramo'),
            'und' => $this->unidadId('unidad'),
            'qq' => $this->unidadId('quintal'),
        ];

        if (in_array(null, $unidades, true)) {
            return;
        }

        $defs = [
            ['nombre' => 'Tomate', 'um' => 'kg', 'stock' => 5200, 'ing' => 2000, 'sal' => 500],
            ['nombre' => 'Papa', 'um' => 'kg', 'stock' => 8100, 'ing' => 3000, 'sal' => 1000],
            ['nombre' => 'Lechuga', 'um' => 'und', 'stock' => 2100, 'ing' => 1000, 'sal' => null],
            ['nombre' => 'Cebolla', 'um' => 'kg', 'stock' => 5800, 'ing' => null, 'sal' => 800],
            ['nombre' => 'Maíz', 'um' => 'qq', 'stock' => 280, 'ing' => null, 'sal' => null],
        ];

        $fecha = now()->toDateString();

        foreach ($defs as $def) {
            $insumo = Insumo::updateOrCreate(
                ['nombre' => $def['nombre'], 'almacenid' => $almacen->almacenid],
                [
                    'tipoinsumoid' => $tipoProd->tipoinsumoid,
                    'unidadmedidaid' => $unidades[$def['um']],
                    'stock' => $def['stock'],
                    'stockminimo' => 50,
                    'descripcion' => self::MARK.' Producto en inventario de almacén.',
                ]
            );

            if ($def['ing']) {
                $this->registrarMovimiento($almacen, $insumo, $usuario, 'ingreso', $def['ing'], $fecha, ['Compra', 'Producción recibida']);
            }
            if ($def['sal']) {
                $this->registrarMovimiento($almacen, $insumo, $usuario, 'salida', $def['sal'], $fecha, ['Envío', 'Despacho']);
            }
        }
    }

    /**
     * @param  array<string, Insumo>  $insumosAgro
     */
    private function seedLoteInsumos(array $insumosAgro, ?Usuario $agricultor): void
    {
        if (! Schema::hasTable('loteinsumo') || ! $agricultor) {
            return;
        }

        $estadoAplicado = EstadoLoteInsumo::firstOrCreate(['nombre' => 'Aplicado'], ['nombre' => 'Aplicado']);
        $estadoPendiente = EstadoLoteInsumo::firstOrCreate(['nombre' => 'Pendiente'], ['nombre' => 'Pendiente']);

        $items = [
            ['lote' => 'Lote Norte A1', 'insumo' => 'npk', 'cant' => 25, 'dias' => 20, 'estado' => $estadoAplicado, 'txt' => 'Fertilización base pre-floración.'],
            ['lote' => 'Lote Este B2', 'insumo' => 'semilla_tomate', 'cant' => 8, 'dias' => 45, 'estado' => $estadoAplicado, 'txt' => 'Resiembra de refuerzo (registro histórico).'],
            ['lote' => 'Lote Sur C3', 'insumo' => 'bio_foliar', 'cant' => 12, 'dias' => 15, 'estado' => $estadoAplicado, 'txt' => 'Aplicación foliar preventiva.'],
            ['lote' => 'Lote Central D4', 'insumo' => 'npk', 'cant' => 18, 'dias' => 5, 'estado' => $estadoAplicado, 'txt' => 'Inicio de ciclo cebolla.'],
            ['lote' => 'Lote Oeste E5', 'insumo' => 'herbicida', 'cant' => 6, 'dias' => 2, 'estado' => $estadoPendiente, 'txt' => 'Control de malezas programado.'],
            ['lote' => 'Lote Norte A1', 'insumo' => 'fungicida', 'cant' => 10, 'dias' => 0, 'estado' => $estadoPendiente, 'txt' => 'Preventivo contra hongos — pendiente de aplicar.'],
        ];

        foreach ($items as $i => $item) {
            $lote = Lote::where('nombre', $item['lote'])->first();
            $insumo = $insumosAgro[$item['insumo']] ?? null;
            if (! $lote || ! $insumo) {
                continue;
            }

            $marcador = self::MARK.' lote-insumo|'.$lote->loteid.'|'.$i;
            LoteInsumo::updateOrCreate(
                ['observaciones' => $marcador],
                [
                    'loteid' => $lote->loteid,
                    'insumoid' => $insumo->insumoid,
                    'usuarioid' => $agricultor->usuarioid,
                    'cantidadusada' => $item['cant'],
                    'fechauo' => now()->subDays($item['dias']),
                    'costototal' => round($item['cant'] * (float) ($insumo->preciounitario ?? 0), 2),
                    'estadoloteinsumoid' => $item['estado']->estadoloteinsumoid,
                ]
            );
        }
    }

    private function seedProductosDistribucionStock(?Almacen $central, ?Almacen $norte, ?Almacen $planta): void
    {
        if (! Schema::hasTable('producto_distribucion') || ! Schema::hasTable('almacen_producto')) {
            return;
        }

        $kgId = $this->unidadId('kilogramo');
        $undId = $this->unidadId('unidad');
        if (! $kgId) {
            return;
        }

        $catHort = CategoriaProducto::firstOrCreate(['nombre' => 'Hortalizas'], ['descripcion' => 'Productos frescos']);
        $catTub = CategoriaProducto::firstOrCreate(['nombre' => 'Tubérculos'], ['descripcion' => 'Papa y similares']);
        $catVer = CategoriaProducto::firstOrCreate(['nombre' => 'Verduras de hoja'], ['descripcion' => 'Lechuga y hojas']);

        $productos = [
            ['codigo' => 'PROD-TOM-01', 'nombre' => 'Tomate cherry premium', 'cat' => $catHort, 'um' => $kgId, 'precio' => 4.5],
            ['codigo' => 'PROD-PAP-01', 'nombre' => 'Papa criolla seleccionada', 'cat' => $catTub, 'um' => $kgId, 'precio' => 3.2],
            ['codigo' => 'PROD-LEC-01', 'nombre' => 'Lechuga hidropónica', 'cat' => $catVer, 'um' => $undId ?? $kgId, 'precio' => 2.5],
            ['codigo' => 'PROD-CEB-01', 'nombre' => 'Cebolla morada', 'cat' => $catHort, 'um' => $kgId, 'precio' => 2.8],
            ['codigo' => 'PROD-MAI-01', 'nombre' => 'Maíz amarillo grano', 'cat' => $catHort, 'um' => $kgId, 'precio' => 1.9],
        ];

        $stockMap = [
            'PROD-TOM-01' => [['alm' => $central, 'stock' => 1500, 'min' => 200], ['alm' => $norte, 'stock' => 400, 'min' => 50]],
            'PROD-PAP-01' => [['alm' => $central, 'stock' => 2300, 'min' => 300]],
            'PROD-LEC-01' => [['alm' => $norte, 'stock' => 800, 'min' => 100]],
            'PROD-CEB-01' => [['alm' => $planta, 'stock' => 1200, 'min' => 150]],
            'PROD-MAI-01' => [['alm' => $central, 'stock' => 900, 'min' => 100]],
        ];

        foreach ($productos as $p) {
            $prod = ProductoDistribucion::updateOrCreate(
                ['codigo' => $p['codigo']],
                [
                    'nombre' => $p['nombre'],
                    'categoriaproductoid' => $p['cat']->categoriaproductoid,
                    'unidadmedidaid' => $p['um'],
                    'precio_unitario' => $p['precio'],
                    'descripcion' => self::MARK,
                    'activo' => true,
                ]
            );

            foreach ($stockMap[$p['codigo']] ?? [] as $s) {
                if (! $s['alm']) {
                    continue;
                }
                AlmacenProducto::updateOrCreate(
                    [
                        'productodistribucionid' => $prod->productodistribucionid,
                        'almacenid' => $s['alm']->almacenid,
                    ],
                    [
                        'stock' => $s['stock'],
                        'stock_minimo' => $s['min'],
                        'en_pedido' => 0,
                    ]
                );
            }
        }
    }

    /**
     * @param  array<int, string>  $tiposPreferidos
     */
    private function registrarMovimiento(
        Almacen $almacen,
        Insumo $insumo,
        Usuario $usuario,
        string $naturaleza,
        float $cantidad,
        string $fecha,
        array $tiposPreferidos
    ): void {
        $tipo = $this->resolverTipoMovimiento($naturaleza, $tiposPreferidos);
        if (! $tipo) {
            return;
        }

        $slug = Str::upper(Str::slug($insumo->nombre, '_'));
        $ref = 'MOD-INV-'.Str::upper($naturaleza === 'ingreso' ? 'ING' : 'SAL').'-'.$slug;

        $mov = AlmacenMovimiento::firstOrCreate(
            ['referencia' => $ref],
            [
                'almacenid' => $almacen->almacenid,
                'insumoid' => $insumo->insumoid,
                'tipo_movimiento_almacenid' => $tipo->tipo_movimiento_almacenid,
                'usuarioid' => $usuario->usuarioid,
                'fecha' => $fecha,
                'cantidad' => $cantidad,
                'destino_motivo' => self::MARK,
                'observaciones' => 'Movimiento demo inventario',
            ]
        );

        if ($mov->wasRecentlyCreated) {
            $insumo->refresh();
            if ($naturaleza === 'ingreso') {
                $insumo->incrementarStock($cantidad);
            } else {
                try {
                    $insumo->decrementarStock($cantidad);
                } catch (\Throwable) {
                    // stock ya reflejado en seed inicial
                }
            }
        }
    }

    /**
     * @param  array<int, string>  $preferredNames
     */
    private function resolverTipoMovimiento(string $naturaleza, array $preferredNames): ?TipoMovimientoAlmacen
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

    private function unidadId(string $nombre): ?int
    {
        return UnidadMedida::whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower(trim($nombre))])->value('unidadmedidaid')
            ?? UnidadMedida::where('nombre', ucfirst($nombre))->value('unidadmedidaid');
    }
}
