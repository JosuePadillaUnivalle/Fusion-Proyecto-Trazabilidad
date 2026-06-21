<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\LocalOrgTrackFallback;

class EnvioDetalleController extends Controller
{
    public function show($id)
    {
        $envio = LocalOrgTrackFallback::envioDetallePayload($id);
        $tieneDetalle = ! empty($envio['particiones']);

        return view('envios.detalle', [
            'id' => $id,
            'envioInicial' => $envio,
            'tieneDetalle' => $tieneDetalle,
        ]);
    }
}
