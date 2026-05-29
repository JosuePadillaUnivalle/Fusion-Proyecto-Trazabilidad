<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rol;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index()
    {
        return response()->json(Rol::all());
    }

    public function show($id)
    {
        return response()->json(Rol::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $rol = Rol::create($data);

        return response()->json($rol, 201);
    }

    public function update(Request $request, $id)
    {
        $rol = Rol::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $rol->update($data);

        return response()->json($rol);
    }

    public function destroy($id)
    {
        $rol = Rol::findOrFail($id);
        $rol->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}