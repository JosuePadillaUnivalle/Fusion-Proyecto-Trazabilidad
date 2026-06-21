@extends('layouts.app')

@section('title', 'Detalle de movimiento')
@section('page_title', 'Detalle de movimiento de almacén')

@push('styles')
@include('partials.modulo-inventario-styles')
<style>
.page-mov-detalle { max-width: 960px; margin: 0 auto; }

.page-mov-detalle .mov-det-hero {
    border-radius: 14px;
    padding: 1.25rem 1.5rem;
    color: #fff;
    margin-bottom: 1.25rem;
    box-shadow: 0 8px 24px rgba(18, 38, 63, .12);
}
.page-mov-detalle .mov-det-hero.ingreso {
    background: linear-gradient(135deg, #1e4620 0%, #2c5530 55%, #4a7c59 100%);
}
.page-mov-detalle .mov-det-hero.salida {
    background: linear-gradient(135deg, #b45309 0%, #d97706 55%, #f59e0b 100%);
}
.page-mov-detalle .mov-det-hero-top {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    gap: .75rem;
    margin-bottom: 1rem;
}
.page-mov-detalle .mov-det-badge {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.28);
    border-radius: 999px;
    padding: .25rem .75rem;
    font-size: .78rem;
    font-weight: 600;
}
.page-mov-detalle .mov-det-id {
    font-size: .8rem;
    opacity: .85;
}
.page-mov-detalle .mov-det-titulo {
    font-size: 1.35rem;
    font-weight: 800;
    margin: 0 0 .35rem;
}
.page-mov-detalle .mov-det-cantidad {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1.1;
}
.page-mov-detalle .mov-det-cantidad small {
    font-size: .95rem;
    font-weight: 600;
    opacity: .9;
}
.page-mov-detalle .mov-det-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: .85rem;
    margin-bottom: 1rem;
}
.page-mov-detalle .mov-det-item {
    background: #fff;
    border: 1px solid #e8f0ea;
    border-radius: 12px;
    padding: .85rem 1rem;
    box-shadow: 0 2px 10px rgba(18, 38, 63, .04);
}
.page-mov-detalle .mov-det-label {
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #64748b;
    font-weight: 700;
    margin-bottom: .3rem;
}
.page-mov-detalle .mov-det-value {
    font-size: .95rem;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.35;
    word-break: break-word;
}
.page-mov-detalle .mov-det-value.mono {
    font-family: ui-monospace, monospace;
    font-size: .88rem;
    color: #475569;
}
.page-mov-detalle .mov-det-nota {
    background: #f8faf9;
    border: 1px solid #e8f0ea;
    border-left: 4px solid #4a7c59;
    border-radius: 0 10px 10px 0;
    padding: .9rem 1rem;
    margin-bottom: 1rem;
}
.page-mov-detalle .mov-det-nota .mov-det-value {
    font-weight: 500;
    font-size: .9rem;
    color: #334155;
}
.page-mov-detalle .mov-det-footer {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: .75rem;
    padding-top: .5rem;
}
.page-mov-detalle .mov-det-meta {
    font-size: .8rem;
    color: #94a3b8;
}
</style>
@endpush

@section('content')
@php
    $esIngreso = $movimiento->tipo?->naturaleza === 'ingreso';
    $responsable = trim(($movimiento->usuario?->nombre ?? '') . ' ' . ($movimiento->usuario?->apellido ?? ''));
    $unidad = $movimiento->insumo?->unidadMedida?->abreviatura ?? '';
@endphp

<div class="modulo-inv page-mov-detalle">

    <div class="mov-det-hero {{ $esIngreso ? 'ingreso' : 'salida' }}">
        <div class="mov-det-hero-top">
            <span class="mov-det-badge">
                <i class="fas fa-arrow-{{ $esIngreso ? 'down' : 'up' }}"></i>
                {{ $esIngreso ? 'Ingreso' : 'Salida' }}
            </span>
            <span class="mov-det-id">#{{ $movimiento->almacen_movimientoid }}</span>
        </div>
        <h2 class="mov-det-titulo">{{ $movimiento->tipo?->nombre ?? 'Movimiento' }}</h2>
        <div class="mov-det-cantidad">
            {{ number_format((float) $movimiento->cantidad, 2) }}
            @if($unidad)<small>{{ $unidad }}</small>@endif
        </div>
        <div class="mt-2 opacity-90" style="font-size:.9rem;">
            <i class="fas fa-box mr-1"></i>{{ $movimiento->insumo?->nombre ?? '—' }}
        </div>
    </div>

    <div class="mov-det-grid">
        <div class="mov-det-item">
            <div class="mov-det-label"><i class="fas fa-calendar-alt mr-1"></i> Fecha</div>
            <div class="mov-det-value">{{ optional($movimiento->fecha)->format('d/m/Y') ?: '—' }}</div>
        </div>
        <div class="mov-det-item">
            <div class="mov-det-label"><i class="fas fa-warehouse mr-1"></i> Almacén</div>
            <div class="mov-det-value">{{ $movimiento->almacen?->nombre ?? '—' }}</div>
        </div>
        <div class="mov-det-item">
            <div class="mov-det-label"><i class="fas fa-user mr-1"></i> Responsable</div>
            <div class="mov-det-value">{{ $responsable ?: '—' }}</div>
        </div>
        <div class="mov-det-item">
            <div class="mov-det-label"><i class="fas fa-barcode mr-1"></i> Referencia</div>
            <div class="mov-det-value mono">{{ $movimiento->referencia ?: '—' }}</div>
        </div>
        <div class="mov-det-item">
            <div class="mov-det-label"><i class="fas fa-map-marker-alt mr-1"></i> Destino / motivo</div>
            <div class="mov-det-value">{{ $movimiento->destino_motivo ?: '—' }}</div>
        </div>
        <div class="mov-det-item">
            <div class="mov-det-label"><i class="fas fa-clock mr-1"></i> Registrado en sistema</div>
            <div class="mov-det-value">{{ optional($movimiento->created_at)->format('d/m/Y H:i') ?: '—' }}</div>
        </div>
    </div>

    @if($movimiento->observaciones)
    <div class="mov-det-nota">
        <div class="mov-det-label mb-2"><i class="fas fa-sticky-note mr-1"></i> Observaciones</div>
        <div class="mov-det-value">{{ $movimiento->observaciones }}</div>
    </div>
    @endif

    <div class="mov-det-footer">
        <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.movimientos.index', array_filter(['naturaleza' => $filtroNaturaleza ?: null])) }}"
           class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>
        <span class="mov-det-meta">
            Movimiento de almacén · {{ $esIngreso ? 'Entrada' : 'Salida' }} de inventario
        </span>
    </div>

</div>
@endsection
