@extends('layouts.app')

@section('title', 'Reporte de Inventario | Fusion-Proyectos')
@section('page_title', 'Reporte de Inventario de Insumos')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
    <li class="breadcrumb-item active">Inventario</li>
@endsection

@php
    $exportParams = ['tipo' => 'inventario'];
    $iconColors = ['info', 'primary', 'secondary', 'success', 'warning', 'indigo'];
    $pctCritico = $stats['total_insumos'] > 0
        ? round(($stats['stock_critico'] / $stats['total_insumos']) * 100)
        : 0;
@endphp

@push('styles')
@include('partials.modulo-reportes-styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
@endpush

@section('content')
<div class="modulo-rep">

@include('reportes.partials.toolbar', [
    'icono' => 'fa-boxes',
    'titulo' => 'Reporte de inventario',
    'descripcion' => 'Control de stock, alertas de reposición, almacenes y consumo reciente.',
    'tema' => 'primary',
    'moduloRuta' => route('insumos.index'),
    'moduloLabel' => 'Gestionar insumos',
    'moduloIcono' => 'fa-cubes',
])
@include('reportes.partials.filtros-inventario')

    {{-- KPIs --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $stats['total_insumos'] }}</h3>
                    <p>Total de insumos</p>
                </div>
                <div class="icon"><i class="fas fa-boxes"></i></div>
                <a href="{{ route('insumos.index') }}" class="small-box-footer">
                    Ver catálogo <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-red">
                <div class="inner">
                    <h3>{{ $stats['stock_critico'] }}</h3>
                    <p>Stock crítico</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                <a href="#alertas-stock" class="small-box-footer">
                    Ver alertas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['stock_disponible'] }}</h3>
                    <p>Stock disponible</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <span class="small-box-footer">Stock por encima del umbral crítico ({{ $umbral ?? 5 }})</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3>{{ $stats['stock_atencion'] ?? 0 }}</h3>
                    <p>En atención</p>
                </div>
                <div class="icon"><i class="fas fa-bell"></i></div>
                <span class="small-box-footer">Entre {{ ($umbral ?? 5) + 1 }} y {{ ($umbral ?? 5) * 2 }} unidades</span>
            </div>
        </div>
    </div>

    {{-- Resumen por tipo: info-box --}}
    @if ($insumosPorTipo->isNotEmpty())
        <div class="row mb-2">
            @foreach ($insumosPorTipo as $idx => $tipo)
                @php $iconBg = $iconColors[$idx % count($iconColors)]; @endphp
                <div class="col-md-4 col-sm-6">
                    <div class="info-box mb-3 shadow-sm">
                        <span class="info-box-icon bg-{{ $iconBg }} elevation-1">
                            <i class="fas fa-layer-group"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">{{ $tipo->tipo ?? 'Sin tipo' }}</span>
                            <span class="info-box-number">
                                {{ number_format($tipo->stock ?? 0, 0) }} u. · {{ $tipo->cantidad }} ítems
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Gráfico + almacenes --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-1 text-primary"></i> Stock por tipo de insumo
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if ($insumosPorTipo->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-chart-bar fa-3x mb-3 text-light"></i>
                            <p class="mb-0">No hay datos para el gráfico.</p>
                        </div>
                    @else
                        <div class="chart-wrap">
                            <canvas id="stockTipoChart"></canvas>
                        </div>
                    @endif
                </div>
                @if ($stats['total_insumos'] > 0)
                    <div class="card-footer">
                        <div class="row text-center">
                            <div class="col-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">{{ $stats['total_insumos'] }}</h5>
                                    <span class="description-text">INSUMOS</span>
                                </div>
                            </div>
                            <div class="col-4 border-right">
                                <div class="description-block">
                                    <span class="description-percentage text-danger">
                                        <i class="fas fa-caret-down"></i> {{ $pctCritico }}%
                                    </span>
                                    <h5 class="description-header">{{ $stats['stock_critico'] }}</h5>
                                    <span class="description-text">EN CRÍTICO</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="description-block">
                                    <h5 class="description-header">Bs. {{ number_format($stats['valor_total'], 0) }}</h5>
                                    <span class="description-text">VALOR TOTAL</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-warning card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-warehouse mr-1 text-warning"></i> Ocupación de almacenes
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0" style="max-height: 380px; overflow-y: auto;">
                    @forelse ($stockAlmacenes as $almacen)
                        @php
                            $porcentaje = $almacen->capacidadmaxima > 0
                                ? min(($almacen->stockactual / $almacen->capacidadmaxima) * 100, 100)
                                : 0;
                            $barClass = $porcentaje >= 90 ? 'danger' : ($porcentaje >= 70 ? 'warning' : 'success');
                        @endphp
                        <div class="p-3 border-bottom">
                            <div class="progress-group mb-1">
                                <span class="float-right"><b>{{ number_format($porcentaje, 0) }}%</b></span>
                                <span class="progress-text font-weight-bold">{{ $almacen->nombre }}</span>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-{{ $barClass }}" style="width: {{ $porcentaje }}%"></div>
                                </div>
                            </div>
                            <small class="text-muted">
                                {{ number_format($almacen->stockactual, 0) }} / {{ number_format($almacen->capacidadmaxima, 0) }} kg
                            </small>
                        </div>
                    @empty
                        <p class="text-muted text-center p-4 mb-0">
                            <i class="fas fa-warehouse fa-2x mb-2 text-light d-block"></i>
                            No hay almacenes configurados.
                        </p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Alertas + consumo --}}
    <div class="row">
        <div class="col-lg-5" id="alertas-stock">
            <div class="card card-danger card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bell mr-1 text-danger"></i> Alertas de stock bajo
                    </h3>
                    <span class="badge badge-danger ml-1">{{ $alertasStock->count() }}</span>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if ($alertasStock->isEmpty())
                        <p class="text-muted text-center p-4 mb-0">
                            <i class="fas fa-check-circle text-success fa-3x mb-2 d-block"></i>
                            No hay insumos en nivel crítico.
                        </p>
                    @else
                        <ul class="products-list product-list-in-card pl-2 pr-2 mb-0">
                            @foreach ($alertasStock as $insumo)
                                @php $umbralAlerta = $umbral ?? \App\Support\InsumoCatalogo::UMBRAL_ALERTA_STOCK; @endphp
                                <li class="item">
                                    <div class="product-img">
                                        <span class="badge badge-danger elevation-2 p-2">
                                            <i class="fas fa-exclamation"></i>
                                        </span>
                                    </div>
                                    <div class="product-info">
                                        <span class="product-title">
                                            {{ Str::limit($insumo->nombre, 26) }}
                                            <a href="{{ route('insumos.index') }}" class="btn btn-xs btn-outline-danger float-right">
                                                Revisar
                                            </a>
                                        </span>
                                        <span class="product-description">
                                            {{ $insumo->tipo->nombre ?? '—' }} ·
                                            Stock <strong class="text-danger">{{ number_format($insumo->stock, 2) }}</strong>
                                            {{ $insumo->unidadMedida->abreviatura ?? '' }}
                                            · alerta ≤ {{ $umbralAlerta }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                @if ($alertasStock->count() >= 10)
                    <div class="card-footer text-center">
                        <a href="{{ route('insumos.index') }}" class="text-danger">
                            Ver todos los insumos <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-info card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-1 text-info"></i> Consumo en lotes
                    </h3>
                    <span class="badge badge-info">30 días</span>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body" style="max-height: 420px; overflow-y: auto;">
                    @if ($consumoReciente->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-flask fa-2x mb-2 text-light d-block"></i>
                            <p class="mb-0">No hay consumos en el último mes.</p>
                        </div>
                    @else
                        <div class="timeline timeline-inverse mb-0">
                            @foreach ($consumoReciente as $mov)
                                @php
                                    $fecha = $mov->fechauo instanceof \Carbon\Carbon
                                        ? $mov->fechauo
                                        : \Carbon\Carbon::parse($mov->fechauo);
                                @endphp
                                <div>
                                    <i class="fas fa-seedling bg-info"></i>
                                    <div class="timeline-item shadow-sm">
                                        <span class="time">
                                            <i class="fas fa-clock"></i> {{ $fecha->format('d/m/Y') }}
                                        </span>
                                        <h3 class="timeline-header no-border">
                                            <strong>{{ $mov->insumo->nombre ?? 'Insumo' }}</strong>
                                            <span class="text-muted">· Lote {{ $mov->lote->nombre ?? '—' }}</span>
                                        </h3>
                                        <div class="timeline-body">
                                            <span class="badge badge-light border">
                                                {{ number_format($mov->cantidad ?? 0, 2) }}
                                                {{ $mov->insumo->unidadMedida->abreviatura ?? '' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla detalle --}}
    <div class="row" id="detalle-inventario">
        <div class="col-12">
            <div class="card card-success card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-1 text-success"></i> Detalle de inventario
                    </h3>
                    <span class="badge badge-success ml-1">{{ $insumos->count() }}</span>
                    <div class="card-tools">
                        <div class="btn-group btn-group-sm mr-2">
                            <a href="{{ route('reportes.exportar', array_merge($exportParams, ['formato' => 'csv'])) }}"
                                class="btn btn-success">
                                <i class="fas fa-file-csv"></i> CSV
                            </a>
                            <a href="{{ route('reportes.exportar', array_merge($exportParams, ['formato' => 'pdf'])) }}"
                                class="btn btn-danger">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                        </div>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table id="inventarioTable" class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Insumo</th>
                                <th>Tipo</th>
                                <th class="text-right">Stock actual</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($insumos as $insumo)
                                @php
                                    $clase = \App\Support\InsumoCatalogo::claseStock((float) $insumo->stock);
                                    if ($clase === 'low') {
                                        $estado = 'Crítico';
                                        $badge = 'danger';
                                        $orden = 1;
                                    } elseif ($clase === 'medium') {
                                        $estado = 'Atención';
                                        $badge = 'warning';
                                        $orden = 2;
                                    } else {
                                        $estado = 'Normal';
                                        $badge = 'success';
                                        $orden = 3;
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $insumo->nombre }}</strong>
                                        @if ($insumo->descripcion)
                                            <br><small class="text-muted">{{ Str::limit($insumo->descripcion, 45) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $insumo->tipo->nombre ?? '—' }}</span>
                                    </td>
                                    <td class="text-right" data-order="{{ $insumo->stock }}">
                                        {{ number_format($insumo->stock, 2) }}
                                        <small class="text-muted">{{ $insumo->unidadMedida->abreviatura ?? '' }}</small>
                                    </td>
                                    <td data-order="{{ $orden }}">
                                        <span class="badge badge-{{ $badge }}">{{ $estado }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-muted small">
                    <i class="fas fa-info-circle mr-1"></i>
                    Ordenado por estado: críticos primero. Usa el buscador para filtrar por nombre o tipo.
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(function () {
            $('#inventarioTable').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' },
                order: [[4, 'asc']],
                pageLength: 10,
                dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                columnDefs: [
                    { targets: [2, 3, 5], className: 'text-right' }
                ]
            });

            var tiposData = @json($insumosPorTipo);
            if (tiposData.length > 0 && document.getElementById('stockTipoChart')) {
                new Chart(document.getElementById('stockTipoChart'), {
                    type: 'bar',
                    data: {
                        labels: tiposData.map(function (t) { return t.tipo || 'Otros'; }),
                        datasets: [{
                            label: 'Stock total',
                            data: tiposData.map(function (t) { return parseFloat(t.stock) || 0; }),
                            backgroundColor: 'rgba(44, 85, 48, 0.75)',
                            borderColor: '#2c5530',
                            borderWidth: 2,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#2c5530'
                            }
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.05)' }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
