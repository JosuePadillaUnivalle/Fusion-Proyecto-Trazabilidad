<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EstadoLote;
use App\Models\Lote;
use App\Models\EstadoLoteTipo;
use Illuminate\Http\Request;

class EstadoLoteController extends Controller
{
    public function index()
    {
        $estados = EstadoLote::with(['lote', 'estadoTipo'])
            ->orderBy('estadoid', 'desc')
            ->paginate(15);

        return view('estadolotes.index', compact('estados'));
    }

    public function create()
    {
        $lotes = Lote::all();
        $tiposEstado = EstadoLoteTipo::all();

        return view('estadolotes.create', compact('lotes', 'tiposEstado'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'estadolotetipoid' => 'required|exists:estadolote_tipo,estadolotetipoid',
            'fecharegistro' => 'nullable|date',
            'observaciones' => 'nullable|string|max:250',
            'imagenurl' => 'nullable|string|max:250',
        ]);

        EstadoLote::create($data);

        return redirect()->route('estadolotes.index')->with('success', 'Estado de lote creado.');
    }

    public function show(EstadoLote $estadolote)
    {
        return view('estadolotes.show', compact('estadolote'));
    }

    public function edit(EstadoLote $estadolote)
    {
        $lotes = Lote::all();
        $tiposEstado = EstadoLoteTipo::all();

        return view('estadolotes.edit', compact('estadolote', 'lotes', 'tiposEstado'));
    }

    public function update(Request $request, EstadoLote $estadolote)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'estadolotetipoid' => 'required|exists:estadolote_tipo,estadolotetipoid',
            'fecharegistro' => 'nullable|date',
            'observaciones' => 'nullable|string|max:250',
            'imagenurl' => 'nullable|string|max:250',
        ]);

        $estadolote->update($data);

        return redirect()->route('estadolotes.index')->with('success', 'Estado de lote actualizado.');
    }

    public function destroy(EstadoLote $estadolote)
    {
        $estadolote->delete();

        return redirect()->route('estadolotes.index')->with('success', 'Estado de lote eliminado.');
    }
}