<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\LocalOrgTrackFallback;

class EnvioDashboardController extends Controller
{
    public function index()
    {
        $panel = LocalOrgTrackFallback::panelEstadisticasEnvios();
        $operacion = LocalOrgTrackFallback::operationalMetrics();

        return view('envios.admin', [
            'metaInicial' => ['source' => 'local', 'count' => $panel['stats']['total'] ?? 0],
            'stats' => array_merge($panel['stats'], [
                'transportistas' => $operacion['transportistas'] ?? 0,
                'vehiculos_activos' => $operacion['vehiculos_activos'] ?? 0,
                'rutas_activas' => $operacion['rutas_activas'] ?? 0,
                'incidentes_abiertos' => $operacion['incidentes_abiertos'] ?? 0,
            ]),
            'porEstado' => $panel['porEstado'],
        ]);
    }
}
