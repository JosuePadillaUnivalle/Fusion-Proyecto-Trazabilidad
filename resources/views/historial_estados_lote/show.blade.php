@extends('layouts.app')

@section('title', 'Detalle de Historial | AgroNexus')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('historial-estados-lote.index') }}" style="color: #2c5530;">Historial</a>
    </li>
    <li class="breadcrumb-item active">Detalle #{{ $registro->historial_estado_id }}</li>
@endsection

@section('content')
    <div class="d-flex justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0 rounded-lg">

                {{-- HEADER PREMIUM --}}
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center py-3"
                    style="background: linear-gradient(135deg, #2c5530, #4a7c59);">
                    <div>
                        <h5 class="mb-0 font-weight-bold">
                            <i class="fas fa-clipboard-check mr-2"></i> Detalle del Registro
                        </h5>
                        <small class="opacity-75">ID: {{ $registro->historial_estado_id }} | Fecha:
                            {{ $registro->fecha_cambio }}</small>
                    </div>
                    <div>
                        <span class="badge badge-light text-success font-weight-bold px-3 py-2" style="font-size: 0.9rem;">
                            {{ $registro->estadoTipo->nombre ?? 'Estado Desconocido' }}
                        </span>
                    </div>
                </div>

                <div class="card-body p-4 bg-light">

                    {{-- INFORMACIÓN PRINCIPAL --}}
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <h6 class="text-uppercase text-muted font-weight-bold mb-3 border-bottom pb-2">Información del
                                Evento</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-secondary small font-weight-bold">Lote Afectado</label>
                                    <div class="h5 text-dark font-weight-bold">
                                        <i class="fas fa-layer-group text-success mr-2"></i>
                                        {{ $registro->lote->nombre ?? 'Lote No Encontrado' }}
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-secondary small font-weight-bold">Registrado Por</label>
                                    <div class="h5 text-dark">
                                        <i class="fas fa-user-circle text-primary mr-2"></i>
                                        {{ $registro->usuario->nombre ?? 'Sistema' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- OBSERVACIONES E IMAGEN --}}
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body">
                                    <h6 class="text-uppercase text-muted font-weight-bold mb-3 border-bottom pb-2">
                                        Observaciones y Detalles</h6>
                                    <p class="text-justify text-dark">
                                        {{ $registro->observaciones ?: 'No se registraron observaciones adicionales para este cambio de estado.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-body text-center">
                                    <h6 class="text-uppercase text-muted font-weight-bold mb-3 border-bottom pb-2">Evidencia
                                        Fotográfica</h6>
                                    @if($registro->imagenurl)
                                        <div class="rounded overflow-hidden shadow-sm mb-2">
                                            <img src="{{ $registro->imagenurl }}" class="img-fluid" alt="Evidencia"
                                                style="max-height: 200px; object-fit: cover; cursor: pointer;"
                                                onclick="window.open(this.src)">
                                        </div>
                                        <small class="text-muted"><i class="fas fa-search-plus"></i> Clic para ampliar</small>
                                    @else
                                        <div class="py-4 text-muted border rounded bg-white">
                                            <i class="fas fa-camera-slash fa-3x mb-2 opacity-50"></i>
                                            <p class="mb-0 small">Sin imagen adjunta</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- FOOTER CON ACCIONES --}}
                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                    <a href="{{ route('historial-estados-lote.index') }}"
                        class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-arrow-left mr-2"></i> Volver al Listado
                    </a>

                    <div class="d-flex gap-2">
                        <a href="{{ route('historial-estados-lote.edit', $registro) }}"
                            class="btn btn-warning text-white rounded-pill px-4 shadow-sm">
                            <i class="fas fa-edit mr-2"></i> Editar
                        </a>

                        <form action="{{ route('historial-estados-lote.destroy', $registro) }}" method="POST"
                            class="d-inline"
                            onsubmit="return confirm('¿Confirma eliminar este registro permanentemente?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger rounded-pill px-4 shadow-sm">
                                <i class="fas fa-trash-alt mr-2"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection