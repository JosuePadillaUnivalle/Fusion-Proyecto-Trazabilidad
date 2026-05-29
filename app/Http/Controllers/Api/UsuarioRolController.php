<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UsuarioRol;
use Illuminate\Http\Request;

class UsuarioRolController extends Controller
{
    public function index()
    {
        return response()->json(
            UsuarioRol::with(['usuario', 'rol'])->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            UsuarioRol::with(['usuario', 'rol'])->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'usuarioid' => 'required|exists:usuario,usuarioid',
            'rolid' => 'required|exists:rol,rolid',
        ]);

        $ur = UsuarioRol::create($data);

        return response()->json($ur, 201);
    }

    public function update(Request $request, $id)
    {
        $ur = UsuarioRol::findOrFail($id);

        $data = $request->validate([
            'usuarioid' => 'sometimes|exists:usuario,usuarioid',
            'rolid' => 'sometimes|exists:rol,rolid',
        ]);

        $ur->update($data);

        return response()->json($ur);
    }

    public function destroy($id)
    {
        $ur = UsuarioRol::findOrFail($id);
        $ur->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}