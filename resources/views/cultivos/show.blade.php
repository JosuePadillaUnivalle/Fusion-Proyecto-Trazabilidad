@extends('layouts.app')

@section('title', $item->nombre.' | Cultivos')
@section('page_title', 'Detalle de cultivo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cultivos.index') }}">Cultivos</a></li>
    <li class="breadcrumb-item active">{{ $item->nombre }}</li>
@endsection

@push('styles')
@include('cultivos.partials.estilos')
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:.5rem;">
            <div>
                <h1 class="m-0 font-weight-bold" style="font-size:1.35rem;">
                    <i class="fas fa-seedling text-success mr-2"></i>{{ $item->nombre }}
                </h1>
            </div>
            <div class="d-flex flex-wrap" style="gap:.4rem;">
                <a href="{{ route('cultivos.index') }}" class="btn btn-outline-secondary btn-sm cv-btn">
                    <i class="fas fa-arrow-left mr-1"></i>Listado
                </a>
                <a href="{{ route('cultivos.edit', $item) }}" class="btn btn-warning btn-sm cv-btn">
                    <i class="fas fa-edit mr-1"></i>Editar
                </a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8">
                <div class="cv-card">
                    <div class="cv-card-hd" style="background:linear-gradient(135deg,#f0fdf4,#ecfdf5);">
                        <i class="fas fa-leaf mr-2 text-success"></i>Información del cultivo
                    </div>
                    <div class="card-body p-4">
                        <div class="cv-detail-row">
                            <div class="cv-detail-label">ID</div>
                            <div class="cv-detail-value">#{{ $item->cultivoid }}</div>
                        </div>
                        <div class="cv-detail-row">
                            <div class="cv-detail-label">Nombre</div>
                            <div class="cv-detail-value cv-detail-value--lg">{{ $item->nombre }}</div>
                        </div>
                        <div class="cv-detail-row">
                            <div class="cv-detail-label">Detalle</div>
                            <div class="cv-detail-value">
                                @if($item->detalleVisible())
                                    {{ $item->detalleVisible() }}
                                @else
                                    <span class="text-muted font-italic">Sin detalle registrado. Puede editarlo para que aparezca en el selector de lotes.</span>
                                @endif
                            </div>
                        </div>
                        <div class="cv-detail-row">
                            <div class="cv-detail-label">Lotes asociados</div>
                            <div class="cv-detail-value">
                                <span class="badge badge-success">{{ $item->lotes()->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light d-flex flex-wrap" style="gap:.5rem;">
                        <a href="{{ route('cultivos.edit', $item) }}" class="btn btn-warning btn-sm cv-btn">
                            <i class="fas fa-edit mr-1"></i>Editar
                        </a>
                        <form action="{{ route('cultivos.destroy', $item) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-outline-danger btn-sm cv-btn"
                                    data-confirm-modal data-confirm-tone="danger"
                                    data-confirm-title="Eliminar cultivo"
                                    data-confirm-message="¿Eliminar «{{ $item->nombre }}»? No se puede si tiene lotes asociados.">
                                <i class="fas fa-trash mr-1"></i>Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('partials.modal-confirmar-accion')
@endsection
