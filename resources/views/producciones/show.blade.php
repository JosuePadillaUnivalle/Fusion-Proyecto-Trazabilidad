@extends('layouts.app')

@section('title', 'Detalle de producción | Fusion-Proyectos')
@section('page_title', 'Detalle de Producción')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('producciones.index') }}" style="color: #2c5530;">Producción</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@push('styles')
<style>
    :root {
        --primary-color: #2c5530;
        --secondary-color: #4a7c59;
    }

    .produccion-header {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
    }

    .produccion-header h2 {
        margin: 0;
        font-weight: 700;
    }

    .cantidad-badge {
        background: rgba(255,255,255,0.2);
        padding: 15px 25px;
        border-radius: 12px;
        text-align: center;
    }

    .cantidad-badge .amount {
        font-size: 2rem;
        font-weight: 700;
    }

    .cantidad-badge .label {
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

    .btn-action {
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 500;
    }

    .produccion-image {
        max-width: 100%;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .almacen-item {
        background: #f8f9fc;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 10px;
    }
</style>
@endpush

@section('content')
<!-- Header -->
<div class="produccion-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h2><i class="fas fa-tractor mr-2"></i>Cosecha de {{ $produccion->lote->nombre ?? 'Lote' }}</h2>
            <p class="mb-0 mt-2" style="opacity: 0.9;">
                @if($produccion->lote && $produccion->lote->cultivo)
                    <span class="badge badge-light"><i class="fas fa-seedling mr-1"></i> {{ $produccion->lote->cultivo->nombre }}</span>
                @endif
                <span class="mx-2">|</span>
                <i class="fas fa-calendar mr-1"></i> {{ $produccion->fechacosecha ? \Carbon\Carbon::parse($produccion->fechacosecha)->format('d/m/Y') : '-' }}
            </p>
        </div>
        <div class="cantidad-badge mt-3 mt-md-0">
            <div class="label">Cantidad Cosechada</div>
            <div class="amount">{{ number_format($produccion->cantidad ?? 0, 2) }} {{ $produccion->unidadMedida->abreviatura ?? 'kg' }}</div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Información Principal -->
    <div class="col-lg-8">
        <div class="info-card">
            <h5><i class="fas fa-info-circle mr-2"></i>Información de la Producción</h5>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="info-item">
                        <label><i class="fas fa-map-marker-alt mr-1"></i> Lote</label>
                        <span>{{ $produccion->lote->nombre ?? 'No especificado' }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <label><i class="fas fa-seedling mr-1"></i> Cultivo</label>
                        <span>
                            @if($produccion->lote && $produccion->lote->cultivo)
                                <span class="badge badge-success">{{ $produccion->lote->cultivo->nombre }}</span>
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <label><i class="fas fa-weight-hanging mr-1"></i> Cantidad</label>
                        <span class="text-success" style="font-size: 1.2rem;">{{ number_format($produccion->cantidad ?? 0, 2) }} {{ $produccion->unidadMedida->nombre ?? 'kg' }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <label><i class="fas fa-calendar mr-1"></i> Fecha de Cosecha</label>
                        <span>{{ $produccion->fechacosecha ? \Carbon\Carbon::parse($produccion->fechacosecha)->format('d/m/Y') : '-' }}</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <label><i class="fas fa-warehouse mr-1"></i> Destino</label>
                        <span><span class="badge badge-info">{{ $produccion->destino->nombre ?? 'No especificado' }}</span></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <label><i class="fas fa-ruler-combined mr-1"></i> Superficie del Lote</label>
                        <span>{{ $produccion->lote->superficie ?? 0 }} ha</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Almacenamiento -->
        @if($produccion->almacenamientos && $produccion->almacenamientos->count() > 0)
            <div class="info-card">
                <h5><i class="fas fa-warehouse mr-2"></i>Almacenamiento</h5>
                
                @foreach($produccion->almacenamientos as $almacenamiento)
                    <div class="almacen-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><i class="fas fa-box mr-1"></i> {{ $almacenamiento->almacen->nombre ?? 'Almacén' }}</strong>
                                <br>
                                <small class="text-muted">{{ $almacenamiento->almacen->ubicacion ?? '' }}</small>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-success" style="font-size: 1rem;">
                                    {{ number_format($almacenamiento->cantidad ?? 0, 2) }} kg
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Observaciones -->
        @if($produccion->observaciones)
            <div class="info-card">
                <h5><i class="fas fa-comment mr-2"></i>Observaciones</h5>
                <p class="mb-0">{{ $produccion->observaciones }}</p>
            </div>
        @endif

        <!-- Imagen -->
        @if($produccion->imagenurl)
            <div class="info-card">
                <h5><i class="fas fa-image mr-2"></i>Imagen</h5>
                <div class="text-center">
                    <img src="{{ asset($produccion->imagenurl) }}" alt="Producción" class="produccion-image">
                </div>
            </div>
        @endif
    </div>

    <!-- Panel Lateral -->
    <div class="col-lg-4">
        <!-- Lote Asociado -->
        <div class="info-card">
            <h5><i class="fas fa-map-marked-alt mr-2"></i>Lote</h5>
            
            @if($produccion->lote)
                <div class="info-item">
                    <label>Nombre</label>
                    <span>{{ $produccion->lote->nombre }}</span>
                </div>
                <div class="info-item">
                    <label>Propietario</label>
                    <span>{{ $produccion->lote->usuario->nombre ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <label>Estado</label>
                    <span><span class="badge badge-info">{{ $produccion->lote->estadoTipo->nombre ?? '-' }}</span></span>
                </div>
                <a href="{{ route('lotes.show', $produccion->lote) }}" class="btn btn-outline-success btn-sm btn-block mt-2">
                    <i class="fas fa-eye mr-1"></i> Ver lote
                </a>
                <a href="{{ route('lotes.trazabilidad', $produccion->lote) }}" class="btn btn-outline-secondary btn-sm btn-block">
                    <i class="fas fa-history mr-1"></i> Trazabilidad del lote
                </a>
            @else
                <p class="text-muted mb-0">Sin lote asociado</p>
            @endif
        </div>

        <!-- Ventas Relacionadas -->
        @if($produccion->ventas && $produccion->ventas->count() > 0)
            <div class="info-card">
                <h5><i class="fas fa-shopping-cart mr-2"></i>Ventas</h5>
                
                @foreach($produccion->ventas->take(3) as $venta)
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <span>{{ $venta->cliente ?? 'Cliente' }}</span>
                        <span class="text-success font-weight-bold">Bs. {{ number_format(($venta->cantidad ?? 0) * ($venta->preciounitario ?? 0), 2) }}</span>
                    </div>
                @endforeach
                
                @if($produccion->ventas->count() > 3)
                    <small class="text-muted">+ {{ $produccion->ventas->count() - 3 }} ventas más</small>
                @endif
            </div>
        @endif

        <!-- Acciones -->
        <div class="info-card">
            <h5><i class="fas fa-cogs mr-2"></i>Acciones</h5>
            
            <a href="{{ route('producciones.edit', $produccion) }}" class="btn btn-warning btn-block btn-action mb-2">
                <i class="fas fa-edit mr-1"></i> Editar
            </a>
            
            <a href="{{ route('producciones.index') }}" class="btn btn-secondary btn-block btn-action mb-2">
                <i class="fas fa-arrow-left mr-1"></i> Volver al Listado
            </a>
            
            <form action="{{ route('producciones.destroy', $produccion) }}" method="POST" onsubmit="return confirm('¿Está seguro de eliminar esta producción?')">
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