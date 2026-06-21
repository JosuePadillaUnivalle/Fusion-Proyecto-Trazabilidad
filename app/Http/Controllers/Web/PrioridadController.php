<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\FiltersCatalogoSimple;
use App\Http\Controllers\Controller;
use App\Models\Prioridad;
use Illuminate\Http\Request;

class PrioridadController extends Controller
{
    use FiltersCatalogoSimple;

    public function index(Request $request)
    {
        $query = Prioridad::query();
        $this->aplicarFiltroBuscar($query, $request, ['nombre']);
        $prioridades = $query->orderByDesc('prioridadid')->paginate(15)->withQueryString();

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
        return view('prioridades.show', ['item' => $prioridad]);
    }

    public function edit(Prioridad $prioridad)
    {
        return view('prioridades.edit', ['item' => $prioridad]);
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
