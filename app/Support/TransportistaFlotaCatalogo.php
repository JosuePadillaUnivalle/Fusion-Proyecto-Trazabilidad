<?php

namespace App\Support;

final class TransportistaFlotaCatalogo
{
    public const AGRICOLA = 'agricola';

    public const PLANTA = 'planta';

    /** @return array<string, string> */
    public static function etiquetas(): array
    {
        return [
            self::AGRICOLA => 'Transportista agrícola',
            self::PLANTA => 'Transportista de planta',
        ];
    }

    public static function etiqueta(?string $ambito): string
    {
        return self::etiquetas()[$ambito ?? ''] ?? 'Sin categoría';
    }

    /** @return list<string> */
    public static function valores(): array
    {
        return [self::AGRICOLA, self::PLANTA];
    }

    public static function categoriaCorta(?string $ambito): string
    {
        return match ($ambito) {
            self::AGRICOLA => 'Agrícola',
            self::PLANTA => 'Planta',
            default => '—',
        };
    }

    public static function badgeClase(?string $ambito): string
    {
        return match ($ambito) {
            self::AGRICOLA => 'badge-success',
            self::PLANTA => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
