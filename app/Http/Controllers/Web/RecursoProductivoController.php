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

        $criticos  = $insumos->filter(fn ($i) => $i->stockBajo())->count();
        $valorTotal = $insumos->sum(fn ($i) => ($i->stock ?? 0) * ($i->preciounitario ?? 0));

        $stats = [
            'cultivos'    => $cultivos->count(),
            'insumos'     => $insumos->count(),
            'criticos'    => $criticos,
            'valor_total' => $valorTotal,
        ];

        return view('recursos_productivos.index', compact('cultivos', 'insumos', 'stats'));
    }
}
