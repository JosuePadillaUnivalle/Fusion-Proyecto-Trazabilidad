@extends('layouts.app')

@section('title', 'Aplicación de Insumos | AgroNexus')
@section('page_title', 'Aplicación de insumos en lotes')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Aplicación de insumos</li>
@endsection

@push('styles')
@include('partials.modulo-inventario-styles')
<style>
.page-lote-insumos .products-list .product-img {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush

@section('content')
<div class="modulo-inv page-lote-insumos">

    <div class="row mb-2">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Aplicaciones registradas</p>
                </div>
                <div class="icon"><i class="fas fa-clipboard-check"></i></div>
                <span class="small-box-footer">Historial completo</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $stats['lotes'] }}</h3>
                    <p>Lotes tratados</p>
                </div>
                <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                <span class="small-box-footer">Lotes distintos</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-purple">
                <div class="inner">
                    <h3>{{ $stats['insumos'] }}</h3>
                    <p>Insumos utilizados</p>
                </div>
                <div class="icon"><i class="fas fa-flask"></i></div>
                <span class="small-box-footer">Insumos distintos</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3>Bs. {{ number_format($stats['costo_total'], 0) }}</h3>
                    <p>Costo acumulado</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
                <span class="small-box-footer">{{ number_format($stats['cantidad_total'], 2) }} u. aplicadas</span>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-flask text-success mr-1"></i>
                Aplicación de insumos
                <span class="badge badge-light border text-muted badge-registros ml-2">{{ $loteInsumos->total() }} registros</span>
            </h3>
            <div class="card-tools d-flex align-items-center flex-wrap" style="gap: 6px;">
                <div class="btn-group btn-group-sm view-toggle mr-1">
                    <button type="button" class="btn btn-default" id="btnCardView" title="Tarjetas">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button type="button" class="btn btn-default active" id="btnTableView" title="Tabla">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#filtrosAplicacionesPanel" title="Filtros">
                    <i class="fas fa-filter"></i>
                </button>
                @can('inventario.create')
                <a href="{{ route('lote-insumos.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nueva
                </a>
                @endcan
            </div>
        </div>

        <div id="filtrosAplicacionesPanel" class="filtros-panel collapse">
            <div class="row">
                <div class="col-lg-5 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="searchInput" class="form-control"
                            placeholder="Lote, insumo o encargado...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 mb-2">
                    <label class="small text-muted mb-1">Estado</label>
                    <select id="filterEstado" class="form-control form-control-sm">
                        <option value="">Todos los estados</option>
                        @foreach($estadosFiltro as $estadoNombre)
                            <option value="{{ strtolower($estadoNombre) }}">{{ $estadoNombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-3 mb-2">
                    <label class="small text-muted mb-1">Encargado</label>
                    <select id="filterEncargado" class="form-control form-control-sm">
                        <option value="">Todos los encargados</option>
                        @foreach($encargadosFiltro as $encargado)
                            <option value="{{ strtolower($encargado) }}">{{ $encargado }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-1 col-md-12 mb-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnLimpiarFiltros" title="Limpiar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="tableView" class="table-responsive">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th>Lote</th>
                        <th>Insumo</th>
                        <th>Cantidad</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Encargado</th>
                        <th class="text-center" style="width: 110px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loteInsumos as $li)
                        @php
                            $searchText = strtolower(trim(
                                ($li->lote->nombre ?? '') . ' ' .
                                ($li->insumo->nombre ?? '') . ' ' .
                                ($li->usuario->nombre ?? '')
                            ));
                        @endphp
                        <tr class="search-item-row"
                            data-nombre="{{ $searchText }}"
                            data-estado="{{ strtolower($li->estado->nombre ?? '') }}"
                            data-encargado="{{ strtolower($li->usuario->nombre ?? '') }}">
                            <td><strong class="text-success">{{ $li->lote->nombre ?? '—' }}</strong></td>
                            <td>{{ $li->insumo->nombre ?? '—' }}</td>
                            <td>{{ number_format((float) $li->cantidadusada, 2) }}</td>
                            <td>{{ $li->fechauo ? \Carbon\Carbon::parse($li->fechauo)->format('d/m/Y') : '—' }}</td>
                            <td><span class="badge badge-secondary">{{ $li->estado->nombre ?? '—' }}</span></td>
                            <td>{{ $li->usuario->nombre ?? '—' }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm btn-actions">
                                    <a href="{{ route('lote-insumos.show', $li) }}" class="btn btn-default" title="Ver"><i class="fas fa-eye text-info"></i></a>
                                    @can('inventario.update')
                                    <a href="{{ route('lote-insumos.edit', $li) }}" class="btn btn-default" title="Editar"><i class="fas fa-edit text-warning"></i></a>
                                    @endcan
                                    @can('inventario.delete')
                                    <form action="{{ route('lote-insumos.destroy', $li) }}" method="POST" class="d-inline on-submit-confirm">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-default" title="Eliminar"><i class="fas fa-trash text-danger"></i></button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-clipboard-list fa-2x mb-2 text-light d-block"></i>
                                No hay aplicaciones registradas.
                                @can('inventario.create')
                                <a href="{{ route('lote-insumos.create') }}" class="d-block mt-2">Registrar primera aplicación</a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="cardView" style="display: none;">
            @forelse($loteInsumos as $li)
                @php
                    $searchText = strtolower(trim(
                        ($li->lote->nombre ?? '') . ' ' .
                        ($li->insumo->nombre ?? '') . ' ' .
                        ($li->usuario->nombre ?? '')
                    ));
                @endphp
                <div class="search-item border-bottom"
                    data-nombre="{{ $searchText }}"
                    data-estado="{{ strtolower($li->estado->nombre ?? '') }}"
                    data-encargado="{{ strtolower($li->usuario->nombre ?? '') }}">
                    <ul class="products-list product-list-in-card pl-2 pr-2 mb-0">
                        <li class="item">
                            <div class="product-img bg-light rounded">
                                <i class="fas fa-seedling text-success"></i>
                            </div>
                            <div class="product-info">
                                <a href="{{ route('lote-insumos.show', $li) }}" class="product-title">
                                    {{ $li->lote->nombre ?? 'Sin lote' }}
                                    <span class="badge badge-light border float-right">
                                        {{ $li->estado->nombre ?? '—' }}
                                    </span>
                                </a>
                                <span class="product-description">
                                    <i class="fas fa-flask text-muted mr-1"></i>{{ $li->insumo->nombre ?? 'Sin insumo' }}
                                    <span class="mx-2 text-muted">|</span>
                                    <i class="fas fa-balance-scale text-muted mr-1"></i>{{ number_format((float) $li->cantidadusada, 2) }}
                                    <span class="mx-2 text-muted">|</span>
                                    <i class="fas fa-calendar text-muted mr-1"></i>
                                    {{ $li->fechauo ? \Carbon\Carbon::parse($li->fechauo)->format('d/m/Y') : '—' }}
                                    <span class="mx-2 text-muted">|</span>
                                    <i class="fas fa-user text-muted mr-1"></i>{{ $li->usuario->nombre ?? '—' }}
                                    @if($li->costototal)
                                    <span class="mx-2 text-muted">|</span>
                                    <i class="fas fa-coins text-muted mr-1"></i>Bs. {{ number_format((float) $li->costototal, 2) }}
                                    @endif
                                </span>
                            </div>
                            <div class="ml-2 text-nowrap">
                                <a href="{{ route('lote-insumos.show', $li) }}" class="btn btn-xs btn-info" title="Ver"><i class="fas fa-eye"></i></a>
                                @can('inventario.update')
                                <a href="{{ route('lote-insumos.edit', $li) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                @endcan
                                @can('inventario.delete')
                                <form action="{{ route('lote-insumos.destroy', $li) }}" method="POST" class="d-inline on-submit-confirm">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </li>
                    </ul>
                </div>
            @empty
                <div class="text-center text-muted py-5">No hay aplicaciones registradas.</div>
            @endforelse
        </div>

        @if($loteInsumos->hasPages())
        <div class="card-footer bg-white d-flex justify-content-end py-2">
            {{ $loteInsumos->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
    $('#btnCardView').on('click', function () {
        $(this).addClass('active').siblings().removeClass('active');
        $('#cardView').show();
        $('#tableView').hide();
    });
    $('#btnTableView').on('click', function () {
        $(this).addClass('active').siblings().removeClass('active');
        $('#tableView').show();
        $('#cardView').hide();
    });

    function aplicarFiltros() {
        var val = ($('#searchInput').val() || '').toLowerCase();
        var estado = ($('#filterEstado').val() || '').toLowerCase();
        var encargado = ($('#filterEncargado').val() || '').toLowerCase();

        $('.search-item').each(function () {
            var matchNombre = (($(this).data('nombre') || '').indexOf(val) > -1);
            var matchEstado = !estado || ($(this).data('estado') || '') === estado;
            var matchEncargado = !encargado || ($(this).data('encargado') || '') === encargado;
            $(this).toggle(matchNombre && matchEstado && matchEncargado);
        });
        $('.search-item-row').each(function () {
            var matchNombre = (($(this).data('nombre') || '').indexOf(val) > -1);
            var matchEstado = !estado || ($(this).data('estado') || '') === estado;
            var matchEncargado = !encargado || ($(this).data('encargado') || '') === encargado;
            $(this).toggle(matchNombre && matchEstado && matchEncargado);
        });
    }

    $('#searchInput').on('keyup', aplicarFiltros);
    $('#filterEstado, #filterEncargado').on('change', aplicarFiltros);

    $('#btnLimpiarFiltros').on('click', function () {
        $('#searchInput').val('');
        $('#filterEstado, #filterEncargado').val('');
        aplicarFiltros();
    });

    $('.on-submit-confirm').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: '¿Eliminar registro?',
            text: 'No podrás revertir esto',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar'
        }).then(function (result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: '¡Completado!',
        html: @json(session('success')),
        confirmButtonColor: '#2c5530',
        timer: 5000,
        timerProgressBar: true
    });
    @endif
});
</script>
@endpush
