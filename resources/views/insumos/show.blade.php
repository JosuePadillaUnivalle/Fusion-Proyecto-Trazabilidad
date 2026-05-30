@extends('layouts.app')

@section('title', $insumo->nombre . ' | Insumo')
@section('page_title', 'Detalle del insumo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('insumos.index') }}">Insumos</a></li>
    <li class="breadcrumb-item active">{{ $insumo->nombre }}</li>
@endsection

@php
    use App\Support\InsumoCatalogo;
    $claseStock = InsumoCatalogo::claseStock((float) $insumo->stock);
    $umbral = $umbral ?? InsumoCatalogo::UMBRAL_ALERTA_STOCK;
    $slugTipo = InsumoCatalogo::slugFromNombreTipo($insumo->tipo->nombre ?? '');
    $iconoTipo = match ($slugTipo) {
        'material_siembra' => 'seedling',
        'fertilizantes' => 'flask',
        'pesticidas' => 'bug',
        'material_riego' => 'tint',
        default => 'box',
    };
@endphp

@push('styles')
<style>
.page-insumo-show .hero-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 14px rgba(0,0,0,.08);
    overflow: hidden;
}
.page-insumo-show .hero-header {
    background: linear-gradient(135deg, #2c5530, #4a7c59);
    color: #fff;
    padding: 1.25rem 1.5rem;
}
.page-insumo-show .tipo-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    background: rgba(255,255,255,.2);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
.page-insumo-show .dato-label {
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #6c757d;
    margin-bottom: .2rem;
}
.page-insumo-show .dato-valor { font-size: 1.05rem; font-weight: 600; color: #212529; }
.page-insumo-show .alerta-banner {
    border-radius: 8px;
    padding: .85rem 1rem;
    font-size: .9rem;
}
.page-insumo-show .alerta-banner.critico { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
.page-insumo-show .alerta-banner.atencion { background: #fff3cd; border: 1px solid #ffeeba; color: #856404; }
.page-insumo-show .alerta-banner.ok { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
</style>
@endpush

@section('content')
<div class="modulo-inv page-insumo-show">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card hero-card card-modulo-main mb-3">
                <div class="hero-header d-flex align-items-center">
                    <span class="tipo-icon mr-3"><i class="fas fa-{{ $iconoTipo }}"></i></span>
                    <div class="flex-grow-1">
                        <h3 class="mb-0">{{ $insumo->nombre }}</h3>
                        <small class="opacity-90">{{ $insumo->tipo->nombre ?? '—' }}</small>
                    </div>
                    <a href="{{ route('insumos.edit', $insumo) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-edit mr-1"></i>Editar
                    </a>
                </div>
                <div class="card-body">
                    <div class="alerta-banner mb-4 {{ $claseStock === 'low' ? 'critico' : ($claseStock === 'medium' ? 'atencion' : 'ok') }}">
                        @if($claseStock === 'low')
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>Alerta de stock:</strong> quedan {{ number_format($insumo->stock, 2) }}
                            {{ $insumo->unidadMedida->abreviatura ?? 'u.' }} (umbral ≤ {{ $umbral }}).
                        @elseif($claseStock === 'medium')
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Atención:</strong> el stock está por encima del umbral crítico pero conviene reponer pronto.
                        @else
                            <i class="fas fa-check-circle mr-1"></i>
                            <strong>Stock normal</strong> — por encima del nivel de atención.
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="dato-label">Tipo de insumo</div>
                            <div class="dato-valor">{{ $insumo->tipo->nombre ?? '—' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="dato-label">Unidad de medida</div>
                            <div class="dato-valor">
                                {{ $insumo->unidadMedida->nombre ?? '—' }}
                                @if($insumo->unidadMedida?->abreviatura)
                                    <span class="text-muted font-weight-normal">({{ $insumo->unidadMedida->abreviatura }})</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="dato-label">Stock actual</div>
                            <div class="dato-valor {{ $claseStock === 'low' ? 'text-danger' : '' }}">
                                {{ number_format($insumo->stock, 2) }}
                                {{ $insumo->unidadMedida->abreviatura ?? '' }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="dato-label">Regla de alerta</div>
                            <div class="dato-valor text-muted font-weight-normal" style="font-size:.95rem;">
                                Aviso automático cuando el stock es ≤ {{ $umbral }} unidades
                            </div>
                        </div>
                        @if($insumo->descripcion)
                        <div class="col-12 mb-2">
                            <div class="dato-label">Descripción</div>
                            <div class="dato-valor font-weight-normal">{{ $insumo->descripcion }}</div>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between flex-wrap">
                    <a href="{{ route('insumos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Volver al listado
                    </a>
                    <form action="{{ route('insumos.destroy', $insumo) }}" method="POST"
                          class="d-inline" onsubmit="return confirm('¿Eliminar este insumo?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash mr-1"></i>Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
