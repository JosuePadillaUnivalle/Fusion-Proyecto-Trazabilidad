<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EstadoLoteInsumo;
use Illuminate\Http\Request;

class EstadoLoteInsumoController extends Controller
{
    public function index()
    {
        return response()->json(EstadoLoteInsumo::all());
    }

    public function show($id)
    {
        return response()->json(EstadoLoteInsumo::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50',
        ]);

        $estado = EstadoLoteInsumo::create($data);

        return response()->json($estado, 201);
    }

    public function update(Request $request, $id)
    {
        $estado = EstadoLoteInsumo::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:50',
        ]);

        $estado->update($data);

        return response()->json($estado);
    }

    public function destroy($id)
    {
        $estado = EstadoLoteInsumo::findOrFail($id);
        $estado->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}