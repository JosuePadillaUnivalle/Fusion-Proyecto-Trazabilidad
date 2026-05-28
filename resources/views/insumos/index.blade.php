@extends('layouts.app')

@section('title', 'Gestión de Insumos | AgroNexus')
@section('page_title', 'Inventario de Insumos')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Insumos</li>
@endsection

@php
    $pctStockBajo = $stats['total'] > 0
        ? round(($stats['stock_bajo'] / $stats['total']) * 100)
        : 0;
@endphp

@push('styles')
    <style>
        .page-insumos .small-box {
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .page-insumos .small-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
        }
        .page-insumos .small-box .icon { font-size: 70px; }
        .page-insumos .small-box-green {
            background: linear-gradient(135deg, #2c5530, #4a7c59) !important;
            color: #fff;
        }
        .page-insumos .small-box-red {
            background: linear-gradient(135deg, #dc3545, #e74a3b) !important;
            color: #fff;
        }
        .page-insumos .small-box-blue {
            background: linear-gradient(135deg, #17a2b8, #20c997) !important;
            color: #fff;
        }
        .page-insumos .small-box-yellow {
            background: linear-gradient(135deg, #fd7e14, #ffc107) !important;
            color: #1a252f;
        }
        .page-insumos .card {
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }
        .page-insumos .products-list .product-img {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .page-insumos .view-toggle .btn.active {
            background-color: #2c5530;
            border-color: #2c5530;
            color: #fff;
        }
        .page-insumos .stock-badge-high { background: #d4edda; color: #155724; }
        .page-insumos .stock-badge-medium { background: #fff3cd; color: #856404; }
        .page-insumos .stock-badge-low { background: #f8d7da; color: #721c24; }
    </style>
@endpush

@section('content')
<div class="page-insumos">

    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info alert-dismissible shadow-sm mb-0">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5 class="mb-1"><i class="icon fas fa-boxes"></i> Catálogo de insumos</h5>
                Administra fertilizantes, semillas, fitosanitarios y demás insumos. Revisa stock mínimo y accede al reporte consolidado.
                <div class="mt-2">
                    @can('reportes.view')
                    <a href="{{ route('reportes.inventario') }}" class="btn btn-sm btn-outline-info">
                        <i class="fas fa-chart-pie mr-1"></i> Reporte de inventario
                    </a>
                    @endcan
                    @can('inventario.create')
                    <a href="{{ route('insumos.create') }}" class="btn btn-sm btn-success ml-1">
                        <i class="fas fa-plus mr-1"></i> Nuevo insumo
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
                <a href="#filtros-insumos" class="small-box-footer" id="linkStockBajo">
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

    @if ($stats['total'] > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card card-outline card-secondary mb-0">
                <div class="card-body py-2">
                    <div class="row text-center">
                        <div class="col-sm-4 border-right">
                            <div class="description-block mb-0">
                                <h5 class="description-header">{{ $stats['total'] }}</h5>
                                <span class="description-text text-muted">REGISTROS</span>
                            </div>
                        </div>
                        <div class="col-sm-4 border-right">
                            <div class="description-block mb-0">
                                <span class="description-percentage text-danger">
                                    <i class="fas fa-caret-down"></i> {{ $pctStockBajo }}%
                                </span>
                                <h5 class="description-header">{{ $stats['stock_bajo'] }}</h5>
                                <span class="description-text text-muted">BAJO MÍNIMO</span>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="description-block mb-0">
                                <h5 class="description-header">{{ $stats['categorias'] }}</h5>
                                <span class="description-text text-muted">TIPOS</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card card-primary card-outline elevation-2" id="filtros-insumos">
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
                <a href="{{ route('insumos.create') }}" class="btn btn-success btn-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo
                </a>
                @endcan
            </div>
        </div>
        <div class="card-body pb-2">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-2">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="searchInput" class="form-control" placeholder="Buscar por nombre...">
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <select id="filterTipo" class="form-control form-control-sm">
                        <option value="">Todos los tipos</option>
                        @foreach($insumos->pluck('tipo.nombre')->filter()->unique()->sort() as $tipoNombre)
                            <option value="{{ strtolower($tipoNombre) }}">{{ $tipoNombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <select id="filterUnidad" class="form-control form-control-sm">
                        <option value="">Todas las unidades</option>
                        @foreach($insumos->pluck('unidadMedida.nombre')->filter()->unique()->sort() as $unidadNombre)
                            <option value="{{ strtolower($unidadNombre) }}">{{ $unidadNombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <select id="filterStock" class="form-control form-control-sm">
                        <option value="">Todos los stocks</option>
                        <option value="low">Stock bajo</option>
                        <option value="medium">Stock medio</option>
                        <option value="high">Stock alto</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btnLimpiarFiltros">
                        <i class="fas fa-eraser mr-1"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

  {{-- Vista tarjetas (products-list AdminLTE) --}}
    <div id="cardView" class="card card-outline card-success elevation-1">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-cubes mr-1 text-success"></i> Listado de insumos</h3>
            <span class="badge badge-secondary ml-2">{{ $insumos->total() }} en catálogo</span>
        </div>
        <div class="card-body p-0">
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
                                <a href="{{ route('insumos.show', $i) }}" class="btn btn-xs btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('inventario.update')
                                <a href="{{ route('insumos.edit', $i) }}" class="btn btn-xs btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('inventario.delete')
                                <form action="{{ route('insumos.destroy', $i) }}" method="POST" class="d-inline on-submit-confirm">
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
                    <i class="fas fa-boxes fa-3x mb-3 text-light"></i>
                    <p class="mb-2">No hay insumos registrados.</p>
                    @can('inventario.create')
                    <a href="{{ route('insumos.create') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus mr-1"></i> Agregar primer insumo
                    </a>
                    @endcan
                </div>
            @endforelse
        </div>
        @if($insumos->hasPages())
        <div class="card-footer">
            {{ $insumos->links() }}
        </div>
        @endif
    </div>

    {{-- Vista tabla --}}
    <div id="tableView" class="card card-outline card-primary elevation-1" style="display: none;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table mr-1 text-primary"></i> Tabla de insumos</h3>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr>
                        <th>Insumo</th>
                        <th>Tipo</th>
                        <th>Unidad</th>
                        <th>Stock</th>
                        <th>Mínimo</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($insumos as $i)
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
                            <td>
                                <strong class="text-success">{{ $i->nombre }}</strong>
                            </td>
                            <td>{{ $i->tipo->nombre ?? '—' }}</td>
                            <td>{{ $i->unidadMedida->nombre ?? '—' }}</td>
                            <td>
                                <span class="badge stock-badge-{{ $rowStockClass }}">
                                    {{ number_format($i->stock, 2) }}
                                </span>
                            </td>
                            <td>{{ number_format($i->stockminimo, 2) }}</td>
                            <td class="text-right text-nowrap">
                                <a href="{{ route('insumos.show', $i) }}" class="btn btn-xs btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('inventario.update')
                                <a href="{{ route('insumos.edit', $i) }}" class="btn btn-xs btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('inventario.delete')
                                <form action="{{ route('insumos.destroy', $i) }}" method="POST" class="d-inline on-submit-confirm">
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
        @if($insumos->hasPages())
        <div class="card-footer">
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
                $('html, body').animate({ scrollTop: $('#filtros-insumos').offset().top - 80 }, 300);
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
