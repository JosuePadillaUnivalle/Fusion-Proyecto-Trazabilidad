@extends('layouts.app')

@section('title', 'Almacenes | AgroNexus')
@section('page_title', 'Gestión de almacenes')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Almacenes</li>
@endsection

@push('styles')
@include('partials.modulo-inventario-styles')
<style>
.page-almacenes .products-list .product-img {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush

@section('content')
<div class="modulo-inv page-almacenes">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    </div>
    @endif

    <div class="row mb-2">
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
                <a href="#" class="small-box-footer" id="linkActivos">
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
                <a href="#" class="small-box-footer" id="linkInactivos">
                    Ver inactivos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-warehouse text-success mr-1"></i>
                Almacenes
                <span class="badge badge-light border text-muted badge-registros ml-2">{{ $almacenes->total() }} registros</span>
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
                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#filtrosAlmacenesPanel" title="Filtros">
                    <i class="fas fa-filter"></i>
                </button>
                @can('inventario.create')
                <a href="{{ route('almacenes.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo
                </a>
                @endcan
            </div>
        </div>

        <div id="filtrosAlmacenesPanel" class="filtros-panel collapse">
            <div class="row">
                <div class="col-lg-5 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar almacén...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 mb-2">
                    <label class="small text-muted mb-1">Tipo</label>
                    <select id="filterTipo" class="form-control form-control-sm">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposFiltro as $tipoNombre)
                            <option value="{{ strtolower($tipoNombre) }}">{{ $tipoNombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-3 mb-2">
                    <label class="small text-muted mb-1">Estado</label>
                    <select id="filterEstado" class="form-control form-control-sm">
                        <option value="">Todos los estados</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
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
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Capacidad</th>
                        <th>Unidad</th>
                        <th>Estado</th>
                        <th class="text-center" style="width: 110px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($almacenes as $a)
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
                            <td class="text-center">
                                <div class="btn-group btn-group-sm btn-actions">
                                    <a href="{{ route('almacenes.show', $a) }}" class="btn btn-default" title="Ver"><i class="fas fa-eye text-info"></i></a>
                                    @can('inventario.update')
                                    <a href="{{ route('almacenes.edit', $a) }}" class="btn btn-default" title="Editar"><i class="fas fa-edit text-warning"></i></a>
                                    @endcan
                                    @can('inventario.delete')
                                    <form action="{{ route('almacenes.destroy', $a) }}" method="POST" class="d-inline on-submit-confirm">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-default" title="Eliminar"><i class="fas fa-trash text-danger"></i></button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-warehouse fa-2x mb-2 text-light d-block"></i>
                                No hay almacenes registrados.
                                @can('inventario.create')
                                <a href="{{ route('almacenes.create') }}" class="d-block mt-2">Crear primer almacén</a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="cardView" style="display: none;">
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
                                <a href="{{ route('almacenes.show', $a) }}" class="btn btn-xs btn-info" title="Ver"><i class="fas fa-eye"></i></a>
                                @can('inventario.update')
                                <a href="{{ route('almacenes.edit', $a) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                @endcan
                                @can('inventario.delete')
                                <form action="{{ route('almacenes.destroy', $a) }}" method="POST" class="d-inline on-submit-confirm">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </li>
                    </ul>
                </div>
            @empty
                <div class="text-center text-muted py-5">No hay almacenes registrados.</div>
            @endforelse
        </div>

        @if($almacenes->hasPages())
        <div class="card-footer bg-white d-flex justify-content-end py-2">
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
        $('#filtrosAlmacenesPanel').collapse('show');
    });

    $('#linkInactivos').on('click', function (e) {
        e.preventDefault();
        $('#filterEstado').val('inactive');
        aplicarFiltros();
        $('#filtrosAlmacenesPanel').collapse('show');
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
});
</script>
@endpush
