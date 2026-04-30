<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActorAbastecimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActorAbastecimientoController extends Controller
{
    public function index(): View
    {
        $actores = ActorAbastecimiento::orderBy('actorid', 'desc')->paginate(15);
        return view('actores_abastecimiento.index', compact('actores'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:120',
            'tipo_actor' => 'required|in:productor,proveedor,mixto',
            'email' => 'nullable|email|max:120',
            'telefono' => 'nullable|string|max:30',
            'activo' => 'nullable|boolean',
        ]);

        $data['activo'] = $request->boolean('activo', true);
        ActorAbastecimiento::create($data);

        return redirect()->route('actores-abastecimiento.index')->with('success', 'Actor de abastecimiento creado.');
    }

    public function update(Request $request, ActorAbastecimiento $actores_abastecimiento): RedirectResponse
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:120',
            'tipo_actor' => 'required|in:productor,proveedor,mixto',
            'email' => 'nullable|email|max:120',
            'telefono' => 'nullable|string|max:30',
            'activo' => 'nullable|boolean',
        ]);

        $data['activo'] = $request->boolean('activo', false);
        $actores_abastecimiento->update($data);

        return redirect()->route('actores-abastecimiento.index')->with('success', 'Actor de abastecimiento actualizado.');
    }

    public function destroy(ActorAbastecimiento $actores_abastecimiento): RedirectResponse
    {
        $actores_abastecimiento->delete();
        return redirect()->route('actores-abastecimiento.index')->with('success', 'Actor de abastecimiento eliminado.');
    }
}

