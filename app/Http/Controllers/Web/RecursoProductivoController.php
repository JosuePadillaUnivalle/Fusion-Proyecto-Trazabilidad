<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cultivo;
use App\Models\Insumo;
use App\Support\InsumoCatalogo;
use Illuminate\View\View;

class RecursoProductivoController extends Controller
{
    public function index(): View
    {
        $cultivos = Cultivo::orderBy('nombre')->get();

        $insumos = Insumo::with(['tipo', 'unidadMedida'])
            ->orderBy('nombre')
            ->get();

        $criticos = $insumos->filter(fn ($i) => $i->stockBajo())->count();
        $enAtencion = $insumos->filter(fn ($i) => InsumoCatalogo::stockMedio((float) $i->stock))->count();

        $stats = [
            'cultivos'    => $cultivos->count(),
            'insumos'     => $insumos->count(),
            'criticos'    => $criticos,
            'en_atencion' => $enAtencion,
            'umbral'      => InsumoCatalogo::UMBRAL_ALERTA_STOCK,
        ];

        return view('recursos_productivos.index', compact('cultivos', 'insumos', 'stats'));
    }
}
