<?php

namespace App\Support;

use App\Models\CertificacionLote;
use App\Models\EvaluacionFinalLoteProduccion;
use App\Models\Lote;
use App\Models\LoteProduccionPedido;
use Illuminate\Support\Collection;

class CertificacionIndexService
{
    public function __construct(
        private LoteProduccionTransformacionService $transformacion
    ) {}

    /** @return array{pendientes: Collection, certificados: Collection, stats: array<string, int>, ambito: string} */
    public function datosPlanta(): array
    {
        $lotes = LoteProduccionPedido::query()
            ->with(['evaluacionesFinales', 'plantillaTransformacion'])
            ->orderByDesc('loteproduccionpedidoid')
            ->get();

        $pendientes = $lotes->filter(function (LoteProduccionPedido $lote) {
            return $this->transformacion->transformacionCompleta($lote)
                && $lote->evaluacionesFinales->isEmpty();
        })->values();

        $evaluaciones = EvaluacionFinalLoteProduccion::query()
            ->with(['loteProduccionPedido', 'inspector'])
            ->orderByDesc('fecha_evaluacion')
            ->limit(20)
            ->get();

        $certificadosOk = $lotes->filter(function (LoteProduccionPedido $lote) {
            $ultima = $lote->evaluacionesFinales->sortByDesc('fecha_evaluacion')->first();

            return $ultima && $ultima->esCertificado();
        });

        return [
            'ambito' => 'planta',
            'pendientes' => $pendientes,
            'certificados' => $evaluaciones,
            'stats' => [
                'pendientes' => $pendientes->count(),
                'certificados' => $certificadosOk->count(),
                'total_lotes' => $lotes->count(),
            ],
        ];
    }

    /** @return array{pendientes: Collection, certificados: Collection, stats: array<string, int>, ambito: string} */
    public function datosCampo(): array
    {
        $estadosCertificables = EstadoLoteCatalogo::idsPorSlugs([
            'listo_para_cosecha',
            'cosechado',
            'finalizado',
        ]);

        $query = Lote::query()
            ->with(['cultivo', 'estadoTipo', 'usuario'])
            ->orderByDesc('loteid');

        if ($estadosCertificables !== []) {
            $query->whereIn('estadolotetipoid', $estadosCertificables);
        }

        $lotesElegibles = $query->get();

        $idsCertificados = CertificacionLote::query()
            ->pluck('loteid')
            ->unique()
            ->all();

        $pendientes = $lotesElegibles
            ->filter(fn (Lote $l) => ! in_array($l->loteid, $idsCertificados, true))
            ->values();

        $certificados = CertificacionLote::query()
            ->with(['lote.cultivo', 'usuario'])
            ->orderByDesc('fecha_certificacion')
            ->limit(20)
            ->get();

        $totalCertificados = CertificacionLote::query()
            ->distinct('loteid')
            ->count('loteid');

        return [
            'ambito' => 'campo',
            'pendientes' => $pendientes,
            'certificados' => $certificados,
            'stats' => [
                'pendientes' => $pendientes->count(),
                'certificados' => $totalCertificados,
                'total_lotes' => $lotesElegibles->count(),
            ],
        ];
    }
}
