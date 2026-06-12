<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Lote;
use App\Models\TipoActividad;
use App\Models\Prioridad;
use App\Models\Usuario;
use App\Support\ActividadPermisos;
use App\Support\LoteEstadoPorActividad;
use App\Support\LoteTrazabilidadService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\NotificacionUsuarioService;
use App\Support\UsuarioRol;

class ActividadController extends Controller
{
    public function __construct(
        private LoteEstadoPorActividad $loteEstadoPorActividad,
        private NotificacionUsuarioService $notificaciones,
        private LoteTrazabilidadService $trazabilidad,
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
        $lotes = Lote::orderBy('nombre')->get(['loteid', 'nombre']);
        $tiposActividad = TipoActividad::orderBy('nombre')->get();

        return view('actividades.index', compact('actividades', 'stats', 'filtros', 'lotes', 'tiposActividad'));
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

        $tiposActividad = TipoActividad::orderBy('nombre')->get();

        return view('actividades.calendario', compact(
            'stats',
            'eventos',
            'lotes',
            'usuarios',
            'tiposActividad',
            'puedeDesignarResponsable',
            'esJefeAgricultorDesignando',
        ));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $tipos = TipoActividad::all();
        $prioridades = Prioridad::all();

        $loteid = $request->integer('loteid') ?: old('loteid');
        $lote = $loteid ? Lote::with('usuario')->find($loteid) : null;
        $loteLabel = $lote?->nombre;

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
        ]);

        // Obtener el lote para asignar usuario automáticamente
        $lote = Lote::with('actividades.tipoActividad')->findOrFail($data['loteid']);
        $tipo = TipoActividad::find($data['tipoactividadid']);

        $duplicada = $this->trazabilidad->mensajeActividadDuplicada($lote, $tipo->nombre ?? null);
        if ($duplicada !== null) {
            return back()->withInput()->with('error', $duplicada);
        }

        // Si no hay descripción, usar el tipo de actividad
        if (empty($data['descripcion'])) {
            $data['descripcion'] = $tipo->nombre ?? 'Actividad';
        }

        // Prioridad por defecto si no se envió
        if (empty($data['prioridadid'])) {
            $prioridadDefault = Prioridad::first();
            $data['prioridadid'] = $prioridadDefault ? $prioridadDefault->prioridadid : null;
        }

        $usuarioid = $this->resolverUsuarioidActividad($request, $lote);

        if (
            $request->boolean('completar')
            && (int) $usuarioid === (int) ($request->user()?->usuarioid ?? 0)
        ) {
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
        ]);

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
        $actividad->load(['lote', 'usuario', 'tipoActividad', 'prioridad']);
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
    public function marcarRealizada(Actividad $actividad)
    {
        $this->autorizarMarcarActividadCompletada(request(), $actividad);

        DB::beginTransaction();

        try {
            $actividad->fechafin = now();
            $actividad->save();

            $estadoAplicado = $this->loteEstadoPorActividad->aplicarDesdeActividad($actividad);
            $this->notificaciones->descartarActividadAsignada((int) $actividad->actividadid);
            $mensajeEstado = $estadoAplicado
                ? " El lote «{$actividad->lote->nombre}» cambió a «{$estadoAplicado}»."
                : '';

            DB::commit();

            return back()->with('success', "Actividad marcada como realizada.{$mensajeEstado}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: '.$e->getMessage());
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
        $appUrl = rtrim((string) config('app.url'), '/');
        if (! str_starts_with($return, '/') && ! str_starts_with($return, $appUrl)) {
            return null;
        }

        return $return;
    }

}