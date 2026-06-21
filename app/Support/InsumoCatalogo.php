<?php

namespace App\Support;

use App\Models\TipoInsumo;
use App\Models\UnidadMedida;
use Illuminate\Support\Collection;

class InsumoCatalogo
{
    /** Umbral fijo de alerta: stock en o por debajo de este valor */
    public const UMBRAL_ALERTA_STOCK = 5;

    /** @var array<string, string> slug => nombre visible */
    public const TIPOS = [
        'material_siembra' => 'Material de Siembra',
        'fertilizantes' => 'Fertilizantes',
        'pesticidas' => 'Control de plagas',
    ];

    /** Nombres de unidad (clave para buscar en BD) por tipo */
    public const UNIDADES_POR_TIPO = [
        'material_siembra' => ['Kilogramo', 'Gramo', 'Quintal', 'Unidad'],
        'fertilizantes' => ['Kilogramo', 'Gramo', 'Quintal', 'Litro'],
        'pesticidas' => ['Kilogramo', 'Gramo', 'Mililitro', 'Litro'],
    ];

    /** @var array<string, string> */
    private const LEGACY_TIPO_SLUG = [
        'semilla' => 'material_siembra',
        'material de siembra' => 'material_siembra',
        'fertilizante' => 'fertilizantes',
        'pesticida' => 'pesticidas',
        'pesticidas' => 'pesticidas',
        'plaguicida' => 'pesticidas',
        'control de plagas' => 'pesticidas',
        'bioinsumo' => 'pesticidas',
    ];

    public static function slugFromNombreTipo(?string $nombre): ?string
    {
        if ($nombre === null || trim($nombre) === '') {
            return null;
        }

        $key = mb_strtolower(trim($nombre));

        if (isset(self::LEGACY_TIPO_SLUG[$key])) {
            return self::LEGACY_TIPO_SLUG[$key];
        }

        foreach (self::TIPOS as $slug => $label) {
            if (mb_strtolower($label) === $key) {
                return $slug;
            }
        }

        return null;
    }

    public static function asegurarCatalogosBase(): void
    {
        foreach (self::TIPOS as $label) {
            TipoInsumo::updateOrCreate(['nombre' => $label], ['nombre' => $label]);
        }

        self::normalizarTiposObsoletos();

        $unidades = [
            ['nombre' => 'Kilogramo', 'abreviatura' => 'kg', 'categoria' => 'peso'],
            ['nombre' => 'Gramo', 'abreviatura' => 'g', 'categoria' => 'peso'],
            ['nombre' => 'Quintal', 'abreviatura' => 'qq', 'categoria' => 'peso'],
            ['nombre' => 'Litro', 'abreviatura' => 'l', 'categoria' => 'volumen'],
            ['nombre' => 'Mililitro', 'abreviatura' => 'ml', 'categoria' => 'volumen'],
            ['nombre' => 'Metro', 'abreviatura' => 'm', 'categoria' => 'longitud'],
            ['nombre' => 'Unidad', 'abreviatura' => 'und', 'categoria' => 'cantidad'],
        ];

        foreach ($unidades as $u) {
            $data = ['nombre' => $u['nombre']];
            if (\Illuminate\Support\Facades\Schema::hasColumn('unidadmedida', 'abreviatura')) {
                $data['abreviatura'] = $u['abreviatura'];
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('unidadmedida', 'categoria')) {
                $data['categoria'] = $u['categoria'];
            }
            UnidadMedida::updateOrCreate(['nombre' => $u['nombre']], $data);
        }
    }

    /** @return Collection<int, TipoInsumo> */
    public static function tiposOrdenados(): Collection
    {
        self::asegurarCatalogosBase();

        $porNombre = TipoInsumo::query()->get()->keyBy(
            fn (TipoInsumo $t) => self::slugFromNombreTipo($t->nombre) ?? 'zzz_'.$t->tipoinsumoid
        );

        return collect(self::TIPOS)
            ->map(fn (string $nombre, string $slug) => $porNombre->get($slug))
            ->filter()
            ->values();
    }

    /**
     * Mapa slug tipo => [{id, nombre, abreviatura}, ...] para el formulario.
     *
     * @return array<string, array<int, array{id: int, nombre: string, abreviatura: string}>>
     */
    public static function unidadesPorTipoParaJs(): array
    {
        self::asegurarCatalogosBase();

        $todas = UnidadMedida::query()->get()->keyBy(fn ($u) => mb_strtolower(trim($u->nombre)));

        $out = [];
        foreach (self::UNIDADES_POR_TIPO as $slug => $nombres) {
            $out[$slug] = [];
            foreach ($nombres as $nombre) {
                $um = $todas->get(mb_strtolower($nombre));
                if ($um) {
                    $out[$slug][] = [
                        'id' => (int) $um->unidadmedidaid,
                        'nombre' => $um->nombre,
                        'abreviatura' => $um->abreviatura ?? $um->nombre,
                    ];
                }
            }
        }

        return $out;
    }

    /** Abreviaturas válidas para el campo dosis_unidad según tipo de insumo. */
    public static function abreviaturasDosisValidasPorTipo(string $slug): array
    {
        return collect(self::unidadesPorTipoParaJs()[$slug] ?? [])
            ->pluck('abreviatura')
            ->filter(fn ($a) => $a !== null && trim((string) $a) !== '')
            ->map(fn ($a) => mb_strtolower(trim((string) $a)))
            ->unique()
            ->values()
            ->all();
    }

    public static function normalizarDosisUnidad(?string $unidad, ?string $slug = null): ?string
    {
        if ($unidad === null || trim($unidad) === '') {
            return null;
        }

        $u = mb_strtolower(trim($unidad));

        if ($slug === 'material_siembra' && in_array($u, ['unidad', 'planta', 'plantas', 'semilla', 'semillas', 'und'], true)) {
            return 'und';
        }

        return $u;
    }

    public static function dosisUnidadEsValida(?string $unidad, string $slug): bool
    {
        $normalizada = self::normalizarDosisUnidad($unidad, $slug);
        if ($normalizada === null) {
            return true;
        }

        return in_array($normalizada, self::abreviaturasDosisValidasPorTipo($slug), true);
    }

    public static function stockCritico(float $stock): bool
    {
        return $stock <= self::UMBRAL_ALERTA_STOCK;
    }

    public static function stockMedio(float $stock): bool
    {
        return $stock > self::UMBRAL_ALERTA_STOCK && $stock <= self::UMBRAL_ALERTA_STOCK * 2;
    }

    public static function claseStock(float $stock): string
    {
        if (self::stockCritico($stock)) {
            return 'low';
        }
        if (self::stockMedio($stock)) {
            return 'medium';
        }

        return 'high';
    }

    /**
     * Estado de stock para productos en almacén planta/mayorista.
     *
     * @return array{
     *     umbral: float,
     *     nivel: string,
     *     etiqueta: string,
     *     mensaje: string,
     *     icono: string,
     *     porcentaje: int,
     *     clase: string,
     *     unidad: string
     * }
     */
    public static function estadoStockAlmacen(\App\Models\Insumo $insumo): array
    {
        $stock = (float) $insumo->stock;
        $umbral = max(0.01, (float) ($insumo->stockminimo ?? self::UMBRAL_ALERTA_STOCK));
        $unidad = $insumo->unidadMedida?->abreviatura ?? $insumo->unidadMedida?->nombre ?? 'u.';
        $referencia = max($umbral * 4, $stock, 1.0);
        $porcentaje = min(100, (int) round(($stock / $referencia) * 100));

        if ($stock <= 0) {
            return [
                'umbral' => $umbral,
                'nivel' => 'agotado',
                'etiqueta' => 'Sin stock',
                'mensaje' => 'No hay unidades disponibles para distribución.',
                'icono' => 'times-circle',
                'porcentaje' => 0,
                'clase' => 'agotado',
                'unidad' => $unidad,
            ];
        }

        if ($stock <= $umbral) {
            return [
                'umbral' => $umbral,
                'nivel' => 'bajo',
                'etiqueta' => 'Stock bajo',
                'mensaje' => 'Quedan '.number_format($stock, 2)." {$unidad}. Reponer antes de llegar a cero.",
                'icono' => 'exclamation-triangle',
                'porcentaje' => min(100, (int) round(($stock / $umbral) * 100)),
                'clase' => 'bajo',
                'unidad' => $unidad,
            ];
        }

        if ($stock <= $umbral * 2) {
            return [
                'umbral' => $umbral,
                'nivel' => 'medio',
                'etiqueta' => 'Stock moderado',
                'mensaje' => number_format($stock, 2)." {$unidad} disponibles. Vigilar reposición.",
                'icono' => 'info-circle',
                'porcentaje' => $porcentaje,
                'clase' => 'medio',
                'unidad' => $unidad,
            ];
        }

        return [
            'umbral' => $umbral,
            'nivel' => 'ok',
            'etiqueta' => 'Stock suficiente',
            'mensaje' => number_format($stock, 2)." {$unidad} listos para pedidos de distribución.",
            'icono' => 'check-circle',
            'porcentaje' => $porcentaje,
            'clase' => 'ok',
            'unidad' => $unidad,
        ];
    }

    /** IDs de tipos válidos (solo los cuatro del catálogo oficial). */
    public static function tiposValidosIds(): array
    {
        self::asegurarCatalogosBase();

        return self::tiposOrdenados()->pluck('tipoinsumoid')->map(fn ($id) => (int) $id)->all();
    }

    /** Solo insumos operativos del campo (excluye tipos obsoletos como «Producto agrícola»). */
    public static function aplicarFiltroOperativo(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        $ids = self::tiposValidosIds();

        return $ids === [] ? $query->whereRaw('1 = 0') : $query->whereIn('tipoinsumoid', $ids);
    }

    public static function tipoProductoTerminadoId(): int
    {
        self::asegurarCatalogosBase();

        return (int) TipoInsumo::firstOrCreate(
            ['nombre' => 'Producto terminado'],
            ['nombre' => 'Producto terminado']
        )->tipoinsumoid;
    }

    /** Producto terminado almacenado en planta o mayorista (no insumos de campo). */
    public static function aplicarFiltroProductoTerminado(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('tipoinsumoid', self::tipoProductoTerminadoId());
    }

    /** Insumos visibles en detalle de almacén según ámbito. */
    public static function aplicarFiltroInsumoPorAmbitoAlmacen(
        \Illuminate\Database\Eloquent\Builder $query,
        string $ambito
    ): \Illuminate\Database\Eloquent\Builder {
        return match ($ambito) {
            \App\Support\AlmacenAmbito::MAYORISTA, \App\Support\AlmacenAmbito::PLANTA => self::aplicarFiltroProductoTerminado($query),
            default => self::aplicarFiltroOperativo($query),
        };
    }

    public static function esInsumoOperativo(?\App\Models\Insumo $insumo): bool
    {
        if ($insumo === null) {
            return false;
        }

        return in_array((int) $insumo->tipoinsumoid, self::tiposValidosIds(), true);
    }

    public static function tipoMaterialSiembraId(): int
    {
        self::asegurarCatalogosBase();

        foreach (self::tiposOrdenados() as $tipo) {
            if (self::slugFromNombreTipo($tipo->nombre) === 'material_siembra') {
                return (int) $tipo->tipoinsumoid;
            }
        }

        return (int) TipoInsumo::firstOrCreate(
            ['nombre' => self::TIPOS['material_siembra']],
            ['nombre' => self::TIPOS['material_siembra']]
        )->tipoinsumoid;
    }

    /** Verduras de campo y producto terminado de planta/mayorista (excluye fertilizantes y pesticidas). */
    public static function tiposProductoTransporteIds(): array
    {
        return array_values(array_unique(array_filter([
            self::tipoMaterialSiembraId(),
            self::tipoProductoTerminadoId(),
        ])));
    }

    /**
     * Productos que pueden ir en un envío: verduras (almacén agrícola) y terminados (planta/mayorista).
     * Un solo registro por nombre aunque exista en varios almacenes.
     *
     * @return array<int, string> insumoid => nombre
     */
    public static function insumosProductosParaTransporte(): array
    {
        self::asegurarCatalogosBase();

        $tipoIds = self::tiposProductoTransporteIds();
        if ($tipoIds === []) {
            return [];
        }

        $vistos = [];
        $out = [];

        foreach (
            \App\Models\Insumo::query()
                ->whereIn('tipoinsumoid', $tipoIds)
                ->orderBy('nombre')
                ->orderBy('insumoid')
                ->get(['insumoid', 'nombre']) as $insumo
        ) {
            $clave = mb_strtolower(trim((string) $insumo->nombre));
            if ($clave === '' || isset($vistos[$clave])) {
                continue;
            }
            $vistos[$clave] = true;
            $out[(int) $insumo->insumoid] = (string) $insumo->nombre;
        }

        uasort($out, fn ($a, $b) => strcasecmp($a, $b));

        return $out;
    }

    /** Productos hortícolas para catálogos logísticos (calibres, empaques, transporte). */
    public static function insumosVerdurasParaLogistica(): array
    {
        return self::insumosProductosParaTransporte();
    }

    public static function esProductoTerminadoDistribucion(?\App\Models\Insumo $insumo): bool
    {
        if ($insumo === null) {
            return false;
        }

        if ((int) $insumo->tipoinsumoid !== self::tipoProductoTerminadoId()) {
            return false;
        }

        $insumo->loadMissing('almacen');

        return in_array($insumo->almacen?->ambito, [
            \App\Support\AlmacenAmbito::PLANTA,
            \App\Support\AlmacenAmbito::MAYORISTA,
        ], true);
    }

    public static function esInsumoGestionable(?\App\Models\Insumo $insumo): bool
    {
        return self::esInsumoOperativo($insumo) || self::esProductoTerminadoDistribucion($insumo);
    }

    public static function asegurarInsumoOperativo(\App\Models\Insumo $insumo): void
    {
        if (! self::esInsumoOperativo($insumo)) {
            abort(404, 'El registro no corresponde al catálogo de insumos operativos.');
        }
    }

    public static function asegurarInsumoGestionable(\App\Models\Insumo $insumo): void
    {
        if (! self::esInsumoGestionable($insumo)) {
            abort(404, 'El registro no corresponde al inventario gestionable.');
        }
    }

    /**
     * Elimina insumos cuyo tipo no es oficial (campo o producto terminado en planta/mayorista).
     */
    public static function purgarInsumosConTipoInvalido(): int
    {
        self::asegurarCatalogosBase();
        $validIds = array_values(array_unique(array_merge(
            self::tiposValidosIds(),
            [self::tipoProductoTerminadoId()]
        )));

        if ($validIds === []) {
            return 0;
        }

        $invalidIds = \App\Models\Insumo::query()
            ->whereNotIn('tipoinsumoid', $validIds)
            ->pluck('insumoid');

        if ($invalidIds->isEmpty()) {
            return 0;
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('loteinsumo')) {
            \App\Models\LoteInsumo::query()->whereIn('insumoid', $invalidIds)->delete();
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('almacen_movimiento')) {
            \Illuminate\Support\Facades\DB::table('almacen_movimiento')
                ->whereIn('insumoid', $invalidIds)
                ->delete();
        }

        return \App\Models\Insumo::query()->whereIn('insumoid', $invalidIds)->delete();
    }

    /**
     * Reasigna insumos con tipos legacy al catálogo oficial y asegura fertilizantes / pesticidas de campo.
     */
    public static function asegurarInsumosCampo(): void
    {
        self::asegurarCatalogosBase();
        self::purgarInsumosConTipoInvalido();
        self::reasignarTiposLegacyEnInsumos();

        $tiposPorSlug = self::tiposOrdenados()
            ->mapWithKeys(fn (\App\Models\TipoInsumo $t) => [
                (string) self::slugFromNombreTipo($t->nombre) => (int) $t->tipoinsumoid,
            ]);

        $almacenId = \App\Support\AlmacenAmbito::scope(
            \App\Models\Almacen::query()->where('activo', true),
            \App\Support\AlmacenAmbito::AGRICOLA
        )->orderBy('almacenid')->value('almacenid');

        $kgId = \App\Models\UnidadMedida::query()->whereRaw('LOWER(nombre) LIKE ?', ['%kilogramo%'])->value('unidadmedidaid')
            ?? \App\Models\UnidadMedida::query()->value('unidadmedidaid');
        $gId = \App\Models\UnidadMedida::query()->whereRaw('LOWER(nombre) LIKE ?', ['%gramo%'])->value('unidadmedidaid')
            ?? $kgId;
        $lId = \App\Models\UnidadMedida::query()->whereRaw('LOWER(nombre) LIKE ?', ['%litro%'])->value('unidadmedidaid')
            ?? $kgId;

        $catalogo = [
            ['nombre' => 'Fertilizante NPK 15-15-15', 'slug' => 'fertilizantes', 'um' => $kgId, 'stock' => 280.0],
            ['nombre' => 'Urea granulada 46%', 'slug' => 'fertilizantes', 'um' => $kgId, 'stock' => 195.0],
            ['nombre' => 'Abono orgánico compost', 'slug' => 'fertilizantes', 'um' => $kgId, 'stock' => 150.0],
            ['nombre' => 'Fungicida cobre hidróxido', 'slug' => 'pesticidas', 'um' => $gId, 'stock' => 126.0],
            ['nombre' => 'Insecticida piretroides', 'slug' => 'pesticidas', 'um' => $lId, 'stock' => 48.0],
            ['nombre' => 'Herbicida glifosato', 'slug' => 'pesticidas', 'um' => $lId, 'stock' => 72.0],
        ];

        foreach ($catalogo as $def) {
            $tipoId = $tiposPorSlug->get($def['slug']);
            if (! $tipoId || ! $def['um']) {
                continue;
            }

            $match = ['nombre' => $def['nombre']];
            if ($almacenId) {
                $match['almacenid'] = $almacenId;
            }

            $insumo = \App\Models\Insumo::query()->firstOrNew($match);
            if (! $insumo->exists) {
                $insumo->stock = $def['stock'];
                $insumo->stockminimo = self::UMBRAL_ALERTA_STOCK;
            } elseif ((float) $insumo->stock <= 0) {
                $insumo->stock = $def['stock'];
            }

            $insumo->tipoinsumoid = $tipoId;
            $insumo->unidadmedidaid = (int) $def['um'];
            if ($almacenId) {
                $insumo->almacenid = (int) $almacenId;
            }
            if (! InsumoImagenCatalogo::esImagenPersonalizada((string) ($insumo->imagenurl ?? ''))) {
                $insumo->imagenurl = InsumoImagenCatalogo::urlPorNombreYTipo($def['nombre'], $def['slug']);
            }
            $insumo->save();
            \App\Services\InsumoEliminacionService::aplicarDosisReferencia($insumo->fresh());
        }

        self::rellenarImagenesInsumosOperativos();
        self::purgarInsumosGranel();
        self::consolidarMaterialSiembraPorNombre();
    }

    /**
     * Fusiona insumos de material de siembra con el mismo nombre (p. ej. tras renombrar «granel»).
     */
    public static function consolidarMaterialSiembraPorNombre(): int
    {
        self::asegurarCatalogosBase();

        $tipoSemillaId = TipoInsumo::query()
            ->where('nombre', self::TIPOS['material_siembra'])
            ->value('tipoinsumoid');

        if (! $tipoSemillaId) {
            return 0;
        }

        $grupos = \App\Models\Insumo::query()
            ->where('tipoinsumoid', (int) $tipoSemillaId)
            ->orderByDesc('stock')
            ->get()
            ->groupBy(fn (\App\Models\Insumo $i) => mb_strtolower(trim((string) $i->nombre)));

        $svc = app(\App\Services\InsumoEliminacionService::class);
        $fusionados = 0;

        foreach ($grupos as $items) {
            if ($items->count() <= 1) {
                continue;
            }

            $canonical = $items->first();
            foreach ($items->skip(1) as $duplicado) {
                $canonical->stock = (float) $canonical->stock + (float) $duplicado->stock;
                $canonical->save();
                $svc->fusionarEn((int) $duplicado->insumoid, (int) $canonical->insumoid);
                $fusionados++;
            }
        }

        return $fusionados;
    }

    /** Elimina insumos cuyo nombre contiene «granel» (catálogo solo verduras por variedad). */
    public static function purgarInsumosGranel(): int
    {
        $ids = \App\Models\Insumo::query()
            ->whereRaw('LOWER(nombre) LIKE ?', ['%granel%'])
            ->pluck('insumoid');

        if ($ids->isEmpty()) {
            return 0;
        }

        $svc = app(\App\Services\InsumoEliminacionService::class);
        foreach ($ids as $id) {
            $insumo = \App\Models\Insumo::query()->find($id);
            if ($insumo) {
                $svc->eliminar($insumo);
            }
        }

        return $ids->count();
    }

    /** Fusiona tipos legacy y elimina categorías retiradas del catálogo. */
    private static function normalizarTiposObsoletos(): void
    {
        $oficialPlagas = TipoInsumo::query()->where('nombre', 'Control de plagas')->first();
        if ($oficialPlagas === null) {
            $oficialPlagas = TipoInsumo::query()->whereIn('nombre', ['Pesticidas', 'Pesticida'])->first();
            if ($oficialPlagas) {
                $oficialPlagas->update(['nombre' => 'Control de plagas']);
            }
        }

        if ($oficialPlagas) {
            $legacyPlagas = TipoInsumo::query()
                ->whereIn('nombre', ['Pesticidas', 'Pesticida'])
                ->where('tipoinsumoid', '!=', $oficialPlagas->tipoinsumoid)
                ->get();

            foreach ($legacyPlagas as $legacy) {
                \App\Models\Insumo::query()
                    ->where('tipoinsumoid', $legacy->tipoinsumoid)
                    ->update(['tipoinsumoid' => $oficialPlagas->tipoinsumoid]);
                $legacy->delete();
            }
        }

        $tiposRiego = TipoInsumo::query()
            ->whereIn('nombre', ['Material de Riego', 'Material de riego', 'Riego'])
            ->get();

        foreach ($tiposRiego as $tipoRiego) {
            $insumoIds = \App\Models\Insumo::query()
                ->where('tipoinsumoid', $tipoRiego->tipoinsumoid)
                ->pluck('insumoid');

            if ($insumoIds->isNotEmpty()) {
                if (\Illuminate\Support\Facades\Schema::hasTable('loteinsumo')) {
                    \App\Models\LoteInsumo::query()->whereIn('insumoid', $insumoIds)->delete();
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('almacen_movimiento')) {
                    \Illuminate\Support\Facades\DB::table('almacen_movimiento')
                        ->whereIn('insumoid', $insumoIds)
                        ->delete();
                }
                \App\Models\Insumo::query()->whereIn('insumoid', $insumoIds)->delete();
            }

            $tipoRiego->delete();
        }
    }

    /** Reasigna tipoinsumoid según el nombre del tipo legacy. */
    public static function reasignarTiposLegacyEnInsumos(): void
    {
        $oficial = self::tiposOrdenados()->keyBy(fn (\App\Models\TipoInsumo $t) => (string) self::slugFromNombreTipo($t->nombre));

        \App\Models\Insumo::query()->with('tipo')->chunkById(100, function ($insumos) use ($oficial) {
            foreach ($insumos as $insumo) {
                $slug = self::slugFromNombreTipo($insumo->tipo?->nombre);
                if ($slug === null) {
                    $slug = self::inferirSlugDesdeNombreInsumo($insumo->nombre);
                }
                if ($slug === null) {
                    continue;
                }

                $tipoOficial = $oficial->get($slug);
                if ($tipoOficial && (int) $insumo->tipoinsumoid !== (int) $tipoOficial->tipoinsumoid) {
                    $insumo->tipoinsumoid = (int) $tipoOficial->tipoinsumoid;
                    $insumo->save();
                }
            }
        });
    }

    private static function inferirSlugDesdeNombreInsumo(string $nombre): ?string
    {
        $n = mb_strtolower($nombre);
        if (str_contains($n, 'fertiliz') || str_contains($n, 'npk') || str_contains($n, 'urea') || str_contains($n, 'abono') || str_contains($n, 'compost')) {
            return 'fertilizantes';
        }
        if (str_contains($n, 'fungicida') || str_contains($n, 'insecticida') || str_contains($n, 'herbicida')
            || str_contains($n, 'plaguicida') || str_contains($n, 'fitosanit')) {
            return 'pesticidas';
        }

        return null;
    }

    public static function rellenarImagenesInsumosOperativos(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasColumn('insumo', 'imagenurl')) {
            return;
        }

        $query = self::aplicarFiltroOperativo(\App\Models\Insumo::query()->with('tipo'));
        $query->chunkById(100, function ($insumos) {
            foreach ($insumos as $insumo) {
                $slug = self::slugFromNombreTipo($insumo->tipo?->nombre);
                $actual = (string) ($insumo->imagenurl ?? '');
                if (InsumoImagenCatalogo::esImagenPersonalizada($actual)) {
                    continue;
                }
                $canonica = InsumoImagenCatalogo::urlPorNombreYTipo((string) $insumo->nombre, $slug);
                if ($actual !== $canonica || InsumoImagenCatalogo::esUrlPlaceholder($actual)) {
                    $insumo->imagenurl = $canonica;
                    $insumo->save();
                }
            }
        });
    }
}
