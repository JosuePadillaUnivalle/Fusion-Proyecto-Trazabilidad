<?php

namespace App\Support;

use App\Models\MaquinaPlanta;
use Illuminate\Support\Str;

final class MaquinaPlantaCodigo
{
    /** Genera un código legible a partir del nombre (editable por el usuario). */
    public static function sugerirDesdeNombre(string $nombre): string
    {
        $palabras = preg_split('/\s+/u', trim($nombre), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($palabras === []) {
            return 'MQ-'.strtoupper(Str::random(4));
        }

        $primera = strtoupper(preg_replace('/[^A-Za-z0-9]/u', '', $palabras[0]) ?? '');
        $base = substr($primera, 0, 6);

        if (count($palabras) > 1) {
            $resto = implode(' ', array_slice($palabras, 1));
            $letras = strtoupper(preg_replace('/[^A-Za-z]/u', '', $resto) ?? '');
            $consonantes = preg_replace('/[AEIOUÁÉÍÓÚÜ]/u', '', $letras) ?? '';
            $sufijo = substr($consonantes !== '' ? $consonantes : $letras, 0, 3);
            $base .= $sufijo;
        }

        $codigo = substr($base, 0, 12);

        return $codigo !== '' ? $codigo : 'MQ-'.strtoupper(Str::random(4));
    }

    /** Código único en `maquina_planta`, opcionalmente ignorando un registro. */
    public static function asegurarUnico(string $codigo, ?int $ignorarId = null): string
    {
        $codigo = strtoupper(trim($codigo));
        if ($codigo === '') {
            $codigo = 'MQ-'.strtoupper(Str::random(4));
        }

        $base = $codigo;
        $sufijo = 2;

        while (self::existe($codigo, $ignorarId)) {
            $codigo = substr($base, 0, max(1, 60 - strlen((string) $sufijo) - 1)).'-'.$sufijo;
            $sufijo++;
        }

        return $codigo;
    }

    public static function resolverParaGuardar(string $nombre, ?string $codigoManual, ?int $ignorarId = null): string
    {
        $manual = trim((string) $codigoManual);

        if ($manual !== '') {
            return self::asegurarUnico($manual, $ignorarId);
        }

        return self::asegurarUnico(self::sugerirDesdeNombre($nombre), $ignorarId);
    }

    private static function existe(string $codigo, ?int $ignorarId): bool
    {
        $query = MaquinaPlanta::query()->where('codigo', $codigo);

        if ($ignorarId !== null) {
            $query->where('maquinaplantaid', '!=', $ignorarId);
        }

        return $query->exists();
    }
}
