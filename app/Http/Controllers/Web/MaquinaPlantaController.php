<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DestinoProduccion;
use App\Models\MaquinaPlanta;
use App\Models\ProcesoPlanta;
use App\Support\PlantillaTransformacionDisponibilidad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MaquinaPlantaController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if ($request->user()?->hasRole('agricultor') || $request->user()?->hasRole('transportista')) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);

        $stats = [
            'total' => MaquinaPlanta::count(),
            'activas' => MaquinaPlanta::where('activo', true)->count(),
            'con_codigo' => MaquinaPlanta::whereNotNull('codigo')->where('codigo', '!=', '')->count(),
        ];

        $maquinas = $query->orderBy('maquinaplantaid', 'desc')->paginate(15)->withQueryString();

        return view('maquinas_planta.index', compact('maquinas', 'stats'));
    }

    public function create(): View
    {
        return view('maquinas_planta.create');
    }

    public function show(Request $request, MaquinaPlanta $maquinas_plantum): View
    {
        $maquina = $maquinas_plantum;
        $maquina->loadCount('producciones');

        $query = $maquina->producciones()
            ->with(['lote.cultivo', 'unidadMedida', 'destino', 'procesoPlanta']);

        if ($request->filled('buscar')) {
            $buscar = '%'.trim((string) $request->buscar).'%';
            $query->where(function ($q) use ($buscar) {
                $q->whereHas('lote', fn ($lq) => $lq->where('nombre', 'like', $buscar))
                    ->orWhereHas('procesoPlanta', fn ($pq) => $pq->where('nombre', 'like', $buscar));
            });
        }

        if ($request->filled('proceso')) {
            $query->where('procesoplantaid', (int) $request->proceso);
        }

        if ($request->filled('destino')) {
            $query->where('destinoproduccionid', (int) $request->destino);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fechacosecha', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fechacosecha', '<=', $request->fecha_hasta);
        }

        $producciones = $query->orderByDesc('produccionid')->paginate(15)->withQueryString();

        $idsProceso = $maquina->producciones()->whereNotNull('procesoplantaid')->distinct()->pluck('procesoplantaid');
        $idsDestino = $maquina->producciones()->whereNotNull('destinoproduccionid')->distinct()->pluck('destinoproduccionid');

        $procesosFiltro = ProcesoPlanta::whereIn('procesoplantaid', $idsProceso)->orderBy('nombre')->get(['procesoplantaid', 'nombre']);
        $destinosFiltro = DestinoProduccion::whereIn('destinoproduccionid', $idsDestino)->orderBy('nombre')->get(['destinoproduccionid', 'nombre']);

        return view('maquinas_planta.show', compact('maquina', 'producciones', 'procesosFiltro', 'destinosFiltro'));
    }

    public function edit(MaquinaPlanta $maquinas_plantum): View
    {
        return view('maquinas_planta.edit', ['maquina' => $maquinas_plantum]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'nullable|string|max:60',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable|boolean',
            'imagen' => 'nullable|file|mimes:jpeg,jpg,png,webp,gif|max:4096',
        ]);
        $data['activo'] = $request->boolean('activo', true);
        if ($request->hasFile('imagen')) {
            $data['imagenurl'] = $this->procesarImagen($request) ?? $data['imagenurl'] ?? null;
        }

        $maquina = MaquinaPlanta::create($data);

        return redirect()
            ->route('maquinas-planta.show', $maquina)
            ->with('success', 'Máquina registrada correctamente.');
    }

    public function update(Request $request, MaquinaPlanta $maquinas_plantum): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'nullable|string|max:60',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable|boolean',
            'imagen' => 'nullable|file|mimes:jpeg,jpg,png,webp,gif|max:4096',
            'quitar_imagen' => 'nullable|boolean',
        ]);
        $data['activo'] = $request->boolean('activo');

        if ($request->boolean('quitar_imagen')) {
            $this->eliminarImagen($maquinas_plantum->imagenurl);
            $data['imagenurl'] = null;
        } elseif ($request->hasFile('imagen')) {
            $nueva = $this->procesarImagen($request, $maquinas_plantum->imagenurl);
            if ($nueva) {
                $data['imagenurl'] = $nueva;
            }
        }

        unset($data['imagen'], $data['quitar_imagen']);
        $maquinas_plantum->update($data);

        return redirect()
            ->route('maquinas-planta.show', $maquinas_plantum)
            ->with('success', 'Máquina actualizada.');
    }

    public function destroy(MaquinaPlanta $maquinas_plantum): RedirectResponse
    {
        $this->eliminarImagen($maquinas_plantum->imagenurl);
        $maquinas_plantum->delete();

        return redirect()->route('maquinas-planta.index')->with('success', 'Máquina eliminada.');
    }

    public function toggleActivo(MaquinaPlanta $maquinas_plantum): RedirectResponse
    {
        $bloqueadasAntes = PlantillaTransformacionDisponibilidad::idsBloqueadas();

        $maquinas_plantum->update(['activo' => ! $maquinas_plantum->activo]);

        $mensaje = $maquinas_plantum->activo
            ? 'La máquina quedó activa y disponible en registro.'
            : 'La máquina quedó en mantenimiento.';

        if (! $maquinas_plantum->activo) {
            $nuevasBloqueadas = count(array_diff(
                PlantillaTransformacionDisponibilidad::idsBloqueadas(),
                $bloqueadasAntes
            ));
            if ($nuevasBloqueadas > 0) {
                $mensaje .= ' '.$nuevasBloqueadas.' proceso(s) de transformación quedaron temporalmente no disponibles.';
            }
        } else {
            $mensaje .= ' Los procesos de transformación vinculados se reevaluarán automáticamente.';
        }

        return back()->with('success', $mensaje);
    }

    private function filteredQuery(Request $request)
    {
        $query = MaquinaPlanta::query();

        if ($request->filled('estado')) {
            if ($request->estado === 'activa') {
                $query->where('activo', true);
            } elseif (in_array($request->estado, ['inactiva', 'mantenimiento'], true)) {
                $query->where('activo', false);
            }
        }

        if ($request->boolean('con_codigo')) {
            $query->whereNotNull('codigo')->where('codigo', '!=', '');
        }

        if ($request->filled('buscar')) {
            $buscar = '%'.trim((string) $request->buscar).'%';
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'like', $buscar)
                    ->orWhere('codigo', 'like', $buscar)
                    ->orWhere('descripcion', 'like', $buscar);
            });
        }

        return $query;
    }

    private function procesarImagen(Request $request, ?string $imagenAnterior = null): ?string
    {
        if (! $request->hasFile('imagen')) {
            return $imagenAnterior;
        }

        $file = $request->file('imagen');
        $nombre = 'maquina_'.uniqid('', true).'.'.$file->getClientOriginalExtension();

        $this->eliminarImagen($imagenAnterior);

        $ruta = $file->storeAs('maquinas_planta', $nombre, 'public');
        if ($ruta === false) {
            Log::error('No se pudo guardar la imagen de máquina en disco público.');

            return $imagenAnterior;
        }

        return $ruta;
    }

    private function eliminarImagen(?string $imagenurl): void
    {
        if (! $imagenurl) {
            return;
        }

        $rel = $imagenurl;
        if (str_contains($rel, '/storage/')) {
            $rel = ltrim(str_replace('/storage/', '', parse_url($rel, PHP_URL_PATH) ?? ''), '/');
        } elseif (str_starts_with($rel, 'storage/')) {
            $rel = substr($rel, 8);
        }

        if ($rel !== '' && ! str_contains($rel, '://')) {
            Storage::disk('public')->delete($rel);
        }
    }
}
