<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;

class UnidadMedidaController extends Controller
{
    public function index()
    {
        return response()->json(UnidadMedida::all());
    }

    public function show($id)
    {
        return response()->json(UnidadMedida::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:20',
        ]);

        $unidad = UnidadMedida::create($data);

        return response()->json($unidad, 201);
    }

    public function update(Request $request, $id)
    {
        $unidad = UnidadMedida::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:20',
        ]);

        $unidad->update($data);

        return response()->json($unidad);
    }

    public function destroy($id)
    {
        $unidad = UnidadMedida::findOrFail($id);
        $unidad->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}