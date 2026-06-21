<?php

namespace App\Support;

use App\Models\Almacen;

/** Referencias estables a almacenes demo (no dependen del nombre visible). */
final class AlmacenDemoReferencia
{
    public const SEMILLA_CENTRAL = '_seed:demo_central';

    public const SEMILLA_NORTE = '_seed:demo_norte';

    public const SEMILLA_PLANTA = '_seed:demo_planta';

    public static function central(): ?Almacen
    {
        return self::porSemilla(self::SEMILLA_CENTRAL);
    }

    public static function norte(): ?Almacen
    {
        return self::porSemilla(self::SEMILLA_NORTE);
    }

    public static function planta(): ?Almacen
    {
        return self::porSemilla(self::SEMILLA_PLANTA);
    }

    public static function porSemilla(string $semilla): ?Almacen
    {
        return Almacen::query()->where('descripcion', $semilla)->first();
    }

    /** @return array{lat: float, lng: float, zona: string, ambito: string, semilla: string, tipo: string} */
    public static function definicionCentral(): array
    {
        return [
            'semilla' => self::SEMILLA_CENTRAL,
            'ambito' => AlmacenAmbito::AGRICOLA,
            'tipo' => 'Central',
            'lat' => -17.7833,
            'lng' => -63.1821,
            'zona' => 'Av. Cristo Redentor, Santa Cruz',
        ];
    }

    /** @return array{lat: float, lng: float, zona: string, ambito: string, semilla: string, tipo: string} */
    public static function definicionNorte(): array
    {
        return [
            'semilla' => self::SEMILLA_NORTE,
            'ambito' => AlmacenAmbito::AGRICOLA,
            'tipo' => 'Secundario',
            'lat' => -17.7398,
            'lng' => -63.1689,
            'zona' => 'Av. Beni, Zona Norte, Santa Cruz',
        ];
    }

    /** @return array{lat: float, lng: float, zona: string, ambito: string, semilla: string, tipo: string} */
    public static function definicionPlanta(): array
    {
        return [
            'semilla' => self::SEMILLA_PLANTA,
            'ambito' => AlmacenAmbito::PLANTA,
            'tipo' => 'Planta',
            'lat' => -17.8025,
            'lng' => -63.1458,
            'zona' => 'Parque Industrial, Santa Cruz',
        ];
    }

    public static function ubicacionConGps(float $lat, float $lng, string $zona): string
    {
        return $zona.' · GPS '.number_format($lat, 5, '.', '').', '.number_format($lng, 5, '.', '');
    }
}
