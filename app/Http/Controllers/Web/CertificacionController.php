<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CertificacionLote;
use App\Models\EstadoLoteTipo;
use App\Models\HistorialEstadoLote;
use App\Models\Lote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificacionController extends Controller
{
    public function index(): View
    {
        $lotes = Lote::query()
            ->with(['cultivo', 'estadoTipo', 'usuario'])
            ->orderByDesc('loteid')
            ->get();

        $certificados = CertificacionLote::query()
            ->with(['lote', 'usuario'])
            ->orderByDesc('fecha_certificacion')
            ->limit(50)
            ->get();

        return view('certificaciones.index', compact('lotes', 'certificados'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'loteid' => ['required', 'integer', 'exists:lote,loteid'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        $lote = Lote::query()->findOrFail($validated['loteid']);
        $estadoCertificado = EstadoLoteTipo::firstOrCreate(
            ['nombre' => 'Certificado'],
            ['descripcion' => 'Lote validado para despacho y trazabilidad']
        );

        $usuarioId = (int) auth()->id();
        $codigo = 'CERT-'.str_pad((string) $lote->loteid, 5, '0', STR_PAD_LEFT).'-'.now()->format('Ymd');

        $certificacion = CertificacionLote::updateOrCreate(
            ['loteid' => $lote->loteid],
            [
                'usuarioid' => $usuarioId,
                'codigo_certificado' => $codigo,
                'observaciones' => $validated['observaciones'] ?? null,
                'fecha_certificacion' => now(),
            ]
        );

        $lote->update(['estadolotetipoid' => $estadoCertificado->estadolotetipoid]);

        HistorialEstadoLote::create([
            'loteid' => $lote->loteid,
            'estadolotetipoid' => $estadoCertificado->estadolotetipoid,
            'fecha_cambio' => now(),
            'observaciones' => 'Certificación registrada: '.$certificacion->codigo_certificado,
            'usuarioid' => $usuarioId,
        ]);

        return redirect()
            ->route('certificaciones.index')
            ->with('success', 'Lote certificado correctamente.');
    }
}

