@extends('layouts.app')

@section('title', 'Documentos de entrega | AgroFusion')
@section('page_title', 'Documentos de entrega')

@push('styles')
@include('partials.logistica-modulo-styles')
@include('logistica.partials.ops-reportes-styles')
@endpush

@php
    $tiposDocumento = $tiposDocumento ?? \App\Support\DocumentoEntregaCatalogo::tiposDocumento();
    $chipTipo = function (string $tipo): string {
        return match (true) {
            str_contains($tipo, 'pod') => 'log-ops-chip--pod',
            str_contains($tipo, 'nota') => 'log-ops-chip--nota',
            str_contains($tipo, 'guia') => 'log-ops-chip--guia',
            str_contains($tipo, 'confirm') => 'log-ops-chip--confirm',
            str_contains($tipo, 'evidencia') => 'log-ops-chip--evidencia',
            default => 'log-ops-chip--default',
        };
    };
@endphp

@section('content')
<div class="log-ops-wrap">
    @if ($errors->any())
        <div class="alert alert-danger border-0 shadow-sm" style="border-radius:12px">{{ $errors->first() }}</div>
    @endif

    <div class="log-ops-hero">
        <p class="log-ops-hero__title"><i class="fas fa-file-signature text-success mr-2"></i>Comprobantes de entrega</p>
        <p class="log-ops-hero__text mb-2">
            Aquí se guardan las pruebas de que un envío llegó a destino: guías firmadas, fotos POD, notas de entrega y confirmaciones.
            Cada archivo queda vinculado al código de envío o pedido para auditoría y trazabilidad.
        </p>
        <ul class="small text-muted mb-0 pl-3">
            <li>Use <strong>Buscar</strong> por título o código (ej. ENV-…).</li>
            <li>Filtre por <strong>tipo de comprobante</strong> o por <strong>fechas</strong> de carga.</li>
            <li>Desde la tabla puede ver, descargar o editar cada documento.</li>
        </ul>
    </div>

    <div class="row log-ops-metrics">
        <div class="col-6 col-md-4 mb-2 mb-md-0">
            <div class="log-ops-metric">
                <span class="log-ops-metric__icon log-ops-metric__icon--green"><i class="fas fa-folder-open"></i></span>
                <span>
                    <div class="log-ops-metric__val">{{ $resumenDocumentos['total'] ?? $documentos->total() }}</div>
                    <div class="log-ops-metric__lbl">Documentos (filtro actual)</div>
                </span>
            </div>
        </div>
        <div class="col-6 col-md-4 mb-2 mb-md-0">
            <div class="log-ops-metric">
                <span class="log-ops-metric__icon log-ops-metric__icon--blue"><i class="fas fa-receipt"></i></span>
                <span>
                    <div class="log-ops-metric__val">{{ $resumenDocumentos['pods'] ?? 0 }}</div>
                    <div class="log-ops-metric__lbl">POD / comprobantes</div>
                </span>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="log-ops-metric">
                <span class="log-ops-metric__icon log-ops-metric__icon--amber"><i class="fas fa-calendar-day"></i></span>
                <span>
                    <div class="log-ops-metric__val">{{ $resumenDocumentos['hoy'] ?? 0 }}</div>
                    <div class="log-ops-metric__lbl">Cargados hoy</div>
                </span>
            </div>
        </div>
    </div>

    @can('documentos.create')
    <div class="log-ops-card">
        <div class="log-ops-card__head">
            <h2 class="log-ops-card__title"><i class="fas fa-cloud-upload-alt"></i> Cargar documento</h2>
        </div>
        <div class="log-ops-upload">
            @role('transportista')
            <p class="text-muted small mb-3">
                <i class="fas fa-info-circle mr-1"></i>
                Solo puede adjuntar comprobantes para envíos o pedidos de sus asignaciones.
            </p>
            @endrole
            <form method="POST" action="{{ route('logistica.documentos.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-6 col-lg-3 log-ops-field form-group">
                        <label>Título</label>
                        <input name="titulo" class="form-control" placeholder="Ej. POD entrega planta" required>
                    </div>
                    <div class="col-md-6 col-lg-3 log-ops-field form-group">
                        <label>Tipo</label>
                        <select name="tipo_documento" class="form-control" required>
                            @foreach($tiposDocumento as $valorTipo => $etiquetaTipo)
                                <option value="{{ $valorTipo }}">{{ $etiquetaTipo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-2 log-ops-field form-group">
                        <label>ID envío</label>
                        <input name="externo_envio_id" class="form-control" placeholder="ENV-…">
                    </div>
                    <div class="col-md-6 col-lg-1 log-ops-field form-group">
                        <label>ID pedido</label>
                        <input type="number" name="pedidoid" class="form-control" placeholder="#">
                    </div>
                    <div class="col-md-6 col-lg-3 log-ops-field form-group">
                        <label>Archivo</label>
                        @include('logistica.partials.ops-file-input', [
                            'inputId' => 'docEntregaArchivo',
                            'required' => true,
                        ])
                    </div>
                </div>
                <button type="submit" class="btn btn-success log-ops-btn-primary mt-1">
                    <i class="fas fa-upload mr-1"></i> Guardar documento
                </button>
            </form>
        </div>
    </div>
    @endcan

    <div class="log-ops-card">
        <div class="log-ops-card__head">
            <h2 class="log-ops-card__title"><i class="fas fa-archive"></i> Documentos cargados</h2>
            <span class="log-ops-card__count">{{ $documentos->total() }} registros</span>
        </div>

        <div class="log-ops-filtros">
            <form method="GET" action="{{ route('logistica.documentos.index') }}">
                <div class="form-row">
                    <div class="col-lg-4 col-md-6">
                        <label for="docFiltroBuscar">Buscar</label>
                        <input type="search" id="docFiltroBuscar" name="q" class="form-control form-control-sm"
                               value="{{ request('q') }}" placeholder="Título o código de envío…">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label for="docFiltroTipo">Tipo de comprobante</label>
                        <select id="docFiltroTipo" name="tipo" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($tiposDocumento as $valorTipo => $etiquetaTipo)
                                <option value="{{ $valorTipo }}" @selected(request('tipo') === $valorTipo)>
                                    {{ $etiquetaTipo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label for="docFiltroDesde">Desde</label>
                        <input type="date" id="docFiltroDesde" name="desde" class="form-control form-control-sm"
                               value="{{ request('desde') }}">
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label for="docFiltroHasta">Hasta</label>
                        <input type="date" id="docFiltroHasta" name="hasta" class="form-control form-control-sm"
                               value="{{ request('hasta') }}">
                    </div>
                    <div class="col-auto log-ops-filtros__submit-col">
                        <label aria-hidden="true">&nbsp;</label>
                        <button type="submit" class="btn btn-success btn-sm px-3">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
            @if(request()->except('page'))
                <p class="log-ops-filtros__activos mb-0">
                    Filtros activos.
                    <a href="{{ route('logistica.documentos.index') }}">Limpiar filtros</a>
                </p>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table mb-0 log-ops-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Envío / pedido</th>
                        <th>Subido por</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documentos as $documento)
                        @php $tipo = $documento->tipo_documento; @endphp
                        <tr>
                            <td class="td-muted">{{ optional($documento->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="td-ref">{{ $documento->titulo }}</td>
                            <td>
                                <span class="log-ops-chip {{ $chipTipo($tipo) }}">
                                    {{ $tiposDocumento[$tipo] ?? \App\Support\DocumentoEntregaCatalogo::etiquetaTipo($tipo) }}
                                </span>
                            </td>
                            <td class="td-ref">{{ \App\Support\DocumentoEntregaCatalogo::etiquetaVinculo($documento) }}</td>
                            <td>{{ \App\Support\DocumentoEntregaCatalogo::etiquetaUsuario($documento->usuario) }}</td>
                            <td class="text-right text-nowrap">
                                <div class="log-ops-actions justify-content-end">
                                    <a class="log-ops-btn-icon log-ops-btn-icon--view" href="{{ route('logistica.documentos.show', $documento) }}" title="Ver detalle">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a class="log-ops-btn-icon log-ops-btn-icon--down" href="{{ route('logistica.documentos.download', $documento) }}" title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    @can('documentos.update')
                                    <a class="log-ops-btn-icon log-ops-btn-icon--edit" href="{{ route('logistica.documentos.edit', $documento) }}" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('documentos.delete')
                                    <form action="{{ route('logistica.documentos.destroy', $documento) }}" method="POST"
                                          onsubmit="return confirm('¿Eliminar este documento?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="log-ops-btn-icon log-ops-btn-icon--del" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="log-ops-empty">
                                    <i class="far fa-folder-open"></i>
                                    No hay documentos con esos filtros.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($documentos->hasPages())
        <div class="log-ops-footer">{{ $documentos->links() }}</div>
        @endif
    </div>
</div>
@endsection
