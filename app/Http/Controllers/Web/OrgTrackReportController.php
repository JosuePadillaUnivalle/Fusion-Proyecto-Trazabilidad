<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EnvioAsignacionMultiple;
use Illuminate\Http\Request;

class OrgTrackReportController extends Controller
{
    public function index(Request $request)
    {
        // dashboard counts
        $counts = [
            'total' => EnvioAsignacionMultiple::count(),
            'pendientes' => EnvioAsignacionMultiple::where('estado','pendiente')->count(),
            'asignados' => EnvioAsignacionMultiple::where('estado','asignado')->count(),
            'en_ruta' => EnvioAsignacionMultiple::where('estado','en_ruta')->count(),
            'entregados' => EnvioAsignacionMultiple::where('estado','entregado')->count(),
        ];

        // top transportistas by asignaciones
        $topTransportistas = EnvioAsignacionMultiple::selectRaw('transportista_usuarioid, count(*) as c')
            ->whereNotNull('transportista_usuarioid')
            ->groupBy('transportista_usuarioid')
            ->orderByDesc('c')
            ->limit(10)
            ->get();

        return view('envios.reportes-distribucion', compact('counts','topTransportistas'));
    }
}
