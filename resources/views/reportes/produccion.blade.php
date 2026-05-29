@extends('layouts.app')

@section('title', 'Reporte de Producción | AgroNexus')
@section('page_title', 'Reporte de Producción')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}" style="color: #2c5530;">Reportes</a></li>
    <li class="breadcrumb-item active">Producción</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        :root {
            --primary-color: #2c5530;
            --production-orange: #fa8c16;
            --production-green: #52c41a;
        }

        .small-box {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .small-box .icon {
            font-size: 70px !important;
        }

        .small-box-prod-total {
            background: linear-gradient(135deg, #fa8c16, #ffc53d) !important;
        }

        .small-box-prod-cosechas {
            background: linear-gradient(135deg, #52c41a, #73d13d) !important;
        }

        .small-box-prod-lotes {
            background: linear-gradient(135deg, #1890ff, #40a9ff) !important;
        }

        .small-box-prod-promedio {
            background: linear-gradient(135deg, #722ed1, #9775fa) !important;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            background: white;
            border-bottom: 2px solid #f1f3f4;
            font-weight: 600;
        }

        .chart-container {
            height: 350px;
        }

        .filter-card {
            background: #f8f9fc;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .top-lote {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fc;
            border-radius: 10px;
            margin-bottom: 10px;
            border-left: 4px solid var(--production-orange);
        }

        .top-lote-rank {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--production-orange);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .table thead th {
            background: var(--primary-color);
            color: white;
            border: none;
        }
    </style>
@endpush

@section('content')
    <!-- Filtros -->
    <div class="filter-card">
        <form method="GET" action="{{ route('reportes.produccion') }}" class="row align-items-end">
            <div class="col-md-2 mb-2">
                <label><i class="fas fa-calendar mr-1"></i>Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ $fechaDesde }}">
            </div>
            <div class="col-md-2 mb-2">
                <label><i class="fas fa-calendar mr-1"></i>Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ $fechaHasta }}">
            </div>
            <div class="col-md-3 mb-2">
                <label><i class="fas fa-seedling mr-1"></i>Cultivo</label>
                <select name="cultivo_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach($cultivos as $cultivo)
                        <option value="{{ $cultivo->cultivoid }}" {{ $cultivoId == $cultivo->cultivoid ? 'selected' : '' }}>
                            {{ $cultivo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-2">
                <label><i class="fas fa-map-marker-alt mr-1"></i>Lote</label>
                <select name="lote_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach($lotes as $lote)
                        <option value="{{ $lote->loteid }}" {{ $loteId == $lote->loteid ? 'selected' : '' }}>
                            {{ $lote->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-warning btn-block text-white">
                    <i class="fas fa-filter mr-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Métricas -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-prod-total">
                <div class="inner">
                    <h3>{{ number_format($stats['total_kg'], 0) }}</h3>
                    <p>Total Producido (kg)</p>
                </div>
                <div class="icon"><i class="fas fa-weight-hanging"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-prod-cosechas">
                <div class="inner">
                    <h3>{{ $stats['num_cosechas'] }}</h3>
                    <p>Cosechas Registradas</p>
                </div>
                <div class="icon"><i class="fas fa-tractor"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-prod-lotes">
                <div class="inner">
                    <h3>{{ $stats['lotes_productivos'] }}</h3>
                    <p>Lotes Productivos</p>
                </div>
                <div class="icon"><i class="fas fa-map"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-prod-promedio">
                <div class="inner">
                    <h3>{{ number_format($stats['promedio_cosecha'], 0) }}</h3>
                    <p>Promedio por Cosecha (kg)</p>
                </div>
                <div class="icon"><i class="fas fa-chart-bar"></i></div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Producción Mensual</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="produccionMesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Por Cultivo</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="produccionCultivoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Lotes y Tabla -->
    <div class="row">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-trophy mr-2 text-warning"></i>Top Lotes Productivos</h3>
                </div>
                <div class="card-body">
                    @forelse($topLotes as $index => $lote)
                        <div class="top-lote">
                            <div class="top-lote-rank">{{ $index + 1 }}</div>
                            <div class="flex-1">
                                <strong>{{ $lote->nombre }}</strong>
                                <br>
                                <small class="text-muted">
                                    <span class="badge badge-success">{{ $lote->cultivo ?? '-' }}</span>
                                    {{ $lote->cosechas }} cosechas
                                </small>
                            </div>
                            <div class="text-right">
                                <strong class="text-warning">{{ number_format($lote->total, 0) }} kg</strong>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No hay datos de producción</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-list mr-2"></i>Detalle de Producción</h3>
                    <div>
                        <a href="{{ route('reportes.exportar', ['tipo' => 'produccion', 'fecha_desde' => $fechaDesde, 'fecha_hasta' => $fechaHasta]) }}"
                            class="btn btn-sm btn-success">
                            <i class="fas fa-file-csv mr-1"></i>Exportar CSV
                        </a>
                        <a href="{{ route('reportes.exportar', ['tipo' => 'produccion', 'fecha_desde' => $fechaDesde, 'fecha_hasta' => $fechaHasta, 'formato' => 'pdf']) }}"
                            class="btn btn-sm btn-danger ml-2">
                            <i class="fas fa-file-pdf mr-1"></i>Exportar PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="produccionTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Lote</th>
                                    <th>Cultivo</th>
                                    <th>Cantidad</th>
                                    <th>Destino</th>
                                    <th>Responsable</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($producciones as $prod)
                                    <tr>
                                        <td>{{ $prod->fechacosecha instanceof \Carbon\Carbon ? $prod->fechacosecha->format('d/m/Y') : $prod->fechacosecha }}
                                        </td>
                                        <td><strong>{{ $prod->lote->nombre ?? '-' }}</strong></td>
                                        <td>
                                            <span class="badge badge-warning">
                                                {{ $prod->lote->cultivo->nombre ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ number_format($prod->cantidad, 2) }}</strong>
                                            {{ $prod->unidadMedida->abreviatura ?? 'kg' }}
                                        </td>
                                        <td>{{ $prod->destino->nombre ?? '-' }}</td>
                                        <td>{{ $prod->lote->usuario->nombre ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
            // DataTable
            $('#produccionTable').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' },
                order: [[0, 'desc']],
                pageLength: 10
            });

            var produccionPorMes = @json($produccionPorMes);
            var produccionPorCultivo = @json($produccionPorCultivo);

            var meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

            // Gráfico mensual
            if (produccionPorMes.length > 0) {
                new Chart(document.getElementById('produccionMesChart'), {
                    type: 'bar',
                    data: {
                        labels: produccionPorMes.map(p => meses[parseInt(p.mes) - 1]),
                        datasets: [{
                            label: 'Producción (kg)',
                            data: produccionPorMes.map(p => parseFloat(p.total)),
                            backgroundColor: '#fa8c16',
                            borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { callback: v => v.toLocaleString() + ' kg' }
                            }
                        }
                    }
                });
            }

            // Gráfico por cultivo
            if (produccionPorCultivo.length > 0) {
                new Chart(document.getElementById('produccionCultivoChart'), {
                    type: 'doughnut',
                    data: {
                        labels: produccionPorCultivo.map(p => p.nombre),
                        datasets: [{
                            data: produccionPorCultivo.map(p => parseFloat(p.total)),
                            backgroundColor: ['#fa8c16', '#52c41a', '#1890ff', '#722ed1', '#eb2f96', '#13c2c2'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom' } }
                    }
                });
            }
        });
    </script>
@endpush