<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PedidoController extends Controller
{
    public function index()
    {
        return response()->json(
            Pedido::with('detalles')->get()
        );
    }

    public function show($id)
    {
        return response()->json(
            Pedido::with('detalles')->findOrFail($id)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'numero_solicitud' => 'required|string|max:50|unique:pedido,numero_solicitud',
            'nombre_planta'    => 'required|string|max:150',

            'latitud'          => 'required|numeric|between:-90,90',
            'longitud'         => 'required|numeric|between:-180,180',
            'direccion_texto'  => 'nullable|string|max:255',

            'estado'           => 'required|in:pendiente,confirmado,en produccion,rechazado',

            'fechapedido'          => 'nullable|date',
            'fechaEntregaDeseada'  => 'nullable|date',

            'observaciones'    => 'nullable|string',

            // Detalles (mínimo 1)
            'detalles' => 'required|array|min:1',
            'detalles.*.cultivo_personalizado' => 'required|string|max:150',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.observaciones' => 'nullable|string',
        ]);

        $pedido = DB::transaction(function () use ($data) {
            $detalles = $data['detalles'];
            unset($data['detalles']);

            $pedido = Pedido::create($data);
            $pedido->detalles()->createMany($detalles);

            return $pedido;
        });

        return response()->json(
            Pedido::with('detalles')->findOrFail($pedido->pedidoid),
            201
        );
    }

    public function update(Request $request, $id)
    {
        $pedido = Pedido::with('detalles')->findOrFail($id);

        $data = $request->validate([
            'numero_solicitud' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('pedido', 'numero_solicitud')->ignore($pedido->pedidoid, 'pedidoid')
            ],
            'nombre_planta'    => 'sometimes|string|max:150',

            'latitud'          => 'sometimes|numeric|between:-90,90',
            'longitud'         => 'sometimes|numeric|between:-180,180',
            'direccion_texto'  => 'nullable|string|max:255',

            'estado'           => 'sometimes|in:pendiente,confirmado,en produccion,rechazado',

            'fechapedido'          => 'nullable|date',
            'fechaEntregaDeseada'  => 'nullable|date',

            'observaciones'    => 'nullable|string',

            // Si envías detalles, se reemplazan todos (simplifica el control)
            'detalles' => 'sometimes|array|min:1',
            'detalles.*.cultivo_personalizado' => 'required_with:detalles|string|max:150',
            'detalles.*.cantidad' => 'required_with:detalles|numeric|min:0.01',
            'detalles.*.observaciones' => 'nullable|string',
        ]);

        DB::transaction(function () use ($pedido, $data) {
            if (array_key_exists('detalles', $data)) {
                $detalles = $data['detalles'];
                unset($data['detalles']);

                // Reemplazo total
                $pedido->detalles()->delete();
                $pedido->detalles()->createMany($detalles);
            }

            $pedido->update($data);
        });

        return response()->json(
            Pedido::with('detalles')->findOrFail($pedido->pedidoid)
        );
    }

    public function destroy($id)
    {
        $pedido = Pedido::findOrFail($id);
        $pedido->delete(); // cascade borra detalles si FK está con cascadeOnDelete

        return response()->json([
            'message' => 'Pedido eliminado correctamente'
        ]);
    }
}