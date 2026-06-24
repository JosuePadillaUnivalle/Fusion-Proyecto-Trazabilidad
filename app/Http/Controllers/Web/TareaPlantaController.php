<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AsignacionEtapaPlanta;
use App\Models\Usuario;
use App\Services\NotificacionUsuarioService;
use App\Support\AsignacionEtapaPlantaService;
use App\Support\LoteProduccionParametrosService;
use App\Support\LoteProduccionTrazabilidadService;
use App\Support\ProcesoPlantaCatalogo;
use App\Support\UsuarioRol;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TareaPlantaController extends Controller
{
    public function __construct(
        private readonly AsignacionEtapaPlantaService $asignaciones,
        private readonly LoteProduccionTrazabilidadService $trazabilidad,
        private readonly LoteProduccionParametrosService $parametros,
        private readonly NotificacionUsuarioService $notificaciones,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        if ($redirect = $this->rechazarSiNoEsOperario($user)) {
            return $redirect;
        }

        $tareasPendientes = AsignacionEtapaPlanta::query()
            ->with(['proceso', 'maquina', 'loteProduccion', 'asignadoPor'])
            ->where('operador_usuarioid', $user->usuarioid)
            ->pendientes()
            ->orderByDesc('creado_en')
            ->get();

        $tareasCompletadas = AsignacionEtapaPlanta::query()
            ->with(['proceso', 'maquina', 'loteProduccion'])
            ->where('operador_usuarioid', $user->usuarioid)
            ->where('estado', AsignacionEtapaPlanta::ESTADO_COMPLETADA)
            ->orderByDesc('completada_en')
            ->limit(10)
            ->get();

        $alertas = $this->notificaciones->noLeidasPara((int) $user->usuarioid, 8, $user);
        $totalAlertas = $this->notificaciones->contarNoLeidas((int) $user->usuarioid, $user);

        return view('tareas_planta.index', compact(
            'tareasPendientes',
            'tareasCompletadas',
            'alertas',
            'totalAlertas',
        ));
    }

    public function show(Request $request, AsignacionEtapaPlanta $asignacion): View|RedirectResponse
    {
        $user = $request->user();
        if ($redirect = $this->rechazarSiNoEsOperario($user)) {
            return $redirect;
        }

        if ((int) $asignacion->operador_usuarioid !== (int) $user->usuarioid) {
            return redirect()
                ->route('tareas-planta.index')
                ->with('error', 'Esta tarea está asignada a otro operario. Inicie sesión con la cuenta correcta.');
        }

        $asignacion->load(['proceso', 'maquina', 'loteProduccion.pedido', 'asignadoPor']);

        $empaquePlan = null;
        $vistaEmpaquetado = null;
        if ($asignacion->loteProduccion) {
            $empaquePlan = \App\Support\ProductoPlantaCatalogo::empaquePlanificadoResumen($asignacion->loteProduccion);
            if (\App\Support\ProductoPlantaCatalogo::esProcesoEmpaquetado($asignacion->proceso?->nombre)) {
                $vistaEmpaquetado = \App\Support\ProductoPlantaCatalogo::vistaPreviaEmpaquetado(
                    $asignacion->loteProduccion,
                    app(\App\Services\AlmacenCapacidadService::class)
                );
            }
        }

        return view('tareas_planta.show', [
            'tarea' => $asignacion,
            'puedeCompletar' => $this->asignaciones->puedeCompletar($asignacion),
            'empaquePlan' => $empaquePlan,
            'vistaEmpaquetado' => $vistaEmpaquetado,
            'parametrosRequeridos' => $this->parametros->parametrosRequeridosParaAsignacion($asignacion),
            'timelineLote' => $asignacion->loteProduccion
                ? app(\App\Support\LoteProduccionTransformacionService::class)->timelineVisual($asignacion->loteProduccion)
                : [],
        ]);
    }

    public function completar(Request $request, AsignacionEtapaPlanta $asignacion): RedirectResponse
    {
        $user = $request->user();
        if ($redirect = $this->rechazarSiNoEsOperario($user)) {
            return $redirect;
        }

        if ((int) $asignacion->operador_usuarioid !== (int) $user->usuarioid) {
            return redirect()
                ->route('tareas-planta.index')
                ->with('error', 'Solo el operario asignado puede completar esta tarea.');
        }

        $inicio = $asignacion->creado_en ?? now();

        $data = $request->validate([
            'parametros' => ['nullable', 'array'],
            'parametros.*.variableestandarid' => ['required_with:parametros', 'integer'],
            'parametros.*.valor' => ['required_with:parametros', 'numeric'],
        ]);

        try {
            $this->asignaciones->completar($asignacion, [
                'hora_inicio' => $inicio->toDateTimeString(),
                'hora_fin' => now()->toDateTimeString(),
                'parametros' => array_values($data['parametros'] ?? []),
            ], $user);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('tareas-planta.index');
    }

    private function rechazarSiNoEsOperario(?Usuario $user): ?RedirectResponse
    {
        if (! $user) {
            abort(401);
        }

        if (UsuarioRol::esOperarioPlanta($user)) {
            return null;
        }

        if (UsuarioRol::esJefePlanta($user)) {
            return redirect()
                ->route('dashboard.panel-planta')
                ->with('error', 'Como jefe de planta usted asigna las tareas. El operario asignado debe iniciar sesión con su cuenta planta para verlas y completarlas.');
        }

        return redirect()
            ->route('dashboard')
            ->with('error', 'Solo los operarios con rol planta pueden acceder a sus tareas de transformación.');
    }
}
