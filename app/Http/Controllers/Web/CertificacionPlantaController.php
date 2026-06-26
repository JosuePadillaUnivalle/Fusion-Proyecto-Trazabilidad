<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EvaluacionFinalLoteProduccion;
use App\Support\CertificacionIndexService;
use App\Support\UsuarioRol;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificacionPlantaController extends Controller
{
    public function __construct(
        private CertificacionIndexService $indexService
    ) {}

    public function index(Request $request): View
    {
        abort_if(UsuarioRol::esOperarioPlanta($request->user()), 403, 'Solo el jefe de planta o administración pueden certificar lotes.');
        $datos = $this->indexService->datosPlanta([
            'q' => $request->string('q')->trim()->toString(),
            'producto' => $request->string('producto')->trim()->toString(),
            'resultado' => $request->string('resultado')->trim()->toString(),
            'desde' => $request->string('desde')->trim()->toString(),
            'hasta' => $request->string('hasta')->trim()->toString(),
        ]);

        return view('certificaciones-planta.index', [
            'lotesPendientes' => $datos['pendientes'],
            'certificados' => $datos['evaluaciones'],
            'stats' => $datos['stats'],
            'filtros' => $datos['filtros'],
        ]);
    }

    public function show(EvaluacionFinalLoteProduccion $evaluacionFinalLoteProduccion): View
    {
        abort_if(UsuarioRol::esOperarioPlanta(auth()->user()), 403, 'Solo el jefe de planta o administración pueden certificar lotes.');
        $evaluacionFinalLoteProduccion->load([
            'loteProduccionPedido.pedido',
            'loteProduccionPedido.plantillaTransformacion',
            'loteProduccionPedido.unidadMedida',
            'inspector',
        ]);

        return view('certificaciones-planta.show', ['eval' => $evaluacionFinalLoteProduccion]);
    }
}
