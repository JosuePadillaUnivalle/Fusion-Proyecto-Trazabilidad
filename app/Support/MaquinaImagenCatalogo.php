<?php

namespace App\Support;

/**
 * URLs públicas (HTTPS) de referencia por código de máquina.
 * Fallback cuando storage/app/public/maquinas_planta no existe en el host (p. ej. Railway).
 */
final class MaquinaImagenCatalogo
{
    /**
     * @return array<string, string> codigo => url https
     */
    public static function urlsPorCodigo(): array
    {
        // Special:FilePath redirige al archivo real en Commons (más estable que thumbs hardcodeados).
        return [
            'L-100' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Washing_vegetables.jpg?width=640',
            'BC-20' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Packages_on_UPS_conveyor_belt.jpg?width=640',
            'SE-10' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Continuous_Band_Sealer_Machine.jpg?width=640',
            'BD-500' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Analytical_balance_mettler_ae-260.jpg?width=640',
            'MX-200' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Industrial_planetary_mixer.jpg?width=640',
            'EX-300' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Pasta_machine.jpg?width=640',
            'MD-400' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Old-fashioned_cookie_cutters_61.jpg?width=640',
            'SC-500' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Food_dehydrator.jpg?width=640',
            'TR-600' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Pizza_in_deep_fat_fryer_2.jpg?width=640',
            'EV-700' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Krostitzer_Brauerei_-_Abfuellung.jpg?width=640',
            'ET-800' => 'https://commons.wikimedia.org/wiki/Special:FilePath/Label_dispenser.jpg?width=640',
        ];
    }

    public static function urlPorCodigo(?string $codigo): ?string
    {
        $codigo = strtoupper(trim((string) $codigo));
        if ($codigo === '') {
            return null;
        }

        return self::urlsPorCodigo()[$codigo] ?? null;
    }

    public static function urlPorNombre(?string $nombre): ?string
    {
        $nombre = strtoupper(trim((string) $nombre));
        if ($nombre === '') {
            return null;
        }

        foreach (array_keys(self::urlsPorCodigo()) as $codigo) {
            if (str_contains($nombre, $codigo)) {
                return self::urlPorCodigo($codigo);
            }
        }

        return null;
    }
}
