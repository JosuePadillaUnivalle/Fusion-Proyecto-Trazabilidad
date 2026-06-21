@extends('layouts.app')

@section('title', 'Cultivos | AgroFusion')
@section('page_title', 'Cultivos')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Cultivos</li>
@endsection

@push('styles')
@include('cultivos.partials.estilos')
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="cv-hero d-flex flex-wrap justify-content-between align-items-center" style="gap:1rem;">
            <div>
                <h1><i class="fas fa-seedling mr-2"></i>Cultivos</h1>
                <p>Catálogo maestro de cultivos y variedades para lotes y producción agrícola.</p>
            </div>
            <span class="cv-stat"><i class="fas fa-list mr-1"></i>{{ $cultivos->total() }} registro(s)</span>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="mb-3 d-flex flex-wrap justify-content-between align-items-center" style="gap:.5rem;">
            <a href="{{ route('cultivos.create') }}" class="btn btn-success cv-btn">
                <i class="fas fa-plus mr-1"></i>Nuevo cultivo
            </a>
        </div>

        <div class="cv-card">
            <div class="cv-filtros">
                <form method="GET" action="{{ route('cultivos.index') }}" class="row align-items-end">
                    <div class="col-md-8 mb-2 mb-md-0">
                        <label class="small text-muted mb-1"><i class="fas fa-search mr-1"></i>Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" style="border-radius:8px;"
                               value="{{ request('buscar') }}" placeholder="Nombre o detalle del cultivo…">
                    </div>
                    <div class="col-md-4 d-flex" style="gap:.4rem;">
                        <button type="submit" class="btn btn-success btn-sm cv-btn flex-grow-1">Filtrar</button>
                        <a href="{{ route('cultivos.index') }}" class="btn btn-outline-secondary btn-sm cv-btn">Limpiar</a>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table cv-table mb-0">
                    <thead>
                        <tr>
                            <th style="width:55px">#</th>
                            <th>Nombre</th>
                            <th>Detalle</th>
                            <th class="text-center" style="width:130px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cultivos as $cultivo)
                        <tr>
                            <td class="text-muted font-weight-bold">#{{ $cultivo->cultivoid }}</td>
                            <td><strong>{{ $cultivo->nombre }}</strong></td>
                            <td class="cv-detalle">{{ $cultivo->detalleVisible() ?: '—' }}</td>
                            <td class="text-center cv-actions">
                                <a href="{{ route('cultivos.show', $cultivo) }}" class="btn btn-sm btn-outline-info" title="Ver"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('cultivos.edit', $cultivo) }}" class="btn btn-sm btn-outline-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('cultivos.destroy', $cultivo) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                            data-confirm-modal data-confirm-tone="danger"
                                            data-confirm-title="Eliminar cultivo"
                                            data-confirm-message="¿Eliminar «{{ $cultivo->nombre }}»?">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">
                                <i class="fas fa-seedling fa-2x mb-2 d-block opacity-50"></i>
                                No hay cultivos que coincidan con la búsqueda.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($cultivos->hasPages())
            <div class="card-footer bg-white">{{ $cultivos->links() }}</div>
            @endif
        </div>
    </div>
</section>

@include('partials.modal-confirmar-accion')
@endsection
