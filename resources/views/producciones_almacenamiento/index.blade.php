@extends('layouts.app')

@section('title', 'Almacenamiento de producción | AgroFusion')
@section('page_title', 'Almacenamiento de producciones')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Almacenamiento</li>
@endsection

@push('styles')
@include('partials.modulo-inventario-styles')
<style>
.page-prod-almacen .products-list .product-img {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush

@section('content')
<div class="modulo-inv page-prod-almacen">

<div class="row mb-2">
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
                <span class="small-box-footer">Depósitos distintos</span>
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

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Almacenamiento de producción"
            icono="fa-pallet"
            :registros="$registros->total()"
            filtros-target="#filtrosAlmacenamientoPanel"
            :view-toggle="true"
            view-default="table"
            :nuevo-href="route('producciones_almacenamiento.create')"
            nuevo-can="inventario.create"
        />

        <div id="filtrosAlmacenamientoPanel" class="filtros-panel collapse">
            <div class="row">
                <div class="col-lg-5 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="searchInput" class="form-control" placeholder="Almacén o lote...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 mb-2">
                    <label class="small text-muted mb-1">Almacén</label>
                    <select id="filterAlmacen" class="form-control form-control-sm">
                        <option value="">Todos los almacenes</option>
                        @foreach($almacenesFiltro as $nombreAlmacen)
                            <option value="{{ strtolower($nombreAlmacen) }}">{{ $nombreAlmacen }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-3 mb-2">
                    <label class="small text-muted mb-1">Unidad</label>
                    <select id="filterUnidad" class="form-control form-control-sm">
                        <option value="">Todas las unidades</option>
                        @foreach($unidadesFiltro as $nombreUnidad)
                            <option value="{{ strtolower($nombreUnidad) }}">{{ $nombreUnidad }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <x-filtros-client-actions />
        </div>

        <div id="tableView" class="table-responsive">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th>Producción</th>
                        <th>Almacén</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Temp.</th>
                        <th>Humedad</th>
                        <th class="text-center" style="width: 110px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registros as $r)
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
                            <td class="text-center">
                                <div class="btn-group btn-group-sm btn-actions">
                                    <a href="{{ route('producciones_almacenamiento.show', $r) }}" class="btn btn-default" title="Ver"><i class="fas fa-eye text-info"></i></a>
                                    @can('inventario.update')
                                    <a href="{{ route('producciones_almacenamiento.edit', $r) }}" class="btn btn-default" title="Editar"><i class="fas fa-edit text-warning"></i></a>
                                    @endcan
                                    @can('inventario.delete')
                                    <form action="{{ route('producciones_almacenamiento.destroy', $r) }}" method="POST" class="d-inline on-submit-confirm">
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
                                <i class="fas fa-box-open fa-2x mb-2 text-light d-block"></i>
                                No hay registros de almacenamiento.
                                @can('inventario.create')
                                <a href="{{ route('producciones_almacenamiento.create') }}" class="d-block mt-2">Crear primer registro</a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="cardView" style="display: none;">
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
                <div class="text-center text-muted py-5">No hay registros de almacenamiento.</div>
            @endforelse
        </div>

        @if($registros->hasPages())
        <div class="card-footer bg-white d-flex justify-content-end py-2">
            {{ $registros->links() }}
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
    $('#btnAplicarFiltros').on('click', aplicarFiltros);

    $('#btnLimpiarFiltros').on('click', function () {
        $('#searchInput').val('');
        $('#filterAlmacen, #filterUnidad').val('');
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
        }).then(function (r) {
            if (r.isConfirmed) form.submit();
        });
    });
});
</script>
@endpush
