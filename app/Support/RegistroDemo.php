<?php

namespace App\Support;

use App\Models\LoteInsumo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class RegistroDemo
{
    /** @var list<string> */
    private const MARCADORES_LOTE_INSUMO = [
        '[demo-lote-insumo-ejemplo]%',
        '[MOD-INV]%',
        '[DEMO%',
        '[demo%',
    ];

    public static function esLoteInsumoDemo(?LoteInsumo $registro): bool
    {
        if (! $registro) {
            return false;
        }

        return self::textoEsDemoLoteInsumo($registro->observaciones);
    }

    public static function textoEsDemoLoteInsumo(?string $observaciones): bool
    {
        $texto = trim((string) $observaciones);
        if ($texto === '') {
            return false;
        }

        foreach (self::MARCADORES_LOTE_INSUMO as $patron) {
            if (self::coincideMarcador($texto, $patron)) {
                return true;
            }
        }

        return false;
    }

    public static function aplicarFiltroLoteInsumoOperativo(Builder $query): Builder
    {
        return $query->whereNot(function (Builder $sub) {
            foreach (self::MARCADORES_LOTE_INSUMO as $patron) {
                $sub->orWhere('observaciones', 'like', $patron);
            }
        });
    }

    /** Elimina aplicaciones de insumo de demostración y devuelve stock. */
    public static function limpiarLoteInsumosDemo(): int
    {
        if (! Schema::hasTable('loteinsumo')) {
            return 0;
        }

        $eliminados = 0;

        LoteInsumo::query()
            ->where(function (Builder $q) {
                foreach (self::MARCADORES_LOTE_INSUMO as $patron) {
                    $q->orWhere('observaciones', 'like', $patron);
                }
            })
            ->orderBy('loteinsumoid')
            ->chunkById(50, function ($registros) use (&$eliminados) {
                foreach ($registros as $registro) {
                    DB::table('insumo')
                        ->where('insumoid', $registro->insumoid)
                        ->increment('stock', $registro->cantidadusada);

                    $registro->delete();
                    $eliminados++;
                }
            }, 'loteinsumoid');

        return $eliminados;
    }

    private static function coincideMarcador(string $texto, string $patron): bool
    {
        $regex = '/^'.str_replace('\%', '.*', preg_quote($patron, '/')).'$/i';

        return (bool) preg_match($regex, $texto);
    }
}
