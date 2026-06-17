<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ReporteDistribucionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrgTrackReportController extends Controller
{
    public function __construct(
        private readonly ReporteDistribucionService $reportes
    ) {}

    public function index(Request $request): View
    {
        $datos = $this->reportes->datosReporte();

        return view('envios.reportes-distribucion', $datos);
    }
}
