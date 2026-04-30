<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HistorialEstadoLote;
use Illuminate\Http\Request;

class HistorialEstadoLoteController extends Controller
{
    public function index()
    {
        return HistorialEstadoLote::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loteid'           => 'required|integer|exists:lote,loteid',
            'estadolotetipoid' => 'required|integer|exists:estadolote_tipo,estadolotetipoid',
            'fecha_cambio'     => 'nullable|date',
            'observaciones'    => 'nullable|string',
            'imagenurl'        => 'nullable|string',
            'usuarioid'        => 'nullable|integer|exists:usuario,usuarioid',
        ]);

        // Si no envías fecha_cambio, se usa el default de la BD
        $historial = HistorialEstadoLote::create($data);

        return response()->json($historial, 201);
    }

    public function show($id)
    {
        $historial = HistorialEstadoLote::findOrFail($id);
        return response()->json($historial);
    }

    public function update(Request $request, $id)
    {
        $historial = HistorialEstadoLote::findOrFail($id);

        $data = $request->validate([
            'estadolotetipoid' => 'sometimes|integer|exists:estadolote_tipo,estadolotetipoid',
            'fecha_cambio'     => 'sometimes|date',
            'observaciones'    => 'sometimes|nullable|string',
            'imagenurl'        => 'sometimes|nullable|string',
            'usuarioid'        => 'sometimes|nullable|integer|exists:usuario,usuarioid',
        ]);

        $historial->update($data);

        return response()->json($historial);
    }

    public function destroy($id)
    {
        $historial = HistorialEstadoLote::findOrFail($id);
        $historial->delete();

        return response()->json(null, 204);
    }
}