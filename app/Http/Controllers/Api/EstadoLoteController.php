<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EstadoLote;
use Illuminate\Http\Request;

class EstadoLoteController extends Controller
{
    public function index()
    {
        return response()->json(
            EstadoLote::with(['lote', 'estadoTipo'])->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            EstadoLote::with(['lote', 'estadoTipo'])->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'estadolotetipoid' => 'required|exists:estadolote_tipo,estadolotetipoid',
            'observaciones' => 'nullable|string|max:250',
            'imagenurl' => 'nullable|string|max:250',
        ]);

        $estado = EstadoLote::create($data);

        return response()->json($estado, 201);
    }

    public function update(Request $request, $id)
    {
        $estado = EstadoLote::findOrFail($id);

        $data = $request->validate([
            'loteid' => 'sometimes|exists:lote,loteid',
            'estadolotetipoid' => 'sometimes|exists:estadolote_tipo,estadolotetipoid',
            'observaciones' => 'nullable|string|max:250',
            'imagenurl' => 'nullable|string|max:250',
        ]);

        $estado->update($data);

        return response()->json($estado);
    }

    public function destroy($id)
    {
        $estado = EstadoLote::findOrFail($id);
        $estado->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}