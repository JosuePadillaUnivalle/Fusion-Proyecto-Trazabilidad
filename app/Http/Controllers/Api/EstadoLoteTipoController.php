<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EstadoLoteTipo;
use Illuminate\Http\Request;

class EstadoLoteTipoController extends Controller
{
    public function index()
    {
        return response()->json(EstadoLoteTipo::all());
    }

    public function show($id)
    {
        return response()->json(EstadoLoteTipo::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $estado = EstadoLoteTipo::create($data);

        return response()->json($estado, 201);
    }

    public function update(Request $request, $id)
    {
        $estado = EstadoLoteTipo::findOrFail($id);

        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:50',
            'descripcion' => 'nullable|string|max:200',
        ]);

        $estado->update($data);

        return response()->json($estado);
    }

    public function destroy($id)
    {
        $estado = EstadoLoteTipo::findOrFail($id);
        $estado->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}