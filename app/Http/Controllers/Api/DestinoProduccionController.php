<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DestinoProduccion;
use Illuminate\Http\Request;

class DestinoProduccionController extends Controller
{
    public function index()
    {
        return response()->json(DestinoProduccion::all());
    }

    public function show($id)
    {
        return response()->json(DestinoProduccion::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
        ]);

        $destino = DestinoProduccion::create($data);

        return response()->json($destino, 201);
    }

    public function update(Request $request, $id)
    {
        $destino = DestinoProduccion::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
        ]);

        $destino->update($data);

        return response()->json($destino);
    }

    public function destroy($id)
    {
        $destino = DestinoProduccion::findOrFail($id);
        $destino->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}