<?php

namespace App\Support;

use App\Models\Cultivo;
use App\Models\Insumo;

final class LoteCultivoResolver
{
    public static function resolver(?int $insumoid): int
    {
        if ($insumoid) {
            $desdeInsumo = self::desdeInsumo($insumoid);
            if ($desdeInsumo !== null) {
                return $desdeInsumo;
            }
        }

        return self::cultivoPendienteId();
    }

    private static function desdeInsumo(int $insumoid): ?int
    {
        $insumo = Insumo::query()->find($insumoid);
        if ($insumo === null) {
            return null;
        }

        $nombreDerivado = PedidoCatalogo::cultivoDesdeInsumo($insumo);
        $clave = mb_strtolower(trim($nombreDerivado));

        $existente = Cultivo::query()
            ->get()
            ->filter(function (Cultivo $c) use ($clave, $insumo) {
                $nombre = mb_strtolower(trim($c->nombre));
                if ($nombre === '' || $clave === '') {
                    return false;
                }

                return $nombre === $clave
                    || str_contains($clave, $nombre)
                    || str_contains(mb_strtolower($insumo->nombre), $nombre);
            })
            ->sortByDesc(fn (Cultivo $c) => mb_strlen(trim($c->nombre)))
            ->first();

        if ($existente !== null) {
            return (int) $existente->cultivoid;
        }

        $dosis = CultivoSiembraCatalogo::sugerenciaParaInsumo($insumo, 1.0);

        $cultivo = Cultivo::create([
            'nombre' => $nombreDerivado,
            'dosis_siembra_por_ha' => $dosis['tiene_dosis'] ? $dosis['por_ha'] : null,
            'dosis_siembra_unidad' => $dosis['tiene_dosis'] ? $dosis['unidad'] : null,
        ]);

        return (int) $cultivo->cultivoid;
    }

    private static function cultivoPendienteId(): int
    {
        $nombre = 'Pendiente de definir';
        $id = Cultivo::query()
            ->whereRaw('LOWER(TRIM(nombre)) = ?', [mb_strtolower($nombre)])
            ->value('cultivoid');

        if ($id) {
            return (int) $id;
        }

        return (int) Cultivo::create(['nombre' => $nombre])->cultivoid;
    }
}
