@extends('layouts.app')

@section('title', 'Reporte de Ventas | AgroNexus')
@section('page_title', 'Reporte de Ventas')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}" style="color: #2c5530;">Reportes</a></li>
    <li class="breadcrumb-item active">Ventas</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        :root {
            --primary-color: #2c5530;
            --success-color: #28a745;
        }

        .small-box {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .small-box .icon {
            font-size: 70px !important;
        }

        .small-box-ventas {
            background: linear-gradient(135deg, #28a745, #20c997) !important;
        }

        .small-box-cantidad {
            background: linear-gradient(135deg, #17a2b8, #6dd5ed) !important;
        }

        .small-box-trans {
            background: linear-gradient(135deg, #ffc107, #ffca2c) !important;
        }

        .small-box-precio {
            background: linear-gradient(135deg, #6f42c1, #9775fa) !important;
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

        .top-cliente {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fc;
            border-radius: 10px;
            margin-bottom: 10px;
            border-left: 4px solid var(--success-color);
        }

        .top-cliente-rank {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--success-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 15px;
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
        <form method="GET" action="{{ route('reportes.ventas') }}" class="row align-items-end">
            <div class="col-md-3 mb-2">
                <label><i class="fas fa-calendar mr-1"></i>Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ $fechaDesde }}">
            </div>
            <div class="col-md-3 mb-2">
                <label><i class="fas fa-calendar mr-1"></i>Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ $fechaHasta }}">
            </div>
            <div class="col-md-2 mb-2">
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
            <div class="col-md-2 mb-2">
                <label><i class="fas fa-user mr-1"></i>Agricultor</label>
                <select name="usuario_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach($usuarios as $usuario)
                        <option value="{{ $usuario->usuarioid }}" {{ $usuarioId == $usuario->usuarioid ? 'selected' : '' }}>
                            {{ $usuario->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-success btn-block">
                    <i class="fas fa-filter mr-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Métricas -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-ventas">
                <div class="inner">
                    <h3>Bs. {{ number_format($stats['total_ventas'], 2) }}</h3>
                    <p>Total Ventas</p>
                </div>
                <div class="icon"><i class="fas fa-dollar-sign"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-cantidad">
                <div class="inner">
                    <h3>{{ number_format($stats['cantidad_kg'], 0) }}</h3>
                    <p>Cantidad Vendida (kg)</p>
                </div>
                <div class="icon"><i class="fas fa-weight-hanging"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-trans">
                <div class="inner">
                    <h3>{{ $stats['num_transacciones'] }}</h3>
                    <p>Transacciones</p>
                </div>
                <div class="icon"><i class="fas fa-receipt"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-precio">
                <div class="inner">
                    <h3>Bs. {{ number_format($stats['precio_promedio'], 2) }}</h3>
                    <p>Precio Promedio</p>
                </div>
                <div class="icon"><i class="fas fa-tag"></i></div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line mr-2"></i>Tendencia de Ventas</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="ventasTendenciaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Ventas por Cultivo</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="ventasCultivoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Clientes y Tabla -->
    <div class="row">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-trophy mr-2 text-warning"></i>Top Clientes</h3>
                </div>
                <div class="card-body">
                    @forelse($topClientes as $index => $cliente)
                        <div class="top-cliente">
                            <div class="top-cliente-rank">{{ $index + 1 }}</div>
                            <div class="flex-1">
                                <strong>{{ $cliente->cliente }}</strong>
                                <br><small class="text-muted">{{ $cliente->transacciones }} transacciones</small>
                            </div>
                            <div class="text-right">
                                <strong class="text-success">Bs. {{ number_format($cliente->total, 2) }}</strong>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No hay datos de clientes</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-list mr-2"></i>Detalle de Ventas</h3>
                    <div>
                        <a href="{{ route('reportes.exportar', ['tipo' => 'ventas', 'fecha_desde' => $fechaDesde, 'fecha_hasta' => $fechaHasta]) }}"
                            class="btn btn-sm btn-success">
                            <i class="fas fa-file-csv mr-1"></i>Exportar CSV
                        </a>
                        <a href="{{ route('reportes.exportar', ['tipo' => 'ventas', 'fecha_desde' => $fechaDesde, 'fecha_hasta' => $fechaHasta, 'formato' => 'pdf']) }}"
                            class="btn btn-sm btn-danger ml-2">
                            <i class="fas fa-file-pdf mr-1"></i>Exportar PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="ventasTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Cultivo</th>
                                    <th>Lote</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ventas as $venta)
                                    <tr>
                                        <td>{{ $venta->fechaventa instanceof \Carbon\Carbon ? $venta->fechaventa->format('d/m/Y') : $venta->fechaventa }}
                                        </td>
                                        <td>{{ $venta->cliente ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-success">
                                                {{ $venta->produccion->lote->cultivo->nombre ?? '-' }}
                                            </span>
                                        </td>
                                        <td>{{ $venta->produccion->lote->nombre ?? '-' }}</td>
                                        <td>{{ number_format($venta->cantidad, 2) }}
                                            {{ $venta->unidadMedida->abreviatura ?? 'kg' }}
                                        </td>
                                        <td>Bs. {{ number_format($venta->preciounitario, 2) }}</td>
                                        <td><strong>Bs. {{ number_format($venta->total, 2) }}</strong></td>
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
            $('#ventasTable').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' },
                order: [[0, 'desc']],
                pageLength: 10
            });

            // Datos para gráficos
            var ventasPorMes = @json($ventasPorMes);
            var ventasPorCultivo = @json($ventasPorCultivo);

            var meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

            // Gráfico de tendencia
            if (ventasPorMes.length > 0) {
                new Chart(document.getElementById('ventasTendenciaChart'), {
                    type: 'line',
                    data: {
                        labels: ventasPorMes.map(v => meses[parseInt(v.mes) - 1] + ' ' + v.anio),
                        datasets: [{
                            label: 'Ventas (Bs.)',
                            data: ventasPorMes.map(v => parseFloat(v.total)),
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { callback: v => 'Bs. ' + v.toLocaleString() }
                            }
                        }
                    }
                });
            }

            // Gráfico por cultivo
            if (ventasPorCultivo.length > 0) {
                new Chart(document.getElementById('ventasCultivoChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ventasPorCultivo.map(v => v.nombre),
                        datasets: [{
                            data: ventasPorCultivo.map(v => parseFloat(v.total)),
                            backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#dc3545', '#6f42c1', '#fd7e14'],
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