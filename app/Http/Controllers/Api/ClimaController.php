<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Clima;
use Illuminate\Http\Request;

class ClimaController extends Controller
{
    public function index()
    {
        return response()->json(
            Clima::with(['lote'])->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            Clima::with(['lote'])->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'fecha' => 'nullable|date',
            'temperatura' => 'nullable|numeric',
            'humedad' => 'nullable|numeric|between:0,100',
            'lluvia' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:200',
        ]);

        $clima = Clima::create($data);

        return response()->json($clima, 201);
    }

    public function update(Request $request, $id)
    {
        $clima = Clima::findOrFail($id);

        $data = $request->validate([
            'loteid' => 'sometimes|exists:lote,loteid',
            'fecha' => 'nullable|date',
            'temperatura' => 'nullable|numeric',
            'humedad' => 'nullable|numeric|between:0,100',
            'lluvia' => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string|max:200',
        ]);

        $clima->update($data);

        return response()->json($clima);
    }

    public function destroy($id)
    {
        $clima = Clima::findOrFail($id);
        $clima->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}