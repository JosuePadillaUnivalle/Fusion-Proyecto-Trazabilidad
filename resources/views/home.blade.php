@extends('layouts.app')

@section('title', 'Dashboard | AgroNexus')
@section('page_title', 'Dashboard Principal')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@push('styles')
    <style>
        :root {
            --primary-color: #2c5530;
            --secondary-color: #4a7c59;
            --accent-color: #e8f5e8;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --text-dark: #1a252f;
            --text-light: #6c757d;
            --border-color: #dee2e6;
        }

        .small-box {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .small-box:hover {
            transform: translateY(-2px);
        }

        .small-box .icon {
            font-size: 70px !important;
        }

        .small-box-green {
            background: linear-gradient(135deg, var(--success-color), #34ce57) !important;
        }

        .small-box-blue {
            background: linear-gradient(135deg, var(--info-color), #20c997) !important;
        }

        .small-box-yellow {
            background: linear-gradient(135deg, var(--warning-color), #ffca2c) !important;
        }

        .small-box-red {
            background: linear-gradient(135deg, var(--danger-color), #e74a3b) !important;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--border-color);
        }

        .card-header {
            background: white;
            border-bottom: 2px solid #f1f3f4;
            font-weight: 600;
            color: var(--text-dark);
        }

        .recent-activity-item {
            padding: 12px;
            border-bottom: 1px solid #f1f3f4;
            display: flex;
            align-items: center;
            transition: background 0.3s ease;
        }

        .recent-activity-item:hover {
            background: #f8f9fc;
        }

        .recent-activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 16px;
            color: white;
        }

        .activity-icon.activity-siembra {
            background: var(--success-color);
        }

        .activity-icon.activity-riego {
            background: var(--info-color);
        }

        .activity-icon.activity-cosecha {
            background: var(--warning-color);
        }

        .activity-icon.activity-fumigacion {
            background: var(--danger-color);
        }

        .activity-icon.activity-labranza {
            background: #6c757d;
        }

        .activity-info h6 {
            margin: 0;
            font-weight: 600;
            color: var(--text-dark);
        }

        .activity-info small {
            color: var(--text-light);
            font-size: 12px;
        }

        .weather-widget {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
            color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }

        .weather-widget .weather-temp {
            font-size: 48px;
            font-weight: 300;
            margin: 10px 0;
        }

        .weather-widget .weather-desc {
            opacity: 0.9;
            margin-bottom: 15px;
        }

        .weather-details {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        .quick-actions .btn {
            margin-bottom: 10px;
            border-radius: 8px;
            font-weight: 500;
            padding: 12px 20px;
        }

        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .alert-item {
            display: flex;
            align-items: center;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
            background: white;
            border-left: 4px solid var(--warning-color);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .alert-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--warning-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            font-size: 14px;
        }

        .alert-content h6 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
        }

        .alert-content small {
            color: var(--text-light);
            font-size: 12px;
        }

        .production-chart-container {
            position: relative;
            flex: 1;
            min-height: 0;
            width: 100%;
            overflow: hidden;
        }

        .production-chart-container canvas {
            position: absolute !important;
            left: 0;
            top: 0;
            width: 100% !important;
            height: 100% !important;
        }

        .progress-group {
            margin-bottom: 15px;
        }

        .progress-group .progress {
            height: 8px;
            border-radius: 4px;
        }

        .description-block {
            text-align: center;
            padding: 15px 0;
        }

        .description-header {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 5px 0;
        }

        .description-text {
            font-size: 12px;
            color: var(--text-light);
            text-transform: uppercase;
        }

        .description-percentage {
            font-size: 12px;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    <!-- FILA 1: Métricas Principales (4 small-boxes) -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['lotes_activos'] ?? 0 }}</h3>
                    <p>Lotes Activos</p>
                </div>
                <div class="icon"><i class="fas fa-map-marked-alt"></i></div>
                @can('lotes.view')
                <a href="{{ route('lotes.index') }}" class="small-box-footer">
                    Ver detalles <i class="fas fa-arrow-circle-right"></i>
                </a>
                @else
                <span class="small-box-footer text-muted" style="cursor: default;">
                    Sin acceso a lotes
                </span>
                @endcan
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ number_format($stats['produccion_mes_kg'] ?? 0, 0) }}<span style="font-size: 20px;">kg</span>
                    </h3>
                    <p>Produccion del Mes</p>
                </div>
                <div class="icon"><i class="fas fa-seedling"></i></div>
                <a href="{{ route('producciones.index') }}" class="small-box-footer">
                    Ver reportes <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3>{{ $stats['insumos_stock_bajo'] ?? 0 }}</h3>
                    <p>Alertas de Inventario</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                <a href="{{ route('insumos.index') }}" class="small-box-footer">
                    Revisar inventario <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box small-box-red">
                <div class="inner">
                    <h3>Bs.{{ number_format($stats['ventas_mes'] ?? 0, 0) }}</h3>
                    <p>Ventas del Mes</p>
                </div>
                <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                @can('ventas.view')
                <a href="{{ route('ventas.index') }}" class="small-box-footer">
                    Ver ventas <i class="fas fa-arrow-circle-right"></i>
                </a>
                @else
                <span class="small-box-footer text-muted" style="cursor: default;">
                    Sin acceso a ventas
                </span>
                @endcan
            </div>
        </div>
    </div>

    <!-- FILA 2: Gráfico de Producción + Clima y Acciones Rápidas -->
    <div class="row">
        <!-- Gráfico de Producción -->
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-2"></i>
                        Produccion de los Ultimos 6 Meses
                    </h3>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="production-chart-container">
                        <canvas id="productionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget del Clima y Acciones Rápidas -->
        <div class="col-md-4">
            <!-- Clima Actual -->
            <div class="card mb-3">
                <div class="card-body p-0">
                    <div class="weather-widget" id="weatherWidget">
                        <h5><i class="fas fa-map-marker-alt mr-2"></i><span id="weatherCity">Santa Cruz, Bolivia</span></h5>
                        <div class="weather-temp" id="weatherTemp">--°C</div>
                        <div class="weather-desc" id="weatherDesc">Cargando...</div>
                        <div class="weather-details">
                            <div><i class="fas fa-tint mr-1"></i><span id="weatherHumedad">--%</span> Humedad</div>
                            <div><i class="fas fa-wind mr-1"></i><span id="weatherViento">--</span> km/h</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt mr-2"></i>
                        Acciones Rapidas
                    </h3>
                </div>
                <div class="card-body quick-actions">
                    @can('lotes.create')
                    <a href="{{ route('lotes.create') }}" class="btn btn-primary btn-block">
                        <i class="fas fa-plus mr-2"></i>Nuevo Lote
                    </a>
                    @endcan
                    @can('lotes.view')
                    <a href="{{ route('actividades.create') }}" class="btn btn-success btn-block">
                        <i class="fas fa-clipboard-list mr-2"></i>Registrar Actividad
                    </a>
                    @endcan
                    @can('inventario.view')
                    <a href="{{ route('insumos.index') }}" class="btn btn-info btn-block">
                        <i class="fas fa-warehouse mr-2"></i>Gestionar Inventario
                    </a>
                    @endcan
                    @can('reportes.view')
                    <a href="{{ route('reportes.index') }}" class="btn btn-warning btn-block">
                        <i class="fas fa-chart-bar mr-2"></i>Ver Reportes
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- FILA 3: Actividades Recientes + Alertas del Sistema -->
    <div class="row">
        <!-- Actividades Recientes -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-2"></i>
                        Actividades Recientes
                    </h3>
                </div>
                <div class="card-body p-0">
                    @forelse($actividadesRecientes as $act)
                        @php
                            $tipoNombre = strtolower($act->tipoActividad->nombre ?? 'default');
                            $iconos = [
                                'siembra' => 'fa-seedling',
                                'riego' => 'fa-tint',
                                'cosecha' => 'fa-truck-loading',
                                'fumigación' => 'fa-spray-can',
                                'labranza' => 'fa-tractor',
                            ];
                            $icono = $iconos[$tipoNombre] ?? 'fa-tasks';
                            $clase = 'activity-' . str_replace('ó', 'o', $tipoNombre);
                        @endphp
                        <div class="recent-activity-item">
                            <div class="activity-icon {{ $clase }}">
                                <i class="fas {{ $icono }}"></i>
                            </div>
                            <div class="activity-info">
                                <h6>{{ $act->tipoActividad->nombre ?? 'Actividad' }} - {{ $act->lote->nombre ?? 'Sin lote' }}
                                </h6>
                                <small>{{ $act->fechainicio ? \Carbon\Carbon::parse($act->fechainicio)->diffForHumans() : '' }}
                                    • Por {{ $act->usuario->nombre ?? 'Usuario' }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p class="mb-0">No hay actividades recientes</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Alertas del Sistema -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bell mr-2"></i>
                        Alertas del Sistema
                    </h3>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    @forelse($insumosStockBajo as $insumo)
                        <div class="alert-item">
                            <div class="alert-icon">
                                <i class="fas fa-exclamation"></i>
                            </div>
                            <div class="alert-content">
                                <h6>Stock bajo: {{ $insumo->nombre }}</h6>
                                <small>Stock actual: {{ $insumo->stock }} | Mirimo: {{ $insumo->stockminimo }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-success py-3 my-auto">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <p class="mb-0">No hay alertas pendientes</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- FILA 4: Estado Actual de Lotes + Top Cultivos por Producción -->
    <div class="row">
        <!-- Estado Actual de Lotes (solo si puede gestionar/ver lotes) -->
        @can('lotes.view')
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-map mr-2"></i>
                        Estado Actual de Lotes
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Columna Izquierda: Info Boxes (60%) -->
                        <div class="col-md-7">
                            <div class="row">
                                @foreach($lotesPorEstado as $estado)
                                    @php
                                        $colores = [
                                            'disponible' => 'secondary',
                                            'en preparación' => 'info',
                                            'sembrado' => 'primary',
                                            'en producción' => 'success',
                                            'cosechado' => 'warning',
                                            'en descanso' => 'dark',
                                        ];
                                        $iconos = [
                                            'disponible' => 'fa-pause',
                                            'en preparación' => 'fa-tractor',
                                            'sembrado' => 'fa-seedling',
                                            'en producción' => 'fa-leaf',
                                            'cosechado' => 'fa-cut',
                                            'en descanso' => 'fa-bed',
                                        ];
                                        $color = $colores[$estado->nombre] ?? 'secondary';
                                        $icono = $iconos[$estado->nombre] ?? 'fa-map';
                                    @endphp
                                    <div class="col-md-6">
                                        <div class="info-box bg-{{ $color }}">
                                            <span class="info-box-icon"><i class="fas {{ $icono }}"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">{{ ucfirst($estado->nombre) }}</span>
                                                <span class="info-box-number">{{ $estado->total }} Lotes</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if($lotesPorEstado->isEmpty())
                                    <div class="col-12 text-center text-muted py-3">
                                        <i class="fas fa-map-marked-alt fa-2x mb-2"></i>
                                        <p class="mb-0">No hay lotes registrados</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Columna Derecha: Gráfico Circular (40%) -->
                        <div class="col-md-5">
                            <div style="height: 250px; position: relative;">
                                <canvas id="lotesStateChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endcan

        <!-- Top Cultivos por Producción -->
        <div class="col-md-{{ auth()->user()->can('lotes.view') ? '6' : '12' }}">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-trophy mr-2"></i>
                        Top Cultivos por Produccion
                    </h3>
                </div>
                <div class="card-body d-flex flex-column justify-content-around">
                    @php
                        $coloresProgress = ['success', 'warning', 'info', 'danger', 'primary'];
                        $maxProduccion = $topCultivos->max('total') ?: 1;
                    @endphp
                    @forelse($topCultivos as $index => $cultivo)
                        <div class="progress-group">
                            <span class="float-left"><b>{{ $cultivo->nombre }}</b></span>
                            <span class="float-right"><b>{{ number_format($cultivo->total, 0) }}kg</b></span>
                            <div class="progress progress-sm">
                                <div class="progress-bar bg-{{ $coloresProgress[$index % 5] }}"
                                    style="width: {{ ($cultivo->total / $maxProduccion) * 100 }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-3 my-auto">
                            <i class="fas fa-chart-bar fa-2x mb-2"></i>
                            <p class="mb-0">No hay datos de produccion</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- FILA 5: Resumen Estadístico del Sistema -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>
                        Resumen Estadistico del Sistema
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 col-sm-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-success">
                                    <i class="fas fa-caret-up"></i>
                                </span>
                                <h5 class="description-header">{{ number_format($stats['hectareas_totales'] ?? 0, 0) }}</h5>
                                <span class="description-text">HECTAREAS TOTALES</span>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-warning">
                                    <i class="fas fa-caret-up"></i>
                                </span>
                                <h5 class="description-header">{{ number_format($stats['produccion_mes_kg'] ?? 0, 0) }}</h5>
                                <span class="description-text">KG PRODUCIDOS</span>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-success">
                                    <i class="fas fa-caret-up"></i>
                                </span>
                                <h5 class="description-header">{{ $stats['total_actividades'] ?? 0 }}</h5>
                                <span class="description-text">ACTIVIDADES</span>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-info">
                                    <i class="fas fa-caret-up"></i>
                                </span>
                                <h5 class="description-header">{{ $stats['usuarios'] ?? 0 }}</h5>
                                <span class="description-text">AGRICULTORES</span>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <div class="description-block border-right">
                                <span class="description-percentage text-danger">
                                    <i class="fas fa-caret-down"></i>
                                </span>
                                <h5 class="description-header">{{ $stats['total_insumos'] ?? 0 }}</h5>
                                <span class="description-text">INSUMOS</span>
                            </div>
                        </div>

                        <div class="col-md-2 col-sm-6">
                            <div class="description-block">
                                <span class="description-percentage text-success">
                                    <i class="fas fa-caret-up"></i>
                                </span>
                                <h5 class="description-header">Bs.{{ number_format($stats['ventas_mes'] ?? 0, 0) }}</h5>
                                <span class="description-text">VENTAS</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        $(document).ready(function () {
            // Cargar clima desde API
            function cargarClima() {
                $.get('{{ route("api.clima") }}', function (data) {
                    if (data.success) {
                        $('#weatherTemp').text(data.temperatura + '°C');
                        $('#weatherDesc').text(data.descripcion);
                        $('#weatherHumedad').text(data.humedad + '%');
                        $('#weatherViento').text(data.viento);
                        $('#weatherCity').text(data.ciudad + ', Bolivia');
                    }
                }).fail(function () {
                    $('#weatherDesc').text('Sin conexion');
                });
            }

            cargarClima();
            setInterval(cargarClima, 600000); // Actualizar cada 10 min

            // Gráfico de Producción
            var chartData = @json($chartData);

            if (chartData.labels && chartData.labels.length > 0) {
                var ctx = document.getElementById('productionChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: chartData.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top' }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#e5e5e5' }
                            },
                            x: {
                                grid: { color: '#e5e5e5' }
                            }
                        },
                        elements: {
                            point: { radius: 6, hoverRadius: 8 }
                        }
                    }
                });
            } else {
                $('#productionChart').parent().html('<div class="text-center text-muted py-5"><i class="fas fa-chart-line fa-3x mb-3 d-block"></i><p>No hay datos de produccion aun</p></div>');
            }

            @can('lotes.view')
            // Gráfico de Estado de Lotes (Pie Chart)
            var lotesData = @json($lotesPorEstado);
            if (lotesData.length > 0 && document.getElementById('lotesStateChart')) {
                var ctxPie = document.getElementById('lotesStateChart').getContext('2d');

                var coloresMap = {
                    'disponible': '#6c757d',
                    'en preparación': '#17a2b8',
                    'sembrado': '#007bff',
                    'en producción': '#28a745',
                    'cosechado': '#ffc107',
                    'en descanso': '#343a40'
                };

                new Chart(ctxPie, {
                    type: 'doughnut',
                    data: {
                        labels: lotesData.map(l => l.nombre.charAt(0).toUpperCase() + l.nombre.slice(1)),
                        datasets: [{
                            data: lotesData.map(l => l.total),
                            backgroundColor: lotesData.map(l => coloresMap[l.nombre.toLowerCase()] || '#6c757d'),
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                                labels: { boxWidth: 15, padding: 10 }
                            }
                        },
                        cutout: '60%'
                    }
                });
            }
            @endcan

            // Efectos hover
            $('.small-box').hover(
                function () { $(this).css('transform', 'translateY(-2px)'); },
                function () { $(this).css('transform', 'translateY(0)'); }
            );

            // Efecto de carga
            $('.card').css('opacity', '0').animate({ 'opacity': '1' }, 800);
        });
    </script>
@endpush