<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TipoInsumo;
use Illuminate\Http\Request;

class TipoInsumoController extends Controller
{
    public function index()
    {
        $tipoInsumos = TipoInsumo::orderBy('tipoinsumoid', 'desc')->paginate(15);

        return view('tipo_insumos.index', compact('tipoInsumos'));
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

        return redirect()
            ->route('tipo-insumos.index')
            ->with('success', 'Tipo de insumo creado correctamente.');
    }

    public function show(TipoInsumo $tipoInsumo)
    {
        return view('tipo_insumos.show', compact('tipoInsumo'));
    }

    public function edit(TipoInsumo $tipoInsumo)
    {
        return view('tipo_insumos.edit', compact('tipoInsumo'));
    }

    public function update(Request $request, TipoInsumo $tipoInsumo)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:50'],
        ]);

        $tipoInsumo->update($data);

        return redirect()
            ->route('tipo-insumos.index')
            ->with('success', 'Tipo de insumo actualizado correctamente.');
    }

    public function destroy(TipoInsumo $tipoInsumo)
    {
        $tipoInsumo->delete();

        return redirect()
            ->route('tipo-insumos.index')
            ->with('success', 'Tipo de insumo eliminado correctamente.');
    }
}