@extends('layouts.app')
@push('styles')
<style>
.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
.x-table thead th{background:#f2f7f3;border-bottom:0}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Documentos de entrega</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
@if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        @can('documentos.create')
        <div class="card x-card">
            <div class="card-header"><h3 class="card-title">Cargar documento</h3></div>
            <div class="card-body">
                @role('transportista')
                <p class="text-muted small mb-3">
                    Solo puede adjuntar comprobantes (POD) para envíos o pedidos que figuren en sus asignaciones. Use el mismo ID de envío que ve en «Mis envíos».
                </p>
                @endrole
                <form method="POST" action="{{ route('logistica.documentos.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Título</label>
                            <input name="titulo" class="form-control" required>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Tipo</label>
                            <select name="tipo_documento" class="form-control" required>
                                <option value="pod">POD / comprobante entrega</option>
                                <option value="nota_entrega">Nota entrega</option>
                                <option value="guia_transporte">Guía transporte</option>
                                <option value="evidencia">Evidencia</option>
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>ID de envío</label>
                            <input name="externo_envio_id" class="form-control">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>ID pedido</label>
                            <input type="number" name="pedidoid" class="form-control">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Archivo</label>
                            <input type="file" name="archivo" class="form-control" required>
                        </div>
                    </div>
                    <button class="btn btn-primary">Guardar documento</button>
                </form>
            </div>
        </div>
        @endcan

        <div class="card card-outline card-success card-modulo-main elevation-1">
            <x-modulo-index-header
                titulo="Documentos cargados"
                icono="fa-folder-open"
                :registros="$documentos->total()"
            />
            <div class="card-body table-responsive p-0">
                <table class="table table-hover x-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Envío/Pedido</th>
                            <th>Cargado por</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documentos as $documento)
                            <tr>
                                <td>{{ optional($documento->created_at)->format('d/m/Y H:i') }}</td>
                                <td>{{ $documento->titulo }}</td>
                                <td><span class="badge badge-pill badge-info">{{ $documento->tipo_documento }}</span></td>
                                <td>{{ $documento->externo_envio_id ?? ('Pedido #'.$documento->pedidoid) }}</td>
                                <td>{{ $documento->usuario?->nombreusuario ?? 'N/D' }}</td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('logistica.documentos.download', $documento) }}">Descargar</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4"><i class="far fa-folder-open mr-1"></i>No hay documentos cargados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $documentos->links() }}</div>
        </div>
    </div>
</section>
@endsection

