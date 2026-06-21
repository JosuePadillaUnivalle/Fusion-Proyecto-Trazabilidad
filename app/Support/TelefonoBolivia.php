<?php

namespace App\Support;

final class TelefonoBolivia
{
    public const PREFIJO = '+591';

    /** Formato: +591 y número local separado por un espacio. */
    public const PATTERN = '/^\+591 [0-9]{6,15}$/';

    public static function normalizar(?string $telefono): ?string
    {
        if ($telefono === null || trim($telefono) === '') {
            return null;
        }

        $raw = trim(preg_replace('/\s+/u', ' ', $telefono) ?? '');

        if (preg_match(self::PATTERN, $raw)) {
            return $raw;
        }

        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '591')) {
            $digits = substr($digits, 3);
        }

        $digits = ltrim($digits, '0');

        if ($digits === '') {
            return null;
        }

        return self::PREFIJO.' '.$digits;
    }
}
