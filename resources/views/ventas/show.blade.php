@extends('layouts.app')

@section('title', 'Detalle de Venta | Fusion-Proyectos')
@section('page_title', 'Detalle de Venta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}">Ventas</a></li>
    <li class="breadcrumb-item active">#{{ $venta->ventaid }}</li>
@endsection

@push('styles')
@include('partials.modulo-ventas-styles')
@endpush

@section('content')
@php $total = ($venta->cantidad ?? 0) * ($venta->preciounitario ?? 0); @endphp
<div class="modulo-ven">

    <div class="ven-page-header show">
        <div class="ven-page-header-inner">
            <div class="ven-page-header-icon"><i class="fas fa-receipt"></i></div>
            <div class="ven-page-header-text">
                <h2>Venta #{{ $venta->ventaid }}</h2>
                <p>
                    <i class="fas fa-user mr-1"></i>{{ $venta->cliente ?? 'Sin cliente' }}
                    · <i class="far fa-calendar-alt mr-1"></i>{{ $venta->fechaventa ? \Carbon\Carbon::parse($venta->fechaventa)->format('d/m/Y') : '—' }}
                </p>
            </div>
        </div>
        <div class="ven-total-badge">
            <div class="label">Total</div>
            <div class="amount">Bs. {{ number_format($total, 2) }}</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="detail-card">
                <h5><i class="fas fa-info-circle mr-1"></i> Información de la venta</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <span class="info-label">Cliente</span>
                        <strong>{{ $venta->cliente ?? '—' }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <span class="info-label">Fecha</span>
                        <strong>{{ $venta->fechaventa ? \Carbon\Carbon::parse($venta->fechaventa)->format('d/m/Y') : '—' }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <span class="info-label">Cantidad</span>
                        <strong>{{ number_format($venta->cantidad ?? 0, 2) }} {{ $venta->unidadMedida->abreviatura ?? 'kg' }}</strong>
                    </div>
                    <div class="col-md-6 mb-3">
                        <span class="info-label">Precio unitario</span>
                        <strong>Bs. {{ number_format($venta->preciounitario ?? 0, 2) }}</strong>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <h5><i class="fas fa-calculator mr-1"></i> Resumen financiero</h5>
                <div class="resumen-linea"><span>Cantidad</span><span>{{ number_format($venta->cantidad ?? 0, 2) }} {{ $venta->unidadMedida->abreviatura ?? 'kg' }}</span></div>
                <div class="resumen-linea"><span>Precio unitario</span><span>Bs. {{ number_format($venta->preciounitario ?? 0, 2) }}</span></div>
                <div class="resumen-linea"><span class="text-success">Total</span><span class="text-success">Bs. {{ number_format($total, 2) }}</span></div>
            </div>

            @if($venta->observaciones)
            <div class="detail-card">
                <h5><i class="fas fa-comment mr-1"></i> Observaciones</h5>
                <p class="mb-0">{{ $venta->observaciones }}</p>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="detail-card">
                <h5><i class="fas fa-boxes mr-1"></i> Producción asociada</h5>
                @if($venta->produccion)
                    <div class="mb-2"><span class="info-label">Lote</span><strong>{{ $venta->produccion->lote->nombre ?? '—' }}</strong></div>
                    @if($venta->produccion->lote?->cultivo)
                    <div class="mb-2"><span class="info-label">Cultivo</span><span class="badge badge-success">{{ $venta->produccion->lote->cultivo->nombre }}</span></div>
                    @endif
                    <div class="mb-2"><span class="info-label">Cosecha</span><strong>{{ $venta->produccion->fechacosecha ? \Carbon\Carbon::parse($venta->produccion->fechacosecha)->format('d/m/Y') : '—' }}</strong></div>
                    @if($venta->produccion->almacenamientos->isNotEmpty())
                    <div class="mb-3"><span class="info-label">Almacén</span><strong>{{ $venta->produccion->almacenamientos->first()->almacen->nombre ?? '—' }}</strong></div>
                    @endif
                    <a href="{{ route('producciones.show', $venta->produccion) }}" class="btn btn-outline-success btn-sm btn-block">
                        <i class="fas fa-eye mr-1"></i> Ver producción
                    </a>
                @else
                    <p class="text-muted mb-0">Sin producción asociada.</p>
                @endif
            </div>

            <div class="detail-card">
                <h5><i class="fas fa-cogs mr-1"></i> Acciones</h5>
                @can('ventas.update')
                <a href="{{ route('ventas.edit', $venta) }}" class="btn btn-warning btn-block mb-2"><i class="fas fa-edit mr-1"></i> Editar</a>
                @endcan
                <a href="{{ route('ventas.index') }}" class="btn btn-secondary btn-block mb-2"><i class="fas fa-arrow-left mr-1"></i> Volver al listado</a>
                @can('ventas.delete')
                <form action="{{ route('ventas.destroy', $venta) }}" method="POST" onsubmit="return confirm('¿Eliminar venta? El stock volverá al almacén.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-block"><i class="fas fa-trash mr-1"></i> Eliminar</button>
                </form>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection
