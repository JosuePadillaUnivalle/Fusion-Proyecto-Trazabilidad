<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class LogisticaHubController extends Controller
{
    public function planificarRutas(): View
    {
        abort_unless(auth()->user()?->can('rutas_multi.view'), 403);

        return view('logistica.rutas.planificar');
    }

    public function mas(): View
    {
        $user = auth()->user();
        abort_unless(
            $user && $user->canany([
                'transportistas.view',
                'vehiculos.view',
                'documentos.view',
                'incidentes.view',
                'envios.view',
            ]),
            403
        );

        return view('logistica.mas');
    }
}
