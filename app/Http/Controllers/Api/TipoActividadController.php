<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TipoActividad;
use Illuminate\Http\Request;

class TipoActividadController extends Controller
{
    public function index()
    {
        return response()->json(TipoActividad::all());
    }

    public function show($id)
    {
        return response()->json(TipoActividad::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $tipo = TipoActividad::create($data);

        return response()->json($tipo, 201);
    }

    public function update(Request $request, $id)
    {
        $tipo = TipoActividad::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $tipo->update($data);

        return response()->json($tipo);
    }

    public function destroy($id)
    {
        $tipo = TipoActividad::findOrFail($id);
        $tipo->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}