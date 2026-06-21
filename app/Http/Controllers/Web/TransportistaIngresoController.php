<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\DashboardFiltros;
use App\Support\TransporteIngresoService;
use App\Support\UsuarioRol;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransportistaIngresoController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && (UsuarioRol::esTransportista($user) || $user->role === 'admin'), 403);

        $filtros = DashboardFiltros::desdeRequest($request);
        $transportistaId = (int) $user->usuarioid;
        $resumen = TransporteIngresoService::resumenPeriodo($transportistaId, $filtros);
        $servicios = TransporteIngresoService::listarCompletados($transportistaId, $filtros);

        return view('logistica.transportista.ingresos', compact('resumen', 'servicios', 'filtros'));
    }
}
