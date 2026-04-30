<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Insumo;
use Illuminate\Http\Request;

class InsumoController extends Controller
{
    public function index()
    {
        $q = Insumo::with(['tipo', 'unidadMedida']);
        $user = auth()->user();
        if ($user?->hasRole('almacen')) {
            if ($user->almacenid) {
                $q->where('almacenid', $user->almacenid);
            } else {
                $q->whereRaw('0 = 1');
            }
        }

        return response()->json($q->get());
    }

    public function show($id)
    {
        $insumo = Insumo::with(['tipo', 'unidadMedida', 'loteInsumos'])->findOrFail($id);
        $this->asegurarInsumoDelAlmacenUsuario($insumo);

        return response()->json($insumo);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'tipoinsumoid' => 'required|exists:tipoinsumo,tipoinsumoid',
            'unidadmedidaid' => 'required|exists:unidadmedida,unidadmedidaid',
            'stock' => 'required|numeric|min:0',
            'stockminimo' => 'required|numeric|min:0',
            'proveedor' => 'nullable|string|max:100',
            'preciounitario' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string',
        ]);

        if ($request->user()?->hasRole('almacen') && $request->user()->almacenid) {
            $data['almacenid'] = $request->user()->almacenid;
        }

        $insumo = Insumo::create($data);

        return response()->json($insumo, 201);
    }

    public function update(Request $request, $id)
    {
        $insumo = Insumo::findOrFail($id);
        $this->asegurarInsumoDelAlmacenUsuario($insumo);

        $data = $request->validate([
            'nombre' => 'sometimes|string|max:100',
            'tipoinsumoid' => 'sometimes|exists:tipoinsumo,tipoinsumoid',
            'unidadmedidaid' => 'sometimes|exists:unidadmedida,unidadmedidaid',
            'stock' => 'sometimes|numeric|min:0',
            'stockminimo' => 'sometimes|numeric|min:0',
            'proveedor' => 'nullable|string|max:100',
            'preciounitario' => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string',
        ]);

        $insumo->update($data);

        return response()->json($insumo);
    }

    public function destroy($id)
    {
        $insumo = Insumo::findOrFail($id);
        $this->asegurarInsumoDelAlmacenUsuario($insumo);
        $insumo->delete();

        return response()->json(['message' => 'Eliminado correctamente']);
    }

    private function asegurarInsumoDelAlmacenUsuario(Insumo $insumo): void
    {
        $u = auth()->user();
        if (! $u?->hasRole('almacen')) {
            return;
        }
        if (! $u->almacenid || (int) $insumo->almacenid !== (int) $u->almacenid) {
            abort(403);
        }
    }
}