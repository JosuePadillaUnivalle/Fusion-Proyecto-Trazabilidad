<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        return response()->json(
            Usuario::with('roles')->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            Usuario::with('roles')->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:usuario,email',
            'nombreusuario' => 'required|string|max:100|unique:usuario,nombreusuario',
            'telefono' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'imagenurl' => 'nullable|string|max:250',
            'informacionadicional' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $usuario = Usuario::create([
            ...$data,
            'passwordhash' => Hash::make($data['password']),
        ]);

        return response()->json($usuario, 201);
    }

    public function update(Request $request, $id)
    {
        $usuario = Usuario::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|string|max:100',
            'apellido' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:100|unique:usuario,email,' . $id . ',usuarioid',
            'nombreusuario' => 'sometimes|string|max:100|unique:usuario,nombreusuario,' . $id . ',usuarioid',
            'telefono' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'imagenurl' => 'nullable|string|max:250',
            'informacionadicional' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        if (isset($data['password'])) {
            $data['passwordhash'] = Hash::make($data['password']);
            unset($data['password']);
        }

        $usuario->update($data);

        return response()->json($usuario);
    }

    public function destroy($id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}