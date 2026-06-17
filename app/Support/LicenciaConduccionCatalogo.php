<?php

namespace App\Support;

final class LicenciaConduccionCatalogo
{
    /** Mayor rango = puede conducir vehículos que exigen licencias menores. */
    private const RANGO = [
        'P' => 2,
        'A' => 3,
        'B' => 4,
        'C' => 5,
        'T' => 5,
    ];

    public static function puedeConducir(?string $licenciaConductor, ?string $licenciaRequerida): bool
    {
        $requerida = self::normalizar($licenciaRequerida);
        if ($requerida === null) {
            return true;
        }

        $conductor = self::normalizar($licenciaConductor);
        if ($conductor === null) {
            return false;
        }

        return (self::RANGO[$conductor] ?? 0) >= (self::RANGO[$requerida] ?? 99);
    }

    /**
     * @param  list<string>  $licenciasConductor
     */
    public static function puedeConducirConLicencias(array $licenciasConductor, ?string $licenciaRequerida): bool
    {
        $requerida = self::normalizar($licenciaRequerida);
        if ($requerida === null) {
            return true;
        }

        $licencias = TiposLicenciaBolivia::normalizarLista($licenciasConductor);
        if ($licencias === []) {
            return false;
        }

        $rangoReq = self::RANGO[$requerida] ?? 99;

        foreach ($licencias as $licencia) {
            if ((self::RANGO[$licencia] ?? 0) >= $rangoReq) {
                return true;
            }
        }

        return false;
    }

    public static function mensajeBloqueo(?string $licenciaConductor, ?string $licenciaRequerida): string
    {
        return self::mensajeBloqueoMultiples(
            $licenciaConductor !== null && $licenciaConductor !== '' ? [$licenciaConductor] : [],
            $licenciaRequerida
        );
    }

    /**
     * @param  list<string>  $licenciasConductor
     */
    public static function mensajeBloqueoMultiples(array $licenciasConductor, ?string $licenciaRequerida): string
    {
        $req = self::normalizar($licenciaRequerida) ?? '?';
        $licencias = TiposLicenciaBolivia::normalizarLista($licenciasConductor);

        if ($licencias === []) {
            return "El transportista no tiene licencias registradas. Este vehículo requiere licencia {$req}.";
        }

        $etiquetas = array_map(
            fn ($c) => TiposLicenciaBolivia::etiqueta($c) ?? $c,
            $licencias
        );

        return 'Las licencias registradas ('.implode(', ', $etiquetas)
            .") no autorizan este vehículo (requiere licencia {$req} o superior).";
    }

    /** @return list<string> */
    public static function codigosAutorizados(?string $licenciaConductor): array
    {
        return self::codigosAutorizadosMultiples(
            $licenciaConductor !== null && $licenciaConductor !== '' ? [$licenciaConductor] : []
        );
    }

    /**
     * @param  list<string>  $licenciasConductor
     * @return list<string>
     */
    public static function codigosAutorizadosMultiples(array $licenciasConductor): array
    {
        $licencias = TiposLicenciaBolivia::normalizarLista($licenciasConductor);
        if ($licencias === []) {
            return [];
        }

        $autorizados = [];
        foreach ($licencias as $licencia) {
            $rangoMax = self::RANGO[$licencia] ?? 0;
            if ($rangoMax <= 0) {
                continue;
            }
            foreach (self::RANGO as $codigo => $rango) {
                if ($rango <= $rangoMax) {
                    $autorizados[$codigo] = true;
                }
            }
        }

        return array_keys($autorizados);
    }

    /**
     * @param  list<string>  $licencias
     */
    public static function rangoMaximo(array $licencias): int
    {
        $max = 0;
        foreach (TiposLicenciaBolivia::normalizarLista($licencias) as $licencia) {
            $max = max($max, self::RANGO[$licencia] ?? 0);
        }

        return $max;
    }

    public static function codigoPorRango(int $rango): ?string
    {
        if ($rango <= 0) {
            return null;
        }

        $mejor = null;
        foreach (self::RANGO as $codigo => $valor) {
            if ($valor === $rango) {
                return $codigo;
            }
            if ($valor <= $rango) {
                $mejor = $codigo;
            }
        }

        return $mejor;
    }

    /** @return list<string> */
    public static function codigosValidos(): array
    {
        return array_keys(self::RANGO);
    }

    private static function normalizar(?string $codigo): ?string
    {
        if ($codigo === null || trim($codigo) === '') {
            return null;
        }

        $codigo = strtoupper(trim($codigo));

        return isset(self::RANGO[$codigo]) ? $codigo : null;
    }
}
