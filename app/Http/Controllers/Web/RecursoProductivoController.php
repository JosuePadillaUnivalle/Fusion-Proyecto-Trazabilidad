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

        return view('recursos_productivos.index', compact('cultivos', 'insumos'));
    }
}

