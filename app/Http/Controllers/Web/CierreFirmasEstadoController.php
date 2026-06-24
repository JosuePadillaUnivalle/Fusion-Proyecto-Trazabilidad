<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EnvioAsignacionMultiple;
use App\Models\RutaDistribucion;
use App\Services\RecepcionQrFirmaService;
use App\Support\MayoristaAccess;
use App\Support\PuntoVentaAccess;
use App\Support\UsuarioRol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CierreFirmasEstadoController extends Controller
{
    public function __construct(
        private readonly RecepcionQrFirmaService $recepcionQr,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $request->validate([
            'ruta' => ['nullable', 'integer'],
            'asignacion' => ['nullable', 'integer'],
        ]);

        if ($request->filled('ruta')) {
            $ruta = RutaDistribucion::query()->findOrFail((int) $request->query('ruta'));
            $this->autorizarRuta($request, $ruta);

            return response()->json($this->recepcionQr->estadoJson($ruta));
        }

        if ($request->filled('asignacion')) {
            $asignacion = EnvioAsignacionMultiple::query()->findOrFail((int) $request->query('asignacion'));
            $this->autorizarAsignacion($request, $asignacion);

            return response()->json($this->recepcionQr->estadoJson($asignacion));
        }

        return response()->json(['mensaje' => 'Indique ruta o asignación.'], 422);
    }

    private function autorizarRuta(Request $request, RutaDistribucion $ruta): void
    {
        $usuario = $request->user();
        if ($usuario === null) {
            abort(401);
        }

        if (UsuarioRol::esAdminGlobal($usuario)
            || (int) $ruta->transportista_usuarioid === (int) $usuario->usuarioid
            || MayoristaAccess::puedeGestionarTraslado($usuario, $ruta)
            || MayoristaAccess::puedeGestionarRutaDistribucion($usuario, $ruta)
            || PuntoVentaAccess::puedeFirmarRecepcionRuta($usuario, $ruta)
            || $usuario->can('asignaciones.update')) {
            return;
        }

        abort(403);
    }

    private function autorizarAsignacion(Request $request, EnvioAsignacionMultiple $asignacion): void
    {
        $usuario = $request->user();
        if ($usuario === null) {
            abort(401);
        }

        if (UsuarioRol::esAdminGlobal($usuario)
            || (int) $asignacion->transportista_usuarioid === (int) $usuario->usuarioid
            || $usuario->can('asignaciones.update')
            || $usuario->can('recepcion_planta.confirm')) {
            return;
        }

        abort(403);
    }
}
