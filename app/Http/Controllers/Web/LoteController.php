<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Lote;
use App\Models\Usuario;
use App\Models\Cultivo;
use App\Models\ActorAbastecimiento;
use App\Models\EstadoLoteTipo;
use App\Models\Produccion;
use App\Support\LoteDefaults;
use App\Services\OperacionAgricolaAutomaticaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
// use App\Services\SupabaseStorage; // COMENTADO TEMPORALMENTE

class LoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Lote::query()
            ->with(['usuario', 'cultivo', 'estadoTipo', 'actorAbastecimiento']);

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
            'en_produccion' => Lote::whereHas('estadoTipo', fn ($q) => $q->whereRaw('LOWER(TRIM(nombre)) = ?', ['en producción']))->count(),
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
        $estados = EstadoLoteTipo::orderBy('nombre')->get();
        $usuarios = Usuario::query()
            ->where('activo', true)
            ->whereIn('role', ['agricultor', 'operador', 'admin'])
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
            'en_produccion' => Lote::whereHas('estadoTipo', fn ($q) => $q->whereRaw('LOWER(TRIM(nombre)) = ?', ['en producción']))->count(),
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
        $estados = EstadoLoteTipo::orderBy('nombre')->get();

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
        $cultivos = Cultivo::orderBy('nombre')->get();
        $mostrarSelectorPropietario = $user && (
            $user->hasRole('admin') || $user->hasRole('operador') || $user->hasRole('Admin')
        );
        $usuarios = $mostrarSelectorPropietario
            ? Usuario::query()
                ->where('activo', true)
                ->whereIn('role', ['agricultor', 'operador', 'admin'])
                ->orderBy('nombre')
                ->get()
            : collect();

        $propietarioPorDefecto = (int) ($user?->usuarioid ?? 0);

        return view('lotes.create', compact(
            'cultivos',
            'mostrarSelectorPropietario',
            'usuarios',
            'propietarioPorDefecto'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'usuarioid' => 'nullable|exists:usuario,usuarioid',
            'nombre' => 'required|string|max:100',
            'ubicacion' => 'nullable|string|max:200',
            'superficie' => 'required|numeric|min:0.01',
            'cultivoid' => 'nullable|exists:cultivo,cultivoid',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
        ]);

        if (empty($data['usuarioid'])) {
            $data['usuarioid'] = (int) auth()->id();
        }

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

        return redirect()->route('lotes.index')->with('success', 'Lote creado exitosamente.');
    }

    /**
     * Fuerza sincronización operativa (clima, actividades desde insumos/cosechas, riegos).
     */
    public function sincronizarOperacion(Request $request, OperacionAgricolaAutomaticaService $service)
    {
        abort_unless(
            $request->user()?->hasRole('admin') || $request->user()?->hasRole('operador'),
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

    public function show(Lote $lote)
    {
        $lote->load([
            'usuario',
            'cultivo',
            'estadoTipo',
            'historialEstados.estadoTipo',
            'historialEstados.usuario',
            'loteInsumos.insumo',
            'loteInsumos.usuario',
            'actividades.tipoActividad',
            'actividades.usuario',
            'producciones.unidadMedida',
            'producciones.destino',
            'clima'
        ]);

        // Construir línea de tiempo/trazabilidad
        $trazabilidad = collect();

        // 1. Fecha de creación/siembra
        if ($lote->fechasiembra) {
            $trazabilidad->push([
                'fecha' => $lote->fechasiembra,
                'tipo' => 'siembra',
                'titulo' => 'Siembra Iniciada',
                'descripcion' => 'Cultivo: ' . ($lote->cultivo->nombre ?? 'No especificado'),
                'icono' => 'seedling',
                'color' => 'success'
            ]);
        }

        // 2. Historial de estados
        foreach ($lote->historialEstados as $historial) {
            $trazabilidad->push([
                'fecha' => $historial->fecha_cambio,
                'tipo' => 'estado',
                'titulo' => 'Cambio de Estado: ' . ($historial->estadoTipo->nombre ?? ''),
                'descripcion' => $historial->observaciones ?? 'Sin observaciones',
                'usuario' => $historial->usuario->nombre ?? null,
                'icono' => 'exchange-alt',
                'color' => 'info'
            ]);
        }

        // 3. Aplicación de insumos
        foreach ($lote->loteInsumos as $insumo) {
            $trazabilidad->push([
                'fecha' => $insumo->fechauo,
                'tipo' => 'insumo',
                'titulo' => 'Aplicación: ' . ($insumo->insumo->nombre ?? 'Insumo'),
                'descripcion' => 'Cantidad: ' . $insumo->cantidadusada . ' - ' . ($insumo->observaciones ?? ''),
                'usuario' => $insumo->usuario->nombre ?? null,
                'icono' => 'flask',
                'color' => 'warning'
            ]);
        }

        // 4. Actividades realizadas
        foreach ($lote->actividades as $actividad) {
            $trazabilidad->push([
                'fecha' => $actividad->fechainicio,
                'tipo' => 'actividad',
                'titulo' => $actividad->tipoActividad->nombre ?? 'Actividad',
                'descripcion' => $actividad->descripcion ?? 'Sin descripción',
                'usuario' => $actividad->usuario->nombre ?? null,
                'icono' => 'tasks',
                'color' => 'primary',
                'completada' => $actividad->fechafin !== null
            ]);
        }

        // 5. Producciones/Cosechas
        foreach ($lote->producciones as $produccion) {
            $trazabilidad->push([
                'fecha' => $produccion->fechacosecha,
                'tipo' => 'cosecha',
                'titulo' => 'Cosecha Registrada',
                'descripcion' => 'Cantidad: ' . number_format($produccion->cantidad, 2) . ' ' . ($produccion->unidadMedida->abreviatura ?? 'kg') . ' - Destino: ' . ($produccion->destino->nombre ?? 'No especificado'),
                'icono' => 'tractor',
                'color' => 'success'
            ]);
        }

        // Ordenar por fecha descendente
        $trazabilidad = $trazabilidad->sortByDesc('fecha')->values();

        // Estadísticas del lote
        $diasDesdeSiembra = null;
        if ($lote->fechasiembra) {
            $fechaSiembra = \Carbon\Carbon::parse($lote->fechasiembra);
            // Si la fecha de siembra es futura, mostrar 0; si es pasada, mostrar días transcurridos
            if ($fechaSiembra->isFuture()) {
                $diasDesdeSiembra = 0;
            } else {
                $diasDesdeSiembra = (int) $fechaSiembra->diffInDays(now());
            }
        }

        $estadisticas = [
            'total_insumos' => $lote->loteInsumos->count(),
            'total_actividades' => $lote->actividades->count(),
            'actividades_completadas' => $lote->actividades->whereNotNull('fechafin')->count(),
            'actividades_pendientes' => $lote->actividades->whereNull('fechafin')->count(),
            'total_aplicaciones' => $lote->loteInsumos->count(),
            'total_cosechas' => $lote->producciones->count(),
            'produccion_total' => $lote->producciones->sum('cantidad'),
            'dias_desde_siembra' => $diasDesdeSiembra,
        ];

        return view('lotes.show', compact('lote', 'trazabilidad', 'estadisticas'));
    }

    public function edit(Lote $lote)
    {
        $usuarios = Usuario::all();
        $cultivos = Cultivo::all();
        $estados = EstadoLoteTipo::all();
        $actores = ActorAbastecimiento::where('activo', true)->orderBy('nombre')->get();

        return view('lotes.edit', compact('lote', 'usuarios', 'cultivos', 'estados', 'actores'));
    }

    public function update(Request $request, Lote $lote)
    {
        $data = $request->validate([
            'usuarioid' => 'required|exists:usuario,usuarioid',
            'nombre' => 'required|string|max:100',
            'ubicacion' => 'nullable|string|max:200',
            'superficie' => 'required|numeric|min:0',
            'cultivoid' => 'nullable|exists:cultivo,cultivoid',
            'actorid' => 'nullable|exists:actor_abastecimiento,actorid',
            'codigo_trazabilidad' => 'nullable|string|max:80',
            'fechasiembra' => 'nullable|date',
            'estadolotetipoid' => 'nullable|exists:estadolote_tipo,estadolotetipoid',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'imagen' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('imagen')) {
            try {
                $file = $request->file('imagen');
                $mime = $file->getMimeType();
                $base64 = base64_encode(file_get_contents($file->getRealPath()));
                $data['imagenurl'] = "data:$mime;base64,$base64";
            } catch (\Exception $e) {
                // Log error
            }
        }

        unset($data['imagen']);

        $lote->update(LoteDefaults::enrich($data, false));

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
}