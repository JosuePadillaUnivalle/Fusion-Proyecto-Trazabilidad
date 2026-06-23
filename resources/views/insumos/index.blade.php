@extends('layouts.app')

@section('title', 'Gestión de Insumos | AgroFusion')
@section('page_title', 'Inventario de Insumos')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Insumos</li>
@endsection

@push('styles')
@include('partials.modulo-inventario-styles')
<style>
.page-insumos .products-list .product-img {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}
.page-insumos .products-list .product-img img,
.page-insumos .insumo-thumb {
    width: 44px;
    height: 44px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    background: #f8f9fa;
}
.page-insumos .insumo-cell {
    display: flex;
    align-items: center;
    gap: .65rem;
}
.page-insumos .stock-badge-high { background: #d4edda; color: #155724; }
.page-insumos .stock-badge-medium { background: #fff3cd; color: #856404; }
.page-insumos .stock-badge-low { background: #f8d7da; color: #721c24; }
</style>
@endpush

@section('content')
<div class="modulo-inv page-insumos">

<div class="alert alert-light border py-2 mb-3 small">
    <i class="fas fa-info-circle text-success mr-1"></i>
    Catálogo de <strong>insumos del campo</strong> (bodega agrícola general): material de siembra, fertilizantes y control de plagas.
    Los productos cosechados se gestionan en <strong>Lotes</strong> y <strong>Almacén agrícola → Movimientos</strong>.
    Al usarlos en un lote, registre una <strong>Actividad</strong>.
</div>

<div class="row mb-2">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total de insumos</p>
                </div>
                <div class="icon"><i class="fas fa-boxes"></i></div>
                <span class="small-box-footer">En tu catálogo</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-red">
                <div class="inner">
                    <h3>{{ $stats['stock_bajo'] }}</h3>
                    <p>Stock bajo</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                <a href="#" class="small-box-footer" id="linkStockBajo">
                    Filtrar críticos <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $stats['categorias'] }}</h3>
                    <p>Categorías (tipos)</p>
                </div>
                <div class="icon"><i class="fas fa-tags"></i></div>
                <span class="small-box-footer">Tipos distintos</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3>≤ {{ $umbral ?? 5 }}</h3>
                    <p>Umbral de alerta</p>
                </div>
                <div class="icon"><i class="fas fa-bell"></i></div>
                <span class="small-box-footer">Aviso cuando el stock llega a {{ $umbral ?? 5 }} o menos</span>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Insumos"
            icono="fa-boxes"
            :registros="$insumos->total()"
            filtros-target="#filtrosInsumosPanel"
            :view-toggle="true"
            view-default="table"
            :nuevo-href="route('insumos.create')"
            nuevo-text="Nuevo Insumo"
            nuevo-can="inventario.create"
        />

        <div id="filtrosInsumosPanel" class="filtros-panel collapse">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre...">
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Tipo</label>
                    <select id="filterTipo" class="form-control form-control-sm">
                        <option value="">Todos los tipos</option>
                        @foreach($tiposFiltro ?? [] as $tipoFiltro)
                            <option value="{{ strtolower($tipoFiltro->nombre) }}">{{ $tipoFiltro->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Unidad</label>
                    <select id="filterUnidad" class="form-control form-control-sm">
                        <option value="">Todas las unidades</option>
                        @foreach($insumos->pluck('unidadMedida.nombre')->filter()->unique()->sort() as $unidadNombre)
                            <option value="{{ strtolower($unidadNombre) }}">{{ $unidadNombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <label class="small text-muted mb-1">Stock</label>
                    <select id="filterStock" class="form-control form-control-sm">
                        <option value="">Todos los stocks</option>
                        <option value="low">Stock bajo</option>
                        <option value="medium">Stock medio</option>
                        <option value="high">Stock alto</option>
                    </select>
                </div>
            </div>
            <x-filtros-client-actions />
        </div>

        <div id="tableView" class="table-responsive">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th>Insumo</th>
                        <th>Tipo</th>
                        <th>Unidad</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th class="text-center" style="width: 110px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($insumos as $i)
                        @php
                            $rowStockClass = \App\Support\InsumoCatalogo::claseStock((float) $i->stock);
                        @endphp
                        <tr class="search-item-row"
                            data-nombre="{{ strtolower($i->nombre) }}"
                            data-tipo="{{ strtolower($i->tipo->nombre ?? '') }}"
                            data-unidad="{{ strtolower($i->unidadMedida->nombre ?? '') }}"
                            data-stockclass="{{ $rowStockClass }}">
                            <td>
                                <div class="insumo-cell">
                                    <img src="{{ $i->imagenSrc(128) }}" alt="{{ $i->nombre }}" class="insumo-thumb" loading="lazy">
                                    <strong class="text-success mb-0">{{ $i->nombre }}</strong>
                                </div>
                            </td>
                            <td>{{ $i->tipo->nombre ?? '—' }}</td>
                            <td>{{ $i->unidadMedida->nombre ?? '—' }}</td>
                            <td>
                                <span class="badge stock-badge-{{ $rowStockClass }}">
                                    {{ number_format($i->stock, 2) }}
                                </span>
                            </td>
                            <td>
                                @if($rowStockClass === 'low')
                                    <span class="text-danger small font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i>Alerta</span>
                                @elseif($rowStockClass === 'medium')
                                    <span class="text-warning small">Atención</span>
                                @else
                                    <span class="text-success small">Normal</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm btn-actions">
                                    <a href="{{ route('insumos.show', $i) }}" class="btn btn-outline-info btn-sm" title="Ver"><i class="fas fa-eye"></i></a>
                                    @can('inventario.update')
                                    <a href="{{ route('insumos.edit', $i) }}" class="btn btn-outline-warning btn-sm" title="Editar"><i class="fas fa-edit"></i></a>
                                    @endcan
                                    @can('inventario.delete')
                                    <form action="{{ route('insumos.destroy', $i) }}" method="POST" class="d-inline on-submit-confirm">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-boxes fa-2x mb-2 text-light d-block"></i>
                                No hay insumos registrados.
                                @can('inventario.create')
                                <a href="{{ route('insumos.create') }}" class="d-block mt-2">Agregar primer insumo</a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div id="cardView" style="display: none;">
            @forelse($insumos as $i)
                @php
                    $stockClass = \App\Support\InsumoCatalogo::claseStock((float) $i->stock);
                    $imagenSrc = $i->imagenSrc(128);
                    $outline = $stockClass === 'low' ? 'danger' : ($stockClass === 'medium' ? 'warning' : 'success');
                @endphp
                <div class="search-item border-bottom"
                    data-nombre="{{ strtolower($i->nombre) }}"
                    data-tipo="{{ strtolower($i->tipo->nombre ?? '') }}"
                    data-unidad="{{ strtolower($i->unidadMedida->nombre ?? '') }}"
                    data-stockclass="{{ $stockClass }}">
                    <ul class="products-list product-list-in-card pl-2 pr-2 mb-0">
                        <li class="item">
                            <div class="product-img bg-light rounded">
                                <img src="{{ $imagenSrc }}" alt="{{ $i->nombre }}" loading="lazy">
                            </div>
                            <div class="product-info">
                                <a href="{{ route('insumos.show', $i) }}" class="product-title">
                                    {{ $i->nombre }}
                                    <span class="badge stock-badge-{{ $stockClass }} float-right">
                                        Stock: {{ number_format($i->stock, 2) }}
                                    </span>
                                </a>
                                <span class="product-description">
                                    <i class="fas fa-tag text-muted mr-1"></i>{{ $i->tipo->nombre ?? '—' }}
                                    <span class="mx-2 text-muted">|</span>
                                    <i class="fas fa-balance-scale text-muted mr-1"></i>{{ $i->unidadMedida->nombre ?? '—' }}
                                    <span class="mx-2 text-muted">|</span>
                                    @if($stockClass === 'low')
                                    <span class="mx-2 text-danger font-weight-bold">| Alerta (≤ {{ $umbral ?? 5 }})</span>
                                    @endif
                                </span>
                            </div>
                            <div class="ml-2 text-nowrap">
                                <a href="{{ route('insumos.show', $i) }}" class="btn btn-xs btn-info" title="Ver"><i class="fas fa-eye"></i></a>
                                @can('inventario.update')
                                <a href="{{ route('insumos.edit', $i) }}" class="btn btn-xs btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                @endcan
                                @can('inventario.delete')
                                <form action="{{ route('insumos.destroy', $i) }}" method="POST" class="d-inline on-submit-confirm">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </li>
                    </ul>
                </div>
            @empty
                <div class="text-center text-muted py-5">No hay insumos registrados.</div>
            @endforelse
        </div>

        @if($insumos->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap py-2">
            <small class="text-muted mb-2 mb-sm-0">
                Mostrando {{ $insumos->firstItem() }}–{{ $insumos->lastItem() }} de {{ $insumos->total() }}
            </small>
            <div class="mb-0">{{ $insumos->links() }}</div>
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
        var unidad = ($('#filterUnidad').val() || '').toLowerCase();
        var stock = ($('#filterStock').val() || '').toLowerCase();

        $('.search-item').each(function () {
            var matchNombre = (($(this).data('nombre') || '').indexOf(val) > -1);
            var matchTipo = !tipo || ($(this).data('tipo') || '') === tipo;
            var matchUnidad = !unidad || ($(this).data('unidad') || '') === unidad;
            var matchStock = !stock || ($(this).data('stockclass') || '') === stock;
            $(this).toggle(matchNombre && matchTipo && matchUnidad && matchStock);
        });

        $('.search-item-row').each(function () {
            var matchNombre = (($(this).data('nombre') || '').indexOf(val) > -1);
            var matchTipo = !tipo || ($(this).data('tipo') || '') === tipo;
            var matchUnidad = !unidad || ($(this).data('unidad') || '') === unidad;
            var matchStock = !stock || ($(this).data('stockclass') || '') === stock;
            $(this).toggle(matchNombre && matchTipo && matchUnidad && matchStock);
        });
    }

    $('#searchInput').on('keyup', aplicarFiltros);
    $('#filterTipo, #filterUnidad, #filterStock').on('change', aplicarFiltros);
    $('#btnAplicarFiltros').on('click', aplicarFiltros);

    $('#btnLimpiarFiltros').on('click', function () {
        $('#searchInput').val('');
        $('#filterTipo, #filterUnidad, #filterStock').val('');
        aplicarFiltros();
    });

    $('#linkStockBajo').on('click', function (e) {
        e.preventDefault();
        $('#filterStock').val('low');
        aplicarFiltros();
        $('#filtrosInsumosPanel').collapse('show');
    });

    var urlStock = new URLSearchParams(window.location.search).get('stock');
    if (urlStock === 'bajo' || urlStock === 'low') {
        $('#filterStock').val('low');
        $('#filtrosInsumosPanel').addClass('show');
        aplicarFiltros();
    }

    $('.on-submit-confirm').on('submit', function (e) {
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: '¿Eliminar insumo?',
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
