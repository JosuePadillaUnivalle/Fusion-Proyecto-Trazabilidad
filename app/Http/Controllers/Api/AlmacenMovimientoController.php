<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            ->with(['almacen', 'insumo', 'tipo', 'usuario'])
            ->orderByDesc('fecha')
            ->orderByDesc('almacen_movimientoid');

        if ($user?->hasRole('almacen')) {
            if ($user->almacenid) {
                $q->where('almacenid', $user->almacenid);
            } else {
                $q->whereRaw('0 = 1');
            }
        }

        return response()->json($q->paginate(20));
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
            return response()->json(['message' => 'El insumo no pertenece al almacen indicado.'], 422);
        }

        $movimiento = DB::transaction(function () use ($data, $insumo, $tipo, $user) {
            $mov = AlmacenMovimiento::create($data + [
                'usuarioid' => $user->usuarioid,
                'tipo_movimiento_almacenid' => $tipo->tipo_movimiento_almacenid,
            ]);

            if ($tipo->naturaleza === 'ingreso') {
                $insumo->incrementarStock((float) $data['cantidad']);
            } else {
                $insumo->decrementarStock((float) $data['cantidad']);
            }

            return $mov;
        });

        return response()->json($movimiento->load(['almacen', 'insumo', 'tipo', 'usuario']), 201);
    }
}
