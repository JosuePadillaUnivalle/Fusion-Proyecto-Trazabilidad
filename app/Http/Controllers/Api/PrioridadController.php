<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prioridad;
use Illuminate\Http\Request;

class PrioridadController extends Controller
{
    public function index()
    {
        return response()->json(Prioridad::all());
    }

    public function show($id)
    {
        return response()->json(Prioridad::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:30',
        ]);

        $prioridad = Prioridad::create($data);

        return response()->json($prioridad, 201);
    }

    public function update(Request $request, $id)
    {
        $prioridad = Prioridad::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:30',
        ]);

        $prioridad->update($data);

        return response()->json($prioridad);
    }

    public function destroy($id)
    {
        $prioridad = Prioridad::findOrFail($id);
        $prioridad->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}