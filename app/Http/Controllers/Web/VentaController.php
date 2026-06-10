<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cultivo;
use App\Models\Produccion;
use App\Models\UnidadMedida;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->ventasFilteredQuery($request);

        $statsQuery = clone $query;
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'ingresos' => (float) (clone $statsQuery)->sum(DB::raw('cantidad * preciounitario')),
            'kg_vendidos' => (float) (clone $statsQuery)->sum('cantidad'),
            'promedio' => 0,
        ];
        if ($stats['total'] > 0) {
            $stats['promedio'] = $stats['ingresos'] / $stats['total'];
        }

        $ventas = $query
            ->with(['produccion.lote.cultivo', 'produccion.unidadMedida', 'unidadMedida'])
            ->orderBy('ventaid', 'desc')
            ->paginate(15)
            ->withQueryString();

        $cultivosFiltro = Cultivo::query()->orderBy('nombre')->get(['cultivoid', 'nombre']);

        return view('ventas.index', compact('ventas', 'stats', 'cultivosFiltro'));
    }

    private function ventasFilteredQuery(Request $request)
    {
        $query = Venta::query();

        if ($request->filled('buscar')) {
            $buscar = '%'.trim((string) $request->buscar).'%';
            $query->where(function ($q) use ($buscar) {
                $q->where('cliente', 'like', $buscar)
                    ->orWhereHas('produccion.lote', fn ($l) => $l->where('nombre', 'like', $buscar))
                    ->orWhereHas('produccion.lote.cultivo', fn ($c) => $c->where('nombre', 'like', $buscar));
            });
        }

        if ($request->filled('cultivo_id')) {
            $query->whereHas('produccion.lote', fn ($l) => $l->where('cultivoid', (int) $request->cultivo_id));
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fechaventa', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fechaventa', '<=', $request->fecha_hasta);
        }

        return $query;
    }

    public function create()
    {
        $hayProductosConStock = Produccion::query()
            ->whereHas('almacenamientos', fn ($q) => $q->where('cantidad', '>', 0))
            ->exists();

        $cultivosFiltro = Cultivo::query()
            ->whereHas('lotes.producciones.almacenamientos', fn ($q) => $q->where('cantidad', '>', 0))
            ->orderBy('nombre')
            ->get(['cultivoid', 'nombre']);

        $oldProduccionLabel = '';
        $oldProduccionExtra = null;
        if (old('produccionid')) {
            $prodOld = Produccion::with(['lote.cultivo', 'unidadMedida', 'almacenamientos.almacen'])
                ->find(old('produccionid'));
            if ($prodOld) {
                $stock = (float) $prodOld->almacenamientos->sum('cantidad');
                $cultivo = $prodOld->lote->cultivo->nombre ?? 'Producto';
                $lote = $prodOld->lote->nombre ?? 'Lote';
                $almacen = $prodOld->almacenamientos->first()->almacen->nombre ?? 'Sin almacén';
                $oldProduccionLabel = $cultivo.' · '.$lote.' · '.$almacen;
                $oldProduccionExtra = [
                    'disponible' => $stock,
                    'unidad' => $prodOld->unidadMedida->abreviatura ?? 'kg',
                    'unidad_id' => $prodOld->unidadmedidaid,
                    'cultivo' => $cultivo,
                    'lote' => $lote,
                    'almacen' => $almacen,
                    'precio' => old('preciounitario'),
                ];
            }
        }

        $unidades = UnidadMedida::where('categoria', 'peso')->get();

        return view('ventas.create', compact(
            'hayProductosConStock',
            'cultivosFiltro',
            'oldProduccionLabel',
            'oldProduccionExtra',
            'unidades'
        ));
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

            $stockDisponible = $produccion->almacenamientos->sum('cantidad');

            if ($data['cantidad'] > $stockDisponible) {
                return back()->withErrors([
                    'cantidad' => "Stock insuficiente en almacén. Disponible: {$stockDisponible}",
                ])->withInput();
            }

            $cantidadAReducir = $data['cantidad'];

            foreach ($produccion->almacenamientos as $almacenamiento) {
                if ($cantidadAReducir <= 0) {
                    break;
                }

                if ($almacenamiento->cantidad > 0) {
                    $reducir = min($almacenamiento->cantidad, $cantidadAReducir);
                    $almacenamiento->cantidad -= $reducir;
                    $almacenamiento->save();
                    $cantidadAReducir -= $reducir;

                    if ($almacenamiento->cantidad <= 0) {
                        $almacenamiento->fechasalida = now();
                        $almacenamiento->save();
                    }
                }
            }

            $total = round((float) $data['cantidad'] * (float) $data['preciounitario'], 2);

            Venta::create([
                'produccionid'   => $data['produccionid'],
                'cliente'        => $data['cliente'],
                'cantidad'       => $data['cantidad'],
                'unidadmedidaid' => $data['unidadmedidaid'],
                'preciounitario' => $data['preciounitario'],
                'total'          => $total,
                'fechaventa'     => now()->toDateString(),
                'observaciones'  => $data['observaciones'],
            ]);

            DB::commit();
            $unidad = UnidadMedida::find($data['unidadmedidaid']);

            return redirect()
                ->route('ventas.index')
                ->with('success', "Venta registrada: {$data['cantidad']} {$unidad->abreviatura} a {$data['cliente']}. Total: Bs. ".number_format($total, 2).'. Stock actualizado en almacén.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Error al registrar venta: '.$e->getMessage()])->withInput();
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

            $cantidadAnterior = $venta->cantidad;
            $almacenamiento = $produccion->almacenamientos->first();
            if ($almacenamiento) {
                $almacenamiento->cantidad += $cantidadAnterior;
                $almacenamiento->fechasalida = null;
                $almacenamiento->save();
            }

            $stockDisponible = $produccion->almacenamientos->sum('cantidad');

            if ($data['cantidad'] > $stockDisponible) {
                DB::rollBack();

                return back()->withErrors([
                    'cantidad' => "Stock insuficiente en almacén. Disponible: {$stockDisponible}",
                ])->withInput();
            }

            $cantidadAReducir = $data['cantidad'];
            foreach ($produccion->almacenamientos as $alm) {
                if ($cantidadAReducir <= 0) {
                    break;
                }

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

            $data['total'] = round((float) $data['cantidad'] * (float) $data['preciounitario'], 2);
            $venta->update($data);

            DB::commit();

            return redirect()
                ->route('ventas.index')
                ->with('success', 'Venta actualizada y stock ajustado.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors(['error' => 'Error al actualizar: '.$e->getMessage()])->withInput();
        }
    }

    public function destroy(Venta $venta)
    {
        DB::beginTransaction();

        try {
            $produccion = Produccion::with('almacenamientos')->find($venta->produccionid);

            if ($produccion) {
                $almacenamiento = $produccion->almacenamientos->first();
                if ($almacenamiento) {
                    $almacenamiento->cantidad += $venta->cantidad;
                    $almacenamiento->fechasalida = null;
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

            return back()->withErrors(['error' => 'Error al eliminar: '.$e->getMessage()]);
        }
    }
}
