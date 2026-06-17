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

    /** Licencias para vehículos (sin moto). */
    public static function codigosVehiculo(): array
    {
        return LicenciaConduccionCatalogo::codigosValidos();
    }

    /**
     * @param  list<string>|null  $licencias
     * @return list<string>
     */
    public static function normalizarLista(?array $licencias): array
    {
        if ($licencias === null || $licencias === []) {
            return [];
        }

        $validos = LicenciaConduccionCatalogo::codigosValidos();

        return array_values(array_unique(array_filter(array_map(
            fn ($c) => strtoupper(trim((string) $c)),
            $licencias
        ), fn ($c) => in_array($c, $validos, true))));
    }

    /** Licencia de mayor rango (compatibilidad con campo único). */
    public static function licenciaPrincipal(array $licencias): ?string
    {
        $rango = LicenciaConduccionCatalogo::rangoMaximo($licencias);

        return LicenciaConduccionCatalogo::codigoPorRango($rango);
    }

    /**
     * @return list<string>
     */
    public static function resolverDesdeSolicitud(bool $todas, ?array $seleccionadas): array
    {
        if ($todas) {
            return self::codigosVehiculo();
        }

        return self::normalizarLista($seleccionadas);
    }
}
