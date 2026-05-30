@extends('layouts.app')

@section('title', 'Vehículo '.$vehiculo->placa.' | AgroFusion')
@section('page_title', 'Detalle del vehículo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.vehiculos') }}">Vehículos</a></li>
    <li class="breadcrumb-item active">{{ $vehiculo->placa }}</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
@endpush

@section('content')
<div class="modulo-env">
    @include('envios.partials.alertas')

    <div class="mb-3">
        <a href="{{ route('envios.vehiculos') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>
        @can('vehiculos.update')
        <a href="{{ route('envios.vehiculos.edit', $vehiculo) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit mr-1"></i> Editar
        </a>
        @endcan
        @can('vehiculos.delete')
        <form method="POST" action="{{ route('envios.vehiculos.destroy', $vehiculo) }}" class="d-inline"
              onsubmit="return confirm('¿Eliminar el vehículo {{ $vehiculo->placa }}?');">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash mr-1"></i> Eliminar</button>
        </form>
        @endcan
    </div>

    <div class="card card-modulo-main">
        <div class="card-header bg-success text-white">
            <h3 class="card-title mb-0"><i class="fas fa-truck mr-2"></i>{{ $vehiculo->placa }}</h3>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Marca / modelo</dt>
                <dd class="col-sm-9">{{ trim(($vehiculo->marca ?? '').' '.($vehiculo->modelo ?? '')) ?: '—' }}</dd>
                <dt class="col-sm-3">Año</dt>
                <dd class="col-sm-9">{{ $vehiculo->anio ?? '—' }}</dd>
                <dt class="col-sm-3">Color</dt>
                <dd class="col-sm-9">{{ $vehiculo->color ?? '—' }}</dd>
                <dt class="col-sm-3">Tipo</dt>
                <dd class="col-sm-9">{{ $vehiculo->tipoVehiculo?->nombre ?? '—' }}</dd>
                <dt class="col-sm-3">Estado</dt>
                <dd class="col-sm-9">{{ $vehiculo->estadoVehiculo?->nombre ?? ($vehiculo->activo ? 'Activo' : 'Inactivo') }}</dd>
                <dt class="col-sm-3">Capacidad</dt>
                <dd class="col-sm-9">
                    @if($vehiculo->tipoVehiculo?->capacidad_kg)
                        {{ number_format((float) $vehiculo->tipoVehiculo->capacidad_kg, 0) }} kg
                    @else
                        —
                    @endif
                </dd>
            </dl>
        </div>
    </div>
</div>
@endsection
