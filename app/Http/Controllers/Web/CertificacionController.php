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
        $todosLotes = Lote::query()
            ->with(['cultivo', 'estadoTipo', 'usuario'])
            ->orderByDesc('loteid')
            ->get();

        $certificados = CertificacionLote::query()
            ->with(['lote.cultivo', 'usuario'])
            ->orderByDesc('fecha_certificacion')
            ->limit(20)
            ->get();

        $lotesCertificadosIds = $certificados->pluck('loteid')->toArray();

        $lotesPendientes = $todosLotes
            ->filter(fn ($l) => ! in_array($l->loteid, $lotesCertificadosIds))
            ->values();

        $stats = [
            'pendientes'   => $lotesPendientes->count(),
            'certificados' => count($lotesCertificadosIds),
            'total'        => $todosLotes->count(),
        ];

        return view('certificaciones.index', compact('lotesPendientes', 'certificados', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'loteid'        => ['required', 'integer', 'exists:lote,loteid'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->certificarLote($validated['loteid'], $validated['observaciones'] ?? null);

        return redirect()
            ->route('certificaciones.index')
            ->with('success', 'Lote certificado correctamente.');
    }

    public function storeBatch(Request $request): RedirectResponse
    {
        $request->validate([
            'lotes'         => ['required', 'array', 'min:1'],
            'lotes.*'       => ['integer', 'exists:lote,loteid'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        $count = 0;
        foreach ($request->lotes as $loteid) {
            $this->certificarLote((int) $loteid, $request->observaciones);
            $count++;
        }

        return redirect()
            ->route('certificaciones.index')
            ->with('success', "$count lote(s) certificado(s) correctamente.");
    }

    private function certificarLote(int $loteid, ?string $observaciones): void
    {
        $lote = Lote::findOrFail($loteid);

        $estadoCertificado = EstadoLoteTipo::firstOrCreate(
            ['nombre' => 'Certificado'],
            ['descripcion' => 'Lote validado para despacho y trazabilidad']
        );

        $certificacion = CertificacionLote::updateOrCreate(
            ['loteid' => $lote->loteid],
            [
                'usuarioid'           => auth()->id(),
                'codigo_certificado'  => 'CERT-' . now()->format('Y') . '-' . str_pad((string) $lote->loteid, 4, '0', STR_PAD_LEFT),
                'observaciones'       => $observaciones,
                'fecha_certificacion' => now(),
            ]
        );

        $lote->update(['estadolotetipoid' => $estadoCertificado->estadolotetipoid]);

        HistorialEstadoLote::create([
            'loteid'           => $lote->loteid,
            'estadolotetipoid' => $estadoCertificado->estadolotetipoid,
            'fecha_cambio'     => now(),
            'observaciones'    => 'Certificación: ' . $certificacion->codigo_certificado,
            'usuarioid'        => auth()->id(),
        ]);
    }
}
