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
    public function index(): View
    {
        $q = DocumentoEntrega::query()
            ->with(['usuario', 'pedido'])
            ->orderByDesc('created_at');

        $user = auth()->user();
        if ($user && $user->hasRole('transportista')) {
            DocumentoEntregaTransportista::restringirConsultaTransportista($q, $user->usuarioid);
        } elseif ($user && $user->hasRole('almacen')) {
            if ($user->almacenid) {
                $q->where('almacenid', $user->almacenid);
            } else {
                $q->whereRaw('0 = 1');
            }
        }

        $documentos = $q->paginate(15);

        return view('logistica.documentos.index', compact('documentos'));
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

        $almacenDoc = null;
        if ($user->hasRole('almacen')) {
            $almacenDoc = $user->almacenid;
        } else {
            $almacenDoc = $validated['almacenid'] ?? null;
        }

        DocumentoEntrega::create([
            'titulo' => $validated['titulo'],
            'tipo_documento' => $validated['tipo_documento'],
            'externo_envio_id' => $validated['externo_envio_id'] ?? null,
            'pedidoid' => $validated['pedidoid'] ?? null,
            'almacenid' => $almacenDoc,
            'archivo_path' => $path,
            'usuarioid' => auth()->id(),
            'metadata' => [
                'original_name' => $request->file('archivo')->getClientOriginalName(),
                'mime' => $request->file('archivo')->getClientMimeType(),
                'size' => $request->file('archivo')->getSize(),
            ],
        ]);

        return back()->with('success', 'Documento cargado correctamente.');
    }

    public function download(DocumentoEntrega $documento): StreamedResponse
    {
        $user = auth()->user();
        if ($user && $user->hasRole('transportista')) {
            abort_unless(
                DocumentoEntregaTransportista::puedeVerDocumento($documento, $user->usuarioid),
                403
            );
        }
        if ($user && $user->hasRole('almacen')) {
            abort_unless(
                $user->almacenid && (int) $documento->almacenid === (int) $user->almacenid,
                403
            );
        }

        abort_unless(Storage::disk('public')->exists($documento->archivo_path), 404, 'Documento no encontrado.');

        return Storage::disk('public')->download(
            $documento->archivo_path,
            ($documento->metadata['original_name'] ?? $documento->titulo.'.pdf')
        );
    }
}

