<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MaquinaPlanta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaquinaPlantaController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if ($request->user()?->hasRole('agricultor') || $request->user()?->hasRole('transportista') || $request->user()?->hasRole('almacen')) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $query = $this->filteredQuery($request);

        $stats = [
            'total' => (clone $query)->count(),
            'activas' => MaquinaPlanta::where('activo', true)->count(),
            'con_codigo' => MaquinaPlanta::whereNotNull('codigo')->where('codigo', '!=', '')->count(),
        ];

        $maquinas = $query->orderBy('maquinaplantaid', 'desc')->paginate(15)->withQueryString();

        return view('maquinas_planta.index', compact('maquinas', 'stats'));
    }

    public function show(MaquinaPlanta $maquinas_plantum): View
    {
        $maquina = $maquinas_plantum;
        $maquina->loadCount('producciones');

        $produccionesRecientes = $maquina->producciones()
            ->with(['lote.cultivo', 'unidadMedida', 'destino', 'procesoPlanta'])
            ->orderByDesc('produccionid')
            ->limit(20)
            ->get();

        return view('maquinas_planta.show', compact('maquina', 'produccionesRecientes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'nullable|string|max:60',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable|boolean',
        ]);
        $data['activo'] = $request->boolean('activo', true);
        MaquinaPlanta::create($data);

        return redirect()->route('maquinas-planta.index')->with('success', 'Máquina creada.');
    }

    public function update(Request $request, MaquinaPlanta $maquinas_plantum): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'nullable|string|max:60',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable|boolean',
        ]);
        $data['activo'] = $request->boolean('activo', false);
        $maquinas_plantum->update($data);

        return redirect()->route('maquinas-planta.index')->with('success', 'Máquina actualizada.');
    }

    public function destroy(MaquinaPlanta $maquinas_plantum): RedirectResponse
    {
        $maquinas_plantum->delete();

        return redirect()->route('maquinas-planta.index')->with('success', 'Máquina eliminada.');
    }

    private function filteredQuery(Request $request)
    {
        $query = MaquinaPlanta::query();

        if ($request->filled('estado')) {
            if ($request->estado === 'activa') {
                $query->where('activo', true);
            } elseif ($request->estado === 'inactiva') {
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
}
