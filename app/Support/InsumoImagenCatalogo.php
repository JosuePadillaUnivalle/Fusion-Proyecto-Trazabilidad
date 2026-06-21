<?php

namespace App\Support;

use App\Models\Insumo;

final class InsumoImagenCatalogo
{
    private const DEFAULT_FILE = 'Seeds_on_a_white_background.jpg';

    /** @var array<string, string> nombre normalizado => archivo Commons */
    private const POR_NOMBRE_EXACTO = [
        'tomate perita' => 'Tomato_je.jpg',
        'cebolla morada' => 'Red_onions.jpg',
        'cebolla blanca' => 'Onion_on_White.JPG',
        'zanahoria imperator' => 'Carrots_of_many_colors.jpg',
        'papa harinosa' => 'Patates.jpg',
        'papa amarilla' => 'Patates.jpg',
        'papa rubíola' => 'Potatoes_in_basket.jpg',
        'papa rubiola' => 'Potatoes_in_basket.jpg',
        'papas fritas' => 'French_fries_(dish).jpg',
        'lechuga crespa' => 'Lettuce_in_supermarket.jpg',
        'repollo blanco' => 'Cabbage_and_cross_section_on_white.jpg',
        'fertilizante npk 15-15-15' => 'Fertilizer.jpg',
        'urea granulada 46%' => 'Urea_N46.jpg',
        'abono orgánico compost' => 'Compost.jpg',
        'abono organico compost' => 'Compost.jpg',
        'fungicida cobre hidróxido' => 'Copper(II)_hydroxide.jpg',
        'fungicida cobre hidroxido' => 'Copper(II)_hydroxide.jpg',
        'fungicida cobre plus' => 'Copper(II)_hydroxide.jpg',
        'insecticida piretroides' => 'Blister roundup.jpg',
        'herbicida glifosato' => 'Roundup acker.jpg',
        'herbicida orgánico ecoweed' => 'Roundup acker.jpg',
        'herbicida organico ecoweed' => 'Roundup acker.jpg',
        'semilla certificada tomate' => 'Seeds_on_a_white_background.jpg',
        'bioestimulante foliar' => 'Fertilizer.jpg',
        'aceite vegetal refinado' => 'Sunflower_oil.jpg',
        'harina de trigo industrial' => 'Flour_in_bowl.jpg',
        'azúcar refinada' => 'White_sugar.jpg',
        'azucar refinada' => 'White_sugar.jpg',
        'sal refinada' => 'Salt-NaCl.jpg',
        'vinagre blanco' => 'Balsamic_vinegar_(drops).jpg',
        'agua tratada' => 'Drinking_water.jpg',
        'tomate' => 'Tomato_je.jpg',
        'papa' => 'Patates.jpg',
        'lechuga' => 'Lettuce_in_supermarket.jpg',
        'cebolla' => 'Onion_on_White.JPG',
        'maíz' => 'Corn.jpg',
        'maiz' => 'Corn.jpg',
    ];

    /** @var array<string, string> fragmento (más largo primero al resolver) => archivo Commons */
    private const POR_FRAGMENTO = [
        'cebolla colorada' => 'Red_onions.jpg',
        'cebolla blanca' => 'Onion_on_White.JPG',
        'tomate pera' => 'Tomato_je.jpg',
        'papa rubíola' => 'Potatoes_in_basket.jpg',
        'papa rubiola' => 'Potatoes_in_basket.jpg',
        'papas fritas' => 'French_fries_(dish).jpg',
        'papa industrial' => 'Patates.jpg',
        'zanahoria' => 'Carrots_of_many_colors.jpg',
        'fertilizante' => 'Fertilizer.jpg',
        'bioestimulante' => 'Fertilizer.jpg',
        'fungicida' => 'Copper(II)_hydroxide.jpg',
        'insecticida' => 'Blister roundup.jpg',
        'herbicida' => 'Roundup acker.jpg',
        'piretroides' => 'Blister roundup.jpg',
        'glifosato' => 'Roundup acker.jpg',
        'ecoweed' => 'Roundup acker.jpg',
        'aceite vegetal' => 'Sunflower_oil.jpg',
        'harina de trigo' => 'Flour_in_bowl.jpg',
        'azúcar' => 'White_sugar.jpg',
        'azucar' => 'White_sugar.jpg',
        'sal refinada' => 'Salt-NaCl.jpg',
        'vinagre' => 'Balsamic_vinegar_(drops).jpg',
        'agua tratada' => 'Drinking_water.jpg',
        'mandioca' => 'Cassava_root.jpg',
        'yuca' => 'Cassava_root.jpg',
        'repollo' => 'Cabbage_and_cross_section_on_white.jpg',
        'lechuga' => 'Lettuce_in_supermarket.jpg',
        'tomate' => 'Tomato_je.jpg',
        'papa' => 'Patates.jpg',
        'cebolla' => 'Onion_on_White.JPG',
        'naranja' => 'Orange-Fruit-Pieces.jpg',
        'valencia' => 'Orange-Fruit-Pieces.jpg',
        'mango' => 'Mangos_-_single_and_halved.jpg',
        'tommy' => 'Mangos_-_single_and_halved.jpg',
        'maíz' => 'Corn.jpg',
        'maiz' => 'Corn.jpg',
        'npk' => 'Fertilizer.jpg',
        'urea' => 'Urea_N46.jpg',
        'granulada' => 'Urea_N46.jpg',
        'compost' => 'Compost.jpg',
        'abono' => 'Compost.jpg',
        'orgánico' => 'Compost.jpg',
        'organico' => 'Compost.jpg',
        'cobre' => 'Copper(II)_hydroxide.jpg',
        'semilla' => 'Seeds_on_a_white_background.jpg',
    ];

    private const POR_TIPO_SLUG = [
        'fertilizantes' => 'Fertilizer.jpg',
        'pesticidas' => 'Blister roundup.jpg',
        'material_siembra' => 'Seeds_on_a_white_background.jpg',
        'producto_terminado' => 'Frozen_food_products.jpg',
    ];

    /** Imagen por defecto para productos procesados sin foto propia. */
    private const DEFAULT_PRODUCTO_TERMINADO = 'Frozen_food_products.jpg';

    public static function urlPara(?Insumo $insumo, int $width = 256): string
    {
        if ($insumo === null) {
            return self::wiki(self::DEFAULT_FILE, $width);
        }

        if (InsumoCatalogo::esProductoTerminadoDistribucion($insumo)) {
            $stored = trim((string) ($insumo->imagenurl ?? ''));
            if (self::esImagenPersonalizada($stored)) {
                return self::urlArchivoLocal($stored);
            }

            return self::urlProductoTerminado((string) $insumo->nombre, $width);
        }

        $stored = trim((string) ($insumo->imagenurl ?? ''));
        if ($stored !== '') {
            if (self::esImagenPersonalizada($stored)) {
                return self::urlArchivoLocal($stored);
            }
            if (! self::esUrlPlaceholder($stored)) {
                return self::ajustarAncho($stored, $width);
            }
        }

        return self::urlPorNombreYTipo(
            (string) $insumo->nombre,
            InsumoCatalogo::slugFromNombreTipo($insumo->tipo?->nombre),
            $width
        );
    }

    public static function esImagenPersonalizada(?string $valor): bool
    {
        if ($valor === null || trim($valor) === '') {
            return false;
        }

        $valor = trim($valor);

        return str_starts_with($valor, 'insumos/')
            || str_contains($valor, '/storage/insumos/');
    }

    public static function urlArchivoLocal(string $path): string
    {
        $path = trim($path);
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }

    public static function rutaAlmacenamiento(?string $valor): ?string
    {
        if (! self::esImagenPersonalizada($valor)) {
            return null;
        }

        $valor = trim((string) $valor);
        if (str_starts_with($valor, 'insumos/')) {
            return $valor;
        }

        if (preg_match('#/storage/(insumos/.+)$#', $valor, $coincidencias)) {
            return $coincidencias[1];
        }

        return null;
    }

    public static function urlPorNombreYTipo(string $nombre, ?string $tipoSlug = null, int $width = 256): string
    {
        $n = mb_strtolower(trim($nombre));

        if (isset(self::POR_NOMBRE_EXACTO[$n])) {
            return self::wiki(self::POR_NOMBRE_EXACTO[$n], $width);
        }

        $fragmentos = self::POR_FRAGMENTO;
        uksort($fragmentos, fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($fragmentos as $frag => $file) {
            if (str_contains($n, $frag)) {
                return self::wiki($file, $width);
            }
        }

        if ($tipoSlug !== null && isset(self::POR_TIPO_SLUG[$tipoSlug])) {
            return self::wiki(self::POR_TIPO_SLUG[$tipoSlug], $width);
        }

        return self::wiki(self::DEFAULT_FILE, $width);
    }

    public static function urlProductoTerminado(string $nombre, int $width = 256): string
    {
        $n = mb_strtolower(trim($nombre));

        if (isset(self::POR_NOMBRE_EXACTO[$n])) {
            return self::wiki(self::POR_NOMBRE_EXACTO[$n], $width);
        }

        $fragmentos = self::POR_FRAGMENTO;
        uksort($fragmentos, fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($fragmentos as $frag => $file) {
            if ($frag === 'papa') {
                continue;
            }
            if (str_contains($n, $frag)) {
                return self::wiki($file, $width);
            }
        }

        if (str_contains($n, 'papa') && str_contains($n, 'frita')) {
            return self::wiki('French_fries_(dish).jpg', $width);
        }

        return self::wiki(self::DEFAULT_PRODUCTO_TERMINADO, $width);
    }

    public static function ajustarAncho(string $url, int $width): string
    {
        if (str_contains($url, 'commons.wikimedia.org/wiki/Special:FilePath')) {
            if (preg_match('/width=\d+/', $url)) {
                return (string) preg_replace('/width=\d+/', 'width='.$width, $url);
            }

            return $url.(str_contains($url, '?') ? '&' : '?').'width='.$width;
        }

        if (str_contains($url, '/thumb/') && preg_match('/\/\d+px-/', $url)) {
            return (string) preg_replace('/\/\d+px-/', '/'.$width.'px-', $url);
        }

        return $url;
    }

    public static function esUrlPlaceholder(string $url): bool
    {
        return str_contains($url, 'picsum.photos')
            || str_contains($url, 'loremflickr.com')
            || str_contains($url, 'placehold.co')
            || str_contains($url, 'placeholder.com');
    }

    private static function wiki(string $file, int $width): string
    {
        return 'https://commons.wikimedia.org/wiki/Special:FilePath/'.rawurlencode($file).'?width='.$width;
    }
}
