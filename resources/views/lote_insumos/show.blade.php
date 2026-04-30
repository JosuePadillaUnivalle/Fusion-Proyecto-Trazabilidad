@extends('layouts.app')

@section('title', 'Detalle de Aplicación | AgroNexus')
@section('page_title', 'Control de Insumos')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lote-insumos.index') }}" style="color: #2c5530;">Aplicaciones</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4 border-left-success">
                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-spray-can mr-2"></i>Detalle de Aplicación
                    </h6>
                    <span class="badge badge-success px-3 py-2">ID: {{ $loteInsumo->loteinsumoid }}</span>
                </div>
                <div class="card-body">

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="p-3 bg-light rounded border-left-primary">
                                <small class="text-uppercase text-muted font-weight-bold">Insumo Aplicado</small>
                                <h4 class="font-weight-bold text-dark mb-0">
                                    {{ $loteInsumo->insumo->nombre ?? 'Insumo Eliminado' }}
                                </h4>
                                <small
                                    class="text-muted">{{ $loteInsumo->insumo->tipoInsumo->nombre ?? 'Tipo General' }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <small class="text-uppercase text-muted font-weight-bold">Lote Destino</small>
                            <h5 class="text-dark"><i
                                    class="fas fa-map-marker-alt mr-2 text-primary"></i>{{ $loteInsumo->lote->nombre ?? 'Lote Desconocido' }}
                            </h5>
                            <small
                                class="text-muted ml-4">{{ $loteInsumo->lote->cultivo->nombre ?? 'Sin cultivo activo' }}</small>
                        </div>
                        <div class="col-md-6 mb-4">
                            <small class="text-uppercase text-muted font-weight-bold">Cantidad Aplicada</small>
                            <div class="d-flex align-items-center">
                                <h4 class="text-success font-weight-bold mr-2">
                                    {{ number_format($loteInsumo->cantidadusada, 2) }}
                                </h4>
                                <span class="text-muted">{{ $loteInsumo->insumo->unidadMedida->abreviatura ?? '' }}</span>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <small class="text-uppercase text-muted font-weight-bold">Responsable</small>
                            <div class="d-flex align-items-center mt-2">
                                <div class="icon-circle bg-light text-primary mr-3"
                                    style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 font-weight-bold">{{ $loteInsumo->usuario->nombre ?? 'Sistema' }}</h6>
                                    <small class="text-muted">Operador</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <small class="text-uppercase text-muted font-weight-bold">Costo Total</small>
                            <h4 class="text-dark font-weight-bold">Bs. {{ number_format($loteInsumo->costototal, 2) }}</h4>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-uppercase text-muted font-weight-bold">Fecha de Aplicación</small>
                            <p class="font-weight-bold mt-1">
                                <i class="far fa-calendar-alt mr-2 text-info"></i>
                                {{ $loteInsumo->fechauo ? \Carbon\Carbon::parse($loteInsumo->fechauo)->format('d/m/Y H:i') : '-' }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-uppercase text-muted font-weight-bold">Estado</small>
                            <p class="mt-1">
                                <span class="badge badge-info">{{ $loteInsumo->estado->nombre ?? 'Registrado' }}</span>
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
                    <a href="{{ route('lote-insumos.edit', $loteInsumo) }}"
                        class="btn btn-warning btn-block mb-3 shadow-sm">
                        <i class="fas fa-edit mr-2"></i>Editar Registro
                    </a>

                    <form action="{{ route('lote-insumos.destroy', $loteInsumo) }}" method="POST"
                        onsubmit="return confirm('¿Eliminar este registro? El stock será devuelto al inventario.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-block">
                            <i class="fas fa-trash-alt mr-2"></i>Eliminar
                        </button>
                    </form>

                    <a href="{{ route('lote-insumos.index') }}" class="btn btn-link btn-block mt-3 text-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Volver a la lista
                    </a>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom-0">
                    <h6 class="font-weight-bold text-dark mb-0">Observaciones</h6>
                </div>
                <div class="card-body bg-light pt-0 pb-3">
                    <p class="mb-0 text-muted font-italic">
                        "{{ $loteInsumo->observaciones ?? 'Sin observaciones.' }}"
                    </p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .border-left-success {
            border-left: .25rem solid #1cc88a !important;
        }

        .border-left-primary {
            border-left: .25rem solid #4e73df !important;
        }

        .text-success {
            color: #1cc88a !important;
        }
    </style>
@endsection