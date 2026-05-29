<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cultivo;
use Illuminate\Http\Request;

class CultivoController extends Controller
{
    public function index()
    {
        return response()->json(Cultivo::all());
    }

    public function show($id)
    {
        return response()->json(Cultivo::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
        ]);

        $cultivo = Cultivo::create($data);

        return response()->json($cultivo, 201);
    }

    public function update(Request $request, $id)
    {
        $cultivo = Cultivo::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:100',
        ]);

        $cultivo->update($data);

        return response()->json($cultivo);
    }

    public function destroy($id)
    {
        $cultivo = Cultivo::findOrFail($id);
        $cultivo->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}