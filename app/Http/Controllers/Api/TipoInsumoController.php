<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TipoInsumo;
use Illuminate\Http\Request;

class TipoInsumoController extends Controller
{
    public function index()
    {
        return response()->json(TipoInsumo::all());
    }

    public function show($id)
    {
        return response()->json(TipoInsumo::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50',
        ]);

        $tipo = TipoInsumo::create($data);

        return response()->json($tipo, 201);
    }

    public function update(Request $request, $id)
    {
        $tipo = TipoInsumo::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:50',
        ]);

        $tipo->update($data);

        return response()->json($tipo);
    }

    public function destroy($id)
    {
        $tipo = TipoInsumo::findOrFail($id);
        $tipo->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}