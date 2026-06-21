<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProduccionAlmacenamiento;
use Illuminate\Http\Request;

class ProduccionAlmacenamientoController extends Controller
{
    public function index()
    {
        return response()->json(
            ProduccionAlmacenamiento::with(['produccion', 'almacen', 'unidadMedida'])->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            ProduccionAlmacenamiento::with(['produccion', 'almacen', 'unidadMedida'])->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'produccionid'   => 'required|exists:produccion,produccionid',
            'almacenid'      => 'required|exists:almacen,almacenid',
            'cantidad'       => 'required|numeric|min:0.01',
            'unidadmedidaid' => 'nullable|exists:unidadmedida,unidadmedidaid',

            'temperatura'     => 'nullable|numeric|between:-50,80',
            'humedad'         => 'nullable|numeric|between:0,100',
            'temperatura_min' => 'nullable|numeric|between:-50,80',
            'temperatura_max' => 'nullable|numeric|between:-50,80',
            'humedad_min'     => 'nullable|numeric|between:0,100',
            'humedad_max'     => 'nullable|numeric|between:0,100',

            'fechaentrada'   => 'nullable|date',
            'fechasalida'    => 'nullable|date',
            'observaciones'  => 'nullable|string|max:250',
        ]);

        $registro = ProduccionAlmacenamiento::create($data);

        return response()->json(
            $registro->load(['produccion', 'almacen', 'unidadMedida']),
            201
        );
    }

    public function update(Request $request, $id)
    {
        $registro = ProduccionAlmacenamiento::findOrFail($id);

        $data = $request->validate([
            'produccionid'   => 'sometimes|exists:produccion,produccionid',
            'almacenid'      => 'sometimes|exists:almacen,almacenid',
            'cantidad'       => 'sometimes|numeric|min:0.01',
            'unidadmedidaid' => 'nullable|exists:unidadmedida,unidadmedidaid',

            'temperatura'     => 'nullable|numeric|between:-50,80',
            'humedad'         => 'nullable|numeric|between:0,100',
            'temperatura_min' => 'nullable|numeric|between:-50,80',
            'temperatura_max' => 'nullable|numeric|between:-50,80',
            'humedad_min'     => 'nullable|numeric|between:0,100',
            'humedad_max'     => 'nullable|numeric|between:0,100',

            'fechaentrada'   => 'nullable|date',
            'fechasalida'    => 'nullable|date',
            'observaciones'  => 'nullable|string|max:250',
        ]);

        $registro->update($data);

        return response()->json(
            $registro->load(['produccion', 'almacen', 'unidadMedida'])
        );
    }

    public function destroy($id)
    {
        $registro = ProduccionAlmacenamiento::findOrFail($id);
        $registro->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}