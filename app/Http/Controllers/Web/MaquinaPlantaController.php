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

    public function index(): View
    {
        $maquinas = MaquinaPlanta::orderBy('maquinaplantaid', 'desc')->paginate(15);
        return view('maquinas_planta.index', compact('maquinas'));
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
}

