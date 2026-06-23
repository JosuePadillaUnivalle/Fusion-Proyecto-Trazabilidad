@extends('layouts.app')

@section('title', $variable->nombre.' | Variables estándar')
@section('page_title', $variable->nombre)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('variables-estandar.index') }}">Variables estándar</a></li>
    <li class="breadcrumb-item active">{{ $variable->nombre }}</li>
@endsection

@push('styles')
@include('partials.modulo-produccion-styles')
<style>
.var-det-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 55%, #3b82f6 100%);
    color: #fff; border-radius: 14px; padding: 1.35rem 1.5rem; margin-bottom: 1rem;
}
.var-det-hero h2 { font-size: 1.35rem; font-weight: 800; margin: 0 0 .35rem; }
.var-det-hero .var-codigo {
    display: inline-block; background: rgba(255,255,255,.18); border-radius: 8px;
    padding: .2rem .65rem; font-family: monospace; font-size: .85rem;
}
.var-det-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
.var-det-item {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem 1.1rem;
}
.var-det-item label { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: #64748b; font-weight: 700; margin: 0 0 .35rem; display: block; }
.var-det-item .valor { font-size: 1.05rem; font-weight: 600; color: #1e293b; margin: 0; }
.var-det-desc {
    background: #f8fafc; border-left: 4px solid #2563eb; border-radius: 0 10px 10px 0;
    padding: 1rem 1.15rem; margin-top: 1rem;
}
</style>
@endpush

@section('content')
<div class="modulo-prod">
    <div class="var-det-hero d-flex flex-wrap justify-content-between align-items-start">
        <div>
            <h2><i class="fas fa-sliders-h mr-2"></i>{{ $variable->nombre }}</h2>
            <span class="var-codigo">{{ $variable->codigo }}</span>
        </div>
        <span class="badge badge-light text-success px-3 py-2 mt-2 mt-md-0">Activa en planta</span>
    </div>

    <div class="var-det-grid">
        <div class="var-det-item">
            <label>Unidad de medida</label>
            <p class="valor">{{ $variable->unidad ?: '—' }}</p>
        </div>
        <div class="var-det-item">
            <label>Identificador</label>
            <p class="valor">#{{ $variable->variableestandarid }}</p>
        </div>
    </div>

    <div class="var-det-desc">
        <label class="small text-muted font-weight-bold text-uppercase mb-1">Descripción</label>
        <p class="mb-0">{{ $variable->descripcion ?: 'Sin descripción adicional.' }}</p>
    </div>

    <div class="d-flex flex-wrap mt-3" style="gap:.5rem">
        <a href="{{ route('variables-estandar.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Volver al listado</a>
        <a href="{{ route('variables-estandar.edit', $variable) }}" class="btn btn-primary"><i class="fas fa-edit mr-1"></i> Editar</a>
    </div>
</div>
@endsection
