<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait FiltersCatalogoSimple
{
    protected function aplicarFiltroBuscar(Builder $query, Request $request, array $columnas = ['nombre']): Builder
    {
        if ($request->filled('buscar')) {
            $buscar = '%'.trim((string) $request->buscar).'%';
            $query->where(function ($q) use ($buscar, $columnas) {
                foreach ($columnas as $i => $columna) {
                    $i === 0
                        ? $q->where($columna, 'like', $buscar)
                        : $q->orWhere($columna, 'like', $buscar);
                }
            });
        }

        return $query;
    }
}
