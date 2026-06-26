@extends('layouts.app')

@section('title', $plantilla->nombre.' | Procesos de transformación')
@section('page_title', 'Detalle del proceso')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('plantillas-transformacion.index') }}">Procesos de transformación</a></li>
    <li class="breadcrumb-item active">{{ $plantilla->nombre }}</li>
@endsection

@push('styles')
@include('partials.modulo-produccion-styles')
@include('plantillas_transformacion.partials.paso-detalle-styles')
@endpush

@section('content')
<div class="modulo-prod">
    @if($plantilla->bloqueadaPorMantenimiento())
    <div class="alert alert-warning border mb-3">
        <i class="fas fa-wrench mr-1"></i>
        <strong>Proceso temporalmente no disponible.</strong>
        Máquinas en mantenimiento: <strong>{{ $plantilla->maquinasEnMantenimiento()->pluck('nombre')->join(', ') }}</strong>.
    </div>
    @endif

    <div class="pt-form-hero mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-start">
            <div>
                <h2><i class="fas fa-project-diagram mr-2"></i>{{ $plantilla->nombre }}</h2>
                <p class="mb-0">{{ $plantilla->descripcion ?: 'Sin descripción adicional.' }}</p>
            </div>
            <div class="mt-2 mt-md-0">
                @include('plantillas_transformacion.partials.badge-estado', ['plantilla' => $plantilla])
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('plantillas-transformacion.index') }}" class="btn btn-sm btn-light"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
            <a href="{{ route('plantillas-transformacion.edit', $plantilla) }}" class="btn btn-sm btn-success"><i class="fas fa-edit mr-1"></i> Editar</a>
        </div>
    </div>

    <div class="pt-linea-wrap">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="font-weight-bold text-success mb-0"><i class="fas fa-stream mr-1"></i> Línea de transformación</h6>
                <p class="small text-muted mb-0">{{ $plantilla->pasos->count() }} etapas · último paso: Empaquetado</p>
            </div>
        </div>

        @forelse($plantilla->pasos as $paso)
            @include('plantillas_transformacion.partials.paso-detalle-card', ['paso' => $paso, 'esUltimo' => $loop->last])
        @empty
            <p class="text-muted mb-0">Sin pasos definidos.</p>
        @endforelse
    </div>
</div>
@endsection

@push('styles')
<style>
.pt-form-hero {
    background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 55%, #3b82f6 100%);
    color: #fff; border-radius: 14px; padding: 1.25rem 1.5rem;
}
.pt-form-hero h2 { font-size: 1.15rem; font-weight: 700; margin: 0 0 .35rem; }
.pt-form-hero p { margin: 0; opacity: .92; font-size: .9rem; }
.pt-linea-wrap {
    background: linear-gradient(180deg, #f0f7f1 0%, #fff 100%);
    border: 1px solid #dce9de; border-radius: 14px; padding: 1.15rem 1.25rem;
}
</style>
@endpush
