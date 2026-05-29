@extends('layouts.app')

@section('title', 'Detalle de Almacenamiento | AgroNexus')
@section('page_title', 'Control de Almacenamiento')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('producciones_almacenamiento.index') }}"
            style="color: #2c5530;">Almacenamiento</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4 border-left-primary">
                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-boxes mr-2"></i>Detalle del Registro</h6>
                    <span class="badge badge-primary px-3 py-2">ID: {{ $registro->produccionalmacenamientoid }}</span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="p-3 bg-light rounded border-left-success">
                                <small class="text-uppercase text-muted font-weight-bold">Producción Origen</small>
                                <h5 class="font-weight-bold text-dark mb-0">
                                    Producción N°{{ $registro->produccionid }}
                                    @if($registro->produccion && $registro->produccion->lote)
                                        <small class="text-muted ml-2">({{ $registro->produccion->lote->nombre }} -
                                            {{ $registro->produccion->lote->cultivo->nombre ?? '' }})</small>
                                    @endif
                                </h5>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <small class="text-uppercase text-muted font-weight-bold">Ubicación (Almacén)</small>
                            <h5 class="text-dark"><i
                                    class="fas fa-warehouse mr-2 text-info"></i>{{ $registro->almacen->nombre ?? 'Desconocido' }}
                            </h5>
                        </div>
                        <div class="col-md-6 mb-4">
                            <small class="text-uppercase text-muted font-weight-bold">Cantidad Almacenada</small>
                            <h4 class="text-success font-weight-bold">
                                {{ number_format($registro->cantidad, 2) }} {{ $registro->unidadMedida->abreviatura ?? '' }}
                            </h4>
                        </div>
                    </div>

                    @if($registro->temperatura || $registro->humedad || $registro->temperatura_min || $registro->humedad_min)
                        <hr>
                        <h6 class="font-weight-bold text-secondary mb-3"><i class="fas fa-thermometer-half mr-2"></i>Condiciones
                            Ambientales</h6>
                        <div class="row text-center">
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light border-0">
                                    <div class="card-body py-2">
                                        <small class="text-muted">Temperatura Actual</small>
                                        <h3 class="font-weight-bold text-danger mb-0">{{ $registro->temperatura ?? '-' }}°C</h3>
                                        @if($registro->temperatura_min && $registro->temperatura_max)
                                            <small class="text-muted text-xs">Rango: {{ $registro->temperatura_min }} -
                                                {{ $registro->temperatura_max }}°C</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card bg-light border-0">
                                    <div class="card-body py-2">
                                        <small class="text-muted">Humedad Actual</small>
                                        <h3 class="font-weight-bold text-primary mb-0">{{ $registro->humedad ?? '-' }}%</h3>
                                        @if($registro->humedad_min && $registro->humedad_max)
                                            <small class="text-muted text-xs">Rango: {{ $registro->humedad_min }} -
                                                {{ $registro->humedad_max }}%</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- Sección oculta porque no hay datos de sensores (Proyecto Universitario sin IoT real) -->
                    @endif

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <small class="text-uppercase text-muted font-weight-bold">Fecha Entrada</small>
                            <p class="font-weight-bold">
                                {{ $registro->fechaentrada ? \Carbon\Carbon::parse($registro->fechaentrada)->format('d/m/Y H:i') : '-' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-uppercase text-muted font-weight-bold">Fecha Salida</small>
                            <p class="font-weight-bold">
                                {{ $registro->fechasalida ? \Carbon\Carbon::parse($registro->fechasalida)->format('d/m/Y H:i') : 'En almacén' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold text-dark">Gestionar</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('producciones_almacenamiento.edit', $registro) }}"
                        class="btn btn-warning btn-block mb-3 shadow-sm">
                        <i class="fas fa-edit mr-2"></i>Actualizar Estado
                    </a>

                    <form action="{{ route('producciones_almacenamiento.destroy', $registro) }}" method="POST"
                        onsubmit="return confirm('¿Eliminar este registro?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-block">
                            <i class="fas fa-trash-alt mr-2"></i>Eliminar Registro
                        </button>
                    </form>

                    <a href="{{ route('producciones_almacenamiento.index') }}"
                        class="btn btn-link btn-block mt-3 text-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0">
                    <h6 class="font-weight-bold text-dark mb-0">Observaciones</h6>
                </div>
                <div class="card-body bg-light pt-0 pb-3">
                    <p class="mb-0 text-muted font-italic">
                        "{{ $registro->observaciones ?? 'Sin observaciones registradas.' }}"
                    </p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .border-left-primary {
            border-left: .25rem solid #4e73df !important;
        }

        .border-left-success {
            border-left: .25rem solid #1cc88a !important;
        }
    </style>
@endsection