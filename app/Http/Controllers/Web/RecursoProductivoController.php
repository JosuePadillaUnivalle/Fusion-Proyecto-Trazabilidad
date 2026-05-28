<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cultivo;
use App\Models\Insumo;
use Illuminate\View\View;

class RecursoProductivoController extends Controller
{
    public function index(): View
    {
        $cultivos = Cultivo::orderBy('nombre')->get();
        $insumos = Insumo::with(['tipo', 'unidadMedida', 'actorAbastecimiento'])
            ->orderBy('nombre')
            ->get();

        $stats = [
            'cultivos' => $cultivos->count(),
            'insumos' => $insumos->count(),
            'stock_bajo' => $insumos->filter(
                fn ($i) => (float) $i->stock <= (float) $i->stockminimo
            )->count(),
            'stock_normal' => $insumos->filter(
                fn ($i) => (float) $i->stock > (float) $i->stockminimo
            )->count(),
            'tipos' => $insumos->pluck('tipo.nombre')->filter()->unique()->count(),
            'valor_total' => $insumos->sum(
                fn ($i) => (float) $i->stock * (float) ($i->preciounitario ?? 0)
            ),
        ];

        $tiposFiltro = $insumos->pluck('tipo.nombre')->filter()->unique()->sort()->values();

        return view('recursos_productivos.index', compact('cultivos', 'insumos', 'stats', 'tiposFiltro'));
    }
}

