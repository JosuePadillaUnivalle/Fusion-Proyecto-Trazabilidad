<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use Illuminate\Http\Request;

class AlmacenController extends Controller
{
    public function index()
    {
        return response()->json(
            Almacen::with(['tipoAlmacen', 'unidadMedida', 'almacenamientos'])->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            Almacen::with(['tipoAlmacen', 'unidadMedida', 'almacenamientos'])->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'        => 'required|string|max:100',
            'descripcion'   => 'nullable|string|max:250',
            'ubicacion'     => 'nullable|string|max:200',
            'capacidad'     => 'nullable|numeric|min:0',
            'unidadmedidaid'=> 'nullable|exists:unidadmedida,unidadmedidaid',
            'tipoalmacenid' => 'nullable|exists:tipoalmacen,tipoalmacenid',
            'activo'        => 'boolean',
        ]);

        $almacen = Almacen::create($data);

        return response()->json(
            $almacen->load(['tipoAlmacen', 'unidadMedida']),
            201
        );
    }

    public function update(Request $request, $id)
    {
        $almacen = Almacen::findOrFail($id);

        $data = $request->validate([
            'nombre'        => 'sometimes|string|max:100',
            'descripcion'   => 'nullable|string|max:250',
            'ubicacion'     => 'nullable|string|max:200',
            'capacidad'     => 'nullable|numeric|min:0',
            'unidadmedidaid'=> 'nullable|exists:unidadmedida,unidadmedidaid',
            'tipoalmacenid' => 'nullable|exists:tipoalmacen,tipoalmacenid',
            'activo'        => 'boolean',
        ]);

        $almacen->update($data);

        return response()->json(
            $almacen->load(['tipoAlmacen', 'unidadMedida'])
        );
    }

    public function destroy($id)
    {
        $almacen = Almacen::findOrFail($id);
        $almacen->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}