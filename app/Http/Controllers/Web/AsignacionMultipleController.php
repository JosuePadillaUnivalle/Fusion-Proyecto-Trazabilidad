<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\EnvioAsignacionMultiple;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AsignacionMultipleController extends Controller
{
    public function index(): View
    {
        $q = EnvioAsignacionMultiple::query()
            ->with(['transportista', 'asignadoPor', 'ruta']);
        $user = auth()->user();
        if ($user?->hasRole('almacen')) {
            if ($user->almacenid) {
                $q->where('almacenid', $user->almacenid);
            } else {
                $q->whereRaw('0 = 1');
            }
        } elseif (auth()->user()->can('asignaciones.create') === false) {
            $q->where('transportista_usuarioid', auth()->id());
        }
        $asignaciones = $q->orderByDesc('created_at')->paginate(20);

        return view('logistica.asignaciones.index', compact('asignaciones'));
    }

    public function create(): View
    {
        return view('logistica.asignaciones.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'externo_envio_id' => ['required', 'string', 'max:64'],
            'pedidoid' => ['nullable', 'integer', 'exists:pedido,pedidoid'],
            'transportista_usuarioid' => ['required', 'integer', 'exists:usuario,usuarioid'],
            'rutamultientregaid' => ['nullable', 'integer', 'exists:ruta_multi_entrega,rutamultientregaid'],
            'vehiculo_ref' => ['nullable', 'string', 'max:80'],
            'almacenid' => ['nullable', 'integer', 'exists:almacen,almacenid'],
        ]);

        EnvioAsignacionMultiple::updateOrCreate(
            [
                'externo_envio_id' => $validated['externo_envio_id'],
                'transportista_usuarioid' => $validated['transportista_usuarioid'],
            ],
            [
                'pedidoid' => $validated['pedidoid'] ?? null,
                'asignadopor_usuarioid' => auth()->id(),
                'rutamultientregaid' => $validated['rutamultientregaid'] ?? null,
                'vehiculo_ref' => $validated['vehiculo_ref'] ?? null,
                'almacenid' => $validated['almacenid'] ?? null,
                'estado' => 'asignado',
                'fecha_asignacion' => now(),
            ]
        );

        return back()->with('success', 'Envío asignado correctamente.');
    }

    public function storeBatch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'envio_ids' => ['required', 'array', 'min:1'],
            'envio_ids.*' => ['required', 'string', 'max:64'],
            'transportista_usuarioid' => ['required', 'integer', 'exists:usuario,usuarioid'],
            'rutamultientregaid' => ['nullable', 'integer', 'exists:ruta_multi_entrega,rutamultientregaid'],
            'vehiculo_ref' => ['nullable', 'string', 'max:80'],
            'almacenid' => ['nullable', 'integer', 'exists:almacen,almacenid'],
            'productos' => ['nullable', 'array'],
            'productos.*.sku' => ['nullable', 'string'],
            'productos.*.cantidad' => ['nullable', 'numeric'],
        ]);

        foreach ($validated['envio_ids'] as $envioId) {
            EnvioAsignacionMultiple::updateOrCreate(
                [
                    'externo_envio_id' => $envioId,
                    'transportista_usuarioid' => $validated['transportista_usuarioid'],
                ],
                [
                    'asignadopor_usuarioid' => auth()->id(),
                    'rutamultientregaid' => $validated['rutamultientregaid'] ?? null,
                    'vehiculo_ref' => $validated['vehiculo_ref'] ?? null,
                    'almacenid' => $validated['almacenid'] ?? null,
                    'estado' => 'asignado',
                    'fecha_asignacion' => now(),
                    'detalles_productos' => $validated['productos'] ?? null,
                ]
            );
        }

        return back()->with('success', 'Asignación múltiple aplicada correctamente.');
    }
}

