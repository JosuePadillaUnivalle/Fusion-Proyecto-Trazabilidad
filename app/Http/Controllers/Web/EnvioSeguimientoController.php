<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Support\LocalOrgTrackFallback;

class EnvioSeguimientoController extends Controller
{
    public function index()
    {
        $filtroEstado = request()->query('estado');
        $filtroEstado = is_string($filtroEstado) && $filtroEstado !== ''
            ? strtolower(trim($filtroEstado))
            : null;

        $panel = LocalOrgTrackFallback::panelEstadisticasEnvios();
        $payload = LocalOrgTrackFallback::enviosPayload(36, $filtroEstado);
        $envios = $payload['data'] ?? [];

        $destinosFiltro = collect($envios)
            ->map(fn ($e) => trim((string) ($e['direccion_destino'] ?? $e['destino'] ?? '')))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('envios.seguimiento', [
            'enviosIniciales' => $envios,
            'metaInicial' => $payload['_meta'] ?? [],
            'statsIniciales' => $panel['stats'],
            'estadosFiltro' => LocalOrgTrackFallback::estadosDistintos(),
            'destinosFiltro' => $destinosFiltro,
            'filtroEstadoActivo' => $filtroEstado,
        ]);
    }
}
