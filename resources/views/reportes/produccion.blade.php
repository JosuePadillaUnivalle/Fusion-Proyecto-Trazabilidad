@extends('layouts.app')

@section('title', 'Reporte de Producción | AgroNexus')
@section('page_title', 'Reporte de Producción')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
    <li class="breadcrumb-item active">Producción</li>
@endsection

@php
    $cultivoSel = $cultivoId ? $cultivos->firstWhere('cultivoid', (int) $cultivoId) : null;
    $loteSel = $loteId ? $lotes->firstWhere('loteid', (int) $loteId) : null;
    $totalCultivosChart = $produccionPorCultivo->sum('total') ?: 1;
    $cultivoLider = $produccionPorCultivo->first();
    $exportParams = array_filter([
        'tipo' => 'produccion',
        'fecha_desde' => $fechaDesde,
        'fecha_hasta' => $fechaHasta,
        'cultivo_id' => $cultivoId,
        'lote_id' => $loteId,
    ]);
    $cultivoBadge = [
        'Tomate' => 'success',
        'Maíz' => 'warning',
        'Maiz' => 'warning',
        'Papa' => 'primary',
        'Cebolla' => 'danger',
        'Lechuga' => 'info',
    ];
    $progressColors = ['warning', 'success', 'info', 'primary', 'danger', 'secondary'];
@endphp

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        .report-produccion .small-box {
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .report-produccion .small-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
        }
        .report-produccion .small-box .icon { font-size: 72px; }
        .report-produccion .small-box-orange {
            background: linear-gradient(135deg, #fd7e14, #ffc107) !important;
            color: #1a252f;
        }
        .report-produccion .small-box-green {
            background: linear-gradient(135deg, #28a745, #34ce57) !important;
            color: #fff;
        }
        .report-produccion .small-box-blue {
            background: linear-gradient(135deg, #17a2b8, #20c997) !important;
            color: #fff;
        }
        .report-produccion .small-box-brand {
            background: linear-gradient(135deg, #2c5530, #4a7c59) !important;
            color: #fff;
        }
        .report-produccion .card {
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }
        .report-produccion .card-header {
            background: #fff;
            border-bottom: 1px solid #f1f3f4;
        }
        .report-produccion .filtros-card .card-body {
            background: linear-gradient(180deg, #fffbf5 0%, #fff 100%);
        }
        .report-produccion .chart-wrap {
            position: relative;
            height: 300px;
        }
        .report-produccion .chart-wrap-sm {
            position: relative;
            height: 190px;
        }
        .report-produccion .products-list .product-img {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .report-produccion .products-list .product-title {
            font-size: 0.95rem;
        }
        .report-produccion .legend-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 6px;
        }
    </style>
@endpush

@section('content')
<div class="report-produccion">

    {{-- Alert superior --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible shadow-sm mb-0">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5 class="mb-1"><i class="icon fas fa-seedling"></i> Panel de producción</h5>
                Analiza cosechas, rendimiento por lote y participación por cultivo en el período seleccionado.
                <div class="mt-2">
                    <a href="{{ route('reportes.index') }}" class="btn btn-sm btn-outline-warning">
                        <i class="fas fa-th-large mr-1"></i> Reportes
                    </a>
                    <a href="{{ route('producciones.index') }}" class="btn btn-sm btn-warning ml-1">
                        <i class="fas fa-tractor mr-1"></i> Ir a producción
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card card-warning card-outline elevation-2 filtros-card mb-4">
        <div class="card-header">
            <h3 class="card-title text-warning">
                <i class="fas fa-sliders-h mr-1"></i> Filtros
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reportes.produccion') }}">
                <div class="row">
                    <div class="col-md-6 col-xl-2 mb-3">
                        <label class="text-muted small text-uppercase mb-1">Desde</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                            </div>
                            <input type="date" name="fecha_desde" class="form-control" value="{{ $fechaDesde }}">
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-2 mb-3">
                        <label class="text-muted small text-uppercase mb-1">Hasta</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-calendar-check"></i></span>
                            </div>
                            <input type="date" name="fecha_hasta" class="form-control" value="{{ $fechaHasta }}">
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3 mb-3">
                        <label class="text-muted small text-uppercase mb-1">Cultivo</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-seedling"></i></span>
                            </div>
                            <select name="cultivo_id" class="form-control">
                                <option value="">Todos</option>
                                @foreach ($cultivos as $cultivo)
                                    <option value="{{ $cultivo->cultivoid }}" @selected($cultivoId == $cultivo->cultivoid)>
                                        {{ $cultivo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3 mb-3">
                        <label class="text-muted small text-uppercase mb-1">Lote</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-map-marked-alt"></i></span>
                            </div>
                            <select name="lote_id" class="form-control">
                                <option value="">Todos</option>
                                @foreach ($lotes as $lote)
                                    <option value="{{ $lote->loteid }}" @selected($loteId == $lote->loteid)>
                                        {{ $lote->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-2 mb-3 d-flex align-items-end">
                        <div class="w-100">
                            <button type="submit" class="btn btn-warning btn-block">
                                <i class="fas fa-search mr-1"></i> Buscar
                            </button>
                            <a href="{{ route('reportes.produccion') }}" class="btn btn-default btn-block btn-sm mt-2">
                                <i class="fas fa-redo mr-1"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer bg-white">
            <i class="fas fa-filter text-warning mr-1"></i>
            <span class="badge badge-warning elevation-1">
                {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
            </span>
            @if ($cultivoSel)
                <span class="badge badge-success elevation-1">{{ $cultivoSel->nombre }}</span>
            @endif
            @if ($loteSel)
                <span class="badge badge-primary elevation-1">{{ $loteSel->nombre }}</span>
            @endif
            @if ($cultivoLider)
                <span class="badge badge-dark elevation-1">
                    <i class="fas fa-crown mr-1"></i>{{ $cultivoLider->nombre }}
                    {{ number_format(($cultivoLider->total / $totalCultivosChart) * 100, 0) }}%
                </span>
            @endif
        </div>
    </div>

    {{-- KPIs --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-orange">
                <div class="inner">
                    <h3>{{ number_format($stats['total_kg'], 0) }}</h3>
                    <p>Total producido (kg)</p>
                </div>
                <div class="icon"><i class="fas fa-weight-hanging"></i></div>
                <a href="#detalle-produccion" class="small-box-footer">
                    Ver detalle <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['num_cosechas'] }}</h3>
                    <p>Cosechas registradas</p>
                </div>
                <div class="icon"><i class="fas fa-tractor"></i></div>
                <span class="small-box-footer">Registros en el período</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $stats['lotes_productivos'] }}</h3>
                    <p>Lotes productivos</p>
                </div>
                <div class="icon"><i class="fas fa-map"></i></div>
                <span class="small-box-footer">Con al menos una cosecha</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-brand">
                <div class="inner">
                    <h3>{{ number_format($stats['promedio_cosecha'], 0) }}</h3>
                    <p>Promedio por cosecha</p>
                </div>
                <div class="icon"><i class="fas fa-chart-bar"></i></div>
                <span class="small-box-footer">kg por registro</span>
            </div>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-warning card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-1 text-warning"></i> Producción mensual
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if ($produccionPorMes->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-chart-bar fa-3x mb-3 text-light"></i>
                            <p class="mb-0">Sin cosechas en el período para graficar.</p>
                        </div>
                    @else
                        <div class="chart-wrap">
                            <canvas id="produccionMesChart"></canvas>
                        </div>
                    @endif
                </div>
                @if ($stats['num_cosechas'] > 0)
                    <div class="card-footer">
                        <div class="row text-center">
                            <div class="col-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">{{ number_format($stats['total_kg'], 0) }}</h5>
                                    <span class="description-text">KG TOTALES</span>
                                </div>
                            </div>
                            <div class="col-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">{{ $stats['lotes_productivos'] }}</h5>
                                    <span class="description-text">LOTES</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="description-block">
                                    <span class="description-percentage text-warning">
                                        <i class="fas fa-chart-line"></i>
                                    </span>
                                    <h5 class="description-header">{{ number_format($stats['promedio_cosecha'], 0) }}</h5>
                                    <span class="description-text">KG / COSECHA</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-success card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-1 text-success"></i> Por cultivo
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if ($produccionPorCultivo->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-seedling fa-2x mb-2 text-light"></i>
                            <p class="mb-0 small">Sin datos por cultivo.</p>
                        </div>
                    @else
                        <div class="chart-wrap-sm mx-auto mb-3" style="max-width: 220px;">
                            <canvas id="produccionCultivoChart"></canvas>
                        </div>
                        @foreach ($produccionPorCultivo as $idx => $item)
                            @php
                                $pct = ($item->total / $totalCultivosChart) * 100;
                                $barColor = $progressColors[$idx % count($progressColors)];
                            @endphp
                            <div class="progress-group mb-2">
                                <span class="float-right"><b>{{ number_format($pct, 0) }}%</b></span>
                                <span class="progress-text">
                                    <span class="legend-dot bg-{{ $barColor }}"></span>{{ $item->nombre }}
                                </span>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-{{ $barColor }}" style="width: {{ $pct }}%"></div>
                                </div>
                                <small class="text-muted">{{ number_format($item->total, 0) }} kg</small>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Top lotes + tabla --}}
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-primary card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy mr-1 text-primary"></i> Top lotes
                    </h3>
                    <span class="badge badge-primary">Top 5</span>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if ($topLotes->isEmpty())
                        <p class="text-muted text-center p-4 mb-0">
                            <i class="fas fa-map fa-2x mb-2 text-light d-block"></i>
                            Sin datos de lotes en este período.
                        </p>
                    @else
                        <ul class="products-list product-list-in-card pl-2 pr-2 mb-0">
                            @foreach ($topLotes as $index => $lote)
                                @php
                                    $rankBadge = match ($index) {
                                        0 => 'warning',
                                        1 => 'secondary',
                                        2 => 'dark',
                                        default => 'light',
                                    };
                                @endphp
                                <li class="item">
                                    <div class="product-img">
                                        <span class="badge badge-{{ $rankBadge }} elevation-2 p-2" style="font-size: 1rem;">
                                            #{{ $index + 1 }}
                                        </span>
                                    </div>
                                    <div class="product-info">
                                        <span class="product-title">
                                            {{ Str::limit($lote->nombre, 22) }}
                                            <span class="badge badge-warning float-right">
                                                {{ number_format($lote->total, 0) }} kg
                                            </span>
                                        </span>
                                        <span class="product-description">
                                            <span class="badge badge-success badge-sm">{{ $lote->cultivo ?? '—' }}</span>
                                            {{ $lote->cosechas }} {{ $lote->cosechas === 1 ? 'cosecha' : 'cosechas' }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8" id="detalle-produccion">
            <div class="card card-success card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-1 text-success"></i> Detalle de producción
                    </h3>
                    <span class="badge badge-success ml-1">{{ $producciones->count() }}</span>
                    <div class="card-tools">
                        <div class="btn-group btn-group-sm mr-2">
                            <a href="{{ route('reportes.exportar', $exportParams) }}" class="btn btn-success">
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
                    @if ($producciones->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3 text-light"></i>
                            <p class="mb-2">No hay cosechas con estos filtros.</p>
                            <a href="{{ route('reportes.produccion') }}" class="btn btn-sm btn-outline-warning">
                                Restablecer filtros
                            </a>
                        </div>
                    @else
                        <table id="produccionTable" class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Lote</th>
                                    <th>Cultivo</th>
                                    <th class="text-right">Cantidad</th>
                                    <th>Destino</th>
                                    <th>Responsable</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($producciones as $prod)
                                    @php
                                        $nombreCultivo = $prod->lote->cultivo->nombre ?? '—';
                                        $badge = $cultivoBadge[$nombreCultivo] ?? 'secondary';
                                    @endphp
                                    <tr>
                                        <td data-order="{{ $prod->fechacosecha instanceof \Carbon\Carbon ? $prod->fechacosecha->format('Y-m-d') : $prod->fechacosecha }}">
                                            <i class="far fa-calendar-alt text-muted mr-1"></i>
                                            {{ $prod->fechacosecha instanceof \Carbon\Carbon ? $prod->fechacosecha->format('d/m/Y') : $prod->fechacosecha }}
                                        </td>
                                        <td><strong>{{ $prod->lote->nombre ?? '—' }}</strong></td>
                                        <td><span class="badge badge-{{ $badge }}">{{ $nombreCultivo }}</span></td>
                                        <td class="text-right">
                                            <strong>{{ number_format($prod->cantidad, 2) }}</strong>
                                            <small class="text-muted">{{ $prod->unidadMedida->abreviatura ?? 'kg' }}</small>
                                        </td>
                                        <td>{{ $prod->destino->nombre ?? '—' }}</td>
                                        <td>{{ $prod->lote->usuario->nombre ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                @if ($producciones->isNotEmpty())
                    <div class="card-footer text-muted small">
                        <i class="fas fa-info-circle mr-1"></i>
                        Usa el buscador para filtrar por lote, cultivo o responsable.
                    </div>
                @endif
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
            @if ($producciones->isNotEmpty())
                $('#produccionTable').DataTable({
                    language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' },
                    order: [[0, 'desc']],
                    pageLength: 10,
                    dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                    columnDefs: [{ targets: [3], className: 'text-right' }]
                });
            @endif

            var meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            var produccionPorMes = @json($produccionPorMes);
            var produccionPorCultivo = @json($produccionPorCultivo);
            var chartColors = ['#fd7e14', '#28a745', '#17a2b8', '#2c5530', '#dc3545', '#6c757d', '#ffc107', '#6f42c1'];

            if (produccionPorMes.length > 0 && document.getElementById('produccionMesChart')) {
                new Chart(document.getElementById('produccionMesChart'), {
                    type: 'bar',
                    data: {
                        labels: produccionPorMes.map(function (p) {
                            return meses[parseInt(p.mes, 10) - 1] + (p.anio ? ' ' + p.anio : '');
                        }),
                        datasets: [{
                            label: 'Producción (kg)',
                            data: produccionPorMes.map(function (p) { return parseFloat(p.total); }),
                            backgroundColor: 'rgba(253, 126, 20, 0.85)',
                            borderColor: '#fd7e14',
                            borderWidth: 2,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { backgroundColor: '#2c5530' }
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: {
                                    callback: function (v) {
                                        return Number(v).toLocaleString('es-BO') + ' kg';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            if (produccionPorCultivo.length > 0 && document.getElementById('produccionCultivoChart')) {
                new Chart(document.getElementById('produccionCultivoChart'), {
                    type: 'doughnut',
                    data: {
                        labels: produccionPorCultivo.map(function (p) { return p.nombre; }),
                        datasets: [{
                            data: produccionPorCultivo.map(function (p) { return parseFloat(p.total); }),
                            backgroundColor: chartColors,
                            borderWidth: 3,
                            borderColor: '#fff',
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '62%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        var t = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                        var pct = t ? ((ctx.parsed / t) * 100).toFixed(1) : 0;
                                        return ' ' + ctx.label + ': ' + ctx.parsed.toLocaleString('es-BO') + ' kg (' + pct + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
