<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DocumentoEntrega;
use App\Support\DocumentoEntregaTransportista;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentoEntregaController extends Controller
{
    public function index(Request $request): View
    {
        $q = DocumentoEntrega::query()
            ->with(['usuario', 'pedido'])
            ->orderByDesc('created_at');

        $user = auth()->user();
        if ($user && $user->hasRole('transportista')) {
            DocumentoEntregaTransportista::restringirConsultaTransportista($q, $user->usuarioid);
        }

        if ($request->filled('q')) {
            $term = $request->string('q')->trim()->toString();
            $q->where(function ($w) use ($term) {
                $w->where('titulo', 'like', "%{$term}%")
                    ->orWhere('externo_envio_id', 'like', "%{$term}%")
                    ->orWhere('tipo_documento', 'like', "%{$term}%")
                    ->orWhereHas('usuario', function ($u) use ($term) {
                        $u->where('nombreusuario', 'like', "%{$term}%")
                            ->orWhere('nombre', 'like', "%{$term}%")
                            ->orWhere('apellido', 'like', "%{$term}%");
                    });
            });
        }

        if ($request->filled('tipo')) {
            $q->where('tipo_documento', $request->string('tipo')->toString());
        }

        if ($request->filled('envio')) {
            $q->where('externo_envio_id', 'like', '%'.$request->string('envio')->trim().'%');
        }

        if ($request->filled('cargado_por')) {
            $q->whereHas('usuario', fn ($u) => $u->where('nombreusuario', $request->string('cargado_por')->toString()));
        }

        if ($request->filled('desde')) {
            $q->whereDate('created_at', '>=', $request->string('desde')->toString());
        }

        if ($request->filled('hasta')) {
            $q->whereDate('created_at', '<=', $request->string('hasta')->toString());
        }

        $documentos = $q->paginate(15)->withQueryString();

        $tiposDisponibles = DocumentoEntrega::query()
            ->select('tipo_documento')
            ->distinct()
            ->orderBy('tipo_documento')
            ->pluck('tipo_documento');

        $usuariosCarga = DocumentoEntrega::query()
            ->with('usuario')
            ->whereNotNull('usuarioid')
            ->get()
            ->pluck('usuario')
            ->filter()
            ->unique('usuarioid')
            ->sortBy('nombreusuario');

        return view('logistica.documentos.index', compact(
            'documentos',
            'tiposDisponibles',
            'usuariosCarga'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'tipo_documento' => ['required', 'string', 'max:50'],
            'externo_envio_id' => ['nullable', 'string', 'max:64'],
            'pedidoid' => ['nullable', 'integer', 'exists:pedido,pedidoid'],
            'archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'almacenid' => ['nullable', 'integer', 'exists:almacen,almacenid'],
        ]);

        $user = $request->user();
        if ($user->hasRole('transportista')) {
            $ext = $validated['externo_envio_id'] ?? null;
            $ped = $validated['pedidoid'] ?? null;
            if (($ext === null || $ext === '') && $ped === null) {
                return back()
                    ->withErrors(['externo_envio_id' => 'Indique el ID de envío o el pedido correspondiente a su asignación.'])
                    ->withInput();
            }
            if (! DocumentoEntregaTransportista::puedeSubirParaSusAsignaciones($user->usuarioid, $ext, $ped)) {
                return back()
                    ->withErrors(['externo_envio_id' => 'Solo puede cargar comprobantes para envíos que tenga asignados.'])
                    ->withInput();
            }
        }

        $path = $request->file('archivo')->store('documentos_entrega', 'public');

        $documento = DocumentoEntrega::create([
            'titulo' => $validated['titulo'],
            'tipo_documento' => $validated['tipo_documento'],
            'externo_envio_id' => $validated['externo_envio_id'] ?? null,
            'pedidoid' => $validated['pedidoid'] ?? null,
            'almacenid' => $validated['almacenid'] ?? null,
            'archivo_path' => $path,
            'usuarioid' => auth()->id(),
            'metadata' => [
                'original_name' => $request->file('archivo')->getClientOriginalName(),
                'mime' => $request->file('archivo')->getClientMimeType(),
                'size' => $request->file('archivo')->getSize(),
            ],
        ]);

        return redirect()->route('logistica.documentos.show', $documento)
            ->with('success', 'Documento cargado correctamente.');
    }

    public function show(DocumentoEntrega $documento): View
    {
        $this->autorizarAccesoDocumento($documento);
        $documento->load(['usuario', 'pedido']);

        return view('logistica.documentos.show', compact('documento'));
    }

    public function edit(DocumentoEntrega $documento): View
    {
        $this->autorizarAccesoDocumento($documento);

        return view('logistica.documentos.edit', compact('documento'));
    }

    public function update(Request $request, DocumentoEntrega $documento): RedirectResponse
    {
        $this->autorizarAccesoDocumento($documento);

        $validated = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'tipo_documento' => ['required', 'string', 'max:50'],
            'externo_envio_id' => ['nullable', 'string', 'max:64'],
            'pedidoid' => ['nullable', 'integer', 'exists:pedido,pedidoid'],
            'archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        if ($request->hasFile('archivo')) {
            if ($documento->archivo_path && Storage::disk('public')->exists($documento->archivo_path)) {
                Storage::disk('public')->delete($documento->archivo_path);
            }
            $path = $request->file('archivo')->store('documentos_entrega', 'public');
            $validated['archivo_path'] = $path;
            $validated['metadata'] = [
                'original_name' => $request->file('archivo')->getClientOriginalName(),
                'mime' => $request->file('archivo')->getClientMimeType(),
                'size' => $request->file('archivo')->getSize(),
            ];
        }

        unset($validated['archivo']);
        $documento->update($validated);

        return redirect()->route('logistica.documentos.show', $documento)
            ->with('success', 'Documento actualizado.');
    }

    public function destroy(DocumentoEntrega $documento): RedirectResponse
    {
        $this->autorizarAccesoDocumento($documento);

        if ($documento->archivo_path && Storage::disk('public')->exists($documento->archivo_path)) {
            Storage::disk('public')->delete($documento->archivo_path);
        }

        $documento->delete();

        return redirect()->route('logistica.documentos.index')
            ->with('success', 'Documento eliminado.');
    }

    public function download(DocumentoEntrega $documento): StreamedResponse
    {
        $this->autorizarAccesoDocumento($documento);
        abort_unless(Storage::disk('public')->exists($documento->archivo_path), 404, 'Documento no encontrado.');

        return Storage::disk('public')->download(
            $documento->archivo_path,
            ($documento->metadata['original_name'] ?? $documento->titulo.'.pdf')
        );
    }

    private function autorizarAccesoDocumento(DocumentoEntrega $documento): void
    {
        $user = auth()->user();
        if ($user && $user->hasRole('transportista')) {
            abort_unless(
                DocumentoEntregaTransportista::puedeVerDocumento($documento, $user->usuarioid),
                403
            );
        }
    }
}
