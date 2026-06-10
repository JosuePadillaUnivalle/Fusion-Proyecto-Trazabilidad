<?php

namespace App\Support;

use App\Models\PlantillaTransformacion;
use Illuminate\Support\Str;

class PlantillaTransformacionResolver
{
    public static function resolverPorProducto(?string $producto): ?PlantillaTransformacion
    {
        $producto = Str::lower(trim((string) $producto));
        if ($producto === '') {
            return null;
        }

        $plantillas = PlantillaTransformacion::query()
            ->operativas()
            ->with('pasos')
            ->orderBy('nombre')
            ->get();

        $mejor = null;
        $mejorPuntaje = 0;

        foreach ($plantillas as $plantilla) {
            $puntaje = self::puntajeCoincidencia($producto, $plantilla);
            if ($puntaje > $mejorPuntaje) {
                $mejorPuntaje = $puntaje;
                $mejor = $plantilla;
            }
        }

        return $mejorPuntaje >= 2 ? $mejor : null;
    }

    public static function resolverPorId(?int $id): ?PlantillaTransformacion
    {
        if (! $id) {
            return null;
        }

        return PlantillaTransformacion::query()
            ->with(['pasos.proceso', 'pasos.maquina'])
            ->find($id);
    }

    private static function puntajeCoincidencia(string $producto, PlantillaTransformacion $plantilla): int
    {
        $puntaje = 0;
        $ejemplo = Str::lower(trim((string) $plantilla->producto_ejemplo));
        $nombre = Str::lower(trim($plantilla->nombre));

        if ($ejemplo !== '' && ($producto === $ejemplo || Str::contains($producto, $ejemplo) || Str::contains($ejemplo, $producto))) {
            $puntaje += 10;
        }

        if ($nombre !== '' && ($producto === $nombre || Str::contains($producto, $nombre) || Str::contains($nombre, $producto))) {
            $puntaje += 8;
        }

        foreach ($plantilla->palabrasClaveLista() as $clave) {
            $clave = Str::lower(trim($clave));
            if ($clave !== '' && Str::contains($producto, $clave)) {
                $puntaje += 3;
            }
        }

        return $puntaje;
    }
}
