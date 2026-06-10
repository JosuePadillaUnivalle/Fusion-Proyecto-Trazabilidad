<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CertificacionLote;
use App\Models\EstadoLoteTipo;
use App\Models\HistorialEstadoLote;
use App\Models\Lote;
use App\Support\CertificacionIndexService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificacionController extends Controller
{
    public function __construct(
        private CertificacionIndexService $indexService
    ) {}

    public function index(Request $request): View
    {
        $ambito = $request->input('ambito', 'planta');
        if (! in_array($ambito, ['planta', 'campo'], true)) {
            $ambito = 'planta';
        }

        $datos = $ambito === 'campo'
            ? $this->indexService->datosCampo()
            : $this->indexService->datosPlanta();

        return view('certificaciones.index', [
            'ambito' => $datos['ambito'],
            'lotesPendientes' => $datos['pendientes'],
            'certificados' => $datos['certificados'],
            'stats' => $datos['stats'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'loteid'        => ['required', 'integer', 'exists:lote,loteid'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->certificarLote($validated['loteid'], $validated['observaciones'] ?? null);

        return redirect()
            ->route('certificaciones.index', ['ambito' => 'campo'])
            ->with('success', 'Lote de campo certificado correctamente.');
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
            ->route('certificaciones.index', ['ambito' => 'campo'])
            ->with('success', "$count lote(s) de campo certificado(s) correctamente.");
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
