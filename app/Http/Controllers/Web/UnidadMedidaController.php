<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\FiltersCatalogoSimple;
use App\Http\Controllers\Controller;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class UnidadMedidaController extends Controller
{
    use FiltersCatalogoSimple;

    public function index(Request $request)
    {
        $query = UnidadMedida::query();
        $this->aplicarFiltroBuscar($query, $request, ['nombre']);
        $unidades = $query->orderByDesc('unidadmedidaid')->paginate(15)->withQueryString();

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

        return redirect()->route('unidades-medida.index')->with('success', 'Unidad de medida creada correctamente.');
    }

    public function show(UnidadMedida $unidad)
    {
        return view('unidades_medida.show', ['item' => $unidad]);
    }

    public function edit(UnidadMedida $unidad)
    {
        return view('unidades_medida.edit', ['item' => $unidad]);
    }

    public function update(Request $request, UnidadMedida $unidad)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:20'],
        ]);

        $unidad->update($data);

        return redirect()->route('unidades-medida.index')->with('success', 'Unidad de medida actualizada correctamente.');
    }

    public function destroy(UnidadMedida $unidad)
    {
        $unidad->delete();

        return redirect()->route('unidades-medida.index')->with('success', 'Unidad de medida eliminada correctamente.');
    }
}
