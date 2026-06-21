<?php

namespace App\Support;

use App\Models\Almacen;

final class AlmacenNombreCatalogo
{
    /** @return array{etiqueta: string, codigo: string} */
    public static function prefijoAmbito(string $ambito): array
    {
        return match ($ambito) {
            AlmacenAmbito::AGRICOLA => ['etiqueta' => 'Almacén Agrícola', 'codigo' => 'AGR'],
            AlmacenAmbito::PLANTA => ['etiqueta' => 'Almacén Planta', 'codigo' => 'PLA'],
            AlmacenAmbito::MAYORISTA => ['etiqueta' => 'Almacén Mayorista', 'codigo' => 'MAY'],
            AlmacenAmbito::PUNTO_VENTA => ['etiqueta' => 'Almacén PDV', 'codigo' => 'PDV'],
            default => ['etiqueta' => 'Almacén', 'codigo' => 'ALM'],
        };
    }

    public static function generar(float $lat, float $lng, string $ambito, ?string $zona = null): string
    {
        $pref = self::prefijoAmbito($ambito);

        return self::formatear($pref['etiqueta'], $pref['codigo'], $lat, $lng, $zona);
    }

    public static function formatear(string $etiqueta, string $codigoAmbito, float $lat, float $lng, ?string $zona = null, ?string $identificador = null): string
    {
        $zonaTxt = trim((string) $zona);
        if ($zonaTxt === '') {
            $zonaTxt = 'Ubicación en mapa';
        }
        if (mb_strlen($zonaTxt) > 50) {
            $zonaTxt = mb_substr($zonaTxt, 0, 47).'…';
        }

        $id = $identificador ?? self::identificadorUnico($codigoAmbito, $lat, $lng);
        $nombre = $etiqueta.', '.$zonaTxt.' - '.$id;

        return mb_substr($nombre, 0, 100);
    }

    public static function nombreDesdeRegistro(Almacen $almacen): string
    {
        $ambito = self::ambitoEfectivo($almacen);
        $pref = self::prefijoAmbito($ambito);
        $coords = UbicacionGpsParser::coordsOrDefault($almacen->ubicacion);
        $zona = self::zonaDesdeRegistro($almacen);
        $id = self::identificadorEstable($pref['codigo'], (int) $almacen->almacenid);

        return self::formatear($pref['etiqueta'], $pref['codigo'], $coords['lat'], $coords['lng'], $zona, $id);
    }

    public static function nombreSemilla(string $ambito, string $semilla, float $lat, float $lng, ?string $zona = null): string
    {
        $pref = self::prefijoAmbito($ambito);
        $id = self::identificadorSemilla($pref['codigo'], $semilla);

        return self::formatear($pref['etiqueta'], $pref['codigo'], $lat, $lng, $zona, $id);
    }

    public static function identificadorEstable(string $codigoAmbito, int $almacenId): string
    {
        $sufijo = strtoupper(substr(md5($codigoAmbito.'#'.$almacenId), 0, 5));

        return strtoupper($codigoAmbito).'_'.$sufijo;
    }

    public static function identificadorSemilla(string $codigoAmbito, string $semilla): string
    {
        $sufijo = strtoupper(substr(md5($codigoAmbito.':'.mb_strtolower(trim($semilla))), 0, 5));

        return strtoupper($codigoAmbito).'_'.$sufijo;
    }

    public static function zonaDesdeRegistro(Almacen $almacen): string
    {
        $limpia = UbicacionGpsParser::limpiarCoordenadasDeTexto($almacen->ubicacion);
        $legible = UbicacionGpsParser::direccionLegible($limpia ?? $almacen->ubicacion);
        if ($legible !== null && $legible !== '') {
            return $legible;
        }

        $visible = UbicacionGpsParser::textoDireccionVisible($almacen->ubicacion, null, (int) $almacen->almacenid);
        if ($visible !== null && $visible !== '') {
            return $visible;
        }

        return UbicacionGpsParser::fallbackSantaCruz((int) $almacen->almacenid, null)['direccion'];
    }

    public static function ambitoEfectivo(Almacen $almacen): string
    {
        $ambito = (string) ($almacen->ambito ?? '');
        if (AlmacenAmbito::esValido($ambito)) {
            return $ambito;
        }

        $nombre = mb_strtolower(trim((string) $almacen->nombre));
        if (str_contains($nombre, 'mayorista') || str_contains($nombre, 'pirai')) {
            return AlmacenAmbito::MAYORISTA;
        }
        if (str_contains($nombre, 'planta') || str_contains($nombre, 'procesadora')) {
            return AlmacenAmbito::PLANTA;
        }
        if (str_contains($nombre, 'pdv') || str_contains($nombre, 'punto de venta')) {
            return AlmacenAmbito::PUNTO_VENTA;
        }

        return AlmacenAmbito::AGRICOLA;
    }

    public static function identificadorUnico(string $codigoAmbito, float $lat, float $lng): string
    {
        $latKey = number_format(abs($lat), 4, '.', '');
        $lngKey = number_format(abs($lng), 4, '.', '');
        $sufijo = strtoupper(substr(md5($latKey.$lngKey.(string) microtime(true)), 0, 5));

        return strtoupper($codigoAmbito).'_'.$sufijo;
    }
}
