<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Convierte firmas data-URL (canvas) en archivos PNG pequeños para DomPDF.
 * DomPDF es muy lento con imágenes base64 embebidas en el HTML.
 */
final class FirmaPdfOptimizador
{
    private const MAX_ANCHO = 360;

    private const MAX_ALTO = 90;

    /** @var array<string, string> */
    private static array $cacheMemoria = [];

    public static function rutaParaDompdf(?string $dataUrl): ?string
    {
        $dataUrl = trim((string) $dataUrl);
        if ($dataUrl === '') {
            return null;
        }

        $clave = sha1($dataUrl);
        if (isset(self::$cacheMemoria[$clave])) {
            return self::$cacheMemoria[$clave];
        }

        if (! str_starts_with($dataUrl, 'data:image/')) {
            return null;
        }

        if (! extension_loaded('gd')) {
            return $dataUrl;
        }

        $coma = strpos($dataUrl, ',');
        if ($coma === false) {
            return null;
        }

        $binario = base64_decode(substr($dataUrl, $coma + 1), true);
        if ($binario === false || $binario === '') {
            return null;
        }

        $imagen = @imagecreatefromstring($binario);
        if ($imagen === false) {
            return null;
        }

        $ancho = imagesx($imagen);
        $alto = imagesy($imagen);
        if ($ancho < 1 || $alto < 1) {
            imagedestroy($imagen);

            return null;
        }

        $escala = min(self::MAX_ANCHO / $ancho, self::MAX_ALTO / $alto, 1.0);
        $nuevoAncho = max(1, (int) round($ancho * $escala));
        $nuevoAlto = max(1, (int) round($alto * $escala));

        $redimensionada = imagecreatetruecolor($nuevoAncho, $nuevoAlto);
        if ($redimensionada === false) {
            imagedestroy($imagen);

            return null;
        }

        imagealphablending($redimensionada, false);
        imagesavealpha($redimensionada, true);
        $transparente = imagecolorallocatealpha($redimensionada, 0, 0, 0, 127);
        imagefill($redimensionada, 0, 0, $transparente);

        imagecopyresampled(
            $redimensionada,
            $imagen,
            0,
            0,
            0,
            0,
            $nuevoAncho,
            $nuevoAlto,
            $ancho,
            $alto
        );
        imagedestroy($imagen);

        $relativa = 'tmp/firmas_pdf/'.Str::substr($clave, 0, 16).'.png';
        $disk = Storage::disk('local');
        $disk->makeDirectory('tmp/firmas_pdf');

        ob_start();
        imagepng($redimensionada, null, 6);
        $png = ob_get_clean();
        imagedestroy($redimensionada);

        if ($png === false || $png === '') {
            return null;
        }

        $disk->put($relativa, $png);
        $absoluta = str_replace('\\', '/', $disk->path($relativa));
        self::$cacheMemoria[$clave] = $absoluta;

        return $absoluta;
    }
}
