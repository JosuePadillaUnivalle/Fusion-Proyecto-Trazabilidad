<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Pedido;
use Illuminate\Support\Facades\Schema;

class EnvioMandarController extends Controller
{
    public function create()
    {
        return view('envios.mandar-envio', [
            'numeroSolicitud' => $this->generarNumeroSolicitud(),
        ]);
    }

    public static function generarNumeroSolicitud(): string
    {
        $fecha = now()->format('Ymd');
        $prefijo = "SOL-{$fecha}-";

        if (Schema::hasTable('pedido')) {
            $secuencia = Pedido::query()
                ->where('numero_solicitud', 'like', $prefijo.'%')
                ->count() + 1;
        } else {
            $secuencia = (int) (EnvioAsignacionMultiple::max('envioasignacionmultipleid') ?? 0) + 1;
        }

        return $prefijo.str_pad((string) $secuencia, 4, '0', STR_PAD_LEFT);
    }
}
