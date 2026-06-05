<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Lote;
use App\Models\Usuario;
use App\Models\Cultivo;
use App\Models\EstadoLoteTipo;
use App\Models\Produccion;
use App\Support\EstadoLoteCatalogo;
use App\Support\LoteDefaults;
use App\Support\LoteEstadoPorActividad;
use App\Support\LoteTrazabilidadService;
use App\Services\NotificacionUsuarioService;
use App\Support\UsuarioRol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
// use App\Services\SupabaseStorage; // COMENTADO TEMPORALMENTE

class LoteController extends Controller
{
    /** Roles que pueden ser responsables de un lote (nunca admin). */
    private const ROLES_RESPONSABLE_LOTE = ['agricultor'];

    public function __construct(
        private LoteTrazabilidadService $trazabilidadService,
        private LoteEstadoPorActividad $loteEstadoPorActividad,
        private NotificacionUsuarioService $notificaciones,
    ) {}

    public function index(Request $request)
    {
        $query = Lote::query()
            ->with(['usuario', 'cultivo', 'estadoTipo']);

        if (UsuarioRol::debeAcotarPorAsignacion($request->user())) {
            $query->where('usuarioid', (int) $request->user()->usuarioid);
        } elseif (
            UsuarioRol::esJefeAgricultor($request->user())
            && ! UsuarioRol::esAdminGlobal($request->user())
        ) {
            $empleadoIds = Usuario::query()
                ->where('supervisor_usuarioid', $request->user()->usuarioid)
                ->whereIn('role', UsuarioRol::rolesEmpleadosGestionables($request->user()))
                ->pluck('usuarioid');

            $query->whereIn('usuarioid', $empleadoIds);
        }

        if ($request->filled('q')) {
            $term = '%'.trim((string) $request->q).'%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', $term)
                    ->orWhere('ubicacion', 'like', $term)
                    ->orWhere('codigo_trazabilidad', 'like', $term);
            });
        }

        if ($request->filled('cultivoid')) {
            $query->where('cultivoid', (int) $request->cultivoid);
        }

        if ($request->filled('estadolotetipoid')) {
            $query->where('estadolotetipoid', (int) $request->estadolotetipoid);
        }

        if ($request->filled('usuarioid')) {
            $query->where('usuarioid', (int) $request->usuarioid);
        }

        if ($request->filled('con_mapa')) {
            if ($request->con_mapa === '1') {
                $query->whereNotNull('latitud')->whereNotNull('longitud');
            } elseif ($request->con_mapa === '0') {
                $query->where(function ($q) {
                    $q->whereNull('latitud')->orWhereNull('longitud');
                });
            }
        }

        $stats = [
            'total' => Lote::count(),
            'en_produccion' => Lote::whereHas('estadoTipo', fn ($q) => $q->whereRaw('LOWER(TRIM(nombre)) = ?', ['en crecimiento']))->count(),
            'sembrados' => Lote::whereHas('estadoTipo', fn ($q) => $q->whereRaw('LOWER(TRIM(nombre)) = ?', ['sembrado']))->count(),
            'hectareas' => round((float) (Lote::sum('superficie') ?? 0), 2),
            'con_mapa' => Lote::whereNotNull('latitud')->whereNotNull('longitud')->count(),
            'sin_gps' => Lote::where(function ($q) {
                $q->whereNull('latitud')->orWhereNull('longitud');
            })->count(),
        ];

        $lotes = $query->orderByDesc('loteid')->paginate(15)->withQueryString();

        $filtros = $request->only(['q', 'cultivoid', 'estadolotetipoid', 'usuarioid', 'con_mapa']);
        $cultivos = Cultivo::orderBy('nombre')->get();
        $estados = EstadoLoteCatalogo::paraSelect();
        $usuarios = Usuario::query()
            ->where('activo', true)
            ->whereIn('role', ['agricultor', 'admin'])
            ->orderBy('nombre')
            ->get();

        return view('lotes.index', compact('lotes', 'stats', 'filtros', 'cultivos', 'estados', 'usuarios'));
    }

    /**
     * Mapa interactivo de todos los lotes
     */
    public function mapa()
    {
        // Estadísticas
        $stats = [
            'total' => Lote::count(),
            'en_produccion' => Lote::whereHas('estadoTipo', fn ($q) => $q->whereRaw('LOWER(TRIM(nombre)) = ?', ['en crecimiento']))->count(),
            'cosechados' => Lote::whereHas('estadoTipo', fn ($q) => $q->whereRaw('LOWER(TRIM(nombre)) = ?', ['cosechado']))->count(),
            'hectareas' => (float) (Lote::sum('superficie') ?? 0),
            'en_mapa' => Lote::whereNotNull('latitud')->whereNotNull('longitud')->count(),
            'sin_coordenadas' => Lote::where(function ($q) {
                $q->whereNull('latitud')->orWhereNull('longitud');
            })->count(),
        ];

        // Lotes con coordenadas para el mapa
        $lotesConCoordenadas = Lote::with(['usuario', 'cultivo', 'estadoTipo'])
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->get()
            ->map(function ($lote) {
                return [
                    'id' => $lote->loteid,
                    'nombre' => $lote->nombre,
                    'latitud' => (float) $lote->latitud,
                    'longitud' => (float) $lote->longitud,
                    'superficie' => (float) $lote->superficie,
                    'ubicacion' => $lote->ubicacion,
                    'propietario' => ($lote->usuario->nombre ?? '') . ' ' . ($lote->usuario->apellido ?? ''),
                    'cultivo' => $lote->cultivo->nombre ?? null,
                    'estado' => $lote->estadoTipo->nombre ?? 'disponible',
                    'usuarioid' => $lote->usuarioid,
                    'cultivoid' => $lote->cultivoid,
                    'estadoid' => $lote->estadolotetipoid,
                ];
            });

        // Lotes sin coordenadas (para alertas)
        $lotesSinCoordenadas = Lote::with(['usuario'])
            ->where(function ($q) {
                $q->whereNull('latitud')->orWhereNull('longitud');
            })
            ->limit(5)
            ->get();

        // Top lotes por producción
        $topLotes = Lote::with(['usuario', 'cultivo'])
            ->select('lote.*')
            ->selectSub(
                Produccion::selectRaw('COALESCE(SUM(cantidad), 0)')
                    ->whereColumn('produccion.loteid', 'lote.loteid'),
                'total_produccion'
            )
            ->orderByDesc('total_produccion')
            ->limit(5)
            ->get();

        // Alertas climáticas (placeholder - se puede conectar con la tabla Clima)
        $alertasClimaticas = collect([]);

        // Lotes que podrían necesitar insumos (en producción sin actividad reciente)
        $lotesStockBajo = Lote::with(['usuario'])
            ->whereHas('estadoTipo', fn($q) => $q->whereIn('nombre', ['sembrado', 'en producción']))
            ->whereNotNull('latitud')
            ->whereNotNull('longitud')
            ->limit(3)
            ->get();

        // Datos para filtros
        $usuarios = Usuario::orderBy('nombre')->get();
        $cultivos = Cultivo::orderBy('nombre')->get();
        $estados = EstadoLoteCatalogo::paraSelect();

        return view('lotes.mapa', compact(
            'stats',
            'lotesConCoordenadas',
            'lotesSinCoordenadas',
            'topLotes',
            'alertasClimaticas',
            'lotesStockBajo',
            'usuarios',
            'cultivos',
            'estados'
        ));
    }

    public function create()
    {
        $user = auth()->user();
        $mostrarSelectorPropietario = $this->puedeDesignarResponsableLote($user);
        $responsableSelectorParams = $this->paramsSelectorResponsable($user);
        $esJefeAgricultorDesignando = $user && UsuarioRol::esJefeAgricultor($user) && ! UsuarioRol::esAdminGlobal($user);

        $propietarioPorDefecto = $this->responsableLotePorDefecto($user);
        $usuarioidInicial = (int) old('usuarioid', $propietarioPorDefecto ?? 0);
        $responsableLabel = null;
        if ($mostrarSelectorPropietario && $usuarioidInicial && $this->puedeAsignarUsuarioALote($user, $usuarioidInicial)) {
            $resp = Usuario::find($usuarioidInicial);
            $responsableLabel = $resp ? trim($resp->nombre.' '.($resp->apellido ?? '')) : null;
        } elseif ($mostrarSelectorPropietario) {
            $usuarioidInicial = 0;
        }

        $cultivoidInicial = old('cultivoid');
        $cultivoLabel = $cultivoidInicial ? Cultivo::find($cultivoidInicial)?->nombre : null;

        return view('lotes.create', compact(
            'mostrarSelectorPropietario',
            'propietarioPorDefecto',
            'usuarioidInicial',
            'responsableLabel',
            'cultivoidInicial',
            'cultivoLabel',
            'responsableSelectorParams',
            'esJefeAgricultorDesignando'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'usuarioid' => ['nullable', 'exists:usuario,usuarioid', $this->reglaResponsableLote($request->user())],
            'nombre' => 'required|string|max:100',
            'ubicacion' => 'nullable|string|max:200',
            'superficie' => 'required|numeric|min:0.01',
            'cultivoid' => 'nullable|exists:cultivo,cultivoid',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'imagen' => 'nullable|image|max:2048',
        ]);

        $data['usuarioid'] = $this->resolverUsuarioidLote($request, $data['usuarioid'] ?? null);

        if (empty($data['ubicacion']) && ! empty($data['latitud']) && ! empty($data['longitud'])) {
            $data['ubicacion'] = sprintf(
                'Parcela GPS %.5f, %.5f',
                (float) $data['latitud'],
                (float) $data['longitud']
            );
        }

        // SUPABASE UPLOAD
        if ($request->hasFile('imagen')) {
            try {
                $file = $request->file('imagen');
                $filename = 'lote_' . uniqid() . '.' . $file->getClientOriginalExtension();

                // Instanciar servicio (no inyección de dependencias para no romper constructor existente si no es necesario)
                $supabase = new \App\Services\SupabaseStorage();
                $response = $supabase->upload($filename, file_get_contents($file), $file->getMimeType());

                if ($response->successful()) {
                    $data['imagenurl'] = $supabase->getPublicUrl($filename);
                }
            } catch (\Exception $e) {
                // Silently fail or log error, don't stop creation
                \Illuminate\Support\Facades\Log::error('Supabase upload error: ' . $e->getMessage());
            }
        }

        unset($data['imagen']);

        $data = LoteDefaults::enrich($data, true);
        $lote = Lote::create($data);
        LoteDefaults::registrarHistorialInicial($lote);
        $this->notificaciones->loteAsignado($lote);

        return redirect()->route('lotes.index')->with('success', 'Lote creado exitosamente.');
    }

    /**
     * Fuerza sincronización operativa (clima, actividades desde insumos/cosechas, riegos).
     */
    public function sincronizarOperacion(Request $request, OperacionAgricolaAutomaticaService $service)
    {
        abort_unless(
            $request->user()?->hasRole('admin') || $request->user()?->hasRole('agricultor'),
            403
        );

        $r = $service->sincronizarTodo();

        return redirect()
            ->route('lotes.index')
            ->with('success', sprintf(
                'Operación sincronizada: %d clima, %d act. insumo, %d cosechas, %d riegos, %d alertas clima.',
                $r['clima_lotes'],
                $r['actividades_insumo'],
                $r['actividades_cosecha'],
                $r['actividades_riego'],
                $r['actividades_clima']
            ));
    }

    public function show(Lote $lote, Request $request)
    {
        $this->autorizarLoteAsignado($request, $lote);

        return view('lotes.show', $this->trazabilidadService->buildLoteDetalleBase($lote));
    }

    public function cambiarEstadoForm(Lote $lote, Request $request)
    {
        $slug = (string) $request->query('estado', '');
        if (! isset(EstadoLoteCatalogo::ESTADOS[$slug])) {
            abort(404);
        }

        if ($slug === 'sembrado') {
            return redirect(EstadoLoteCatalogo::urlCambioEstado($lote, 'sembrado'));
        }

        return view('lotes.cambiar-estado', [
            'lote' => $lote,
            'slug' => $slug,
        ]);
    }

    public function cambiarEstadoStore(Request $request, Lote $lote)
    {
        $data = $request->validate([
            'estado' => 'required|string',
            'motivo' => 'required|string|max:500',
        ]);

        $slug = $data['estado'];
        if (! isset(EstadoLoteCatalogo::ESTADOS[$slug]) || $slug === 'sembrado') {
            abort(422);
        }

        $estadoAplicado = $this->loteEstadoPorActividad->aplicarCambioManual(
            $lote,
            $slug,
            $data['motivo'],
            $request->user()
        );

        if (! $estadoAplicado) {
            return back()->with('warning', 'El lote ya se encuentra en ese estado.');
        }

        return redirect()
            ->route('lotes.trazabilidad', $lote)
            ->with('success', "Estado actualizado a «{$estadoAplicado}».")
            ->withFragment('historial-eventos');
    }

    public function trazabilidad(Lote $lote, Request $request)
    {
        return view('lotes.trazabilidad', $this->trazabilidadService->dashboardLote($lote, $request));
    }

    public function ubicacion(Lote $lote)
    {
        return view('lotes.ubicacion', $this->trazabilidadService->buildLoteDetalleBase($lote));
    }

    public function edit(Lote $lote, Request $request)
    {
        $user = $request->user();
        $this->autorizarLoteAsignado($request, $lote);

        $lote->load(['usuario', 'cultivo', 'estadoTipo']);

        $puedeDesignarResponsable = $this->puedeDesignarResponsableLote($user);
        $responsableSelectorParams = $this->paramsSelectorResponsable($user);
        $esJefeAgricultorDesignando = $user && UsuarioRol::esJefeAgricultor($user) && ! UsuarioRol::esAdminGlobal($user);

        $responsableLabel = ($lote->usuario && $this->puedeSerResponsableDeLote((int) $lote->usuarioid))
            ? trim($lote->usuario->nombre.' '.($lote->usuario->apellido ?? ''))
            : null;
        $cultivoLabel = $lote->cultivo?->nombre;
        $mostrarFechaSiembra = (bool) $lote->fechasiembra;

        return view('lotes.edit', compact(
            'lote',
            'responsableLabel',
            'cultivoLabel',
            'mostrarFechaSiembra',
            'puedeDesignarResponsable',
            'responsableSelectorParams',
            'esJefeAgricultorDesignando'
        ));
    }

    public function update(Request $request, Lote $lote)
    {
        $this->autorizarLoteAsignado($request, $lote);

        $data = $request->validate([
            'usuarioid' => ['required', 'exists:usuario,usuarioid', $this->reglaResponsableLote($request->user())],
            'nombre' => 'required|string|max:100',
            'ubicacion' => 'nullable|string|max:200',
            'superficie' => 'required|numeric|min:0',
            'cultivoid' => 'nullable|exists:cultivo,cultivoid',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
        ]);

        $anteriorUsuarioid = (int) $lote->usuarioid;
        $lote->update(LoteDefaults::enrich($data, false));
        $lote->refresh();
        $this->notificaciones->loteAsignado($lote, $anteriorUsuarioid);

        return redirect()->route('lotes.index')->with('success', 'Lote actualizado.');
    }

    public function destroy(Lote $lote)
    {
        // ELIMINAR IMAGEN LOCAL
        if ($lote->imagenurl) {
            $path = str_replace('/storage/', '', $lote->imagenurl);
            Storage::disk('public')->delete($path);
        }

        $lote->delete();

        return redirect()->route('lotes.index')->with('success', 'Lote eliminado.');
    }

    private function reglaResponsableLote(?Usuario $actor = null): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($actor): void {
            if ($value === null || $value === '') {
                return;
            }
            if (! $this->puedeAsignarUsuarioALote($actor ?? auth()->user(), (int) $value)) {
                $fail(UsuarioRol::esJefeAgricultor($actor ?? auth()->user())
                    ? 'Debe elegir un agricultor de su equipo.'
                    : 'El administrador no puede ser responsable de un lote. Elija un agricultor.');
            }
        };
    }

    private function puedeSerResponsableDeLote(int $usuarioid): bool
    {
        $usuario = Usuario::find($usuarioid);
        if (! $usuario || ! $usuario->activo) {
            return false;
        }

        if ($this->usuarioEsAdmin($usuario)) {
            return false;
        }

        if ($usuario->hasRole('agricultor')) {
            return true;
        }

        return in_array(strtolower((string) ($usuario->role ?? '')), self::ROLES_RESPONSABLE_LOTE, true);
    }

    private function usuarioEsAdmin(Usuario $usuario): bool
    {
        return UsuarioRol::esAdminGlobal($usuario)
            || in_array(strtolower((string) ($usuario->role ?? '')), ['admin'], true);
    }

    private function autorizarLoteAsignado(Request $request, Lote $lote): void
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        if (UsuarioRol::esJefeAgricultor($user) && ! UsuarioRol::esAdminGlobal($user)) {
            $empleadoIds = Usuario::query()
                ->where('supervisor_usuarioid', $user->usuarioid)
                ->whereIn('role', UsuarioRol::rolesEmpleadosGestionables($user))
                ->pluck('usuarioid')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (! in_array((int) $lote->usuarioid, $empleadoIds, true)) {
                abort(403, 'No tienes acceso a este lote.');
            }

            return;
        }

        if (! UsuarioRol::debeAcotarPorAsignacion($user)) {
            return;
        }

        if ((int) $lote->usuarioid !== (int) $user->usuarioid) {
            abort(403, 'No tienes acceso a este lote.');
        }
    }

    private function puedeDesignarResponsableLote(?Usuario $user): bool
    {
        return $user && (
            UsuarioRol::esAdminGlobal($user) || UsuarioRol::esJefeAgricultor($user)
        );
    }

    /** @return array<string, mixed> */
    private function paramsSelectorResponsable(?Usuario $user): array
    {
        $params = ['roles' => 'agricultor'];

        if ($user && UsuarioRol::esJefeAgricultor($user) && ! UsuarioRol::esAdminGlobal($user)) {
            $params['supervisor_usuarioid'] = $user->usuarioid;
        }

        return $params;
    }

    private function puedeAsignarUsuarioALote(?Usuario $actor, int $usuarioid): bool
    {
        if (! $this->puedeSerResponsableDeLote($usuarioid)) {
            return false;
        }

        if (! $actor || UsuarioRol::esAdminGlobal($actor)) {
            return true;
        }

        if (UsuarioRol::esJefeAgricultor($actor)) {
            $empleado = Usuario::find($usuarioid);

            return $empleado
                && (int) $empleado->supervisor_usuarioid === (int) $actor->usuarioid;
        }

        return (int) $usuarioid === (int) $actor->usuarioid;
    }

    private function responsableLotePorDefecto(?Usuario $user): ?int
    {
        if (! $user || $this->usuarioEsAdmin($user)) {
            return null;
        }

        $role = strtolower((string) ($user->role ?? ''));
        if (in_array($role, self::ROLES_RESPONSABLE_LOTE, true)) {
            return (int) $user->usuarioid;
        }

        return null;
    }

    private function resolverUsuarioidLote(Request $request, mixed $usuarioid): int
    {
        $auth = $request->user();

        if ($usuarioid && $this->puedeAsignarUsuarioALote($auth, (int) $usuarioid)) {
            return (int) $usuarioid;
        }

        if ($auth && ! $this->usuarioEsAdmin($auth)) {
            $defecto = $this->responsableLotePorDefecto($auth);
            if ($defecto) {
                return $defecto;
            }
        }

        throw ValidationException::withMessages([
            'usuarioid' => 'Debe asignar un agricultor como responsable del lote. El administrador solo supervisa.',
        ]);
    }
}