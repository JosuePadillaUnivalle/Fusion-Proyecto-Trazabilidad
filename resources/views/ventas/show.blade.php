@extends('layouts.app')

@section('title', 'Detalle de Venta | AgroNexus')
@section('page_title', 'Detalle de Venta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('ventas.index') }}" style="color: #2c5530;">Ventas</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@push('styles')
<style>
    :root {
        --primary-color: #2c5530;
        --secondary-color: #4a7c59;
    }

    .venta-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
    }

    .venta-header h2 {
        margin: 0;
        font-weight: 700;
    }

    .total-badge {
        background: rgba(255,255,255,0.2);
        padding: 15px 25px;
        border-radius: 12px;
        text-align: center;
    }

    .total-badge .amount {
        font-size: 2rem;
        font-weight: 700;
    }

    .total-badge .label {
        font-size: 0.85rem;
        opacity: 0.9;
    }

    .info-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        padding: 20px;
        height: 100%;
        margin-bottom: 20px;
    }

    .info-card h5 {
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f1f3f4;
    }

    .info-item {
        margin-bottom: 15px;
    }

    .info-item label {
        display: block;
        font-size: 0.8rem;
        color: #6c757d;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .info-item span {
        font-size: 1rem;
        font-weight: 600;
        color: #1a252f;
    }

    .resumen-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f1f3f4;
    }

    .resumen-item:last-child {
        border-bottom: none;
        font-weight: 700;
        font-size: 1.1rem;
    }

    .btn-action {
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 500;
    }
</style>
@endpush

@section('content')
@php
    $total = ($venta->cantidad ?? 0) * ($venta->preciounitario ?? 0);
@endphp

<!-- Header -->
<div class="venta-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h2><i class="fas fa-receipt mr-2"></i>Detalle de Venta</h2>
            <p class="mb-0 mt-2" style="opacity: 0.9;">
                <i class="fas fa-user mr-1"></i> {{ $venta->cliente ?? 'Cliente no especificado' }}
                <span class="mx-2">|</span>
                <i class="fas fa-calendar mr-1"></i> {{ $venta->fechaventa ? \Carbon\Carbon::parse($venta->fechaventa)->format('d/m/Y') : '-' }}
            </p>
        </div>
        <div class="total-badge mt-3 mt-md-0">
            <div class="label">Total Venta</div>
            <div class="amount">Bs. {{ number_format($total, 2) }}</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Información Principal -->
    <div class="col-md-8">
        <div class="info-card">
            <h5><i class="fas fa-info-circle mr-2"></i>Información de la Venta</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="info-item">
                        <label><i class="fas fa-user mr-1"></i> Cliente</label>
                        <span>{{ $venta->cliente ?? 'No especificado' }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <label><i class="fas fa-calendar mr-1"></i> Fecha de Venta</label>
                        <span>{{ $venta->fechaventa ? \Carbon\Carbon::parse($venta->fechaventa)->format('d/m/Y') : '-' }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <label><i class="fas fa-weight-hanging mr-1"></i> Cantidad</label>
                        <span>{{ number_format($venta->cantidad ?? 0, 2) }} {{ $venta->unidadMedida->abreviatura ?? 'kg' }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <label><i class="fas fa-tag mr-1"></i> Precio Unitario</label>
                        <span>Bs. {{ number_format($venta->preciounitario ?? 0, 2) }} / {{ $venta->unidadMedida->abreviatura ?? 'kg' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen Financiero -->
        <div class="info-card">
            <h5><i class="fas fa-calculator mr-2"></i>Resumen Financiero</h5>
            
            <div class="resumen-item">
                <span>Cantidad:</span>
                <span>{{ number_format($venta->cantidad ?? 0, 2) }} {{ $venta->unidadMedida->abreviatura ?? 'kg' }}</span>
            </div>
            <div class="resumen-item">
                <span>Precio Unitario:</span>
                <span>Bs. {{ number_format($venta->preciounitario ?? 0, 2) }}</span>
            </div>
            <div class="resumen-item">
                <span class="text-success">Total:</span>
                <span class="text-success">Bs. {{ number_format($total, 2) }}</span>
            </div>
        </div>

        <!-- Observaciones -->
        @if($venta->observaciones)
            <div class="info-card">
                <h5><i class="fas fa-comment mr-2"></i>Observaciones</h5>
                <p class="mb-0">{{ $venta->observaciones }}</p>
            </div>
        @endif
    </div>

    <!-- Panel Lateral -->
    <div class="col-md-4">
        <!-- Producción Asociada -->
        <div class="info-card">
            <h5><i class="fas fa-boxes mr-2"></i>Producción Asociada</h5>
            
            @if($venta->produccion)
                <div class="info-item">
                    <label>Lote</label>
                    <span>{{ $venta->produccion->lote->nombre ?? '-' }}</span>
                </div>
                @if($venta->produccion->lote && $venta->produccion->lote->cultivo)
                    <div class="info-item">
                        <label>Cultivo</label>
                        <span><span class="badge badge-success">{{ $venta->produccion->lote->cultivo->nombre }}</span></span>
                    </div>
                @endif
                <div class="info-item">
                    <label>Fecha Cosecha</label>
                    <span>{{ $venta->produccion->fechacosecha ? \Carbon\Carbon::parse($venta->produccion->fechacosecha)->format('d/m/Y') : '-' }}</span>
                </div>
                <div class="info-item">
                    <label>Producción Total</label>
                    <span>{{ number_format($venta->produccion->cantidad ?? 0, 2) }} kg</span>
                </div>
                <a href="{{ route('producciones.show', $venta->produccion) }}" class="btn btn-outline-success btn-sm btn-block mt-3">
                    <i class="fas fa-eye mr-1"></i> Ver Producción
                </a>
            @else
                <p class="text-muted mb-0">Sin producción asociada</p>
            @endif
        </div>

        <!-- Acciones -->
        <div class="info-card">
            <h5><i class="fas fa-cogs mr-2"></i>Acciones</h5>
            
            <a href="{{ route('ventas.edit', $venta) }}" class="btn btn-warning btn-block btn-action mb-2">
                <i class="fas fa-edit mr-1"></i> Editar
            </a>
            
            <a href="{{ route('ventas.index') }}" class="btn btn-secondary btn-block btn-action mb-2">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
            </a>
            
            <form action="{{ route('ventas.destroy', $venta) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta venta?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-block btn-action">
                    <i class="fas fa-trash mr-1"></i> Eliminar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection