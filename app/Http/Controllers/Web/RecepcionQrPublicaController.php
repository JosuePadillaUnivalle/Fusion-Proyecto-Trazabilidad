<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EnvioAsignacionMultiple;
use App\Models\RutaDistribucion;
use App\Services\RecepcionQrFirmaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecepcionQrPublicaController extends Controller
{
    public function __construct(
        private readonly RecepcionQrFirmaService $recepcionQr,
    ) {}

    public function show(string $token): View
    {
        $qr = $this->recepcionQr->resolverPorToken($token);
        $operacion = $this->recepcionQr->resolverOperacion($qr);
        $operacion->loadMissing('firmaTransportista', 'firmaRecepcion');

        $titulo = 'Firma de recepción';
        $codigo = $operacion instanceof RutaDistribucion
            ? ($operacion->codigo ?? 'Ruta #'.$operacion->rutadistribucionid)
            : ($operacion->externo_envio_id ?? 'Envío #'.$operacion->envioasignacionmultipleid);

        return view('recepcion.publica', [
            'token' => $token,
            'titulo' => $titulo,
            'codigo' => $codigo,
            'yaFirmado' => $operacion->firmaRecepcion !== null,
            'sinFirmaTransportista' => $operacion->firmaTransportista === null,
        ]);
    }

    public function firmar(Request $request, string $token): RedirectResponse|JsonResponse
    {
        $request->merge([
            'nombrefirmante' => trim((string) $request->input('nombrefirmante', '')),
        ]);

        $validated = $request->validate([
            'nombrefirmante' => ['required', 'string', 'max:200'],
            'imagen_firma' => ['required', 'string'],
        ]);

        try {
            $this->recepcionQr->guardarFirmaRecepcionPublica(
                $token,
                $validated['nombrefirmante'],
                $validated['imagen_firma'],
            );
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json(['mensaje' => $e->getMessage()], 422);
            }

            return back()->withInput()->withErrors(['firma' => $e->getMessage()]);
        }

        if ($request->expectsJson()) {
            return response()->json(['mensaje' => 'Firma de recepción registrada correctamente.']);
        }

        return redirect()->route('recepcion.publica', $token)
            ->with('exito', 'Firma de recepción registrada correctamente.');
    }
}
