<?php

namespace App\Support;

final class TiposLicenciaBolivia
{
    public static function todos(): array
    {
        return config('tipos_licencia_bolivia', []);
    }

    /** @return list<string> */
    public static function codigos(): array
    {
        return array_keys(self::todos());
    }

    public static function etiqueta(?string $codigo): ?string
    {
        if ($codigo === null || $codigo === '') {
            return null;
        }

        $tipos = self::todos();

        if (! isset($tipos[$codigo])) {
            return $codigo;
        }

        return $codigo.' — '.$tipos[$codigo];
    }
}
