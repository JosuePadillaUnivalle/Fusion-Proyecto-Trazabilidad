@extends('layouts.app')

@section('title', 'Gestión de Insumos | AgroNexus')
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
}
.page-insumos .stock-badge-high { background: #d4edda; color: #155724; }
.page-insumos .stock-badge-medium { background: #fff3cd; color: #856404; }
.page-insumos .stock-badge-low { background: #f8d7da; color: #721c24; }
</style>
@endpush

@section('content')
<div class="modulo-inv page-insumos">

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
                    <h3>Bs. {{ number_format($stats['valor_total'], 0) }}</h3>
                    <p>Valor en stock</p>
                </div>
                <div class="icon"><i class="fas fa-coins"></i></div>
                <span class="small-box-footer">Stock × precio unitario</span>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-boxes text-success mr-1"></i>
                Insumos
                <span class="badge badge-light border text-muted badge-registros ml-2">{{ $insumos->total() }} registros</span>
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
                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#filtrosInsumosPanel" title="Filtros">
                    <i class="fas fa-filter"></i>
                </button>
                @can('inventario.create')
                <a href="{{ route('insumos.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo
                </a>
                @endcan
            </div>
        </div>

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
                        @foreach($insumos->pluck('tipo.nombre')->filter()->unique()->sort() as $tipoNombre)
                            <option value="{{ strtolower($tipoNombre) }}">{{ $tipoNombre }}</option>
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
                <div class="col-lg-2 col-md-6 mb-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnLimpiarFiltros">
                        <i class="fas fa-times mr-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>

        <div id="tableView" class="table-responsive">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th>Insumo</th>
                        <th>Tipo</th>
                        <th>Unidad</th>
                        <th>Stock</th>
                        <th>Mínimo</th>
                        <th class="text-center" style="width: 110px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($insumos as $i)
                        @php
                            $rowStockClass = 'high';
                            if ($i->stock <= $i->stockminimo) {
                                $rowStockClass = 'low';
                            } elseif ($i->stock < $i->stockminimo * 1.5) {
                                $rowStockClass = 'medium';
                            }
                        @endphp
                        <tr class="search-item-row"
                            data-nombre="{{ strtolower($i->nombre) }}"
                            data-tipo="{{ strtolower($i->tipo->nombre ?? '') }}"
                            data-unidad="{{ strtolower($i->unidadMedida->nombre ?? '') }}"
                            data-stockclass="{{ $rowStockClass }}">
                            <td><strong class="text-success">{{ $i->nombre }}</strong></td>
                            <td>{{ $i->tipo->nombre ?? '—' }}</td>
                            <td>{{ $i->unidadMedida->nombre ?? '—' }}</td>
                            <td>
                                <span class="badge stock-badge-{{ $rowStockClass }}">
                                    {{ number_format($i->stock, 2) }}
                                </span>
                            </td>
                            <td>{{ number_format($i->stockminimo, 2) }}</td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm btn-actions">
                                    <a href="{{ route('insumos.show', $i) }}" class="btn btn-default" title="Ver"><i class="fas fa-eye text-info"></i></a>
                                    @can('inventario.update')
                                    <a href="{{ route('insumos.edit', $i) }}" class="btn btn-default" title="Editar"><i class="fas fa-edit text-warning"></i></a>
                                    @endcan
                                    @can('inventario.delete')
                                    <form action="{{ route('insumos.destroy', $i) }}" method="POST" class="d-inline on-submit-confirm">
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
                    $stockClass = 'high';
                    if ($i->stock <= $i->stockminimo) {
                        $stockClass = 'low';
                    } elseif ($i->stock < $i->stockminimo * 1.5) {
                        $stockClass = 'medium';
                    }
                    $icon = 'box';
                    $tipo = strtolower($i->tipo->nombre ?? '');
                    if (str_contains($tipo, 'fertil')) {
                        $icon = 'flask';
                    } elseif (str_contains($tipo, 'semilla')) {
                        $icon = 'seedling';
                    } elseif (str_contains($tipo, 'pest')) {
                        $icon = 'bug';
                    }
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
                                <i class="fas fa-{{ $icon }} text-{{ $outline }}"></i>
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
                                    <i class="fas fa-level-down-alt text-muted mr-1"></i>Mín: {{ number_format($i->stockminimo, 2) }}
                                    @if($i->preciounitario)
                                    <span class="mx-2 text-muted">|</span>
                                    <i class="fas fa-coins text-muted mr-1"></i>Bs. {{ number_format($i->preciounitario, 2) }}
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
        <div class="card-footer bg-white d-flex justify-content-end py-2">
            {{ $insumos->links() }}
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
