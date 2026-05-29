<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\RutaMultiEntrega;
use App\Models\RutaParada;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RutaMultiEntregaController extends Controller
{
    public function index(): View
    {
        $q = RutaMultiEntrega::query()
            ->with(['transportista'])
            ->withCount('paradas');
        if (auth()->user()->can('rutas_multi.create') === false && auth()->user()->hasRole('transportista')) {
            $q->where('transportista_usuarioid', auth()->id());
        }
        $rutas = $q->orderByDesc('created_at')->paginate(15);

        return view('logistica.rutas.index', compact('rutas'));
    }

    public function create(): View
    {
        return view('logistica.rutas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'transportista_usuarioid' => ['nullable', 'integer', 'exists:usuario,usuarioid'],
            'fecha_salida' => ['nullable', 'date'],
            'paradas' => ['nullable', 'array'],
            'paradas.*.destino' => ['nullable', 'string', 'max:255'],
            'paradas.*.externo_envio_id' => ['nullable', 'string', 'max:64'],
            'paradas.*.pedidoid' => ['nullable', 'integer', 'exists:pedido,pedidoid'],
        ]);

        $ruta = DB::transaction(function () use ($validated) {
            $ruta = RutaMultiEntrega::create([
                'nombre' => $validated['nombre'],
                'creadopor_usuarioid' => auth()->id(),
                'transportista_usuarioid' => $validated['transportista_usuarioid'] ?? null,
                'fecha_salida' => $validated['fecha_salida'] ?? null,
                'estado' => 'planificada',
            ]);

            foreach (($validated['paradas'] ?? []) as $index => $parada) {
                RutaParada::create([
                    'rutamultientregaid' => $ruta->rutamultientregaid,
                    'orden' => $index + 1,
                    'destino' => $parada['destino'] ?? null,
                    'externo_envio_id' => $parada['externo_envio_id'] ?? null,
                    'pedidoid' => $parada['pedidoid'] ?? null,
                    'estado' => 'pendiente',
                ]);
            }

            return $ruta;
        });

        return redirect()->route('logistica.rutas.show', $ruta)
            ->with('success', 'Ruta multi-entrega creada correctamente.');
    }

    public function show(RutaMultiEntrega $ruta): View
    {
        $ruta->load(['transportista', 'paradas.pedido']);

        return view('logistica.rutas.show', compact('ruta'));
    }

    public function update(Request $request, RutaMultiEntrega $ruta): RedirectResponse
    {
        $validated = $request->validate([
            'estado' => ['required', 'in:planificada,en_ruta,completada,cancelada'],
            'fecha_cierre' => ['nullable', 'date'],
        ]);

        $ruta->update($validated);

        return back()->with('success', 'Ruta actualizada correctamente.');
    }

    public function reorder(Request $request, RutaMultiEntrega $ruta): RedirectResponse
    {
        $validated = $request->validate([
            'orden' => ['required', 'array', 'min:1'],
            'orden.*' => ['required', 'integer'],
        ]);

        $paradas = $ruta->paradas()->pluck('rutaparadaid')->all();
        DB::transaction(function () use ($validated, $paradas) {
            foreach ($validated['orden'] as $index => $paradaId) {
                if (in_array($paradaId, $paradas, true)) {
                    RutaParada::where('rutaparadaid', $paradaId)->update(['orden' => $index + 1]);
                }
            }
        });

        return back()->with('success', 'Orden de paradas actualizado.');
    }
}

