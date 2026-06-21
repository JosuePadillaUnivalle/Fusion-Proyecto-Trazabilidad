<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EnvioAsignacionMultiple;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EnvioMandarController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            if (! $user || (! $user->can('envios.create') && ! $user->can('pedidos.create'))) {
                abort(403);
            }

            return $next($request);
        })->only('create');
    }

    public function create(Request $request)
    {
        if ($request->user()?->can('pedidos.create')) {
            return redirect()->route('pedidos.create', $request->only('destino'));
        }

        return view('envios.mandar-envio', [
            'numeroSolicitud' => $this->generarNumeroSolicitud(),
            'destinoPreset' => $request->string('destino')->toString(),
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
