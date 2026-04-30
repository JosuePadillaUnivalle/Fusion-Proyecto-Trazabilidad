<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PedidoController extends Controller
{
    public function index()
    {
        $pedidos = Pedido::with('detalles')->get();
        return view('pedidos.index', compact('pedidos'));
    }

    public function create()
    {
        // Ya no se necesitan cultivos ni unidades
        return view('pedidos.create');
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
            'fechaEntregaDeseada' => 'nullable|date',

            'observaciones'    => 'nullable|string',

            // Detalles
            'detalles' => 'required|array|min:1',
            'detalles.*.cultivo_personalizado' => 'required|string|max:150',
            'detalles.*.cantidad' => 'required|numeric|min:0.01',
            'detalles.*.observaciones' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data) {
            $detalles = $data['detalles'];
            unset($data['detalles']);

            $pedido = Pedido::create($data);
            $pedido->detalles()->createMany($detalles);
        });

        return redirect()->route('pedidos.index');
    }

    public function show($id)
    {
        $pedido = Pedido::with('detalles')->findOrFail($id);
        return view('pedidos.show', compact('pedido'));
    }

    // En tu sistema normalmente solo cambias estado (lo mantengo así)
    public function update(Request $request, Pedido $pedido)
    {
        $data = $request->validate([
            'estado' => 'required|in:pendiente,confirmado,en produccion,rechazado',
        ]);

        $pedido->update($data);

        return back();
    }

    public function destroy($id)
    {
        Pedido::findOrFail($id)->delete();
        return redirect()->route('pedidos.index');
    }
}