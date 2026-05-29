<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TipoAlmacen;
use Illuminate\Http\Request;

class TipoAlmacenController extends Controller
{
    public function index()
    {
        return response()->json(TipoAlmacen::all());
    }

    public function show($id)
    {
        return response()->json(TipoAlmacen::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $tipo = TipoAlmacen::create($data);

        return response()->json($tipo, 201);
    }

    public function update(Request $request, $id)
    {
        $tipo = TipoAlmacen::findOrFail($id);

        $data = $request->validate([
            'nombre'      => 'sometimes|required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $tipo->update($data);

        return response()->json($tipo);
    }

    public function destroy($id)
    {
        $tipo = TipoAlmacen::findOrFail($id);
        $tipo->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}