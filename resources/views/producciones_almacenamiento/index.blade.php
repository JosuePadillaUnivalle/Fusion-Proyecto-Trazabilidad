@extends('layouts.app')

@section('title', 'Almacenamiento de producción | AgroNexus')
@section('page_title', 'Almacenamiento de producciones')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('almacenes.index') }}">Inventario</a></li>
    <li class="breadcrumb-item active">Almacenamiento</li>
@endsection

@php
    $pctTempAlta = $stats['total'] > 0
        ? round(($stats['temp_alta'] / $stats['total']) * 100)
        : 0;
@endphp

@push('styles')
<style>
.page-prod-almacen .small-box {
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.page-prod-almacen .small-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
}
.page-prod-almacen .small-box .icon { font-size: 70px; }
.page-prod-almacen .small-box-green {
    background: linear-gradient(135deg, #2c5530, #4a7c59) !important;
    color: #fff;
}
.page-prod-almacen .small-box-teal {
    background: linear-gradient(135deg, #009688, #4db6ac) !important;
    color: #fff;
}
.page-prod-almacen .small-box-orange {
    background: linear-gradient(135deg, #e65100, #ff7043) !important;
    color: #fff;
}
.page-prod-almacen .small-box-blue {
    background: linear-gradient(135deg, #17a2b8, #20c997) !important;
    color: #fff;
}
.page-prod-almacen .card { border-radius: 10px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06); }
.page-prod-almacen .products-list .product-img {
    width: 50px; height: 50px;
    display: flex; align-items: center; justify-content: center;
}
.page-prod-almacen .view-toggle .btn.active {
    background-color: #2c5530; border-color: #2c5530; color: #fff;
}
</style>
@endpush

@section('content')
<div class="page-prod-almacen">

    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info alert-dismissible shadow-sm mb-0">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5 class="mb-1"><i class="icon fas fa-pallet"></i> Almacenamiento de cosecha</h5>
                Registra dónde y bajo qué condiciones se guarda cada producción: almacén, cantidad, temperatura y humedad.
                <div class="mt-2">
                    <a href="{{ route('almacenes.index') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-warehouse mr-1"></i> Almacenes
                    </a>
                    @can('inventario.create')
                    <a href="{{ route('producciones_almacenamiento.create') }}" class="btn btn-sm btn-success ml-1">
                        <i class="fas fa-plus mr-1"></i> Nuevo registro
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
                    <p>Registros de almacenamiento</p>
                </div>
                <div class="icon"><i class="fas fa-box-open"></i></div>
                <span class="small-box-footer">Historial</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-teal">
                <div class="inner">
                    <h3>{{ $stats['almacenes'] }}</h3>
                    <p>Almacenes en uso</p>
                </div>
                <div class="icon"><i class="fas fa-warehouse"></i></div>
                <a href="{{ route('almacenes.index') }}" class="small-box-footer">
                    Ver almacenes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-orange">
                <div class="inner">
                    <h3>{{ $stats['temp_alta'] }}</h3>
                    <p>Temp. &gt; 25°C</p>
                </div>
                <div class="icon"><i class="fas fa-thermometer-full"></i></div>
                <span class="small-box-footer">Revisar condiciones</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ number_format($stats['cantidad_total'], 0) }}</h3>
                    <p>Cantidad almacenada</p>
                </div>
                <div class="icon"><i class="fas fa-balance-scale"></i></div>
                <span class="small-box-footer">{{ $stats['producciones'] }} producciones</span>
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
                                <span class="description-text text-muted">REGISTROS</span>
                            </div>
                        </div>
                        <div class="col-sm-3 border-right">
                            <div class="description-block mb-0">
                                <h5 class="description-header">{{ $stats['almacenes'] }}</h5>
                                <span class="description-text text-muted">ALMACENES</span>
                            </div>
                        </div>
                        <div class="col-sm-3 border-right">
                            <div class="description-block mb-0">
                                <span class="description-percentage text-warning">
                                    <i class="fas fa-thermometer-half"></i> {{ $pctTempAlta }}%
                                </span>
                                <h5 class="description-header">{{ $stats['temp_alta'] }}</h5>
                                <span class="description-text text-muted">TEMP. ALTA</span>
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

    <div class="card card-primary card-outline elevation-2" id="filtros-almacenamiento">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter mr-1"></i> Buscar y filtrar</h3>
            <div class="card-tools">
                <div class="btn-group btn-group-sm view-toggle mr-2">
                    <button type="button" class="btn btn-default active" id="btnCardView"><i class="fas fa-th-large"></i></button>
                    <button type="button" class="btn btn-default" id="btnTableView"><i class="fas fa-list"></i></button>
                </div>
                @can('inventario.create')
                <a href="{{ route('producciones_almacenamiento.create') }}" class="btn btn-success btn-sm">
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
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar por almacén o lote...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 mb-2">
                    <select id="filterAlmacen" class="form-control form-control-sm">
                        <option value="">Todos los almacenes</option>
                        @foreach($almacenesFiltro as $nombreAlmacen)
                            <option value="{{ strtolower($nombreAlmacen) }}">{{ $nombreAlmacen }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-3 mb-2">
                    <select id="filterUnidad" class="form-control form-control-sm">
                        <option value="">Todas las unidades</option>
                        @foreach($unidadesFiltro as $nombreUnidad)
                            <option value="{{ strtolower($nombreUnidad) }}">{{ $nombreUnidad }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-1 col-md-12 mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnLimpiarFiltros">
                        <i class="fas fa-eraser"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="cardView" class="card card-outline card-success elevation-1">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-pallet mr-1 text-success"></i> Registros</h3>
            <span class="badge badge-secondary ml-2">{{ $registros->total() }} en total</span>
        </div>
        <div class="card-body p-0">
            @forelse($registros as $r)
                @php
                    $loteNombre = $r->produccion?->lote?->nombre ?? '';
                    $searchText = strtolower(trim(($r->almacen->nombre ?? '') . ' ' . $loteNombre));
                    $tempAlta = $r->temperatura !== null && (float) $r->temperatura > 25;
                @endphp
                <div class="search-item border-bottom"
                    data-nombre="{{ $searchText }}"
                    data-almacen="{{ strtolower($r->almacen->nombre ?? '') }}"
                    data-unidad="{{ strtolower($r->unidadMedida->nombre ?? '') }}">
                    <ul class="products-list product-list-in-card pl-2 pr-2 mb-0">
                        <li class="item">
                            <div class="product-img bg-light rounded">
                                <i class="fas fa-box text-{{ $tempAlta ? 'warning' : 'success' }}"></i>
                            </div>
                            <div class="product-info">
                                <a href="{{ route('producciones_almacenamiento.show', $r) }}" class="product-title">
                                    Producción N°{{ $r->produccionid }}
                                    <span class="badge badge-{{ $tempAlta ? 'warning' : 'info' }} float-right">
                                        {{ $r->unidadMedida->nombre ?? '—' }}
                                    </span>
                                </a>
                                <span class="product-description">
                                    <i class="fas fa-warehouse text-muted mr-1"></i>{{ $r->almacen->nombre ?? 'Sin almacén' }}
                                    <span class="mx-2 text-muted">|</span>
                                    <i class="fas fa-map-marker-alt text-muted mr-1"></i>{{ $loteNombre ?: '—' }}
                                    <span class="mx-2 text-muted">|</span>
                                    <i class="fas fa-balance-scale text-muted mr-1"></i>{{ number_format((float) $r->cantidad, 2) }}
                                    <span class="mx-2 text-muted">|</span>
                                    <i class="fas fa-thermometer-half text-muted mr-1"></i>
                                    {{ $r->temperatura !== null ? number_format((float) $r->temperatura, 1) . '°C' : '—' }}
                                    / {{ $r->humedad !== null ? number_format((float) $r->humedad, 1) . '%' : '—' }}
                                </span>
                            </div>
                            <div class="ml-2 text-nowrap">
                                <a href="{{ route('producciones_almacenamiento.show', $r) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                @can('inventario.update')
                                <a href="{{ route('producciones_almacenamiento.edit', $r) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                                @endcan
                                @can('inventario.delete')
                                <form action="{{ route('producciones_almacenamiento.destroy', $r) }}" method="POST" class="d-inline on-submit-confirm">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </li>
                    </ul>
                </div>
            @empty
                <div class="text-center text-muted py-5">
                    <i class="fas fa-box-open fa-3x mb-3 text-light d-block"></i>
                    <p class="mb-2">No hay registros de almacenamiento.</p>
                    @can('inventario.create')
                    <a href="{{ route('producciones_almacenamiento.create') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus mr-1"></i> Crear primer registro
                    </a>
                    @endcan
                </div>
            @endforelse
        </div>
        @if($registros->hasPages())
        <div class="card-footer">{{ $registros->links() }}</div>
        @endif
    </div>

    <div id="tableView" class="card card-outline card-primary elevation-1" style="display: none;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table mr-1 text-primary"></i> Tabla de registros</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>Producción</th>
                        <th>Almacén</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Temp.</th>
                        <th>Humedad</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registros as $r)
                        @php
                            $loteNombre = $r->produccion?->lote?->nombre ?? '';
                            $searchText = strtolower(trim(($r->almacen->nombre ?? '') . ' ' . $loteNombre));
                        @endphp
                        <tr class="search-item-row"
                            data-nombre="{{ $searchText }}"
                            data-almacen="{{ strtolower($r->almacen->nombre ?? '') }}"
                            data-unidad="{{ strtolower($r->unidadMedida->nombre ?? '') }}">
                            <td>
                                <strong class="text-success">N°{{ $r->produccionid }}</strong>
                                @if($loteNombre)<br><small class="text-muted">{{ $loteNombre }}</small>@endif
                            </td>
                            <td>{{ $r->almacen->nombre ?? '—' }}</td>
                            <td>{{ number_format((float) $r->cantidad, 2) }}</td>
                            <td>{{ $r->unidadMedida->nombre ?? '—' }}</td>
                            <td>{{ $r->temperatura !== null ? number_format((float) $r->temperatura, 1) . '°C' : '—' }}</td>
                            <td>{{ $r->humedad !== null ? number_format((float) $r->humedad, 1) . '%' : '—' }}</td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('producciones_almacenamiento.show', $r) }}" class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                @can('inventario.update')
                                <a href="{{ route('producciones_almacenamiento.edit', $r) }}" class="btn btn-xs btn-warning"><i class="fas fa-edit"></i></a>
                                @endcan
                                @can('inventario.delete')
                                <form action="{{ route('producciones_almacenamiento.destroy', $r) }}" method="POST" class="d-inline on-submit-confirm">
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
        @if($registros->hasPages())
        <div class="card-footer">{{ $registros->links() }}</div>
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
        $('#cardView').show(); $('#tableView').hide();
    });
    $('#btnTableView').on('click', function () {
        $(this).addClass('active').siblings().removeClass('active');
        $('#tableView').show(); $('#cardView').hide();
    });

    function aplicarFiltros() {
        var val = ($('#searchInput').val() || '').toLowerCase();
        var almacen = ($('#filterAlmacen').val() || '').toLowerCase();
        var unidad = ($('#filterUnidad').val() || '').toLowerCase();
        $('.search-item, .search-item-row').each(function () {
            var matchNombre = (($(this).data('nombre') || '').indexOf(val) > -1);
            var matchAlmacen = !almacen || ($(this).data('almacen') || '') === almacen;
            var matchUnidad = !unidad || ($(this).data('unidad') || '') === unidad;
            $(this).toggle(matchNombre && matchAlmacen && matchUnidad);
        });
    }
    $('#searchInput').on('keyup', aplicarFiltros);
    $('#filterAlmacen, #filterUnidad').on('change', aplicarFiltros);
    $('#btnLimpiarFiltros').on('click', function () {
        $('#searchInput').val('');
        $('#filterAlmacen, #filterUnidad').val('');
        aplicarFiltros();
    });
    $('.on-submit-confirm').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: '¿Eliminar registro?', text: 'No podrás revertir esto', icon: 'warning',
            showCancelButton: true, confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar'
        }).then(function (r) { if (r.isConfirmed) form.submit(); });
    });
    @if(session('success'))
    Swal.fire({ icon: 'success', title: '¡Hecho!', text: @json(session('success')),
        confirmButtonColor: '#2c5530', timer: 3000, showConfirmButton: false });
    @endif
});
</script>
@endpush
