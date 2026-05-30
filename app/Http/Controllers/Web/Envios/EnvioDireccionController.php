<?php

namespace App\Http\Controllers\Web\Envios;

use App\Http\Controllers\Controller;
use App\Models\DireccionLogistica;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnvioDireccionController extends Controller
{
    public function __construct()
    {
        $this->middleware('action.permission:direcciones,read')->only(['index', 'show']);
        $this->middleware('action.permission:direcciones,create')->only(['create', 'store']);
        $this->middleware('action.permission:direcciones,update')->only(['edit', 'update']);
        $this->middleware('action.permission:direcciones,delete')->only(['destroy']);
    }

    public function index(Request $request): View
    {
        $q = DireccionLogistica::query();

        if ($request->filled('buscar')) {
            $b = '%'.trim((string) $request->buscar).'%';
            $q->where(function ($query) use ($b) {
                $query->where('nombre', 'like', $b)
                    ->orWhere('direccion_completa', 'like', $b)
                    ->orWhere('ciudad', 'like', $b);
            });
        }

        if ($request->filled('tipo')) {
            $q->where('tipo_punto', $request->tipo);
        }

        $direcciones = $q->orderByDesc('direccionlogisticaid')->paginate(15)->withQueryString();

        return view('envios.direcciones.index', [
            'direcciones' => $direcciones,
            'stats' => [
                'total' => DireccionLogistica::count(),
                'origenes' => DireccionLogistica::where('tipo_punto', 'origen')->count(),
                'destinos' => DireccionLogistica::where('tipo_punto', 'destino')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('envios.direcciones.create');
    }

    public function show(DireccionLogistica $direccion): View
    {
        return view('envios.direcciones.show', compact('direccion'));
    }

    public function edit(DireccionLogistica $direccion): View
    {
        return view('envios.direcciones.edit', compact('direccion'));
    }

    public function store(Request $request): RedirectResponse
    {
        $direccion = DireccionLogistica::create($this->validar($request));

        return redirect()
            ->route('envios.direcciones.show', $direccion)
            ->with('success', 'Dirección registrada correctamente.');
    }

    public function update(Request $request, DireccionLogistica $direccion): RedirectResponse
    {
        $direccion->update($this->validar($request));

        return redirect()
            ->route('envios.direcciones.show', $direccion)
            ->with('success', 'Dirección actualizada.');
    }

    public function destroy(DireccionLogistica $direccion): RedirectResponse
    {
        $direccion->delete();

        return redirect()
            ->route('envios.direcciones.index')
            ->with('success', 'Dirección eliminada.');
    }

    private function validar(Request $request): array
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo_punto' => 'required|in:origen,destino,hub',
            'direccion_completa' => 'required|string|max:500',
            'ciudad' => 'required|string|max:100',
            'departamento' => 'nullable|string|max:100',
            'pais' => 'nullable|string|max:100',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'referencia' => 'nullable|string',
            'activo' => 'nullable|boolean',
        ]);

        $data['activo'] = $request->boolean('activo', true);
        $data['pais'] = $data['pais'] ?? 'Bolivia';

        return $data;
    }
}
