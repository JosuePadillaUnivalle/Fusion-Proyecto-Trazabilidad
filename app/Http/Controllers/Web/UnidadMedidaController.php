<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class UnidadMedidaController extends Controller
{
    public function index()
    {
        $unidades = UnidadMedida::orderBy('unidadmedidaid', 'desc')->paginate(15);

        return view('unidades_medida.index', compact('unidades'));
    }

    public function create()
    {
        return view('unidades_medida.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:20'],
        ]);

        UnidadMedida::create($data);

        return redirect()
            ->route('unidades-medida.index')
            ->with('success', 'Unidad de medida creada correctamente.');
    }

    // 👇 Ya no usamos $unidades_medido, ahora $unidad
    public function show(UnidadMedida $unidad)
    {
        return view('unidades_medida.show', compact('unidad'));
    }

    public function edit(UnidadMedida $unidad)
    {
        return view('unidades_medida.edit', compact('unidad'));
    }

    public function update(Request $request, UnidadMedida $unidad)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:20'],
        ]);

        $unidad->update($data);

        return redirect()
            ->route('unidades-medida.index')
            ->with('success', 'Unidad de medida actualizada correctamente.');
    }

    public function destroy(UnidadMedida $unidad)
    {
        $unidad->delete();

        return redirect()
            ->route('unidades-medida.index')
            ->with('success', 'Unidad de medida eliminada correctamente.');
    }
}