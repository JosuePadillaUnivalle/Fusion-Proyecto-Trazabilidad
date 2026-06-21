<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Concerns\FiltersCatalogoSimple;
use App\Http\Controllers\Controller;
use App\Models\EstadoLoteInsumo;
use Illuminate\Http\Request;

class EstadoLoteInsumoController extends Controller
{
    use FiltersCatalogoSimple;

    public function index(Request $request)
    {
        $query = EstadoLoteInsumo::query();
        $this->aplicarFiltroBuscar($query, $request, ['nombre']);
        $estados = $query->orderByDesc('estadoloteinsumoid')->paginate(15)->withQueryString();

        return view('estado_lote_insumos.index', compact('estados'));
    }

    public function create()
    {
        return view('estado_lote_insumos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50',
        ]);

        EstadoLoteInsumo::create($data);

        return redirect()->route('estado-lote-insumos.index')->with('success', 'Estado de insumo creado.');
    }

    public function show(EstadoLoteInsumo $estadoLoteInsumo)
    {
        return view('estado_lote_insumos.show', ['item' => $estadoLoteInsumo]);
    }

    public function edit(EstadoLoteInsumo $estadoLoteInsumo)
    {
        return view('estado_lote_insumos.edit', ['item' => $estadoLoteInsumo]);
    }

    public function update(Request $request, EstadoLoteInsumo $estadoLoteInsumo)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50',
        ]);

        $estadoLoteInsumo->update($data);

        return redirect()->route('estado-lote-insumos.index')->with('success', 'Estado de insumo actualizado.');
    }

    public function destroy(EstadoLoteInsumo $estadoLoteInsumo)
    {
        $estadoLoteInsumo->delete();

        return redirect()->route('estado-lote-insumos.index')->with('success', 'Estado de insumo eliminado.');
    }
}
