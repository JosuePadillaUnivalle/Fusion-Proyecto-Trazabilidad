<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\IncidenteEnvio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncidenteEnvioController extends Controller
{
    public function index(Request $request): View
    {
        $estado = $request->string('estado')->toString();
        $q = IncidenteEnvio::query()
            ->with(['reportadoPor', 'resueltoPor', 'pedido'])
            ->when($estado !== '', fn($query) => $query->where('estado', $estado))
            ->orderByDesc('created_at');

        $incidentes = $q->paginate(15);

        return view('logistica.incidentes.index', compact('incidentes', 'estado'));
    }

    public function create(): View
    {
        return view('logistica.incidentes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'externo_envio_id' => ['nullable', 'string', 'max:64'],
            'pedidoid' => ['nullable', 'integer', 'exists:pedido,pedidoid'],
            'tipo' => ['required', 'string', 'max:100'],
            'descripcion' => ['required', 'string', 'max:3000'],
        ]);

        $validated['reportadopor_usuarioid'] = auth()->id();
        $validated['estado'] = 'abierto';

        IncidenteEnvio::create($validated);

        return redirect()->route('logistica.incidentes.index')
            ->with('success', 'Incidente registrado correctamente.');
    }

    public function resolve(Request $request, IncidenteEnvio $incidente): RedirectResponse
    {
        $validated = $request->validate([
            'nota_resolucion' => ['nullable', 'string', 'max:2000'],
        ]);

        $incidente->update([
            'estado' => 'resuelto',
            'nota_resolucion' => $validated['nota_resolucion'] ?? null,
            'resueltopor_usuarioid' => auth()->id(),
            'fecha_resolucion' => now(),
        ]);

        return back()->with('success', 'Incidente resuelto correctamente.');
    }
}

