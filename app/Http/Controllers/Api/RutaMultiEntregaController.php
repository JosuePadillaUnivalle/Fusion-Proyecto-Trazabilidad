<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RutaMultiEntrega;
use App\Models\RutaParada;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RutaMultiEntregaController extends Controller
{
    public function index(): JsonResponse
    {
        $rutas = RutaMultiEntrega::query()
            ->with(['transportista'])
            ->withCount('paradas')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($rutas);
    }

    public function store(Request $request): JsonResponse
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

        return response()->json($ruta->load(['transportista', 'paradas']), 201);
    }

    public function show(RutaMultiEntrega $ruta): JsonResponse
    {
        return response()->json($ruta->load(['transportista', 'paradas.pedido']));
    }

    public function update(Request $request, RutaMultiEntrega $ruta): JsonResponse
    {
        $validated = $request->validate([
            'estado' => ['required', 'in:planificada,en_ruta,completada,cancelada'],
            'fecha_cierre' => ['nullable', 'date'],
        ]);

        $ruta->update($validated);

        return response()->json($ruta->fresh(['transportista', 'paradas']));
    }

    public function reorder(Request $request, RutaMultiEntrega $ruta): JsonResponse
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

        return response()->json($ruta->fresh(['paradas']));
    }
}

