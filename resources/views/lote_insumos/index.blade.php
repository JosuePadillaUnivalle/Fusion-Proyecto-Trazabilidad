@extends('layouts.app')

@section('title', 'Aplicación de Insumos | AgroNexus')
@section('page_title', 'Aplicación de insumos en lotes')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('insumos.index') }}">Inventario</a></li>
    <li class="breadcrumb-item active">Aplicación de insumos</li>
@endsection

@push('styles')
<style>
.page-lote-insumos .small-box {
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.page-lote-insumos .small-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
}
.page-lote-insumos .small-box .icon { font-size: 70px; }
.page-lote-insumos .small-box-green {
    background: linear-gradient(135deg, #2c5530, #4a7c59) !important;
    color: #fff;
}
.page-lote-insumos .small-box-blue {
    background: linear-gradient(135deg, #17a2b8, #20c997) !important;
    color: #fff;
}
.page-lote-insumos .small-box-purple {
    background: linear-gradient(135deg, #6f42c1, #8e64e8) !important;
    color: #fff;
}
.page-lote-insumos .small-box-yellow {
    background: linear-gradient(135deg, #fd7e14, #ffc107) !important;
    color: #1a252f;
}
.page-lote-insumos .card {
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}
.page-lote-insumos .products-list .product-img {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.page-lote-insumos .view-toggle .btn.active {
    background-color: #2c5530;
    border-color: #2c5530;
    color: #fff;
}
</style>
@endpush

@section('content')
<div class="page-lote-insumos">

    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info alert-dismissible shadow-sm mb-0">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5 class="mb-1"><i class="icon fas fa-flask"></i> Aplicación de insumos</h5>
                Registra y consulta el uso de insumos por lote: cantidades, fechas, encargados y estado de cada aplicación.
                <div class="mt-2">
                    <a href="{{ route('lotes.index') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-map-marked-alt mr-1"></i> Lotes
                    </a>
                    <a href="{{ route('insumos.index') }}" class="btn btn-sm btn-info ml-1">
                        <i class="fas fa-boxes mr-1"></i> Insumos
                    </a>
                    @can('inventario.create')
                    <a href="{{ route('lote-insumos.create') }}" class="btn btn-sm btn-success ml-1">
                        <i class="fas fa-plus mr-1"></i> Nueva aplicación
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="row">
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
                <a href="{{ route('lotes.index') }}" class="small-box-footer">
                    Ver lotes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-purple">
                <div class="inner">
                    <h3>{{ $stats['insumos'] }}</h3>
                    <p>Insumos utilizados</p>
                </div>
                <div class="icon"><i class="fas fa-flask"></i></div>
                <a href="{{ route('insumos.index') }}" class="small-box-footer">
                    Ver insumos <i class="fas fa-arrow-circle-right"></i>
                </a>
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

    @if ($stats['total'] > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-secondary mb-0">
                <div class="card-body py-2">
                    <div class="row text-center">
                        <div class="col-sm-3 border-right">
                            <div class="description-block mb-0">
                                <h5 class="description-header">{{ $stats['total'] }}</h5>
                                <span class="description-text text-muted">APLICACIONES</span>
                            </div>
                        </div>
                        <div class="col-sm-3 border-right">
                            <div class="description-block mb-0">
                                <h5 class="description-header">{{ $stats['lotes'] }}</h5>
                                <span class="description-text text-muted">LOTES</span>
                            </div>
                        </div>
                        <div class="col-sm-3 border-right">
                            <div class="description-block mb-0">
                                <h5 class="description-header">{{ $stats['insumos'] }}</h5>
                                <span class="description-text text-muted">INSUMOS</span>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="description-block mb-0">
                                <h5 class="description-header">{{ number_format($stats['cantidad_total'], 2) }}</h5>
                                <span class="description-text text-muted">CANTIDAD TOTAL</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card card-primary card-outline elevation-2" id="filtros-aplicaciones">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Buscar y filtrar</h3>
            <div class="card-tools">
                <div class="btn-group btn-group-sm view-toggle mr-2">
                    <button type="button" class="btn btn-default active" id="btnCardView" title="Vista tarjetas">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button type="button" class="btn btn-default" id="btnTableView" title="Vista tabla">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
                @can('inventario.create')
                <a href="{{ route('lote-insumos.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nueva
                </a>
                @endcan
            </div>
        </div>
        <div class="card-body pb-2">
            <div class="row">
                <div class="col-lg-5 col-md-6 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="searchInput" class="form-control"
                            placeholder="Buscar por lote, insumo o encargado...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 mb-2">
                    <select id="filterEstado" class="form-control form-control-sm">
                        <option value="">Todos los estados</option>
                        @foreach($estadosFiltro as $estadoNombre)
                            <option value="{{ strtolower($estadoNombre) }}">{{ $estadoNombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-3 mb-2">
                    <select id="filterEncargado" class="form-control form-control-sm">
                        <option value="">Todos los encargados</option>
                        @foreach($encargadosFiltro as $encargado)
                            <option value="{{ strtolower($encargado) }}">{{ $encargado }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-1 col-md-12 mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnLimpiarFiltros" title="Limpiar">
                        <i class="fas fa-eraser"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="cardView" class="card card-outline card-success elevation-1">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-clipboard-list mr-1 text-success"></i> Aplicaciones</h3>
            <span class="badge badge-secondary ml-2">{{ $loteInsumos->total() }} registros</span>
        </div>
        <div class="card-body p-0">
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
                                <a href="{{ route('lote-insumos.show', $li) }}" class="btn btn-xs btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('inventario.update')
                                <a href="{{ route('lote-insumos.edit', $li) }}" class="btn btn-xs btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('inventario.delete')
                                <form action="{{ route('lote-insumos.destroy', $li) }}" method="POST" class="d-inline on-submit-confirm">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </li>
                    </ul>
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="fas fa-clipboard-list fa-3x mb-3 text-light d-block"></i>
                    <p class="mb-2">No hay aplicaciones registradas.</p>
                    @can('inventario.create')
                    <a href="{{ route('lote-insumos.create') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus mr-1"></i> Registrar primera aplicación
                    </a>
                    @endcan
                </div>
            @endforelse
        </div>
        @if($loteInsumos->hasPages())
        <div class="card-footer">
            {{ $loteInsumos->links() }}
        </div>
        @endif
    </div>

    <div id="tableView" class="card card-outline card-primary elevation-1" style="display: none;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table mr-1 text-primary"></i> Tabla de aplicaciones</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>Lote</th>
                        <th>Insumo</th>
                        <th>Cantidad</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Encargado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($loteInsumos as $li)
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
                            <td class="text-right text-nowrap">
                                <a href="{{ route('lote-insumos.show', $li) }}" class="btn btn-xs btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('inventario.update')
                                <a href="{{ route('lote-insumos.edit', $li) }}" class="btn btn-xs btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('inventario.delete')
                                <form action="{{ route('lote-insumos.destroy', $li) }}" method="POST" class="d-inline on-submit-confirm">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($loteInsumos->hasPages())
        <div class="card-footer">
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
        title: '¡Hecho!',
        text: @json(session('success')),
        confirmButtonColor: '#2c5530',
        timer: 3000,
        showConfirmButton: false
    });
    @endif
});
</script>
@endpush
