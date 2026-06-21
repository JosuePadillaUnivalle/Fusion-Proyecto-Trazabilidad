<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\FiltersCatalogoSimple;
use App\Http\Controllers\Controller;
use App\Models\TipoInsumo;
use Illuminate\Http\Request;

class TipoInsumoController extends Controller
{
    use FiltersCatalogoSimple;

    public function index(Request $request)
    {
        $query = TipoInsumo::query();
        $this->aplicarFiltroBuscar($query, $request, ['nombre']);
        $tipos = $query->orderByDesc('tipoinsumoid')->paginate(15)->withQueryString();

        return view('tipo_insumos.index', compact('tipos'));
    }

    public function create()
    {
        return view('tipo_insumos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
        ]);

        TipoInsumo::create($data);

        return redirect()->route('tipo-insumos.index')->with('success', 'Tipo de insumo creado correctamente.');
    }

    public function show(TipoInsumo $tipoInsumo)
    {
        return view('tipo_insumos.show', ['item' => $tipoInsumo]);
    }

    public function edit(TipoInsumo $tipoInsumo)
    {
        return view('tipo_insumos.edit', ['item' => $tipoInsumo]);
    }

    public function update(Request $request, TipoInsumo $tipoInsumo)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
        ]);

        $tipoInsumo->update($data);

        return redirect()->route('tipo-insumos.index')->with('success', 'Tipo de insumo actualizado correctamente.');
    }

    public function destroy(TipoInsumo $tipoInsumo)
    {
        $tipoInsumo->delete();

        return redirect()->route('tipo-insumos.index')->with('success', 'Tipo de insumo eliminado correctamente.');
    }
}
