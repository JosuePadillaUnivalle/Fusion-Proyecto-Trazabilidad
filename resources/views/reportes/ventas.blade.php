@extends('layouts.app')

@section('title', 'Reporte de Ventas | AgroNexus')
@section('page_title', 'Reporte de Ventas')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
    <li class="breadcrumb-item active">Ventas</li>
@endsection

@php
    $cultivoSel = $cultivoId ? $cultivos->firstWhere('cultivoid', (int) $cultivoId) : null;
    $usuarioSel = $usuarioId ? $usuarios->firstWhere('usuarioid', (int) $usuarioId) : null;
    $totalCultivosChart = $ventasPorCultivo->sum('total') ?: 1;
    $cultivoLider = $ventasPorCultivo->first();
    $exportParams = array_filter([
        'tipo' => 'ventas',
        'fecha_desde' => $fechaDesde,
        'fecha_hasta' => $fechaHasta,
        'cultivo_id' => $cultivoId,
        'usuario_id' => $usuarioId,
    ]);
    $cultivoBadge = [
        'Tomate' => 'success',
        'Maíz' => 'warning',
        'Maiz' => 'warning',
        'Papa' => 'primary',
        'Cebolla' => 'danger',
        'Lechuga' => 'info',
    ];
    $progressColors = ['success', 'info', 'warning', 'danger', 'primary', 'secondary'];
@endphp

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        .report-ventas .small-box {
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .report-ventas .small-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
        }
        .report-ventas .small-box .icon { font-size: 72px; }
        .report-ventas .small-box-green {
            background: linear-gradient(135deg, #28a745, #34ce57) !important;
            color: #fff;
        }
        .report-ventas .small-box-blue {
            background: linear-gradient(135deg, #17a2b8, #20c997) !important;
            color: #fff;
        }
        .report-ventas .small-box-yellow {
            background: linear-gradient(135deg, #fd7e14, #ffc107) !important;
            color: #1a252f;
        }
        .report-ventas .small-box-brand {
            background: linear-gradient(135deg, #2c5530, #4a7c59) !important;
            color: #fff;
        }
        .report-ventas .card {
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        }
        .report-ventas .card-header {
            background: #fff;
            border-bottom: 1px solid #f1f3f4;
        }
        .report-ventas .filtros-card .card-body {
            background: linear-gradient(180deg, #f8faf9 0%, #fff 100%);
        }
        .report-ventas .chart-wrap {
            position: relative;
            height: 300px;
        }
        .report-ventas .chart-wrap-sm {
            position: relative;
            height: 190px;
        }
        .report-ventas .products-list .product-img {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .report-ventas .products-list .product-title {
            font-size: 0.95rem;
        }
        .report-ventas .legend-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 6px;
        }
    </style>
@endpush

@section('content')
<div class="report-ventas">

    {{-- Barra superior: acciones rápidas --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible shadow-sm mb-0">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5 class="mb-1"><i class="icon fas fa-chart-line"></i> Panel de ventas</h5>
                Analiza ingresos, tendencias y clientes del período seleccionado.
                <div class="mt-2">
                    <a href="{{ route('reportes.index') }}" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-th-large mr-1"></i> Reportes
                    </a>
                    @can('ventas.view')
                        <a href="{{ route('ventas.index') }}" class="btn btn-sm btn-success ml-1">
                            <i class="fas fa-shopping-cart mr-1"></i> Ir a ventas
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card card-success card-outline elevation-2 filtros-card mb-4">
        <div class="card-header">
            <h3 class="card-title text-success">
                <i class="fas fa-sliders-h mr-1"></i> Filtros
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reportes.ventas') }}">
                <div class="row">
                    <div class="col-md-6 col-xl-3 mb-3">
                        <label class="text-muted small text-uppercase mb-1">Desde</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-calendar-alt"></i></span>
                            </div>
                            <input type="date" name="fecha_desde" class="form-control" value="{{ $fechaDesde }}">
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3 mb-3">
                        <label class="text-muted small text-uppercase mb-1">Hasta</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="far fa-calendar-check"></i></span>
                            </div>
                            <input type="date" name="fecha_hasta" class="form-control" value="{{ $fechaHasta }}">
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-2 mb-3">
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
                    <div class="col-md-6 col-xl-2 mb-3">
                        <label class="text-muted small text-uppercase mb-1">Agricultor</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <select name="usuario_id" class="form-control">
                                <option value="">Todos</option>
                                @foreach ($usuarios as $usuario)
                                    <option value="{{ $usuario->usuarioid }}" @selected($usuarioId == $usuario->usuarioid)>
                                        {{ $usuario->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-2 mb-3 d-flex align-items-end">
                        <div class="w-100">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-search mr-1"></i> Buscar
                            </button>
                            <a href="{{ route('reportes.ventas') }}" class="btn btn-default btn-block btn-sm mt-2">
                                <i class="fas fa-redo mr-1"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer bg-white">
            <i class="fas fa-filter text-success mr-1"></i>
            <span class="badge badge-success elevation-1">
                {{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}
            </span>
            @if ($cultivoSel)
                <span class="badge badge-primary elevation-1">{{ $cultivoSel->nombre }}</span>
            @endif
            @if ($usuarioSel)
                <span class="badge badge-info elevation-1">{{ $usuarioSel->nombre }}</span>
            @endif
            @if ($cultivoLider)
                <span class="badge badge-warning elevation-1">
                    <i class="fas fa-crown mr-1"></i>{{ $cultivoLider->nombre }}
                    {{ number_format(($cultivoLider->total / $totalCultivosChart) * 100, 0) }}%
                </span>
            @endif
        </div>
    </div>

    {{-- KPIs (small-box + estilo dashboard) --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>Bs. {{ number_format($stats['total_ventas'], 0) }}</h3>
                    <p>Total ventas</p>
                </div>
                <div class="icon"><i class="fas fa-hand-holding-usd"></i></div>
                <a href="#detalle-ventas" class="small-box-footer">
                    Ver detalle <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ number_format($stats['cantidad_kg'], 0) }}</h3>
                    <p>Cantidad vendida</p>
                </div>
                <div class="icon"><i class="fas fa-balance-scale"></i></div>
                <span class="small-box-footer">Unidades en el período</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3>{{ $stats['num_transacciones'] }}</h3>
                    <p>Transacciones</p>
                </div>
                <div class="icon"><i class="fas fa-receipt"></i></div>
                <span class="small-box-footer">
                    Ticket: Bs. {{ number_format($stats['ticket_promedio'], 2) }}
                </span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-brand">
                <div class="inner">
                    <h3>Bs. {{ number_format($stats['precio_promedio'], 2) }}</h3>
                    <p>Precio unit. prom.</p>
                </div>
                <div class="icon"><i class="fas fa-tags"></i></div>
                <span class="small-box-footer">Promedio por unidad</span>
            </div>
        </div>
    </div>

    {{-- Gráficos --}}
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-area mr-1 text-primary"></i> Tendencia mensual
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if ($ventasPorMes->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-chart-line fa-3x mb-3 text-light"></i>
                            <p class="mb-0">Sin ventas en el período para graficar.</p>
                        </div>
                    @else
                        <div class="chart-wrap">
                            <canvas id="ventasTendenciaChart"></canvas>
                        </div>
                    @endif
                </div>
                @if ($stats['num_transacciones'] > 0)
                    <div class="card-footer">
                        <div class="row text-center">
                            <div class="col-4 border-right">
                                <div class="description-block">
                                    <span class="description-percentage text-success">
                                        <i class="fas fa-caret-up"></i> {{ $ventas->count() }}
                                    </span>
                                    <h5 class="description-header">Registros</h5>
                                    <span class="description-text">EN TABLA</span>
                                </div>
                            </div>
                            <div class="col-4 border-right">
                                <div class="description-block">
                                    <h5 class="description-header">Bs. {{ number_format($stats['ticket_promedio'], 0) }}</h5>
                                    <span class="description-text">TICKET PROM.</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="description-block">
                                    <h5 class="description-header">{{ $ventasPorCultivo->count() }}</h5>
                                    <span class="description-text">CULTIVOS</span>
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
                    @if ($ventasPorCultivo->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-seedling fa-2x mb-2 text-light"></i>
                            <p class="mb-0 small">Sin datos por cultivo.</p>
                        </div>
                    @else
                        <div class="chart-wrap-sm mx-auto mb-3" style="max-width: 220px;">
                            <canvas id="ventasCultivoChart"></canvas>
                        </div>
                        @foreach ($ventasPorCultivo as $idx => $item)
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
                                <small class="text-muted">Bs. {{ number_format($item->total, 2) }}</small>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Top clientes + tabla --}}
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-warning card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy mr-1 text-warning"></i> Top clientes
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-warning">Top 5</span>
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if ($topClientes->isEmpty())
                        <p class="text-muted text-center p-4 mb-0">
                            <i class="fas fa-user-friends fa-2x mb-2 text-light d-block"></i>
                            Sin clientes en este período.
                        </p>
                    @else
                        <ul class="products-list product-list-in-card pl-2 pr-2 mb-0">
                            @foreach ($topClientes as $index => $cliente)
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
                                            {{ Str::limit($cliente->cliente, 28) }}
                                            <span class="badge badge-success float-right">
                                                Bs. {{ number_format($cliente->total, 0) }}
                                            </span>
                                        </span>
                                        <span class="product-description">
                                            {{ $cliente->transacciones }} {{ $cliente->transacciones === 1 ? 'venta' : 'ventas' }}
                                            · Bs. {{ number_format($cliente->total, 2) }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8" id="detalle-ventas">
            <div class="card card-success card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-table mr-1 text-success"></i> Detalle de ventas
                    </h3>
                    <span class="badge badge-success ml-1">{{ $ventas->count() }}</span>
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
                    @if ($ventas->isEmpty())
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3 text-light"></i>
                            <p class="mb-2">No hay ventas con estos filtros.</p>
                            <a href="{{ route('reportes.ventas') }}" class="btn btn-sm btn-outline-success">
                                Restablecer filtros
                            </a>
                        </div>
                    @else
                        <table id="ventasTable" class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Cultivo</th>
                                    <th>Lote</th>
                                    <th class="text-right">Cantidad</th>
                                    <th class="text-right">P. unit.</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ventas as $venta)
                                    @php
                                        $nombreCultivo = $venta->produccion->lote->cultivo->nombre ?? '—';
                                        $badge = $cultivoBadge[$nombreCultivo] ?? 'secondary';
                                    @endphp
                                    <tr>
                                        <td data-order="{{ $venta->fechaventa instanceof \Carbon\Carbon ? $venta->fechaventa->format('Y-m-d') : $venta->fechaventa }}">
                                            <i class="far fa-calendar-alt text-muted mr-1"></i>
                                            {{ $venta->fechaventa instanceof \Carbon\Carbon ? $venta->fechaventa->format('d/m/Y') : $venta->fechaventa }}
                                        </td>
                                        <td>{{ $venta->cliente ?? '—' }}</td>
                                        <td><span class="badge badge-{{ $badge }}">{{ $nombreCultivo }}</span></td>
                                        <td><span class="text-muted">{{ $venta->produccion->lote->nombre ?? '—' }}</span></td>
                                        <td class="text-right">
                                            {{ number_format($venta->cantidad, 2) }}
                                            <small class="text-muted">{{ $venta->unidadMedida->abreviatura ?? 'kg' }}</small>
                                        </td>
                                        <td class="text-right text-muted">Bs. {{ number_format($venta->preciounitario, 2) }}</td>
                                        <td class="text-right">
                                            <strong class="text-success">Bs. {{ number_format($venta->total, 2) }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                @if ($ventas->isNotEmpty())
                    <div class="card-footer text-muted small">
                        <i class="fas fa-info-circle mr-1"></i>
                        Usa el buscador de la tabla para filtrar por cliente o cultivo.
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
            @if ($ventas->isNotEmpty())
                $('#ventasTable').DataTable({
                    language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' },
                    order: [[0, 'desc']],
                    pageLength: 10,
                    dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                    columnDefs: [{ targets: [4, 5, 6], className: 'text-right' }]
                });
            @endif

            var meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            var ventasPorMes = @json($ventasPorMes);
            var ventasPorCultivo = @json($ventasPorCultivo);
            var chartColors = ['#28a745', '#17a2b8', '#ffc107', '#dc3545', '#2c5530', '#6c757d', '#20c997', '#6f42c1'];

            if (ventasPorMes.length > 0 && document.getElementById('ventasTendenciaChart')) {
                new Chart(document.getElementById('ventasTendenciaChart'), {
                    type: 'line',
                    data: {
                        labels: ventasPorMes.map(function (v) {
                            return meses[parseInt(v.mes, 10) - 1] + ' ' + v.anio;
                        }),
                        datasets: [{
                            label: 'Ventas (Bs.)',
                            data: ventasPorMes.map(function (v) { return parseFloat(v.total); }),
                            borderColor: '#2c5530',
                            backgroundColor: 'rgba(44, 85, 48, 0.08)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: '#28a745',
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#2c5530',
                                callbacks: {
                                    label: function (ctx) {
                                        return ' Bs. ' + ctx.parsed.y.toLocaleString('es-BO', { minimumFractionDigits: 2 });
                                    }
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false } },
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(0,0,0,0.05)' },
                                ticks: {
                                    callback: function (v) {
                                        return 'Bs. ' + Number(v).toLocaleString('es-BO');
                                    }
                                }
                            }
                        }
                    }
                });
            }

            if (ventasPorCultivo.length > 0 && document.getElementById('ventasCultivoChart')) {
                new Chart(document.getElementById('ventasCultivoChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ventasPorCultivo.map(function (v) { return v.nombre; }),
                        datasets: [{
                            data: ventasPorCultivo.map(function (v) { return parseFloat(v.total); }),
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
                                        var p = t ? ((ctx.parsed / t) * 100).toFixed(1) : 0;
                                        return ' ' + ctx.label + ': Bs. ' + ctx.parsed.toLocaleString('es-BO') + ' (' + p + '%)';
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
