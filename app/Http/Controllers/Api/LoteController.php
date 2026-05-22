<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lote;
use App\Support\LoteDefaults;
use Illuminate\Http\Request;

class LoteController extends Controller
{
    public function index()
    {
        return response()->json(
            Lote::with(['usuario', 'cultivo', 'estadoTipo'])->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            Lote::with(['usuario', 'cultivo', 'estadoTipo', 'producciones', 'actividades'])
                ->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'usuarioid' => 'required|exists:usuario,usuarioid',
            'nombre' => 'required|string|max:100',
            'ubicacion' => 'nullable|string|max:200',
            'superficie' => 'required|numeric|min:0.01',
            'cultivoid' => 'nullable|exists:cultivo,cultivoid',
            'fechasiembra' => 'nullable|date',
            'estadolotetipoid' => 'nullable|exists:estadolote_tipo,estadolotetipoid',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'imagenurl' => 'nullable|string|max:250',
        ]);

        $lote = Lote::create(LoteDefaults::enrich($data, true));
        LoteDefaults::registrarHistorialInicial($lote);

        return response()->json($lote->fresh(['usuario', 'cultivo', 'estadoTipo']), 201);
    }

    public function update(Request $request, $id)
    {
        $lote = Lote::findOrFail($id);

        $data = $request->validate([
            'usuarioid' => 'sometimes|exists:usuario,usuarioid',
            'nombre' => 'sometimes|string|max:100',
            'ubicacion' => 'nullable|string|max:200',
            'superficie' => 'sometimes|numeric|min:0.01',
            'cultivoid' => 'nullable|exists:cultivo,cultivoid',
            'fechasiembra' => 'nullable|date',
            'estadolotetipoid' => 'nullable|exists:estadolote_tipo,estadolotetipoid',
            'latitud' => 'nullable|numeric|between:-90,90',
            'longitud' => 'nullable|numeric|between:-180,180',
            'imagenurl' => 'nullable|string|max:250',
        ]);

        $lote->update($data);

        return response()->json($lote);
    }

    public function destroy($id)
    {
        $lote = Lote::findOrFail($id);
        $lote->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}