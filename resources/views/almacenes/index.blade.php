@extends('layouts.app')

@section('title', 'Almacenes | AgroNexus')
@section('page_title', 'Gestión de almacenes')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('insumos.index') }}">Inventario</a></li>
    <li class="breadcrumb-item active">Almacenes</li>
@endsection

@php
    $pctActivos = $stats['total'] > 0
        ? round(($stats['activos'] / $stats['total']) * 100)
        : 0;
@endphp

@push('styles')
<style>
.page-almacenes .small-box {
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.page-almacenes .small-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
}
.page-almacenes .small-box .icon { font-size: 70px; }
.page-almacenes .small-box-green {
    background: linear-gradient(135deg, #2c5530, #4a7c59) !important;
    color: #fff;
}
.page-almacenes .small-box-orange {
    background: linear-gradient(135deg, #fd7e14, #ffc107) !important;
    color: #1a252f;
}
.page-almacenes .small-box-blue {
    background: linear-gradient(135deg, #17a2b8, #20c997) !important;
    color: #fff;
}
.page-almacenes .small-box-red {
    background: linear-gradient(135deg, #dc3545, #e74a3b) !important;
    color: #fff;
}
.page-almacenes .card {
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}
.page-almacenes .products-list .product-img {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.page-almacenes .view-toggle .btn.active {
    background-color: #2c5530;
    border-color: #2c5530;
    color: #fff;
}
</style>
@endpush

@section('content')
<div class="page-almacenes">

    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info alert-dismissible shadow-sm mb-0">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5 class="mb-1"><i class="icon fas fa-warehouse"></i> Almacenes y capacidad</h5>
                Administra depósitos, tipos, capacidad y estado operativo. Consulta movimientos y almacenamiento de producción.
                <div class="mt-2">
                    @can('almacen.movimientos.view')
                    <a href="{{ route('almacen-movimientos.index') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-dolly mr-1"></i> Movimientos
                    </a>
                    @endcan
                    <a href="{{ route('producciones_almacenamiento.index') }}" class="btn btn-sm btn-info ml-1">
                        <i class="fas fa-pallet mr-1"></i> Almacenamiento producción
                    </a>
                    @can('inventario.create')
                    <a href="{{ route('almacenes.create') }}" class="btn btn-sm btn-success ml-1">
                        <i class="fas fa-plus mr-1"></i> Nuevo almacén
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
                    <p>Total de almacenes</p>
                </div>
                <div class="icon"><i class="fas fa-warehouse"></i></div>
                <span class="small-box-footer">Registrados</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-orange">
                <div class="inner">
                    <h3>{{ number_format($stats['capacidad_total'], 0) }}</h3>
                    <p>Capacidad combinada</p>
                </div>
                <div class="icon"><i class="fas fa-balance-scale"></i></div>
                <span class="small-box-footer">Suma de capacidades</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $stats['activos'] }}</h3>
                    <p>Almacenes activos</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <a href="#filtros-almacenes" class="small-box-footer" id="linkActivos">
                    Ver activos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-red">
                <div class="inner">
                    <h3>{{ $stats['inactivos'] }}</h3>
                    <p>Inactivos</p>
                </div>
                <div class="icon"><i class="fas fa-ban"></i></div>
                <a href="#filtros-almacenes" class="small-box-footer" id="linkInactivos">
                    Ver inactivos <i class="fas fa-arrow-circle-right"></i>
                </a>
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
                                <span class="description-text text-muted">ALMACENES</span>
                            </div>
                        </div>
                        <div class="col-sm-3 border-right">
                            <div class="description-block mb-0">
                                <span class="description-percentage text-success">
                                    <i class="fas fa-caret-up"></i> {{ $pctActivos }}%
                                </span>
                                <h5 class="description-header">{{ $stats['activos'] }}</h5>
                                <span class="description-text text-muted">ACTIVOS</span>
                            </div>
                        </div>
                        <div class="col-sm-3 border-right">
                            <div class="description-block mb-0">
                                <h5 class="description-header">{{ number_format($stats['capacidad_total'], 0) }}</h5>
                                <span class="description-text text-muted">CAPACIDAD</span>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="description-block mb-0">
                                <h5 class="description-header">{{ $stats['tipos'] }}</h5>
                                <span class="description-text text-muted">TIPOS</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card card-primary card-outline elevation-2" id="filtros-almacenes">
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
                <a href="{{ route('almacenes.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo
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
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar almacén...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 mb-2">
                    <select id="filterTipo" class="form-control form-control-sm">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposFiltro as $tipoNombre)
                            <option value="{{ strtolower($tipoNombre) }}">{{ $tipoNombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-3 mb-2">
                    <select id="filterEstado" class="form-control form-control-sm">
                        <option value="">Todos los estados</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
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
            <h3 class="card-title"><i class="fas fa-warehouse mr-1 text-success"></i> Listado de almacenes</h3>
            <span class="badge badge-secondary ml-2">{{ $almacenes->total() }} registros</span>
        </div>
        <div class="card-body p-0">
            @forelse($almacenes as $a)
                @php
                    $searchText = strtolower(trim(
                        ($a->nombre ?? '') . ' ' . ($a->tipoAlmacen->nombre ?? '')
                    ));
                @endphp
                <div class="search-item border-bottom"
                    data-nombre="{{ $searchText }}"
                    data-tipo="{{ strtolower($a->tipoAlmacen->nombre ?? '') }}"
                    data-estado="{{ $a->activo ? 'active' : 'inactive' }}">
                    <ul class="products-list product-list-in-card pl-2 pr-2 mb-0">
                        <li class="item">
                            <div class="product-img bg-light rounded">
                                <i class="fas fa-warehouse text-{{ $a->activo ? 'success' : 'secondary' }}"></i>
                            </div>
                            <div class="product-info">
                                <a href="{{ route('almacenes.show', $a) }}" class="product-title">
                                    {{ $a->nombre }}
                                    @if($a->codigo)
                                    <small class="text-muted ml-1">({{ $a->codigo }})</small>
                                    @endif
                                    <span class="badge badge-{{ $a->activo ? 'success' : 'secondary' }} float-right">
                                        {{ $a->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </a>
                                <span class="product-description">
                                    <i class="fas fa-cubes text-muted mr-1"></i>{{ $a->tipoAlmacen->nombre ?? 'Sin tipo' }}
                                    <span class="mx-2 text-muted">|</span>
                                    <i class="fas fa-balance-scale text-muted mr-1"></i>
                                    {{ number_format((float) $a->capacidad, 2) }}
                                    {{ $a->unidadMedida->abreviatura ?? $a->unidadMedida->nombre ?? '' }}
                                    @if($a->ubicacion)
                                    <span class="mx-2 text-muted">|</span>
                                    <i class="fas fa-map-marker-alt text-muted mr-1"></i>{{ $a->ubicacion }}
                                    @endif
                                </span>
                            </div>
                            <div class="ml-2 text-nowrap">
                                <a href="{{ route('almacenes.show', $a) }}" class="btn btn-xs btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('inventario.update')
                                <a href="{{ route('almacenes.edit', $a) }}" class="btn btn-xs btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('inventario.delete')
                                <form action="{{ route('almacenes.destroy', $a) }}" method="POST" class="d-inline on-submit-confirm">
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
                    <i class="fas fa-warehouse fa-3x mb-3 text-light d-block"></i>
                    <p class="mb-2">No hay almacenes registrados.</p>
                    @can('inventario.create')
                    <a href="{{ route('almacenes.create') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus mr-1"></i> Crear primer almacén
                    </a>
                    @endcan
                </div>
            @endforelse
        </div>
        @if($almacenes->hasPages())
        <div class="card-footer">
            {{ $almacenes->links() }}
        </div>
        @endif
    </div>

    <div id="tableView" class="card card-outline card-primary elevation-1" style="display: none;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table mr-1 text-primary"></i> Tabla de almacenes</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Capacidad</th>
                        <th>Unidad</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($almacenes as $a)
                        @php
                            $searchText = strtolower(trim(
                                ($a->nombre ?? '') . ' ' . ($a->tipoAlmacen->nombre ?? '')
                            ));
                        @endphp
                        <tr class="search-item-row"
                            data-nombre="{{ $searchText }}"
                            data-tipo="{{ strtolower($a->tipoAlmacen->nombre ?? '') }}"
                            data-estado="{{ $a->activo ? 'active' : 'inactive' }}">
                            <td>
                                <strong class="text-success">{{ $a->nombre }}</strong>
                                @if($a->codigo)
                                <br><small class="text-muted">{{ $a->codigo }}</small>
                                @endif
                            </td>
                            <td>{{ $a->tipoAlmacen->nombre ?? '—' }}</td>
                            <td>{{ number_format((float) $a->capacidad, 2) }}</td>
                            <td>{{ $a->unidadMedida->nombre ?? '—' }}</td>
                            <td>
                                @if($a->activo)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('almacenes.show', $a) }}" class="btn btn-xs btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('inventario.update')
                                <a href="{{ route('almacenes.edit', $a) }}" class="btn btn-xs btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('inventario.delete')
                                <form action="{{ route('almacenes.destroy', $a) }}" method="POST" class="d-inline on-submit-confirm">
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
        @if($almacenes->hasPages())
        <div class="card-footer">
            {{ $almacenes->links() }}
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
        var tipo = ($('#filterTipo').val() || '').toLowerCase();
        var estado = ($('#filterEstado').val() || '').toLowerCase();

        $('.search-item').each(function () {
            var matchNombre = (($(this).data('nombre') || '').indexOf(val) > -1);
            var matchTipo = !tipo || ($(this).data('tipo') || '') === tipo;
            var matchEstado = !estado || ($(this).data('estado') || '') === estado;
            $(this).toggle(matchNombre && matchTipo && matchEstado);
        });
        $('.search-item-row').each(function () {
            var matchNombre = (($(this).data('nombre') || '').indexOf(val) > -1);
            var matchTipo = !tipo || ($(this).data('tipo') || '') === tipo;
            var matchEstado = !estado || ($(this).data('estado') || '') === estado;
            $(this).toggle(matchNombre && matchTipo && matchEstado);
        });
    }

    $('#searchInput').on('keyup', aplicarFiltros);
    $('#filterTipo, #filterEstado').on('change', aplicarFiltros);

    $('#btnLimpiarFiltros').on('click', function () {
        $('#searchInput').val('');
        $('#filterTipo, #filterEstado').val('');
        aplicarFiltros();
    });

    $('#linkActivos').on('click', function (e) {
        e.preventDefault();
        $('#filterEstado').val('active');
        aplicarFiltros();
        $('html, body').animate({ scrollTop: $('#filtros-almacenes').offset().top - 80 }, 300);
    });

    $('#linkInactivos').on('click', function (e) {
        e.preventDefault();
        $('#filterEstado').val('inactive');
        aplicarFiltros();
        $('html, body').animate({ scrollTop: $('#filtros-almacenes').offset().top - 80 }, 300);
    });

    $('.on-submit-confirm').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: '¿Eliminar almacén?',
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
