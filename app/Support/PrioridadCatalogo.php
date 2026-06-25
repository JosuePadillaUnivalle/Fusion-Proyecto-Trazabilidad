<?php

namespace App\Support;

final class PrioridadCatalogo
{
    public static function badgeClase(?string $nombre): string
    {
        return match (mb_strtolower(trim($nombre ?? ''))) {
            'urgente', 'alta' => 'danger',
            'media' => 'warning',
            'baja' => 'secondary',
            default => 'light',
        };
    }
}
