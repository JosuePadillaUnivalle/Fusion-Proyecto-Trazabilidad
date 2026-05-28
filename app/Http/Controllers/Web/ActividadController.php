<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use App\Models\Lote;
use App\Models\TipoActividad;
use App\Models\Prioridad;
use App\Models\Usuario;
use App\Models\EstadoLoteTipo;
use App\Models\HistorialEstadoLote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActividadController extends Controller
{
    public function index(Request $request)
    {
        $query = Actividad::query()->with(['lote.cultivo', 'usuario', 'tipoActividad', 'prioridad']);

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
    public function calendario()
    {
        // Estadísticas
        $stats = [
            'total' => Actividad::whereNotNull('fechainicio')->count(),
            'mes' => Actividad::whereMonth('fechainicio', now()->month)
                ->whereYear('fechainicio', now()->year)
                ->count(),
            'hoy' => Actividad::whereDate('fechainicio', now()->toDateString())->count(),
            'pendientes' => Actividad::whereNull('fechafin')->count(),
            'completadas' => Actividad::whereNotNull('fechafin')->count(),
        ];

        $actividades = Actividad::with(['lote', 'usuario', 'tipoActividad'])
            ->whereNotNull('fechainicio')
            ->orderBy('fechainicio')
            ->get();

        $eventos = $actividades->map(fn ($act) => $this->formatEventoCalendario($act))->values();

        // Datos para filtros y formulario
        $lotes = Lote::with('cultivo')->orderBy('nombre')->get();
        $usuarios = Usuario::orderBy('nombre')->get();
        $tiposActividad = TipoActividad::orderBy('nombre')->get();

        return view('actividades.calendario', compact(
            'stats',
            'eventos',
            'lotes',
            'usuarios',
            'tiposActividad'
        ));
    }

    public function create()
    {
        $lotes = Lote::with('usuario')->get();
        $tipos = TipoActividad::all();
        $prioridades = Prioridad::all();

        return view('actividades.create', compact('lotes', 'tipos', 'prioridades'));
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
        $lote = Lote::findOrFail($data['loteid']);
        $tipo = TipoActividad::find($data['tipoactividadid']);

        // Si no hay descripción, usar el tipo de actividad
        if (empty($data['descripcion'])) {
            $data['descripcion'] = $tipo->nombre ?? 'Actividad';
        }

        // Prioridad por defecto si no se envió
        if (empty($data['prioridadid'])) {
            $prioridadDefault = Prioridad::first();
            $data['prioridadid'] = $prioridadDefault ? $prioridadDefault->prioridadid : null;
        }

        Actividad::create([
            'loteid' => $data['loteid'],
            'usuarioid' => $lote->usuarioid,
            'descripcion' => $data['descripcion'],
            'fechainicio' => $data['fechainicio'] ?? now(),
            'fechafin' => $data['fechafin'] ?? null,
            'tipoactividadid' => $data['tipoactividadid'],
            'prioridadid' => $data['prioridadid'],
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        // Detectar si viene del calendario
        if ($request->has('from_calendar') || $request->header('referer') && str_contains($request->header('referer'), 'calendario')) {
            return redirect()->route('actividades.calendario')
                ->with('success', "Actividad de {$tipo->nombre} registrada para el lote {$lote->nombre}.");
        }

        return redirect()->route('actividades.index')
            ->with('success', "Actividad de {$tipo->nombre} registrada para el lote {$lote->nombre}.");
    }

    public function show(Actividad $actividad)
    {
        $actividad->load(['lote', 'usuario', 'tipoActividad', 'prioridad']);
        return view('actividades.show', compact('actividad'));
    }

    public function edit(Actividad $actividad)
    {
        $lotes = Lote::with('usuario')->get();
        $tipos = TipoActividad::all();
        $prioridades = Prioridad::all();
        $usuarios = Usuario::orderBy('nombre')->get();

        return view('actividades.edit', compact('actividad', 'lotes', 'tipos', 'prioridades', 'usuarios'));
    }

    public function update(Request $request, Actividad $actividad)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'descripcion' => 'required|string|max:200',
            'fechainicio' => 'nullable|date',
            'fechafin' => 'nullable|date|after_or_equal:fechainicio',
            'tipoactividadid' => 'required|exists:tipoactividad,tipoactividadid',
            'prioridadid' => 'required|exists:prioridad,prioridadid',
            'observaciones' => 'nullable|string|max:250',
        ]);

        // Obtener usuario del lote
        $lote = Lote::findOrFail($data['loteid']);
        $data['usuarioid'] = $lote->usuarioid;

        $actividad->update($data);

        return redirect()->route('actividades.index')->with('success', 'Actividad actualizada.');
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
        DB::beginTransaction();

        try {
            $actividad->load(['lote', 'tipoActividad']);
            $lote = $actividad->lote;
            $tipoActividad = strtolower(trim($actividad->tipoActividad->nombre ?? ''));

            // Marcar la actividad como realizada (fecha fin = hoy)
            $actividad->fechafin = now();
            $actividad->save();

            // Mapeo de tipo de actividad -> nuevo estado del lote
            $nombreEstado = $this->obtenerNuevoEstado($tipoActividad);
            $mensajeEstado = '';

            if ($nombreEstado && $lote) {
                // Buscar estado exacto (case insensitive)
                $nuevoEstado = EstadoLoteTipo::whereRaw('LOWER(nombre) = ?', [strtolower($nombreEstado)])->first();

                if ($nuevoEstado) {
                    // Actualizar estado del lote
                    $lote->estadolotetipoid = $nuevoEstado->estadolotetipoid;
                    $lote->fechamodificacion = now();
                    $lote->save();

                    // Registrar en historial
                    HistorialEstadoLote::create([
                        'loteid' => $lote->loteid,
                        'estadolotetipoid' => $nuevoEstado->estadolotetipoid,
                        'fecha_cambio' => now(),
                        'observaciones' => "Actividad '{$actividad->tipoActividad->nombre}' completada",
                        'usuarioid' => $lote->usuarioid,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $mensajeEstado = " El lote '{$lote->nombre}' cambió a estado '{$nuevoEstado->nombre}'.";
                }
            }

            DB::commit();

            return back()->with('success', "Actividad marcada como realizada.{$mensajeEstado}");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Formato de evento para FullCalendar: un día por actividad salvo rangos reales multi-día.
     * Sin fechafin = pendiente (normal); no se envía "end" para evitar barras infinitas en el mes.
     */
    private function formatEventoCalendario(Actividad $act): array
    {
        $tipo = $act->tipoActividad->nombre ?? 'Actividad';
        $lote = $act->lote->nombre ?? 'Sin lote';
        $pendiente = $act->fechafin === null;
        $inicio = Carbon::parse($act->fechainicio);

        $evento = [
            'id' => (string) $act->actividadid,
            'title' => $tipo,
            'start' => $inicio->format('Y-m-d'),
            'allDay' => true,
            'extendedProps' => [
                'id' => $act->actividadid,
                'tipo' => $tipo,
                'tipoSlug' => Str::slug($tipo),
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

        // En calendario siempre un solo día (fechainicio); el rango real se ve en detalle/lista.

        return $evento;
    }

    /**
     * Obtener el nuevo estado del lote según el tipo de actividad
     *
     * Tipos de actividad: siembra, riego, fumigación, cosecha, labranza
     * Estados disponibles: disponible, en preparación, sembrado, en producción, cosechado, en descanso
     */
    private function obtenerNuevoEstado($tipoActividad)
    {
        $tipoActividad = strtolower(trim($tipoActividad));

        // Mapeo EXACTO de actividades a estados
        $mapeo = [
            'labranza' => 'en preparación',
            'siembra' => 'sembrado',
            'riego' => 'en producción',
            'fumigación' => 'en producción',
            'fumigacion' => 'en producción',
            'cosecha' => 'cosechado',
        ];

        return $mapeo[$tipoActividad] ?? null;
    }
}