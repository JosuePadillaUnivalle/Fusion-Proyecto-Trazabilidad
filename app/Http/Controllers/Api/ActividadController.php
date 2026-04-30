<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Actividad;
use Illuminate\Http\Request;

class ActividadController extends Controller
{
    public function index()
    {
        return response()->json(
            Actividad::with(['lote', 'usuario', 'tipoActividad', 'prioridad'])->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            Actividad::with(['lote', 'usuario', 'tipoActividad', 'prioridad'])->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'loteid' => 'required|exists:lote,loteid',
            'usuarioid' => 'required|exists:usuario,usuarioid',
            'descripcion' => 'required|string|max:200',
            'fechainicio' => 'nullable|date',
            'fechafin' => 'nullable|date',
            'tipoactividadid' => 'required|exists:tipoactividad,tipoactividadid',
            'prioridadid' => 'required|exists:prioridad,prioridadid',
            'observaciones' => 'nullable|string|max:250',
        ]);

        $actividad = Actividad::create($data);

        return response()->json($actividad, 201);
    }

    public function update(Request $request, $id)
    {
        $actividad = Actividad::findOrFail($id);

        $data = $request->validate([
            'loteid' => 'sometimes|exists:lote,loteid',
            'usuarioid' => 'sometimes|exists:usuario,usuarioid',
            'descripcion' => 'sometimes|string|max:200',
            'fechainicio' => 'nullable|date',
            'fechafin' => 'nullable|date',
            'tipoactividadid' => 'sometimes|exists:tipoactividad,tipoactividadid',
            'prioridadid' => 'sometimes|exists:prioridad,prioridadid',
            'observaciones' => 'nullable|string|max:250',
        ]);

        $actividad->update($data);

        return response()->json($actividad);
    }

    public function destroy($id)
    {
        $actividad = Actividad::findOrFail($id);
        $actividad->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}