<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\HistorialEstadoLote;
use App\Models\Lote;
use App\Models\EstadoLoteTipo;
use App\Models\Usuario;
use Illuminate\Http\Request;

class HistorialEstadoLoteController extends Controller
{
    public function index()
    {
        $historial = HistorialEstadoLote::with(['lote', 'estadoTipo', 'usuario'])
            ->orderBy('historial_estado_id', 'desc')
            ->paginate(15);

        return view('historial_estados_lote.index', compact('historial'));
    }

    public function create()
    {
        $lotes = Lote::all();
        $tiposEstado = EstadoLoteTipo::all();
        $usuarios = Usuario::all();

        return view('historial_estados_lote.create', compact('lotes', 'tiposEstado', 'usuarios'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'estadolotetipoid' => 'required|exists:estadolote_tipo,estadolotetipoid',
            'fecha_cambio' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'imagenurl' => 'nullable|string|max:250',
            'usuarioid' => 'nullable|exists:usuario,usuarioid',
        ]);

        HistorialEstadoLote::create($data);

        return redirect()->route('historial-estados-lote.index')->with('success', 'Historial creado.');
    }

    public function show(HistorialEstadoLote $historial_estados_lote)
    {
        return view('historial_estados_lote.show', [
            'registro' => $historial_estados_lote,
        ]);
    }

    public function edit(HistorialEstadoLote $historial_estados_lote)
    {
        $lotes = Lote::all();
        $tiposEstado = EstadoLoteTipo::all();
        $usuarios = Usuario::all();

        return view('historial_estados_lote.edit', [
            'registro' => $historial_estados_lote,
            'lotes' => $lotes,
            'tiposEstado' => $tiposEstado,
            'usuarios' => $usuarios,
        ]);
    }

    public function update(Request $request, HistorialEstadoLote $historial_estados_lote)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'estadolotetipoid' => 'required|exists:estadolote_tipo,estadolotetipoid',
            'fecha_cambio' => 'nullable|date',
            'observaciones' => 'nullable|string',
            'imagenurl' => 'nullable|string|max:250',
            'usuarioid' => 'nullable|exists:usuario,usuarioid',
        ]);

        $historial_estados_lote->update($data);

        return redirect()->route('historial-estados-lote.index')->with('success', 'Historial actualizado.');
    }

    public function destroy(HistorialEstadoLote $historial_estados_lote)
    {
        $historial_estados_lote->delete();

        return redirect()->route('historial-estados-lote.index')->with('success', 'Historial eliminado.');
    }
}