<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Models\Produccion;
use App\Models\ProduccionAlmacenamiento;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with(['produccion.lote.cultivo', 'produccion.unidadMedida', 'unidadMedida'])
            ->orderBy('ventaid', 'desc')
            ->paginate(15);

        return view('ventas.index', compact('ventas'));
    }

    public function create()
    {
        // Obtener producciones que tienen stock en almacén
        $producciones = Produccion::with(['lote.cultivo', 'unidadMedida', 'destino', 'almacenamientos.almacen'])
            ->whereHas('almacenamientos', function($q) {
                $q->where('cantidad', '>', 0);
            })
            ->get()
            ->map(function($p) {
                // Stock disponible = suma de cantidad en almacenamientos
                $p->stock_disponible = $p->almacenamientos->sum('cantidad');
                $p->almacen_nombre = $p->almacenamientos->first()->almacen->nombre ?? 'Sin almacén';
                return $p;
            })
            ->filter(function($p) {
                return $p->stock_disponible > 0;
            });

        $unidades = UnidadMedida::where('categoria', 'peso')->get();

        return view('ventas.create', compact('producciones', 'unidades'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'produccionid'   => 'required|exists:produccion,produccionid',
            'cliente'        => 'required|string|max:100',
            'cantidad'       => 'required|numeric|min:0.01',
            'unidadmedidaid' => 'required|exists:unidadmedida,unidadmedidaid',
            'preciounitario' => 'required|numeric|min:0',
            'observaciones'  => 'nullable|string|max:200',
        ]);

        DB::beginTransaction();

        try {
            $produccion = Produccion::with('almacenamientos')->findOrFail($data['produccionid']);

            // Calcular stock disponible en almacén
            $stockDisponible = $produccion->almacenamientos->sum('cantidad');

            if ($data['cantidad'] > $stockDisponible) {
                return back()->withErrors([
                    'cantidad' => "Stock insuficiente en almacén. Disponible: {$stockDisponible}"
                ])->withInput();
            }

            // Reducir stock del almacén (FIFO - del primero que tenga stock)
            $cantidadAReducir = $data['cantidad'];
            
            foreach ($produccion->almacenamientos as $almacenamiento) {
                if ($cantidadAReducir <= 0) break;
                
                if ($almacenamiento->cantidad > 0) {
                    $reducir = min($almacenamiento->cantidad, $cantidadAReducir);
                    $almacenamiento->cantidad -= $reducir;
                    $almacenamiento->save();
                    $cantidadAReducir -= $reducir;
                    
                    // Si el almacenamiento queda en 0, marcar fecha de salida
                    if ($almacenamiento->cantidad <= 0) {
                        $almacenamiento->fechasalida = now();
                        $almacenamiento->save();
                    }
                }
            }

            // Crear la venta con fecha automática
            Venta::create([
                'produccionid'   => $data['produccionid'],
                'cliente'        => $data['cliente'],
                'cantidad'       => $data['cantidad'],
                'unidadmedidaid' => $data['unidadmedidaid'],
                'preciounitario' => $data['preciounitario'],
                'fechaventa'     => now()->toDateString(),
                'observaciones'  => $data['observaciones'],
            ]);

            DB::commit();

            $total = $data['cantidad'] * $data['preciounitario'];
            $unidad = UnidadMedida::find($data['unidadmedidaid']);
            
            return redirect()
                ->route('ventas.index')
                ->with('success', "Venta registrada: {$data['cantidad']} {$unidad->abreviatura} a {$data['cliente']}. Total: Bs. " . number_format($total, 2) . ". Stock actualizado en almacén.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar venta: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Venta $venta)
    {
        $venta->load(['produccion.lote.cultivo', 'produccion.unidadMedida', 'produccion.almacenamientos.almacen', 'unidadMedida']);
        return view('ventas.show', compact('venta'));
    }

    public function edit(Venta $venta)
    {
        $producciones = Produccion::with(['lote.cultivo', 'unidadMedida'])->get();
        $unidades = UnidadMedida::where('categoria', 'peso')->get();

        return view('ventas.edit', compact('venta', 'producciones', 'unidades'));
    }

    public function update(Request $request, Venta $venta)
    {
        $data = $request->validate([
            'produccionid'   => 'required|exists:produccion,produccionid',
            'cliente'        => 'required|string|max:100',
            'cantidad'       => 'required|numeric|min:0.01',
            'unidadmedidaid' => 'required|exists:unidadmedida,unidadmedidaid',
            'preciounitario' => 'required|numeric|min:0',
            'fechaventa'     => 'required|date',
            'observaciones'  => 'nullable|string|max:200',
        ]);

        DB::beginTransaction();

        try {
            $produccion = Produccion::with('almacenamientos')->findOrFail($data['produccionid']);
            
            // Devolver la cantidad anterior al almacén
            $cantidadAnterior = $venta->cantidad;
            $almacenamiento = $produccion->almacenamientos->first();
            if ($almacenamiento) {
                $almacenamiento->cantidad += $cantidadAnterior;
                $almacenamiento->fechasalida = null; // Reabrir si estaba cerrado
                $almacenamiento->save();
            }

            // Verificar si hay suficiente stock para la nueva cantidad
            $stockDisponible = $produccion->almacenamientos->sum('cantidad');
            
            if ($data['cantidad'] > $stockDisponible) {
                DB::rollBack();
                return back()->withErrors([
                    'cantidad' => "Stock insuficiente en almacén. Disponible: {$stockDisponible}"
                ])->withInput();
            }

            // Reducir la nueva cantidad
            $cantidadAReducir = $data['cantidad'];
            foreach ($produccion->almacenamientos as $alm) {
                if ($cantidadAReducir <= 0) break;
                
                if ($alm->cantidad > 0) {
                    $reducir = min($alm->cantidad, $cantidadAReducir);
                    $alm->cantidad -= $reducir;
                    $alm->save();
                    $cantidadAReducir -= $reducir;
                    
                    if ($alm->cantidad <= 0) {
                        $alm->fechasalida = now();
                        $alm->save();
                    }
                }
            }

            $venta->update($data);

            DB::commit();

            return redirect()
                ->route('ventas.index')
                ->with('success', 'Venta actualizada y stock ajustado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy(Venta $venta)
    {
        DB::beginTransaction();

        try {
            // Devolver la cantidad al almacén
            $produccion = Produccion::with('almacenamientos')->find($venta->produccionid);
            
            if ($produccion) {
                $almacenamiento = $produccion->almacenamientos->first();
                if ($almacenamiento) {
                    $almacenamiento->cantidad += $venta->cantidad;
                    $almacenamiento->fechasalida = null; // Reabrir si estaba cerrado
                    $almacenamiento->save();
                }
            }

            $cantidadDevuelta = $venta->cantidad;
            $venta->delete();

            DB::commit();

            return redirect()
                ->route('ventas.index')
                ->with('success', "Venta eliminada. Se devolvieron {$cantidadDevuelta} al stock del almacén.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }
}