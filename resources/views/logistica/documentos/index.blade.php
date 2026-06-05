@extends('layouts.app')

@section('title', 'Documentos de entrega | AgroFusion')
@section('page_title', 'Documentos de entrega')

@push('styles')
@include('partials.logistica-modulo-styles')
<style>
.doc-upload-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
.doc-upload-card .card-header{padding:1rem 1.35rem}
.doc-upload-card .card-body{padding:1.35rem}
</style>
@endpush

@section('content')
<section class="content">
    <div class="container-fluid px-3 px-lg-4">
        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        @can('documentos.create')
        <div class="card doc-upload-card mb-4">
            <div class="card-header bg-white">
                <h3 class="card-title mb-0 font-weight-bold"><i class="fas fa-upload text-success mr-2"></i>Cargar documento</h3>
            </div>
            <div class="card-body">
                @role('transportista')
                <p class="text-muted small mb-3">
                    Solo puede adjuntar comprobantes para envíos o pedidos de sus asignaciones.
                </p>
                @endrole
                <form method="POST" action="{{ route('logistica.documentos.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold">Título</label>
                            <input name="titulo" class="form-control" required>
                        </div>
                        <div class="col-md-2 form-group">
                            <label class="small font-weight-bold">Tipo</label>
                            <select name="tipo_documento" class="form-control" required>
                                <option value="pod">POD / comprobante entrega</option>
                                <option value="nota_entrega">Nota entrega</option>
                                <option value="guia_transporte">Guía transporte</option>
                                <option value="guia_entrega">Guía entrega</option>
                                <option value="confirmacion_entrega">Confirmación entrega</option>
                                <option value="evidencia">Evidencia</option>
                            </select>
                        </div>
                        <div class="col-md-2 form-group">
                            <label class="small font-weight-bold">ID de envío</label>
                            <input name="externo_envio_id" class="form-control">
                        </div>
                        <div class="col-md-2 form-group">
                            <label class="small font-weight-bold">ID pedido</label>
                            <input type="number" name="pedidoid" class="form-control">
                        </div>
                        <div class="col-md-3 form-group">
                            <label class="small font-weight-bold">Archivo</label>
                            <input type="file" name="archivo" class="form-control" required>
                        </div>
                    </div>
                    <button class="btn btn-success px-4 py-2"><i class="fas fa-upload mr-1"></i>Guardar documento</button>
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

            @include('partials.modulo-filtros-form', [
                'action' => route('logistica.documentos.index'),
                'campos' => [
                    ['name' => 'q', 'label' => 'Buscar', 'placeholder' => 'Título, envío, usuario...', 'col' => 'col-md-3'],
                    ['name' => 'tipo', 'label' => 'Tipo', 'type' => 'select', 'col' => 'col-md-2',
                        'options' => ($tiposDisponibles ?? collect())->mapWithKeys(fn ($t) => [$t => $t])->all()],
                    ['name' => 'envio', 'label' => 'ID envío', 'placeholder' => 'ENV-...', 'col' => 'col-md-2'],
                    ['name' => 'cargado_por', 'label' => 'Cargado por', 'type' => 'select', 'col' => 'col-md-2',
                        'options' => ($usuariosCarga ?? collect())->mapWithKeys(fn ($u) => [$u->nombreusuario => $u->nombreusuario])->all()],
                    ['name' => 'desde', 'label' => 'Desde', 'type' => 'date', 'col' => 'col-md-1'],
                    ['name' => 'hasta', 'label' => 'Hasta', 'type' => 'date', 'col' => 'col-md-1'],
                ],
            ])

            <div class="card-body table-responsive p-0">
                <table class="table table-hover mb-0 modulo-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Envío/Pedido</th>
                            <th>Cargado por</th>
                            <th style="min-width:220px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documentos as $documento)
                            <tr>
                                <td>{{ optional($documento->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="font-weight-bold">{{ $documento->titulo }}</td>
                                <td><span class="badge badge-pill badge-info px-3 py-2">{{ $documento->tipo_documento }}</span></td>
                                <td>{{ $documento->externo_envio_id ?? ('Pedido #'.$documento->pedidoid) }}</td>
                                <td>{{ $documento->usuario?->nombreusuario ?? 'N/D' }}</td>
                                <td>
                                    <div class="crud-actions">
                                        <a class="btn btn-info" href="{{ route('logistica.documentos.show', $documento) }}" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a class="btn btn-outline-primary" href="{{ route('logistica.documentos.download', $documento) }}" title="Descargar">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @can('documentos.update')
                                        <a class="btn btn-warning" href="{{ route('logistica.documentos.edit', $documento) }}" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('documentos.delete')
                                        <form action="{{ route('logistica.documentos.destroy', $documento) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar este documento?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5"><i class="far fa-folder-open fa-2x mb-2 d-block"></i>No hay documentos con esos filtros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer py-3">{{ $documentos->links() }}</div>
        </div>
    </div>
</section>
@endsection
