@extends('layouts.app')

@section('title', 'Detalle de movimiento')
@section('page_title', 'Detalle de movimiento de almacén')
@push('styles')
<style>.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}.detail-label{font-size:.8rem;color:#6c757d;text-transform:uppercase;letter-spacing:.03em;margin-bottom:.15rem}</style>
@endpush

@section('content')
    @php
        $esIngreso = $movimiento->tipo?->naturaleza === 'ingreso';
        $responsable = trim(($movimiento->usuario?->nombre ?? '') . ' ' . ($movimiento->usuario?->apellido ?? ''));
    @endphp

    <div class="card x-card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="card-title mb-0">
                <span class="badge {{ $esIngreso ? 'badge-success' : 'badge-warning' }} mr-2">
                    {{ $esIngreso ? 'Ingreso' : 'Salida' }}
                </span>
                {{ $movimiento->tipo?->nombre ?? 'Movimiento' }}
            </h3>
            <span class="text-muted small">ID #{{ $movimiento->almacen_movimientoid }}</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="detail-label">Fecha</div>
                    <div class="h5 mb-0">{{ optional($movimiento->fecha)->format('d/m/Y') }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="detail-label">Cantidad</div>
                    <div class="h5 mb-0">
                        {{ number_format((float) $movimiento->cantidad, 3) }}
                        {{ $movimiento->insumo?->unidadMedida?->abreviatura }}
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="detail-label">Almacén</div>
                    <div>{{ $movimiento->almacen?->nombre ?? '-' }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="detail-label">Insumo / producto</div>
                    <div>{{ $movimiento->insumo?->nombre ?? '-' }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="detail-label">Responsable</div>
                    <div>{{ $responsable ?: '-' }}</div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="detail-label">Referencia</div>
                    <div>{{ $movimiento->referencia ?: '—' }}</div>
                </div>
                <div class="col-12 mb-3">
                    <div class="detail-label">Destino / motivo</div>
                    <div>{{ $movimiento->destino_motivo ?: '—' }}</div>
                </div>
                <div class="col-12 mb-3">
                    <div class="detail-label">Observaciones</div>
                    <div class="border rounded p-3 bg-light">{{ $movimiento->observaciones ?: '—' }}</div>
                </div>
                <div class="col-md-6">
                    <div class="detail-label">Registrado en sistema</div>
                    <div class="text-muted small">{{ optional($movimiento->created_at)->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('almacen-movimientos.index', array_filter(['naturaleza' => $filtroNaturaleza ?: null])) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver al listado
            </a>
        </div>
    </div>
@endsection
