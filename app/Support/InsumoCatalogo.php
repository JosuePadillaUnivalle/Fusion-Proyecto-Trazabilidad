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

    public static function esInsumoOperativo(?\App\Models\Insumo $insumo): bool
    {
        if ($insumo === null) {
            return false;
        }

        return in_array((int) $insumo->tipoinsumoid, self::tiposValidosIds(), true);
    }

    public static function asegurarInsumoOperativo(\App\Models\Insumo $insumo): void
    {
        if (! self::esInsumoOperativo($insumo)) {
            abort(404, 'El registro no corresponde al catálogo de insumos operativos.');
        }
    }

    /**
     * Elimina insumos cuyo tipo no es uno de los cuatro oficiales y sus aplicaciones en lote.
     */
    public static function purgarInsumosConTipoInvalido(): int
    {
        self::asegurarCatalogosBase();
        $validIds = self::tiposValidosIds();

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
        }

        self::rellenarImagenesInsumosOperativos();
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
