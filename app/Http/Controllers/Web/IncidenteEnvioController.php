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
        $q = IncidenteEnvio::query()
            ->with(['reportadoPor', 'resueltoPor', 'pedido'])
            ->orderByDesc('created_at');

        if ($request->filled('q')) {
            $term = $request->string('q')->trim()->toString();
            $q->where(function ($w) use ($term) {
                $w->where('descripcion', 'like', "%{$term}%")
                    ->orWhere('tipo', 'like', "%{$term}%")
                    ->orWhere('externo_envio_id', 'like', "%{$term}%")
                    ->orWhere('nota_resolucion', 'like', "%{$term}%")
                    ->orWhereHas('reportadoPor', function ($u) use ($term) {
                        $u->where('nombreusuario', 'like', "%{$term}%")
                            ->orWhere('nombre', 'like', "%{$term}%")
                            ->orWhere('apellido', 'like', "%{$term}%");
                    });
            });
        }

        if ($request->filled('estado')) {
            $q->where('estado', $request->string('estado')->toString());
        }

        if ($request->filled('tipo')) {
            $q->where('tipo', 'like', '%'.$request->string('tipo')->trim().'%');
        }

        if ($request->filled('envio')) {
            $q->where('externo_envio_id', 'like', '%'.$request->string('envio')->trim().'%');
        }

        if ($request->filled('desde')) {
            $q->whereDate('created_at', '>=', $request->string('desde')->toString());
        }

        if ($request->filled('hasta')) {
            $q->whereDate('created_at', '<=', $request->string('hasta')->toString());
        }

        $resumenIncidentes = [
            'total' => (clone $q)->count(),
            'activos' => (clone $q)->whereIn('estado', ['abierto', 'pendiente'])->count(),
            'resueltos' => (clone $q)->where('estado', 'resuelto')->count(),
        ];

        $incidentes = $q->paginate(15)->withQueryString();

        $tiposDisponibles = IncidenteEnvio::query()
            ->select('tipo')
            ->distinct()
            ->orderBy('tipo')
            ->pluck('tipo');

        return view('logistica.incidentes.index', compact(
            'incidentes',
            'tiposDisponibles',
            'resumenIncidentes'
        ));
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

        $incidente = IncidenteEnvio::create($validated);

        return redirect()->route('logistica.incidentes.show', $incidente)
            ->with('success', 'Incidente registrado correctamente.');
    }

    public function show(IncidenteEnvio $incidente): View
    {
        $incidente->load(['reportadoPor', 'resueltoPor', 'pedido']);

        return view('logistica.incidentes.show', compact('incidente'));
    }

    public function edit(IncidenteEnvio $incidente): View
    {
        return view('logistica.incidentes.edit', compact('incidente'));
    }

    public function update(Request $request, IncidenteEnvio $incidente): RedirectResponse
    {
        $validated = $request->validate([
            'externo_envio_id' => ['nullable', 'string', 'max:64'],
            'pedidoid' => ['nullable', 'integer', 'exists:pedido,pedidoid'],
            'tipo' => ['required', 'string', 'max:100'],
            'descripcion' => ['required', 'string', 'max:3000'],
            'estado' => ['nullable', 'string', 'in:abierto,pendiente,resuelto'],
            'nota_resolucion' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! auth()->user()?->can('incidentes.resolve')) {
            unset($validated['estado'], $validated['nota_resolucion']);
        }

        if (($validated['estado'] ?? null) === 'resuelto' && $incidente->estado !== 'resuelto') {
            $validated['resueltopor_usuarioid'] = auth()->id();
            $validated['fecha_resolucion'] = now();
        }

        $incidente->update($validated);

        return redirect()->route('logistica.incidentes.show', $incidente)
            ->with('success', 'Incidente actualizado.');
    }

    public function destroy(IncidenteEnvio $incidente): RedirectResponse
    {
        $incidente->delete();

        return redirect()->route('logistica.incidentes.index')
            ->with('success', 'Incidente eliminado.');
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
