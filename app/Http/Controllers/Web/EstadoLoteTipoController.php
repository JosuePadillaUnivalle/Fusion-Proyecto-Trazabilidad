<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EstadoLoteTipo;
use Illuminate\Http\Request;

class EstadoLoteTipoController extends Controller
{
    public function index()
    {
        $tipos = EstadoLoteTipo::orderBy('estadolotetipoid', 'desc')->paginate(15);

        return view('estado_lote_tipos.index', compact('tipos'));
    }

    public function create()
    {
        return view('estado_lote_tipos.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
        ]);

        EstadoLoteTipo::create($data);

        return redirect()->route('estado-lote-tipos.index')->with('success', 'Tipo de estado creado.');
    }

    public function show(EstadoLoteTipo $estadoLoteTipo)
    {
        return view('estado_lote_tipos.show', compact('estadoLoteTipo'));
    }

    public function edit(EstadoLoteTipo $estadoLoteTipo)
    {
        return view('estado_lote_tipos.edit', compact('estadoLoteTipo'));
    }

    public function update(Request $request, EstadoLoteTipo $estadoLoteTipo)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $estadoLoteTipo->update($data);

        return redirect()->route('estado-lote-tipos.index')->with('success', 'Tipo de estado actualizado.');
    }

    public function destroy(EstadoLoteTipo $estadoLoteTipo)
    {
        $estadoLoteTipo->delete();

        return redirect()->route('estado-lote-tipos.index')->with('success', 'Tipo de estado eliminado.');
    }
}