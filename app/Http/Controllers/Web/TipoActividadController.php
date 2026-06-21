<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\FiltersCatalogoSimple;
use App\Http\Controllers\Controller;
use App\Models\TipoActividad;
use Illuminate\Http\Request;

class TipoActividadController extends Controller
{
    use FiltersCatalogoSimple;

    public function index(Request $request)
    {
        $query = TipoActividad::query();
        $this->aplicarFiltroBuscar($query, $request, ['nombre', 'descripcion']);
        $tipos = $query->orderByDesc('tipoactividadid')->paginate(15)->withQueryString();

        return view('tipo_actividad.index', compact('tipos'));
    }

    public function create()
    {
        return view('tipo_actividad.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
        ]);

        TipoActividad::create($data);

        return redirect()->route('tipo-actividad.index')->with('success', 'Tipo de actividad creado.');
    }

    public function show(TipoActividad $tipoActividad)
    {
        return view('tipo_actividad.show', ['item' => $tipoActividad]);
    }

    public function edit(TipoActividad $tipoActividad)
    {
        return view('tipo_actividad.edit', ['item' => $tipoActividad]);
    }

    public function update(Request $request, TipoActividad $tipoActividad)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $tipoActividad->update($data);

        return redirect()->route('tipo-actividad.index')->with('success', 'Tipo de actividad actualizado.');
    }

    public function destroy(TipoActividad $tipoActividad)
    {
        $tipoActividad->delete();

        return redirect()->route('tipo-actividad.index')->with('success', 'Tipo de actividad eliminado.');
    }
}
