@extends('layouts.app')

@section('title', 'Traslado '.$ruta->codigo)
@section('page_title', 'Traslado planta → mayorista')

@push('styles')
@include('partials.logistica-modulo-styles')
<style>
.tpm-show .tpm-head {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 1.15rem;
    background: #fff;
    border: 1px solid #dee2e6;
    border-top: 3px solid #5b21b6;
    border-radius: 8px;
    margin-bottom: 1rem;
}
.tpm-show .tpm-head__actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .85rem 1.25rem;
    margin-left: auto;
    padding-top: .15rem;
}
@media (max-width: 767px) {
    .tpm-show .tpm-head__actions {
        width: 100%;
        margin-left: 0;
        padding-top: .5rem;
        border-top: 1px solid #e2e8f0;
    }
}
.tpm-show .tpm-head__code {
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 1.05rem;
    font-weight: 700;
    color: #5b21b6;
}
.tpm-show .tpm-head__estado {
    font-size: .8rem;
    font-weight: 600;
    color: #0f766e;
}
.tpm-show .tpm-head__fecha { font-size: .78rem; color: #94a3b8; }
.tpm-show .tpm-ruta {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    gap: .5rem;
    padding: .75rem;
    margin-top: .75rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
}
@media (max-width: 575px) {
    .tpm-show .tpm-ruta { grid-template-columns: 1fr; }
}
.tpm-show .tpm-ruta__etiq {
    font-size: .62rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #94a3b8;
}
.tpm-show .tpm-ruta__nom { font-size: .88rem; font-weight: 600; line-height: 1.35; }
.tpm-show .tpm-ruta__origen .tpm-ruta__nom { color: #2c5530; }
.tpm-show .tpm-ruta__destino .tpm-ruta__nom { color: #9f1239; }
.tpm-show .tpm-ruta__sep { display: flex; align-items: center; color: #cbd5e1; }
.tpm-show .tpm-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .75rem;
    margin-bottom: 1rem;
}
@media (max-width: 991px) { .tpm-show .tpm-grid { grid-template-columns: repeat(2, 1fr); } }
.tpm-show .tpm-info {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: .85rem 1rem;
}
.tpm-show .tpm-info__label {
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #94a3b8;
    margin-bottom: .2rem;
}
.tpm-show .tpm-info__value { font-weight: 600; color: #334155; font-size: .88rem; }
.tpm-show .tpm-panel {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
}
.tpm-show .tpm-panel__head {
    padding: .85rem 1rem;
    border-bottom: 1px solid #dee2e6;
    font-size: .95rem;
    font-weight: 700;
    color: #1e293b;
}
.tpm-show .tpm-table thead th {
    background: #f8fafc;
    font-size: .68rem;
    text-transform: uppercase;
    color: #64748b;
    border-top: 0;
}
.tpm-show .tpm-table tbody td { vertical-align: middle; font-size: .875rem; }
.tpm-show .tpm-producto { font-weight: 600; color: #1e293b; }
</style>
@endpush

@section('content')
@php
    $badge = \App\Support\RutaDistribucionCatalogo::badgeEstado($ruta);
    $origen = $ruta->almacenPlantaOrigen?->nombre;
    $destino = $ruta->almacenMayoristaDestino?->nombre;
    $rutaPrefijo = $rutaPrefijo ?? 'logistica.traslados-planta';
@endphp

<div class="tpm-show">
    <div class="tpm-head">
        <div>
            <div class="tpm-head__code">{{ $ruta->codigo }}</div>
            <div class="mt-1">
                <span class="badge badge-{{ $badge['clase'] }}">{{ $badge['etiqueta'] }}</span>
            </div>
            @if($origen || $destino)
            <div class="tpm-ruta">
                <div class="tpm-ruta__origen">
                    <span class="tpm-ruta__etiq">Origen (planta)</span>
                    <span class="tpm-ruta__nom">{{ $origen ?? '—' }}</span>
                </div>
                <div class="tpm-ruta__sep"><i class="fas fa-arrow-right"></i></div>
                <div class="tpm-ruta__destino">
                    <span class="tpm-ruta__etiq">Destino (mayorista)</span>
                    <span class="tpm-ruta__nom">{{ $destino ?? '—' }}</span>
                </div>
            </div>
            @endif
        </div>
        <div class="tpm-head__actions">
            @if(!empty($volverUrl))
            <a href="{{ $volverUrl }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
            @endif
            @if($simulacionActiva)
            <a href="{{ route('logistica.rutas-tiempo-real.show', ['tipo' => 'planta_mayorista', 'id' => $ruta->rutadistribucionid]) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-map-marked-alt mr-1"></i> Ver en tiempo real
            </a>
            @endif
            @if(($rutaPrefijo ?? '') === 'logistica.traslados-planta')
            <a href="{{ route('pedidos.create', ['destino' => 'mayorista']) }}" class="btn btn-success btn-sm ml-md-2">
                <i class="fas fa-plus mr-1"></i> Nuevo traslado
            </a>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="tpm-grid">
                <div class="tpm-info">
                    <div class="tpm-info__label">Chofer</div>
                    <div class="tpm-info__value">{{ trim(($ruta->transportista?->nombre ?? '').' '.($ruta->transportista?->apellido ?? '')) ?: '—' }}</div>
                </div>
                <div class="tpm-info">
                    <div class="tpm-info__label">Vehículo</div>
                    <div class="tpm-info__value">{{ $ruta->vehiculo?->placa ?? 'Sin vehículo' }}</div>
                </div>
                <div class="tpm-info">
                    <div class="tpm-info__label">Costo servicio</div>
                    <div class="tpm-info__value">{{ $ruta->costo_bs ? number_format((float) $ruta->costo_bs, 2).' Bs' : '—' }}</div>
                </div>
                <div class="tpm-info">
                    <div class="tpm-info__label">Productos</div>
                    <div class="tpm-info__value">{{ $ruta->detallesTraslado->count() }} ítem(s)</div>
                </div>
            </div>

            <div class="tpm-panel">
                <div class="tpm-panel__head">Productos del traslado</div>
                <div class="card-body pt-0 pb-2 px-0">
                    @if($ruta->detallesTraslado->isEmpty())
                        <p class="text-muted small mb-0 px-3 py-3">Sin productos registrados.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm tpm-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="pl-3">Producto</th>
                                        <th>Presentación</th>
                                        <th class="text-right">Unidades</th>
                                        <th class="text-right">Total (kg)</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ruta->detallesTraslado as $detalle)
                                    <tr>
                                        <td class="pl-3">
                                            <span class="tpm-producto">{{ $detalle->producto_nombre }}</span>
                                        </td>
                                        <td class="text-muted small">{{ $detalle->presentacion_nombre ?: '—' }}</td>
                                        <td class="text-right text-nowrap">
                                            @if($detalle->cantidad_unidades)
                                                {{ number_format((float) $detalle->cantidad_unidades, 0) }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold text-nowrap">
                                            {{ number_format((float) $detalle->cantidad, 2) }}
                                            {{ $detalle->insumo?->unidadMedida?->abreviatura ?? 'kg' }}
                                        </td>
                                        <td class="text-muted small">{{ $detalle->observaciones ?: '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="tpm-panel">
                <div class="tpm-panel__head">
                    @if($pendienteAprobacion ?? false)
                        Aprobación mayorista
                    @elseif(($ruta->estado ?? '') === \App\Support\RutaDistribucionCatalogo::ESTADO_COMPLETADA)
                        Traslado completado
                    @elseif(($ruta->estado ?? '') === \App\Support\RutaDistribucionCatalogo::ESTADO_EN_RUTA)
                        En ruta
                    @else
                        Salida en ruta
                    @endif
                </div>
                <div class="card-body pt-0">
                    @include('logistica.partials.accion-aprobacion-mayorista-traslado', [
                        'ruta' => $ruta,
                        'pendienteAprobacion' => $pendienteAprobacion ?? false,
                        'puedeGestionarMayorista' => $puedeGestionarMayorista ?? false,
                        'rutaPrefijo' => $rutaPrefijo,
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
