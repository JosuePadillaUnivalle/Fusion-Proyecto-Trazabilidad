<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Lote;
use App\Models\TipoActividad;
use App\Models\Prioridad;
use App\Models\Usuario;
use App\Support\ActividadPermisos;
use App\Support\EvidenciaFoto;
use App\Support\LoteEstadoPorActividad;
use App\Support\LoteTrazabilidadService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\ActividadInsumoService;
use App\Services\NotificacionUsuarioService;
use App\Support\ActividadDetalleCatalogo;
use App\Support\UsuarioRol;

class ActividadController extends Controller
{
    public function __construct(
        private LoteEstadoPorActividad $loteEstadoPorActividad,
        private NotificacionUsuarioService $notificaciones,
        private LoteTrazabilidadService $trazabilidad,
        private ActividadInsumoService $actividadInsumos,
    ) {}

    public function index(Request $request)
    {
        $query = $this->queryActividadesVisibles($request)->with(['lote.cultivo', 'usuario', 'tipoActividad', 'prioridad']);

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->q).'%';
            $query->where(function ($sub) use ($term) {
                $sub->where('descripcion', 'like', $term)
                    ->orWhereHas('lote', fn ($l) => $l->where('nombre', 'like', $term))
                    ->orWhereHas('tipoActividad', fn ($t) => $t->where('nombre', 'like', $term))
                    ->orWhereHas('usuario', fn ($u) => $u->where('nombre', 'like', $term));
            });
        }

        if ($request->filled('estado')) {
            if ($request->estado === 'pendiente') {
                $query->whereNull('fechafin');
            } elseif ($request->estado === 'completada') {
                $query->whereNotNull('fechafin');
            }
        }

        if ($request->filled('loteid')) {
            $query->where('loteid', (int) $request->loteid);
        }

        if ($request->filled('tipoactividadid')) {
            $query->where('tipoactividadid', (int) $request->tipoactividadid);
        }

        $stats = [
            'total' => Actividad::count(),
            'pendientes' => Actividad::whereNull('fechafin')->count(),
            'completadas' => Actividad::whereNotNull('fechafin')->count(),
            'hoy' => Actividad::whereDate('fechainicio', now()->toDateString())->count(),
        ];

        $actividades = $query->orderByDesc('actividadid')->paginate(15)->withQueryString();

        $filtros = $request->only(['q', 'estado', 'loteid', 'tipoactividadid']);
        $loteFiltroNombre = $request->filled('loteid')
            ? (Lote::whereKey((int) $request->loteid)->value('nombre') ?? '')
            : '';
        $tiposActividad = TipoActividad::orderBy('nombre')->get();

        return view('actividades.index', compact('actividades', 'stats', 'filtros', 'loteFiltroNombre', 'tiposActividad'));
    }

    /**
     * Calendario de actividades
     */
    public function calendario(Request $request)
    {
        $baseQuery = $this->queryActividadesVisibles($request)->whereNotNull('fechainicio');

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'mes' => (clone $baseQuery)
                ->whereMonth('fechainicio', now()->month)
                ->whereYear('fechainicio', now()->year)
                ->count(),
            'hoy' => (clone $baseQuery)->whereDate('fechainicio', now()->toDateString())->count(),
            'pendientes' => (clone $baseQuery)->whereNull('fechafin')->count(),
            'completadas' => (clone $baseQuery)->whereNotNull('fechafin')->count(),
        ];

        $actividades = (clone $baseQuery)
            ->with(['lote.cultivo', 'usuario', 'tipoActividad'])
            ->orderBy('fechainicio')
            ->get();

        $eventos = $actividades->map(fn ($act) => $this->formatEventoCalendario($act))->values();

        $user = $request->user();
        $lotes = $this->queryLotesParaActividad($request)->get();
        $usuarios = $this->usuariosResponsablesActividad($request);
        $puedeDesignarResponsable = $this->puedeDesignarResponsableActividad($user);
        $esJefeAgricultorDesignando = $user && UsuarioRol::esJefeAgricultor($user) && ! UsuarioRol::esAdminGlobal($user);
        $responsableSelectorParams = $this->paramsSelectorResponsableActividad($user);

        $tiposActividad = TipoActividad::orderBy('nombre')->get();

        return view('actividades.calendario', compact(
            'stats',
            'eventos',
            'lotes',
            'usuarios',
            'tiposActividad',
            'puedeDesignarResponsable',
            'esJefeAgricultorDesignando',
            'responsableSelectorParams',
        ));
    }

    public function create(Request $request)
    {
        if ($request->filled('loteid') && $request->filled('tipo')
            && str_contains(mb_strtolower(trim((string) $request->tipo)), 'siembra')) {
            $params = ['lote' => $request->integer('loteid')];
            $returnUrl = $this->validReturnUrl($request->input('return'));
            if ($returnUrl) {
                $params['return'] = $returnUrl;
            }

            return redirect()->route('lotes.siembra.create', $params);
        }

        $user = $request->user();
        $tipos = TipoActividad::query()
            ->orderBy('nombre')
            ->get()
            ->reject(fn (TipoActividad $t) => in_array(
                mb_strtolower(trim($t->nombre)),
                ['cosecha', 'labranza', 'fumigacion', 'fumigación', 'siembra'],
                true
            ))
            ->values();
        $prioridades = Prioridad::all();

        $loteid = $request->integer('loteid') ?: old('loteid');
        $lote = $loteid ? Lote::with('usuario')->find($loteid) : null;
        $loteLabel = $lote?->nombre;

        if ($lote) {
            $faseLote = $this->trazabilidad->resolverFaseActual($lote);
            $tipos = $tipos->filter(
                fn (TipoActividad $t) => $this->trazabilidad->tipoActividadPermitidoEnFase($t->nombre, $faseLote)
            )->values();
        }

        $tipoPreselect = null;
        if ($request->filled('tipo')) {
            $tipoBusqueda = mb_strtolower(trim((string) $request->tipo));
            $tipoPreselect = $tipos->first(function ($t) use ($tipoBusqueda) {
                $nombre = mb_strtolower(trim($t->nombre ?? ''));

                return $nombre === $tipoBusqueda || str_contains($nombre, $tipoBusqueda);
            });
        }

        $returnUrl = $this->validReturnUrl($request->input('return'));
        $desdeTrazabilidad = $returnUrl !== null;
        $puedeDesignarResponsable = $this->puedeDesignarResponsableActividad($user);
        $responsableSelectorParams = $this->paramsSelectorResponsableActividad($user);
        $esJefeAgricultorDesignando = $user && UsuarioRol::esJefeAgricultor($user) && ! UsuarioRol::esAdminGlobal($user);
        $responsableInicial = old('usuarioid');
        $responsableLabel = '';
        if ($responsableInicial) {
            $u = Usuario::find($responsableInicial);
            $responsableLabel = $u ? trim($u->nombre.' '.($u->apellido ?? '')) : '';
        } elseif (
            $lote?->usuarioid
            && $this->puedeAsignarResponsableActividad($user, (int) $lote->usuarioid)
        ) {
            $responsableInicial = $lote->usuarioid;
            $responsableLabel = trim($lote->usuario->nombre.' '.($lote->usuario->apellido ?? ''));
        } elseif (! $puedeDesignarResponsable && $user) {
            $responsableInicial = $user->usuarioid;
            $responsableLabel = trim($user->nombre.' '.($user->apellido ?? ''));
        }

        $usuariosResponsables = $this->usuariosResponsablesActividad($request);

        return view('actividades.create', compact(
            'tipos',
            'prioridades',
            'loteLabel',
            'loteid',
            'tipoPreselect',
            'returnUrl',
            'desdeTrazabilidad',
            'puedeDesignarResponsable',
            'responsableSelectorParams',
            'esJefeAgricultorDesignando',
            'responsableInicial',
            'responsableLabel',
            'usuariosResponsables',
        ));
    }

    public function createSiembra(Request $request, Lote $lote)
    {
        $this->autorizarLoteParaActividad($request, $lote);
        $lote->load(['usuario', 'cultivo', 'estadoTipo']);

        $sugerenciaSiembra = $lote->cultivo
            ? \App\Support\CultivoSiembraCatalogo::sugerenciaParaLote($lote->cultivo, (float) $lote->superficie)
            : null;
        $insumosSiembra = $this->actividadInsumos->listarInsumosParaModal('material_siembra', $lote);

        $tipoSiembra = $this->tipoActividadSiembra();
        $faseActual = $this->trazabilidad->resolverFaseActual($lote);
        $bloqueo = $this->trazabilidad->mensajeActividadNoPermitida($lote, $tipoSiembra->nombre);

        if (! $this->trazabilidad->tipoActividadPermitidoEnFase($tipoSiembra->nombre, $faseActual) || $bloqueo !== null) {
            return redirect()
                ->route('lotes.trazabilidad', $lote)
                ->with('error', $bloqueo ?? 'Este lote no está en fase de siembra.');
        }

        $user = $request->user();
        $prioridades = Prioridad::all();
        $returnUrl = $this->validReturnUrl($request->input('return'))
            ?? route('lotes.trazabilidad', $lote);
        $puedeDesignarResponsable = $this->puedeDesignarResponsableActividad($user);
        $responsableSelectorParams = $this->paramsSelectorResponsableActividad($user);
        $esJefeAgricultorDesignando = $user && UsuarioRol::esJefeAgricultor($user) && ! UsuarioRol::esAdminGlobal($user);
        $responsableInicial = old('usuarioid');
        $responsableLabel = '';
        if ($responsableInicial) {
            $u = Usuario::find($responsableInicial);
            $responsableLabel = $u ? trim($u->nombre.' '.($u->apellido ?? '')) : '';
        } elseif (
            $lote->usuarioid
            && $this->puedeAsignarResponsableActividad($user, (int) $lote->usuarioid)
        ) {
            $responsableInicial = $lote->usuarioid;
            $responsableLabel = trim($lote->usuario->nombre.' '.($lote->usuario->apellido ?? ''));
        } elseif (! $puedeDesignarResponsable && $user) {
            $responsableInicial = $user->usuarioid;
            $responsableLabel = trim($user->nombre.' '.($user->apellido ?? ''));
        }

        return view('lotes.siembra.create', compact(
            'lote',
            'tipoSiembra',
            'prioridades',
            'returnUrl',
            'puedeDesignarResponsable',
            'responsableSelectorParams',
            'esJefeAgricultorDesignando',
            'responsableInicial',
            'responsableLabel',
            'sugerenciaSiembra',
            'insumosSiembra',
        ));
    }

    public function storeSiembra(Request $request, Lote $lote)
    {
        $this->autorizarLoteParaActividad($request, $lote);

        $tipoSiembra = $this->tipoActividadSiembra();
        $bloqueo = $this->trazabilidad->mensajeActividadNoPermitida($lote, $tipoSiembra->nombre);
        if ($bloqueo !== null) {
            return back()->withInput()->with('error', $bloqueo);
        }

        $data = $request->validate([
            'fechainicio' => 'nullable|date',
            'descripcion' => 'nullable|string|max:200',
            'prioridadid' => 'nullable|exists:prioridad,prioridadid',
            'observaciones' => 'nullable|string|max:250',
        ]);

        $request->merge([
            'loteid' => $lote->loteid,
            'tipoactividadid' => $tipoSiembra->tipoactividadid,
            'descripcion' => $data['descripcion'] ?? null,
            'fechainicio' => $data['fechainicio'] ?? null,
            'prioridadid' => $data['prioridadid'] ?? null,
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        return $this->store($request);
    }

    public function asignarSiembra(Request $request, Lote $lote)
    {
        $this->autorizarLoteParaActividad($request, $lote);
        $lote->loadMissing('usuario');

        try {
            $responsableId = $this->resolverUsuarioidActividad($request, $lote);
            app(\App\Services\LoteSiembraService::class)->asignar(
                $lote,
                $responsableId,
                $request->user()?->usuarioid
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->route('lotes.trazabilidad', $lote)
                ->withErrors($e->errors())
                ->withInput();
        }

        return redirect()
            ->route('lotes.trazabilidad', $lote)
            ->with('success', 'Siembra asignada. El agricultor responsable podrá marcarla como realizada con una foto.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'descripcion' => 'nullable|string|max:200',
            'tipoactividadid' => 'required|exists:tipoactividad,tipoactividadid',
            'prioridadid' => 'nullable|exists:prioridad,prioridadid',
            'fechainicio' => 'nullable|date',
            'fechafin' => 'nullable|date|after_or_equal:fechainicio',
            'observaciones' => 'nullable|string|max:250',
            'detalle_actividad_json' => 'nullable|string',
        ]);

        // Obtener el lote para asignar usuario automáticamente
        $lote = Lote::with('actividades.tipoActividad')->findOrFail($data['loteid']);
        $tipo = TipoActividad::find($data['tipoactividadid']);

        $noPermitida = $this->trazabilidad->mensajeActividadNoPermitida($lote, $tipo->nombre ?? null);
        if ($noPermitida !== null) {
            return back()->withInput()->with('error', $noPermitida);
        }

        $detalleRaw = $this->actividadInsumos->parseDetalleDesdeRequest($request, $tipo->nombre ?? null);
        $detalle = [];
        if (ActividadDetalleCatalogo::requiereInsumos($tipo->nombre) || ActividadDetalleCatalogo::esRiego($tipo->nombre)) {
            $detalle = $this->actividadInsumos->validarDetalle($detalleRaw, $tipo->nombre ?? null);
        }

        $resumenDetalle = ActividadDetalleCatalogo::textoResumenDesdeDetalle($tipo->nombre ?? null, $detalle);
        if ($resumenDetalle !== null) {
            $data['descripcion'] = $resumenDetalle;
        } elseif (empty($data['descripcion'])) {
            $data['descripcion'] = $tipo->nombre ?? 'Actividad';
        }

        // Prioridad por defecto si no se envió
        if (empty($data['prioridadid'])) {
            $prioridadDefault = Prioridad::first();
            $data['prioridadid'] = $prioridadDefault ? $prioridadDefault->prioridadid : null;
        }

        $usuarioid = $this->resolverUsuarioidActividad($request, $lote);

        $marcandoCompletada = $request->boolean('completar')
            && (int) $usuarioid === (int) ($request->user()?->usuarioid ?? 0);

        $evidenciaPath = $this->guardarEvidenciaSiCompletada($request, $marcandoCompletada);

        if ($marcandoCompletada) {
            $data['fechafin'] = now();
        }

        $actividad = Actividad::create([
            'loteid' => $data['loteid'],
            'usuarioid' => $usuarioid,
            'descripcion' => $data['descripcion'],
            'fechainicio' => $data['fechainicio'] ?? now(),
            'fechafin' => $data['fechafin'] ?? null,
            'tipoactividadid' => $data['tipoactividadid'],
            'prioridadid' => $data['prioridadid'],
            'observaciones' => $data['observaciones'] ?? null,
            'evidencia_foto_path' => $evidenciaPath,
            'detalle_json' => $detalle !== [] ? json_encode($detalle, JSON_UNESCAPED_UNICODE) : null,
        ]);

        if (! empty($data['fechafin']) && $detalle !== []) {
            $this->actividadInsumos->aplicarStockSiCorresponde($actividad, $detalle);
        }

        $actividad->load('lote');
        if ((int) $usuarioid !== (int) ($request->user()?->usuarioid ?? 0)) {
            $this->notificaciones->actividadAsignada($actividad);
        }

        $msgEstado = '';
        if (! empty($data['fechafin'])) {
            $estadoAplicado = $this->loteEstadoPorActividad->aplicarDesdeActividad($actividad);
            if ($estadoAplicado) {
                $msgEstado = " El lote pasó a estado «{$estadoAplicado}».";
            }
        }

        $tipoNombre = mb_strtolower(trim($tipo->nombre ?? ''));
        if (
            ! empty($data['fechafin'])
            && str_contains($tipoNombre, 'siembra')
            && ! $lote->fechasiembra
        ) {
            $lote->fechasiembra = $data['fechainicio'] ?? now();
            $lote->fechamodificacion = now();
            $lote->save();
        }

        $mensaje = "Actividad de {$tipo->nombre} registrada para el lote {$lote->nombre}.{$msgEstado}";

        $returnUrl = $this->validReturnUrl($request->input('return'));
        if (! $returnUrl && $request->boolean('desde_trazabilidad')) {
            $returnUrl = route('lotes.trazabilidad', $lote, absolute: false);
        }
        if ($returnUrl) {
            return redirect($returnUrl)->with('success', $mensaje);
        }

        // Detectar si viene del calendario
        if ($request->has('from_calendar') || $request->header('referer') && str_contains($request->header('referer'), 'calendario')) {
            return redirect()->route('actividades.calendario')
                ->with('success', $mensaje);
        }

        return redirect()->route('actividades.index')
            ->with('success', $mensaje);
    }

    public function show(Request $request, Actividad $actividad)
    {
        $this->autorizarActividadAsignada($request, $actividad);
        $actividad->load(['lote']);

        if ($actividad->lote) {
            return redirect()
                ->route('lotes.trazabilidad', $actividad->lote)
                ->withFragment('historial-eventos');
        }

        $actividad->load(['usuario', 'tipoActividad', 'prioridad']);
        $puedeMarcarCompletada = ActividadPermisos::puedeMarcarCompletada($request->user(), $actividad);

        return view('actividades.show', compact('actividad', 'puedeMarcarCompletada'));
    }

    public function edit(Request $request, Actividad $actividad)
    {
        $this->autorizarActividadAsignada($request, $actividad);

        $user = $request->user();
        $lotes = $this->queryLotesParaActividad($request)->get();
        $tipos = TipoActividad::all();
        $prioridades = Prioridad::all();
        $usuarios = $this->usuariosResponsablesActividad($request);
        $puedeDesignarResponsable = $this->puedeDesignarResponsableActividad($user);
        $responsableSelectorParams = $this->paramsSelectorResponsableActividad($user);
        $esJefeAgricultorDesignando = $user && UsuarioRol::esJefeAgricultor($user) && ! UsuarioRol::esAdminGlobal($user);
        $responsableLabel = $actividad->usuario
            ? trim($actividad->usuario->nombre.' '.($actividad->usuario->apellido ?? ''))
            : '';

        return view('actividades.edit', compact(
            'actividad',
            'lotes',
            'tipos',
            'prioridades',
            'usuarios',
            'puedeDesignarResponsable',
            'responsableSelectorParams',
            'esJefeAgricultorDesignando',
            'responsableLabel',
        ));
    }

    public function update(Request $request, Actividad $actividad)
    {
        $this->autorizarActividadAsignada($request, $actividad);

        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'descripcion' => 'required|string|max:200',
            'fechainicio' => 'nullable|date',
            'fechafin' => 'nullable|date|after_or_equal:fechainicio',
            'tipoactividadid' => 'required|exists:tipoactividad,tipoactividadid',
            'prioridadid' => 'required|exists:prioridad,prioridadid',
            'observaciones' => 'nullable|string|max:250',
        ]);

        $lote = Lote::findOrFail($data['loteid']);
        $responsableAnterior = (int) $actividad->usuarioid;
        $data['usuarioid'] = $this->resolverUsuarioidActividad($request, $lote);

        $actividad->update($data);
        $actividad->refresh();

        if (
            (int) $data['usuarioid'] !== $responsableAnterior
            && (int) $data['usuarioid'] !== (int) ($request->user()?->usuarioid ?? 0)
        ) {
            $this->notificaciones->actividadAsignada($actividad);
        }

        $msg = 'Actividad actualizada.';
        if (! empty($data['fechafin'])) {
            $estadoAplicado = $this->loteEstadoPorActividad->aplicarDesdeActividad($actividad);
            if ($estadoAplicado) {
                $msg .= " El lote pasó a estado «{$estadoAplicado}».";
            }
        }

        return redirect()->route('actividades.index')->with('success', $msg);
    }

    public function destroy(Actividad $actividad)
    {
        $actividad->delete();

        return redirect()->route('actividades.index')->with('success', 'Actividad eliminada.');
    }

    /**
     * Marcar actividad como realizada y cambiar estado del lote según tipo
     */
    public function marcarRealizada(Request $request, Actividad $actividad)
    {
        $this->autorizarMarcarActividadCompletada($request, $actividad);

        if ($actividad->fechafin !== null) {
            return $this->redirectDespuesDeMarcar($request, $actividad, 'error', 'Esta actividad ya está completada.');
        }

        $validator = Validator::make($request->all(), [
            'evidencia_foto' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ], [
            'evidencia_foto.required' => 'Debe subir una foto que demuestre que la actividad fue realizada.',
            'evidencia_foto.image' => 'El archivo debe ser una imagen.',
            'evidencia_foto.max' => 'La imagen no puede superar 5 MB.',
        ]);

        if ($validator->fails()) {
            return $this->redirectDespuesDeMarcar($request, $actividad)
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $actividad->evidencia_foto_path = EvidenciaFoto::guardar(
                $request->file('evidencia_foto'),
                'actividades_evidencia'
            );
            $actividad->fechafin = now();
            $actividad->save();

            if ($actividad->detalle_json) {
                $detalle = json_decode($actividad->detalle_json, true);
                if (is_array($detalle)) {
                    $this->actividadInsumos->aplicarStockSiCorresponde($actividad, $detalle);
                }
            }

            $estadoAplicado = $this->loteEstadoPorActividad->aplicarDesdeActividad($actividad);

            $actividad->loadMissing(['lote', 'tipoActividad']);
            $tipoNombre = mb_strtolower(trim($actividad->tipoActividad->nombre ?? ''));
            if (str_contains($tipoNombre, 'siembra') && $actividad->lote && ! $actividad->lote->fechasiembra) {
                $actividad->lote->fechasiembra = now()->toDateString();
                $actividad->lote->fechamodificacion = now();
                $actividad->lote->save();
            }

            $this->notificaciones->descartarActividadAsignada((int) $actividad->actividadid);
            $mensajeEstado = $estadoAplicado
                ? " El lote «{$actividad->lote->nombre}» cambió a «{$estadoAplicado}»."
                : '';

            DB::commit();

            return $this->redirectDespuesDeMarcar(
                $request,
                $actividad,
                'success',
                "Actividad marcada como realizada.{$mensajeEstado}"
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->redirectDespuesDeMarcar($request, $actividad, 'error', 'Error: '.$e->getMessage());
        }
    }

    private function redirectDespuesDeMarcar(Request $request, Actividad $actividad, ?string $flashKey = null, ?string $message = null)
    {
        $actividad->loadMissing('lote');

        if ($request->input('scroll_to') === 'historial-eventos' && $actividad->lote) {
            $response = redirect()->to(route('lotes.trazabilidad', $actividad->lote).'#historial-eventos');
        } else {
            $response = back();
        }

        if ($flashKey !== null && $message !== null) {
            $response = $response->with($flashKey, $message);
        }

        return $response;
    }

    private function guardarEvidenciaSiCompletada(Request $request, bool $marcandoCompletada): ?string
    {
        if (! $marcandoCompletada) {
            return null;
        }

        $request->validate([
            'evidencia_foto' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ], [
            'evidencia_foto.required' => 'Debe subir una foto que demuestre que la actividad fue realizada.',
            'evidencia_foto.image' => 'El archivo debe ser una imagen.',
            'evidencia_foto.max' => 'La imagen no puede superar 5 MB.',
        ]);

        return EvidenciaFoto::guardar($request->file('evidencia_foto'), 'actividades_evidencia');
    }

    private function tipoActividadSiembra(): TipoActividad
    {
        return TipoActividad::query()
            ->whereRaw('LOWER(TRIM(nombre)) LIKE ?', ['%siembra%'])
            ->orderBy('tipoactividadid')
            ->firstOrFail();
    }

    private function autorizarLoteParaActividad(Request $request, Lote $lote): void
    {
        $permitido = $this->queryLotesParaActividad($request)
            ->where('loteid', $lote->loteid)
            ->exists();

        if (! $permitido) {
            abort(403, 'No tienes acceso a este lote.');
        }
    }

    private function autorizarActividadAsignada(Request $request, Actividad $actividad): void
    {
        if (! ActividadPermisos::puedeAcceder($request->user(), $actividad)) {
            abort(403, 'No tienes acceso a esta actividad.');
        }
    }

    private function autorizarMarcarActividadCompletada(Request $request, Actividad $actividad): void
    {
        if (! ActividadPermisos::puedeMarcarCompletada($request->user(), $actividad)) {
            abort(403, 'No tienes permiso para completar esta actividad.');
        }
    }

    private function queryActividadesVisibles(Request $request)
    {
        $query = Actividad::query();
        $user = $request->user();

        if (UsuarioRol::debeAcotarPorAsignacion($user)) {
            $query->where('usuarioid', (int) $user->usuarioid);
        } elseif (UsuarioRol::esJefeAgricultor($user) && ! UsuarioRol::esAdminGlobal($user)) {
            $query->whereIn('usuarioid', UsuarioRol::idsEmpleadosOperativosDeJefeAgricultor($user));
        }

        return $query;
    }

    private function queryLotesParaActividad(Request $request)
    {
        $query = Lote::query()->with(['cultivo', 'usuario'])->orderBy('nombre');
        $user = $request->user();

        if (UsuarioRol::debeAcotarPorAsignacion($user)) {
            $query->where('usuarioid', (int) $user->usuarioid);
        } elseif (UsuarioRol::esJefeAgricultor($user) && ! UsuarioRol::esAdminGlobal($user)) {
            $query->whereIn('usuarioid', UsuarioRol::idsUsuariosBajoJefeAgricultor($user));
        }

        return $query;
    }

    private function usuariosResponsablesActividad(Request $request)
    {
        $user = $request->user();

        if (UsuarioRol::debeAcotarPorAsignacion($user)) {
            return Usuario::query()
                ->where('usuarioid', (int) $user->usuarioid)
                ->orderBy('nombre')
                ->get();
        }

        if (UsuarioRol::esJefeAgricultor($user) && ! UsuarioRol::esAdminGlobal($user)) {
            $ids = UsuarioRol::idsEmpleadosOperativosDeJefeAgricultor($user);
            if ($ids === []) {
                return collect();
            }

            return Usuario::query()
                ->whereIn('usuarioid', $ids)
                ->orderBy('nombre')
                ->get();
        }

        return Usuario::query()
            ->where('activo', true)
            ->whereIn('role', ['agricultor'])
            ->whereDoesntHave('roles', fn ($q) => $q->where('name', 'jefe_agricultor'))
            ->orderBy('nombre')
            ->get();
    }

    private function puedeDesignarResponsableActividad(?Usuario $user): bool
    {
        return $user && (
            UsuarioRol::esAdminGlobal($user) || UsuarioRol::esJefeAgricultor($user)
        );
    }

    /** @return array<string, mixed> */
    private function paramsSelectorResponsableActividad(?Usuario $user): array
    {
        $params = [
            'roles' => 'agricultor',
            'excluir_jefes_agricolas' => 1,
        ];

        if ($user && UsuarioRol::esJefeAgricultor($user) && ! UsuarioRol::esAdminGlobal($user)) {
            $params['supervisor_usuarioid'] = $user->usuarioid;
            $params['solo_empleados_equipo'] = 1;
        }

        return $params;
    }

    private function puedeAsignarResponsableActividad(?Usuario $actor, int $usuarioid): bool
    {
        $usuario = Usuario::find($usuarioid);
        if (! $usuario || ! $usuario->activo) {
            return false;
        }

        if (! UsuarioRol::esResponsableActividadPermitido($usuario)) {
            return false;
        }

        if (! $actor || UsuarioRol::esAdminGlobal($actor)) {
            return true;
        }

        if (UsuarioRol::esJefeAgricultor($actor)) {
            return in_array($usuarioid, UsuarioRol::idsEmpleadosOperativosDeJefeAgricultor($actor), true);
        }

        return false;
    }

    private function resolverUsuarioidActividad(Request $request, Lote $lote): int
    {
        $auth = $request->user();

        if (UsuarioRol::debeAcotarPorAsignacion($auth)) {
            return (int) $auth->usuarioid;
        }

        $usuarioid = $request->integer('usuarioid');
        if ($usuarioid && $this->puedeAsignarResponsableActividad($auth, $usuarioid)) {
            return $usuarioid;
        }

        if (
            $this->puedeDesignarResponsableActividad($auth)
            && $lote->usuarioid
            && $this->puedeAsignarResponsableActividad($auth, (int) $lote->usuarioid)
        ) {
            return (int) $lote->usuarioid;
        }

        $mensaje = UsuarioRol::esJefeAgricultor($auth) && ! UsuarioRol::esAdminGlobal($auth)
            ? 'Debe asignar un agricultor de su equipo como responsable. El jefe agrícola no ejecuta actividades de campo.'
            : 'Debe asignar un agricultor operativo como responsable de la actividad.';

        throw ValidationException::withMessages([
            'usuarioid' => $mensaje,
        ]);
    }

    private function formatEventoCalendario(Actividad $act): array
    {
        $tipo = $act->tipoActividad->nombre ?? 'Actividad';
        $lote = $act->lote->nombre ?? 'Sin lote';
        $pendiente = $act->fechafin === null;
        $inicio = Carbon::parse($act->fechainicio);
        $hora = $inicio->format('H:i');

        return [
            'id' => (string) $act->actividadid,
            'title' => $tipo.' — '.$lote,
            'start' => $inicio->format('Y-m-d'),
            'allDay' => true,
            'extendedProps' => [
                'id' => $act->actividadid,
                'tipo' => $tipo,
                'tipoSlug' => Str::slug($tipo, '-', 'es'),
                'lote' => $lote,
                'loteid' => $act->loteid,
                'responsable' => trim(($act->usuario->nombre ?? '').' '.($act->usuario->apellido ?? '')),
                'usuarioid' => $act->usuarioid,
                'hora' => $hora,
                'horaTimestamp' => $inicio->timestamp,
                'fechainicioFmt' => $inicio->format('d/m/Y H:i'),
                'fechafin' => $pendiente ? null : Carbon::parse($act->fechafin)->format('d/m/Y H:i'),
                'pendiente' => $pendiente,
                'observaciones' => $act->observaciones ?: $act->descripcion,
            ],
            'classNames' => $pendiente ? ['event-pendiente'] : ['event-completada'],
        ];
    }

    private function validReturnUrl(mixed $return): ?string
    {
        if (! is_string($return) || trim($return) === '') {
            return null;
        }

        $return = trim($return);

        if (str_starts_with($return, '/') && ! str_starts_with($return, '//')) {
            return $return;
        }

        foreach (array_filter([
            rtrim((string) config('app.url'), '/'),
            rtrim((string) config('app.public_url', ''), '/'),
        ]) as $base) {
            if ($base !== '' && str_starts_with($return, $base)) {
                $path = parse_url($return, PHP_URL_PATH) ?: '';
                $query = parse_url($return, PHP_URL_QUERY);
                $fragment = parse_url($return, PHP_URL_FRAGMENT);
                $normalized = $path;
                if ($query) {
                    $normalized .= '?'.$query;
                }
                if ($fragment) {
                    $normalized .= '#'.$fragment;
                }

                return $normalized !== '' ? $normalized : $return;
            }
        }

        return null;
    }

}