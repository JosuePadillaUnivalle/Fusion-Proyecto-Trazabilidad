<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CertificacionLote;
use App\Models\EstadoLoteTipo;
use App\Models\HistorialEstadoLote;
use App\Models\Lote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CertificacionController extends Controller
{
    public function index(): View
    {
        $certificadosIds = CertificacionLote::query()->pluck('loteid');

        $lotesPendientes = Lote::query()
            ->with(['cultivo', 'estadoTipo', 'usuario'])
            ->whereNotIn('loteid', $certificadosIds)
            ->orderByDesc('loteid')
            ->get();

        $certificados = CertificacionLote::query()
            ->with([
                'lote.cultivo',
                'lote.estadoTipo',
                'lote.unidadSuperficie',
                'lote.actorAbastecimiento',
                'usuario',
            ])
            ->orderByDesc('fecha_certificacion')
            ->limit(50)
            ->get();

        $stats = [
            'pendientes' => $lotesPendientes->count(),
            'certificados' => $certificados->count(),
            'total_lotes' => Lote::count(),
        ];

        return view('certificaciones.index', compact(
            'lotesPendientes',
            'certificados',
            'stats'
        ));
    }

    public function show(CertificacionLote $certificacion): View
    {
        $certificacion->load([
            'lote.cultivo',
            'lote.estadoTipo',
            'lote.unidadSuperficie',
            'lote.actorAbastecimiento',
            'usuario',
        ]);

        return view('certificaciones.show', ['cert' => $certificacion]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'loteid' => ['required', 'integer', 'exists:lote,loteid'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->certificarLote(
            (int) $validated['loteid'],
            $validated['observaciones'] ?? null
        );

        return redirect()
            ->route('certificaciones.index')
            ->with('success', 'Lote certificado correctamente.');
    }

    public function storeBulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'modo' => ['required', 'in:seleccion,todos'],
            'loteids' => ['required_if:modo,seleccion', 'array'],
            'loteids.*' => ['integer', 'exists:lote,loteid'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ]);

        $observaciones = $validated['observaciones'] ?? null;

        if ($validated['modo'] === 'todos') {
            $yaCertificados = CertificacionLote::query()->pluck('loteid');
            $loteIds = Lote::query()
                ->whereNotIn('loteid', $yaCertificados)
                ->pluck('loteid')
                ->all();
        } else {
            $loteIds = array_map('intval', $validated['loteids'] ?? []);
        }

        if ($loteIds === []) {
            return redirect()
                ->route('certificaciones.index')
                ->with('success', 'No hay lotes pendientes por certificar.');
        }

        $certificados = 0;

        DB::transaction(function () use ($loteIds, $observaciones, &$certificados) {
            foreach ($loteIds as $loteId) {
                if (CertificacionLote::where('loteid', $loteId)->exists()) {
                    continue;
                }
                $this->certificarLote($loteId, $observaciones);
                $certificados++;
            }
        });

        return redirect()
            ->route('certificaciones.index')
            ->with('success', "Se certificaron {$certificados} lote(s) correctamente.");
    }

    private function certificarLote(int $loteId, ?string $observaciones): CertificacionLote
    {
        $lote = Lote::query()->findOrFail($loteId);
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
                'observaciones' => $observaciones,
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

        return $certificacion;
    }
}
