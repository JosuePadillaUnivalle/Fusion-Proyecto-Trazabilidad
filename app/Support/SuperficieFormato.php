<?php

namespace App\Support;

final class SuperficieFormato
{
    public static function etiqueta(float|int|string|null $valor, int $decimales = 2): string
    {
        if ($valor === null || $valor === '') {
            return '—';
        }

        $n = (float) $valor;
        $numero = number_format($n, $decimales, ',', '.');
        $unidad = abs($n - 1.0) < 0.00001 ? 'hectárea' : 'hectáreas';

        return $numero.' '.$unidad;
    }

    public static function nombreUnidadLegible(?string $nombre): string
    {
        if ($nombre === null || trim($nombre) === '') {
            return 'hectáreas';
        }

        $lower = mb_strtolower(trim($nombre));
        if (in_array($lower, ['ha', 'hectarea', 'hectárea', 'hectareas', 'hectáreas'], true)) {
            return 'hectáreas';
        }

        return $nombre;
    }
}
