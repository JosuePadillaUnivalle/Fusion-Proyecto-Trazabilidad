@extends('layouts.app')

@section('title', 'Movimiento por cosecha | AgroFusion')
@section('page_title', 'Ingreso por cosecha')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.movimientos.index', ['naturaleza' => $filtroNaturaleza]) }}">Movimientos</a></li>
    <li class="breadcrumb-item active">Cosecha</li>
@endsection

@section('content')
<div class="modulo-inv">
    <div class="card card-outline card-success">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-seedling text-success mr-1"></i> Ingreso por cosecha al almacén
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="text-muted small mb-0">Almacén destino</label>
                    <p class="h5 text-success mb-0">{{ $registro->almacen?->nombre ?? '—' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small mb-0">Fecha de ingreso</label>
                    <p class="mb-0">{{ $registro->fechaentrada ? \Carbon\Carbon::parse($registro->fechaentrada)->format('d/m/Y H:i') : '—' }}</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small mb-0">Lote / cultivo</label>
                    <p class="mb-0">
                        <strong>{{ $registro->produccion?->lote?->cultivo?->nombre ?? 'Cultivo' }}</strong>
                        — {{ $registro->produccion?->lote?->nombre ?? ('Producción #'.$registro->produccionid) }}
                    </p>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="text-muted small mb-0">Cantidad ingresada</label>
                    <p class="mb-0 h5">
                        {{ number_format((float) $registro->cantidad, 2) }}
                        {{ $registro->unidadMedida?->abreviatura ?? 'kg' }}
                    </p>
                </div>
                @if($resumenCapacidad)
                <div class="col-12 mb-3">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-chart-pie mr-1"></i>
                        Ocupación actual del almacén:
                        <strong>{{ number_format($resumenCapacidad['ocupado_kg'], 0) }} kg</strong>
                        de <strong>{{ number_format($resumenCapacidad['capacidad_kg'], 0) }} kg</strong>
                        ({{ $resumenCapacidad['porcentaje'] }}% utilizado).
                        Disponible: <strong>{{ number_format($resumenCapacidad['disponible_kg'], 0) }} kg</strong>.
                    </div>
                </div>
                @endif
                @if($registro->observaciones)
                <div class="col-12">
                    <label class="text-muted small mb-0">Observaciones</label>
                    <p class="mb-0">{{ $registro->observaciones }}</p>
                </div>
                @endif
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.movimientos.index', ['naturaleza' => $filtroNaturaleza]) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Volver a movimientos
            </a>
            @if($registro->produccion)
            <a href="{{ route('producciones.show', $registro->produccion) }}" class="btn btn-outline-success ml-1">
                <i class="fas fa-seedling mr-1"></i> Ver cosecha
            </a>
            @endif
        </div>
    </div>
</div>
@endsection
