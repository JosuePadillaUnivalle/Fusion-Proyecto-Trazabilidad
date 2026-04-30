<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Almacen;
use App\Models\AlmacenMovimiento;
use App\Models\Insumo;
use App\Models\TipoMovimientoAlmacen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlmacenMovimientoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $q = AlmacenMovimiento::query()
            ->with(['almacen', 'insumo.unidadMedida', 'tipo', 'usuario'])
            ->orderByDesc('fecha')
            ->orderByDesc('almacen_movimientoid');

        if ($user?->hasRole('almacen')) {
            if ($user->almacenid) {
                $q->where('almacenid', $user->almacenid);
            } else {
                $q->whereRaw('0 = 1');
            }
        }

        if ($request->filled('naturaleza')) {
            $q->whereHas('tipo', fn($t) => $t->where('naturaleza', $request->string('naturaleza')));
        }

        $movimientos = $q->paginate(20)->withQueryString();

        return view('almacen_movimientos.index', compact('movimientos'));
    }

    public function create(Request $request, string $naturaleza)
    {
        abort_unless(in_array($naturaleza, ['ingreso', 'salida'], true), 404);
        $permisoCrear = $naturaleza === 'ingreso' ? 'almacen.ingresos.create' : 'almacen.salidas.create';
        abort_unless($request->user()?->can($permisoCrear), 403);

        $user = $request->user();
        $almacenes = Almacen::query()->orderBy('nombre');
        $insumos = Insumo::query()->with('unidadMedida')->orderBy('nombre');

        if ($user?->hasRole('almacen')) {
            if (! $user->almacenid) {
                abort(403);
            }
            $almacenes->where('almacenid', $user->almacenid);
            $insumos->where('almacenid', $user->almacenid);
        }

        $tipos = TipoMovimientoAlmacen::query()
            ->where('activo', true)
            ->where('naturaleza', $naturaleza)
            ->orderBy('nombre')
            ->get();

        return view('almacen_movimientos.create', [
            'naturaleza' => $naturaleza,
            'almacenes' => $almacenes->get(),
            'insumos' => $insumos->get(),
            'tipos' => $tipos,
        ]);
    }

    public function store(Request $request, string $naturaleza)
    {
        abort_unless(in_array($naturaleza, ['ingreso', 'salida'], true), 404);
        $permisoCrear = $naturaleza === 'ingreso' ? 'almacen.ingresos.create' : 'almacen.salidas.create';
        abort_unless($request->user()?->can($permisoCrear), 403);

        $data = $request->validate([
            'almacenid' => 'required|exists:almacen,almacenid',
            'insumoid' => 'required|exists:insumo,insumoid',
            'tipo_movimiento_almacenid' => 'required|exists:tipo_movimiento_almacen,tipo_movimiento_almacenid',
            'fecha' => 'required|date',
            'cantidad' => 'required|numeric|min:0.001',
            'referencia' => 'nullable|string|max:100',
            'destino_motivo' => 'nullable|string|max:150',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        if ($user?->hasRole('almacen')) {
            if (! $user->almacenid || (int) $user->almacenid !== (int) $data['almacenid']) {
                abort(403);
            }
        }

        $tipo = TipoMovimientoAlmacen::query()
            ->whereKey($data['tipo_movimiento_almacenid'])
            ->where('naturaleza', $naturaleza)
            ->where('activo', true)
            ->firstOrFail();

        $insumo = Insumo::query()->findOrFail($data['insumoid']);
        if ((int) $insumo->almacenid !== (int) $data['almacenid']) {
            return back()->withInput()->withErrors([
                'insumoid' => 'El insumo seleccionado no pertenece al almacen indicado.',
            ]);
        }

        DB::transaction(function () use ($data, $insumo, $tipo, $user) {
            AlmacenMovimiento::create($data + [
                'usuarioid' => $user->usuarioid,
                'tipo_movimiento_almacenid' => $tipo->tipo_movimiento_almacenid,
            ]);

            if ($tipo->naturaleza === 'ingreso') {
                $insumo->incrementarStock((float) $data['cantidad']);
            } else {
                $insumo->decrementarStock((float) $data['cantidad']);
            }
        });

        return redirect()
            ->route('almacen-movimientos.index', ['naturaleza' => $naturaleza])
            ->with('success', 'Movimiento de almacen registrado correctamente.');
    }

    public function reportes(Request $request)
    {
        $user = $request->user();
        $almacenId = $request->integer('almacenid') ?: null;

        if ($user?->hasRole('almacen')) {
            $almacenId = $user->almacenid ?: 0;
        }

        $base = AlmacenMovimiento::query()
            ->with(['almacen', 'insumo', 'tipo'])
            ->when($almacenId, fn($q) => $q->where('almacenid', $almacenId))
            ->when($request->filled('fecha_desde'), fn($q) => $q->whereDate('fecha', '>=', $request->string('fecha_desde')))
            ->when($request->filled('fecha_hasta'), fn($q) => $q->whereDate('fecha', '<=', $request->string('fecha_hasta')));

        $movimientos = (clone $base)->orderByDesc('fecha')->limit(200)->get();

        $resumenProducto = (clone $base)
            ->join('tipo_movimiento_almacen as tma', 'almacen_movimiento.tipo_movimiento_almacenid', '=', 'tma.tipo_movimiento_almacenid')
            ->join('insumo as i', 'almacen_movimiento.insumoid', '=', 'i.insumoid')
            ->select('i.nombre as producto')
            ->selectRaw("SUM(CASE WHEN tma.naturaleza = 'ingreso' THEN almacen_movimiento.cantidad ELSE 0 END) as ingresos")
            ->selectRaw("SUM(CASE WHEN tma.naturaleza = 'salida' THEN almacen_movimiento.cantidad ELSE 0 END) as salidas")
            ->groupBy('i.nombre')
            ->orderBy('i.nombre')
            ->get();

        $stockPorAlmacen = Insumo::query()
            ->select('almacen.nombre as almacen')
            ->selectRaw('SUM(insumo.stock) as stock')
            ->join('almacen', 'insumo.almacenid', '=', 'almacen.almacenid')
            ->when($almacenId, fn($q) => $q->where('insumo.almacenid', $almacenId))
            ->groupBy('almacen.nombre')
            ->orderBy('almacen.nombre')
            ->get();

        $almacenes = Almacen::query()
            ->when($user?->hasRole('almacen'), fn($q) => $q->where('almacenid', $user->almacenid ?: 0))
            ->orderBy('nombre')
            ->get();

        return view('almacen_movimientos.reportes', compact('movimientos', 'resumenProducto', 'stockPorAlmacen', 'almacenes', 'almacenId'));
    }
}
