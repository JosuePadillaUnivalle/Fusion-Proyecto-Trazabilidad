<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoteInsumo;
use Illuminate\Http\Request;

class LoteInsumoController extends Controller
{
    public function index()
    {
        return response()->json(
            LoteInsumo::with(['lote', 'insumo', 'usuario', 'estado'])->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            LoteInsumo::with(['lote', 'insumo', 'usuario', 'estado'])->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'insumoid' => 'required|exists:insumo,insumoid',
            'usuarioid' => 'required|exists:usuario,usuarioid',
            'cantidadusada' => 'required|numeric|min:0.01',
            'costototal' => 'nullable|numeric|min:0',
            'estadoloteinsumoid' => 'nullable|exists:estadoloteinsumo,estadoloteinsumoid',
            'observaciones' => 'nullable|string|max:200',
        ]);

        $registro = LoteInsumo::create($data);

        return response()->json($registro, 201);
    }

    public function update(Request $request, $id)
    {
        $registro = LoteInsumo::findOrFail($id);

        $data = $request->validate([
            'loteid' => 'sometimes|exists:lote,loteid',
            'insumoid' => 'sometimes|exists:insumo,insumoid',
            'usuarioid' => 'sometimes|exists:usuario,usuarioid',
            'cantidadusada' => 'sometimes|numeric|min:0.01',
            'costototal' => 'nullable|numeric|min:0',
            'estadoloteinsumoid' => 'nullable|exists:estadoloteinsumo,estadoloteinsumoid',
            'observaciones' => 'nullable|string|max:200',
        ]);

        $registro->update($data);

        return response()->json($registro);
    }

    public function destroy($id)
    {
        $registro = LoteInsumo::findOrFail($id);
        $registro->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}