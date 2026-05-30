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
        'pesticidas' => 'Pesticidas',
        'material_riego' => 'Material de Riego',
    ];

    /** Nombres de unidad (clave para buscar en BD) por tipo */
    public const UNIDADES_POR_TIPO = [
        'material_siembra' => ['Kilogramo', 'Gramo', 'Quintal', 'Unidad'],
        'fertilizantes' => ['Kilogramo', 'Gramo', 'Quintal', 'Litro'],
        'pesticidas' => ['Kilogramo', 'Gramo', 'Mililitro', 'Litro'],
        'material_riego' => ['Metro', 'Unidad'],
    ];

    /** @var array<string, string> */
    private const LEGACY_TIPO_SLUG = [
        'semilla' => 'material_siembra',
        'material de siembra' => 'material_siembra',
        'fertilizante' => 'fertilizantes',
        'pesticida' => 'pesticidas',
        'plaguicida' => 'pesticidas',
        'material de riego' => 'material_riego',
        'riego' => 'material_riego',
        'herramienta' => 'material_riego',
        'equipo' => 'material_riego',
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
        foreach (self::TIPOS as $nombre) {
            TipoInsumo::updateOrCreate(['nombre' => $nombre], ['nombre' => $nombre]);
        }

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
}
