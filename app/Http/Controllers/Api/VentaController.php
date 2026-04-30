<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function index()
    {
        return response()->json(
            Venta::with(['produccion'])->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            Venta::with(['produccion'])->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'produccionid' => 'required|exists:produccion,produccionid',
            'cliente' => 'nullable|string|max:100',
            'cantidadkg' => 'nullable|numeric|min:0',
            'preciokg' => 'nullable|numeric|min:0',
            'fechaventa' => 'nullable|date',
            'observaciones' => 'nullable|string|max:200',
        ]);

        $venta = Venta::create($data);

        // total NO se envía porque lo calcula PostgreSQL

        return response()->json($venta, 201);
    }

    public function update(Request $request, $id)
    {
        $venta = Venta::findOrFail($id);

        $data = $request->validate([
            'produccionid' => 'sometimes|exists:produccion,produccionid',
            'cliente' => 'nullable|string|max:100',
            'cantidadkg' => 'nullable|numeric|min:0',
            'preciokg' => 'nullable|numeric|min:0',
            'fechaventa' => 'nullable|date',
            'observaciones' => 'nullable|string|max:200',
        ]);

        $venta->update($data);

        return response()->json($venta);
    }

    public function destroy($id)
    {
        $venta = Venta::findOrFail($id);
        $venta->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }
}