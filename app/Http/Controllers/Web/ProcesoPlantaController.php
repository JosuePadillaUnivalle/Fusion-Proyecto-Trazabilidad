<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProcesoPlanta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcesoPlantaController extends Controller
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
        $procesos = ProcesoPlanta::orderBy('procesoplantaid', 'desc')->paginate(15);
        return view('procesos_planta.index', compact('procesos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable|boolean',
        ]);
        $data['activo'] = $request->boolean('activo', true);
        ProcesoPlanta::create($data);

        return redirect()->route('procesos-planta.index')->with('success', 'Proceso creado.');
    }

    public function update(Request $request, ProcesoPlanta $procesos_plantum): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'activo' => 'nullable|boolean',
        ]);
        $data['activo'] = $request->boolean('activo', false);
        $procesos_plantum->update($data);

        return redirect()->route('procesos-planta.index')->with('success', 'Proceso actualizado.');
    }

    public function destroy(ProcesoPlanta $procesos_plantum): RedirectResponse
    {
        $procesos_plantum->delete();
        return redirect()->route('procesos-planta.index')->with('success', 'Proceso eliminado.');
    }
}

