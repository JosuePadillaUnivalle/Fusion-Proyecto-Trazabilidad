<?php

namespace App\Support;

use App\Models\Lote;
use App\Models\Usuario;
use Illuminate\Support\Collection;

final class UsuarioGestionResumen
{
    public static function cantidadLotesComoResponsable(Usuario $usuario): int
    {
        if ($usuario->relationLoaded('lotes')) {
            return $usuario->lotes->count();
        }

        if (isset($usuario->lotes_count)) {
            return (int) $usuario->lotes_count;
        }

        return $usuario->lotes()->count();
    }

    /** @return list<string> */
    public static function nombresLotesComoResponsable(Usuario $usuario): array
    {
        $lotes = self::lotesComoResponsable($usuario);

        return $lotes
            ->map(fn (Lote $lote) => (string) $lote->nombre)
            ->values()
            ->all();
    }

    /** @return Collection<int, Lote> */
    public static function lotesComoResponsable(Usuario $usuario): Collection
    {
        if ($usuario->relationLoaded('lotes')) {
            return $usuario->lotes;
        }

        return $usuario->lotes()->orderByDesc('loteid')->get();
    }

    /** @return Collection<int, Lote> */
    public static function lotesRecientesParaDetalle(Usuario $usuario, int $limite = 10): Collection
    {
        if ($usuario->relationLoaded('lotes')) {
            return $usuario->lotes
                ->sortByDesc('loteid')
                ->take($limite)
                ->values();
        }

        return $usuario->lotes()
            ->with(['cultivo', 'estadoTipo'])
            ->orderByDesc('loteid')
            ->limit($limite)
            ->get();
    }
}
