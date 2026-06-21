<?php

namespace App\Support;

final class TransportistaFlotaCatalogo
{
    public const AGRICOLA = 'agricola';

    public const PLANTA = 'planta';

    public const MAYORISTA = 'mayorista';

    /** @return array<string, string> */
    public static function etiquetas(): array
    {
        return [
            self::AGRICOLA => 'Transportista agrícola',
            self::PLANTA => 'Transportista de planta',
            self::MAYORISTA => 'Transportista mayorista',
        ];
    }

    public static function etiqueta(?string $ambito): string
    {
        return self::etiquetas()[$ambito ?? ''] ?? 'Sin categoría';
    }

    /** @return list<string> */
    public static function valores(): array
    {
        return [self::AGRICOLA, self::PLANTA, self::MAYORISTA];
    }

    public static function categoriaCorta(?string $ambito): string
    {
        return match ($ambito) {
            self::AGRICOLA => 'Agrícola',
            self::PLANTA => 'Planta',
            self::MAYORISTA => 'Mayorista',
            default => '—',
        };
    }

    public static function badgeClase(?string $ambito): string
    {
        return match ($ambito) {
            self::AGRICOLA => 'badge-success',
            self::PLANTA => 'badge-danger',
            self::MAYORISTA => 'badge-warning',
            default => 'badge-secondary',
        };
    }

    /** Orden de listado: agrícola → planta → mayorista */
    public static function indiceOrden(?string $ambito): int
    {
        return match ($ambito) {
            self::AGRICOLA => 1,
            self::PLANTA => 2,
            self::MAYORISTA => 3,
            default => 9,
        };
    }
}
