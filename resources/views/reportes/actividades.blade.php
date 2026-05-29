@extends('layouts.app')

@section('title', 'Reporte de Actividades | AgroNexus')
@section('page_title', 'Reporte de Actividades')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}" style="color: #2c5530;">Reportes</a></li>
    <li class="breadcrumb-item active">Actividades</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        :root {
            --primary-color: #2c5530;
            --activity-purple: #6f42c1;
            --activity-green: #28a745;
            --activity-orange: #fd7e14;
        }

        .small-box {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .small-box .icon {
            font-size: 70px !important;
        }

        .small-box-act-total {
            background: linear-gradient(135deg, #6f42c1, #9775fa) !important;
        }

        .small-box-act-done {
            background: linear-gradient(135deg, #28a745, #20c997) !important;
        }

        .small-box-act-pending {
            background: linear-gradient(135deg, #fd7e14, #ffc107) !important;
        }

        .small-box-act-lotes {
            background: linear-gradient(135deg, #17a2b8, #6dd5ed) !important;
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
            height: 300px;
        }

        .filter-card {
            background: #f8f9fc;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .activity-type-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fc;
            border-radius: 10px;
            margin-bottom: 10px;
            border-left: 4px solid var(--activity-purple);
        }

        .activity-type-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            background: var(--activity-purple);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 18px;
            flex-shrink: 0;
        }

        .table thead th {
            background: var(--primary-color);
            color: white;
            border: none;
        }

        .badge-completada {
            background: #28a745;
            color: white;
        }

        .badge-pendiente {
            background: #ffc107;
            color: #1a252f;
        }

        .badge-en-progreso {
            background: #17a2b8;
            color: white;
        }
    </style>
@endpush

@section('content')
    <!-- Filtros -->
    <div class="filter-card">
        <form method="GET" action="{{ route('reportes.actividades') }}" class="row align-items-end">
            <div class="col-md-2 mb-2">
                <label><i class="fas fa-calendar mr-1"></i>Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ $fechaDesde }}">
            </div>
            <div class="col-md-2 mb-2">
                <label><i class="fas fa-calendar mr-1"></i>Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ $fechaHasta }}">
            </div>
            <div class="col-md-3 mb-2">
                <label><i class="fas fa-tags mr-1"></i>Tipo de Actividad</label>
                <select name="tipo_id" class="form-control">
                    <option value="">Todos</option>
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo->tipoactividadid }}" {{ $tipoId == $tipo->tipoactividadid ? 'selected' : '' }}>
                            {{ $tipo->nombre }}
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
                <button type="submit" class="btn btn-block" style="background: #6f42c1; color: white;">
                    <i class="fas fa-filter mr-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Métricas -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-act-total">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Actividades</p>
                </div>
                <div class="icon"><i class="fas fa-tasks"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-act-done">
                <div class="inner">
                    <h3>{{ $stats['completadas'] }}</h3>
                    <p>Completadas</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-act-pending">
                <div class="inner">
                    <h3>{{ $stats['pendientes'] }}</h3>
                    <p>Pendientes</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-act-lotes">
                <div class="inner">
                    <h3>{{ $stats['lotes_activos'] }}</h3>
                    <p>Lotes Activos</p>
                </div>
                <div class="icon"><i class="fas fa-map"></i></div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Actividades por Día</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="actividadesDiaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Por Tipo</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="actividadesTipoChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actividades por tipo y tabla -->
    <div class="row">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list-alt mr-2"></i>Resumen por Tipo</h3>
                </div>
                <div class="card-body">
                    @forelse($actividadesPorTipo as $tipo)
                        @php
                            $icons = [
                                'siembra' => 'seedling',
                                'riego' => 'tint',
                                'fertilización' => 'flask',
                                'fumigación' => 'spray-can',
                                'cosecha' => 'tractor',
                                'poda' => 'cut',
                                'control' => 'bug',
                                'mantenimiento' => 'tools',
                            ];
                            $icon = 'tasks';
                            foreach ($icons as $key => $val) {
                                if (str_contains(strtolower($tipo->nombre), $key)) {
                                    $icon = $val;
                                    break;
                                }
                            }
                        @endphp
                        <div class="activity-type-item">
                            <div class="activity-type-icon">
                                <i class="fas fa-{{ $icon }}"></i>
                            </div>
                            <div class="flex-1">
                                <strong>{{ $tipo->nombre }}</strong>
                            </div>
                            <div>
                                <span class="badge badge-primary" style="font-size: 1rem;">{{ $tipo->total }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No hay actividades registradas</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="fas fa-clipboard-list mr-2"></i>Detalle de Actividades</h3>
                    <div>
                        <a href="{{ route('reportes.exportar', ['tipo' => 'actividades', 'fecha_desde' => $fechaDesde, 'fecha_hasta' => $fechaHasta]) }}"
                            class="btn btn-sm btn-success">
                            <i class="fas fa-file-csv mr-1"></i>Exportar CSV
                        </a>
                        <a href="{{ route('reportes.exportar', ['tipo' => 'actividades', 'fecha_desde' => $fechaDesde, 'fecha_hasta' => $fechaHasta, 'formato' => 'pdf']) }}"
                            class="btn btn-sm btn-danger ml-2">
                            <i class="fas fa-file-pdf mr-1"></i>Exportar PDF
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="actividadesTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Fecha Inicio</th>
                                    <th>Tipo</th>
                                    <th>Lote</th>
                                    <th>Responsable</th>
                                    <th>Estado</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($actividades as $act)
                                    @php
                                        $estado = $act->fechafin ? 'completada' : 'pendiente';
                                    @endphp
                                    <tr>
                                        <td>{{ $act->fechainicio instanceof \Carbon\Carbon ? $act->fechainicio->format('d/m/Y') : $act->fechainicio }}
                                        </td>
                                        <td>
                                            <span class="badge" style="background: #6f42c1; color: white;">
                                                {{ $act->tipoActividad->nombre ?? '-' }}
                                            </span>
                                        </td>
                                        <td><strong>{{ $act->lote->nombre ?? '-' }}</strong></td>
                                        <td>{{ $act->usuario->nombre ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $estado }}">
                                                {{ ucfirst($estado) }}
                                            </span>
                                        </td>
                                        <td>{{ Str::limit($act->descripcion, 40) ?? '-' }}</td>
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
            $('#actividadesTable').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' },
                order: [[0, 'desc']],
                pageLength: 10
            });

            var actividadesPorDia = @json($actividadesPorDia ?? []);
            var actividadesPorTipo = @json($actividadesPorTipo);

            // Gráfico por día
            if (actividadesPorDia.length > 0) {
                new Chart(document.getElementById('actividadesDiaChart'), {
                    type: 'bar',
                    data: {
                        labels: actividadesPorDia.map(a => a.dia),
                        datasets: [{
                            label: 'Actividades',
                            data: actividadesPorDia.map(a => a.total),
                            backgroundColor: '#6f42c1',
                            borderRadius: 5
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, ticks: { stepSize: 1 } }
                        }
                    }
                });
            } else {
                document.getElementById('actividadesDiaChart').parentNode.innerHTML = '<p class="text-muted text-center py-5">No hay datos suficientes</p>';
            }

            // Gráfico por tipo
            if (actividadesPorTipo.length > 0) {
                new Chart(document.getElementById('actividadesTipoChart'), {
                    type: 'doughnut',
                    data: {
                        labels: actividadesPorTipo.map(a => a.nombre),
                        datasets: [{
                            data: actividadesPorTipo.map(a => a.total),
                            backgroundColor: ['#6f42c1', '#28a745', '#fd7e14', '#17a2b8', '#dc3545', '#ffc107', '#20c997', '#e83e8c'],
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