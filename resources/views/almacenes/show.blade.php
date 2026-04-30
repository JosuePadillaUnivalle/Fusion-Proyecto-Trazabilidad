@extends('layouts.app')

@section('title', 'Detalle de Almacén | AgroNexus')
@section('page_title', 'Detalle de Almacén')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('almacenes.index') }}" style="color: #2c5530;">Almacenes</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4 border-left-info">
                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-warehouse mr-2"></i>Información del
                        Almacén</h6>
                    @if($almacen->activo)
                        <span class="badge badge-success px-3 py-2">Activo</span>
                    @else
                        <span class="badge badge-secondary px-3 py-2">Inactivo</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <small class="text-uppercase text-muted font-weight-bold">Nombre</small>
                            <h4 class="font-weight-bold text-dark">{{ $almacen->nombre }}</h4>
                        </div>
                        <div class="col-md-6 mb-4">
                            <small class="text-uppercase text-muted font-weight-bold">Tipo</small>
                            <h5 class="text-dark"><span
                                    class="badge badge-info">{{ $almacen->tipoAlmacen->nombre ?? 'General' }}</span></h5>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <small class="text-uppercase text-muted font-weight-bold">Ubicación</small>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-map-marker-alt text-danger mr-2"></i>
                                <span class="h5 mb-0">{{ $almacen->ubicacion ?? 'No especificada' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <small class="text-uppercase text-muted font-weight-bold">Capacidad Total</small>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-ruler-combined text-primary mr-2"></i>
                                <span class="h4 font-weight-bold mb-0 text-primary">
                                    {{ number_format($almacen->capacidad, 2) }}
                                    <span class="h6 text-muted">{{ $almacen->unidadMedida->abreviatura ?? '' }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <small class="text-uppercase text-muted font-weight-bold">Descripción</small>
                            <p class="text-dark bg-light p-3 rounded mt-1">{{ $almacen->descripcion ?? 'Sin descripción.' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h6 class="m-0 font-weight-bold text-dark">Acciones</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('almacenes.edit', $almacen) }}" class="btn btn-warning btn-block mb-3 shadow-sm">
                        <i class="fas fa-edit mr-2"></i>Editar Datos
                    </a>

                    <hr>

                    <form action="{{ route('almacenes.destroy', $almacen) }}" method="POST"
                        onsubmit="return confirm('¿Está seguro de eliminar este almacén? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-block">
                            <i class="fas fa-trash-alt mr-2"></i>Eliminar Almacén
                        </button>
                    </form>

                    <a href="{{ route('almacenes.index') }}" class="btn btn-link btn-block mt-3 text-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
                    </a>
                </div>
            </div>

            <div class="card shadow-sm border-left-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Stock Actual (Estimado)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                -- <!-- Aquí podría ir lógica de stock real si existiera la relación directa -->
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .border-left-info {
            border-left: .25rem solid #36b9cc !important;
        }

        .border-left-success {
            border-left: .25rem solid #1cc88a !important;
        }

        .text-primary {
            color: #4e73df !important;
        }
    </style>
@endsection