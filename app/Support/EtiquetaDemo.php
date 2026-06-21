<?php

namespace App\Support;

class EtiquetaDemo
{
    public static function esDemo(?string $texto): bool
    {
        if ($texto === null || trim($texto) === '') {
            return false;
        }

        $t = mb_strtolower(trim($texto));

        if (preg_match('/^(test|demo|prueba)(\s*[\d._-]*)?$/u', $t)) {
            return true;
        }

        return (bool) preg_match('/\b(test|demo|prueba)\b/u', $t);
    }
}
