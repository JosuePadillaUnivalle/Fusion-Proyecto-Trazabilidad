<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\TrazabilidadProductoPdvService;
use Illuminate\View\View;

class TrazabilidadPublicaController extends Controller
{
    public function show(string $codigo, TrazabilidadProductoPdvService $service): View
    {
        $reporte = $service->reportePorCodigo($codigo);

        abort_if($reporte === null, 404);

        return view('trazabilidad.publica', compact('reporte'));
    }
}
