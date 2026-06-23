<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CondicionTransporte;
use App\Models\EnvioAsignacionMultiple;
use App\Models\TipoIncidenteTransporte;
use App\Services\CierreEnvioAgricolaService;
use App\Support\EnvioCierreAgricolaCatalogo;
use App\Support\EnvioPedidoService;
use App\Support\PedidoCatalogo;
use App\Support\UsuarioRol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnvioCierreAgricolaController extends Controller
{
    public function __construct(
        private readonly CierreEnvioAgricolaService $cierre
    ) {}

    public function panel(Request $request, EnvioAsignacionMultiple $asignacion): View
    {
        $this->autorizarVer($asignacion);

        $asignacion->load([
            'pedido.detalles',
            'transportista',
            'checklistCondicionVehiculo.detalles.condicion',
            'checklistIncidente.detalles.tipoIncidente',
            'firmaTransportista',
            'firmaRecepcion',
            'llegadaConfirmadaPor',
        ]);

        $resumen = $this->cierre->resumenPasos($asignacion);
        $pasoActual = $resumen['paso_actual'];
        $pasosCompletados = EnvioCierreAgricolaCatalogo::pasosCompletados($resumen);
        $pasoSolicitado = (string) $request->query('paso', '');

        if ($pasoSolicitado !== ''
            && in_array($pasoSolicitado, EnvioCierreAgricolaCatalogo::pasosOrdenados(), true)) {
            if ($pasoSolicitado === $pasoActual) {
                $pasoVista = $pasoActual;
                $modoConsulta = false;
            } elseif (in_array($pasoSolicitado, $pasosCompletados, true)) {
                $pasoVista = $pasoSolicitado;
                $modoConsulta = true;
            } else {
                $pasoVista = $pasoActual;
                $modoConsulta = false;
            }
        } else {
            $pasoVista = $pasoActual;
            $modoConsulta = false;
        }

        $paradasMapa = EnvioPedidoService::paradasMapaEnvio($asignacion);

        return view('logistica.cierre-agricola.panel', [
            'asignacion' => $asignacion,
            'resumen' => $resumen,
            'pasoActual' => $pasoActual,
            'pasoVista' => $pasoVista,
            'modoConsulta' => $modoConsulta,
            'pasosCompletados' => $pasosCompletados,
            'condicionesCatalogo' => CondicionTransporte::query()->orderBy('condiciontransporteid')->get(),
            'incidentesCatalogo' => TipoIncidenteTransporte::query()->orderBy('tipoincidentetransporteid')->get(),
            'volverUrl' => route('logistica.asignaciones.show', $asignacion),
            'paradasMapa' => $paradasMapa,
            'trayectoPartes' => EnvioPedidoService::trayectoPartes($asignacion),
            'urlTrazadoRuta' => $asignacion->ruta
                ? route('logistica.rutas.trazado', $asignacion->ruta)
                : null,
        ]);
    }

    public function registrarCondiciones(Request $request, EnvioAsignacionMultiple $asignacion): RedirectResponse|JsonResponse
    {
        $this->autorizarVer($asignacion);

        $validated = $request->validate([
            'perfectas_condiciones' => ['nullable', 'boolean'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'condiciones' => ['nullable', 'array'],
            'condiciones.*.id' => ['required_with:condiciones', 'integer'],
            'condiciones.*.valor' => ['required_with:condiciones'],
        ]);

        $perfectas = (bool) ($validated['perfectas_condiciones'] ?? false);

        if (! $perfectas && ! empty($validated['condiciones'])) {
            $todasSi = collect($validated['condiciones'])
                ->every(fn (array $item) => filter_var($item['valor'] ?? false, FILTER_VALIDATE_BOOLEAN));
            if ($todasSi) {
                $perfectas = true;
            }
        }

        try {
            $this->cierre->registrarCondicionesVehiculo(
                $asignacion,
                $request->user(),
                $perfectas,
                $validated['condiciones'] ?? null,
                $validated['observaciones'] ?? null,
            );
        } catch (\InvalidArgumentException $e) {
            return $this->respuestaError($e->getMessage());
        }

        return $this->respuestaExito('Condiciones del vehículo registradas correctamente.');
    }

    public function confirmarLlegada(Request $request, EnvioAsignacionMultiple $asignacion): RedirectResponse|JsonResponse
    {
        try {
            $this->cierre->confirmarLlegada($asignacion, $request->user());
        } catch (\InvalidArgumentException $e) {
            return $this->respuestaError($e->getMessage());
        }

        return $this->respuestaExito('Llegada al destino confirmada. Registre incidentes para continuar.');
    }

    public function registrarIncidentes(Request $request, EnvioAsignacionMultiple $asignacion): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'sin_incidentes' => ['nullable', 'boolean'],
            'observaciones' => ['nullable', 'string', 'max:500'],
            'incidentes' => ['nullable', 'array'],
            'incidentes.*.id' => ['required_with:incidentes', 'integer'],
            'incidentes.*.ocurrio' => ['nullable'],
        ]);

        $sinIncidentes = (bool) ($validated['sin_incidentes'] ?? false);

        if (! $sinIncidentes) {
            $incidentes = $validated['incidentes'] ?? [];
            $algunoMarcado = collect($incidentes)
                ->contains(fn (array $item) => filter_var($item['ocurrio'] ?? false, FILTER_VALIDATE_BOOLEAN));
            if (! $algunoMarcado) {
                $sinIncidentes = true;
            }
        }

        try {
            $this->cierre->registrarIncidentes(
                $asignacion,
                $request->user(),
                $sinIncidentes,
                $validated['incidentes'] ?? null,
                $validated['observaciones'] ?? null,
            );
        } catch (\InvalidArgumentException $e) {
            return $this->respuestaError($e->getMessage());
        }

        return $this->respuestaExito('Registro de incidentes guardado. Proceda con las firmas.');
    }

    public function firmaTransportista(Request $request, EnvioAsignacionMultiple $asignacion): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'imagen_firma' => ['required', 'string'],
        ]);

        try {
            $this->cierre->guardarFirmaTransportista($asignacion, $request->user(), $validated['imagen_firma']);
        } catch (\InvalidArgumentException $e) {
            return $this->respuestaError($e->getMessage());
        }

        return $this->respuestaExito('Firma del transportista registrada.');
    }

    public function firmaRecepcion(Request $request, EnvioAsignacionMultiple $asignacion): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'imagen_firma' => ['required', 'string'],
        ]);

        try {
            $this->cierre->guardarFirmaRecepcion($asignacion, $request->user(), $validated['imagen_firma']);
        } catch (\InvalidArgumentException $e) {
            return $this->respuestaError($e->getMessage());
        }

        return $this->respuestaExito('Firma de recepción registrada.');
    }

    public function finalizar(Request $request, EnvioAsignacionMultiple $asignacion): RedirectResponse|JsonResponse
    {
        try {
            $documento = $this->cierre->finalizarEntrega($asignacion, $request->user());
        } catch (\InvalidArgumentException $e) {
            return $this->respuestaError($e->getMessage());
        }

        if ($request->expectsJson()) {
            return response()->json([
                'mensaje' => 'Entrega finalizada. Documento de transporte generado.',
                'documento_url' => route('logistica.documentos.show', $documento),
            ]);
        }

        return redirect()
            ->route('logistica.documentos.show', $documento);
    }

    private function autorizarVer(EnvioAsignacionMultiple $asignacion): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        if (
            UsuarioRol::esAdminGlobal($user)
            || $user->can('asignaciones.read')
            || $user->can('asignaciones.update')
        ) {
            return;
        }

        if ((int) $asignacion->transportista_usuarioid === (int) $user->usuarioid) {
            abort_unless(
                PedidoCatalogo::envioOperativoParaTransportista($asignacion),
                403,
                'Este envío estará disponible cuando producción agrícola confirme el pedido.'
            );

            return;
        }

        abort(403);
    }

    private function respuestaExito(string $mensaje): RedirectResponse|JsonResponse
    {
        if (request()->expectsJson()) {
            return response()->json(['mensaje' => $mensaje]);
        }

        return back();
    }

    private function respuestaError(string $mensaje): RedirectResponse|JsonResponse
    {
        if (request()->expectsJson()) {
            return response()->json(['mensaje' => $mensaje], 422);
        }

        return back()->with('error', $mensaje);
    }
}
