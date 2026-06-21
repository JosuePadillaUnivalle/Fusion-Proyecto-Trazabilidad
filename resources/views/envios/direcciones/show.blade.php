@extends('layouts.app')

@section('title', 'Dirección | AgroFusion')
@section('page_title', 'Detalle de dirección')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.direcciones') }}">Direcciones</a></li>
    <li class="breadcrumb-item active">{{ $direccion->nombre }}</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
@endpush

@section('content')
<div class="modulo-env">
    @include('envios.partials.alertas')

    <div class="mb-3">
        <a href="{{ route('envios.direcciones') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>
        @can('direcciones.update')
        <a href="{{ route('envios.direcciones.edit', $direccion) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit mr-1"></i> Editar
        </a>
        @endcan
        @can('direcciones.delete')
        <form method="POST" action="{{ route('envios.direcciones.destroy', $direccion) }}" class="d-inline"
              onsubmit="return confirm('¿Eliminar {{ $direccion->nombre }}?');">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash mr-1"></i> Eliminar</button>
        </form>
        @endcan
    </div>

    <div class="card card-modulo-main">
        <div class="card-header bg-success text-white">
            <h3 class="card-title mb-0"><i class="fas fa-map-marker-alt mr-2"></i>{{ $direccion->nombre }}</h3>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Tipo</dt>
                <dd class="col-sm-9">{{ $direccion->etiquetaTipo() }}</dd>
                <dt class="col-sm-3">Dirección</dt>
                <dd class="col-sm-9">{{ $direccion->direccion_completa }}</dd>
                <dt class="col-sm-3">Ciudad</dt>
                <dd class="col-sm-9">{{ $direccion->ciudad }}@if($direccion->departamento), {{ $direccion->departamento }}@endif</dd>
                <dt class="col-sm-3">País</dt>
                <dd class="col-sm-9">{{ $direccion->pais ?? 'Bolivia' }}</dd>
                <dt class="col-sm-3">Coordenadas</dt>
                <dd class="col-sm-9">
                    @if($direccion->latitud && $direccion->longitud)
                        {{ $direccion->latitud }}, {{ $direccion->longitud }}
                    @else
                        —
                    @endif
                </dd>
                <dt class="col-sm-3">Referencia</dt>
                <dd class="col-sm-9">{{ $direccion->referencia ?? '—' }}</dd>
                <dt class="col-sm-3">Estado</dt>
                <dd class="col-sm-9">
                    @if($direccion->activo)
                        <span class="badge badge-success">Activa</span>
                    @else
                        <span class="badge badge-secondary">Inactiva</span>
                    @endif
                </dd>
            </dl>
        </div>
    </div>
</div>
@endsection
