<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Prioridad;
use Illuminate\Http\Request;

class PrioridadController extends Controller
{
    public function index()
    {
        $prioridades = Prioridad::orderBy('prioridadid', 'desc')->paginate(15);
        return view('prioridades.index', compact('prioridades'));
    }

    public function create()
    {
        return view('prioridades.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:30',
        ]);

        Prioridad::create($data);

        return redirect()->route('prioridades.index')->with('success', 'Prioridad creada.');
    }

    public function show(Prioridad $prioridad)
    {
        return view('prioridades.show', compact('prioridad'));
    }

    public function edit(Prioridad $prioridad)
    {
        return view('prioridades.edit', compact('prioridad'));
    }

    public function update(Request $request, Prioridad $prioridad)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:30',
        ]);

        $prioridad->update($data);

        return redirect()->route('prioridades.index')->with('success', 'Prioridad actualizada.');
    }

    public function destroy(Prioridad $prioridad)
    {
        $prioridad->delete();

        return redirect()->route('prioridades.index')->with('success', 'Prioridad eliminada.');
    }
}