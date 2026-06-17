@extends('layouts.app')

@section('title', 'Detalle documento | AgroFusion')
@section('page_title', 'Detalle del documento')

@push('styles')
@include('partials.logistica-modulo-styles')
<style>
.doc-det-card{border-radius:14px;overflow:hidden}
.doc-det-card .card-body{padding:1.5rem 1.75rem}
.doc-det-card .card-footer{padding:1.15rem 1.75rem}
</style>
@endpush

@section('content')
<section class="content">
    <div class="container-fluid px-3 px-lg-4">
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif
        <div class="card card-outline card-success elevation-1 doc-det-card mb-3">
            <div class="card-header bg-white py-3 px-4">
                <h5 class="mb-0 font-weight-bold text-success"><i class="fas fa-file-alt mr-2"></i>{{ $documento->titulo }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <small class="text-muted text-uppercase d-block mb-1">Tipo</small>
                        <span class="badge badge-info px-3 py-2">{{ $documento->tipo_documento }}</span>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted text-uppercase d-block mb-1">Envío / pedido</small>
                        <strong>{{ $documento->externo_envio_id ?? ('Pedido #'.$documento->pedidoid) }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted text-uppercase d-block mb-1">Cargado por</small>
                        <strong>{{ \App\Support\DocumentoEntregaCatalogo::etiquetaUsuario($documento->usuario) }}</strong>
                    </div>
                    <div class="col-md-4 mb-3">
                        <small class="text-muted text-uppercase d-block mb-1">Fecha</small>
                        <strong>{{ optional($documento->created_at)->format('d/m/Y H:i') }}</strong>
                    </div>
                    <div class="col-md-8 mb-3">
                        <small class="text-muted text-uppercase d-block mb-1">Archivo</small>
                        <strong>{{ $documento->metadata['original_name'] ?? basename($documento->archivo_path) }}</strong>
                    </div>
                </div>
                <a href="{{ route('logistica.documentos.download', $documento) }}" class="btn btn-primary px-4 py-2">
                    <i class="fas fa-download mr-1"></i>Descargar archivo
                </a>
            </div>
            <div class="card-footer bg-white crud-actions">
                <a href="{{ route('logistica.documentos.index') }}" class="btn btn-outline-secondary px-4 py-2">
                    <i class="fas fa-arrow-left mr-1"></i>Volver
                </a>
                @can('documentos.update')
                <a href="{{ route('logistica.documentos.edit', $documento) }}" class="btn btn-warning px-4 py-2">
                    <i class="fas fa-edit mr-1"></i>Editar
                </a>
                @endcan
                @can('documentos.delete')
                <form action="{{ route('logistica.documentos.destroy', $documento) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar este documento?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-outline-danger px-4 py-2"><i class="fas fa-trash mr-1"></i>Eliminar</button>
                </form>
                @endcan
            </div>
        </div>
    </div>
</section>
@endsection
