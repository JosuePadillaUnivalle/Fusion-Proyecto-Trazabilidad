@extends('layouts.app')

@section('title', 'Reporte de Actividades | AgroNexus')
@section('page_title', 'Reporte de Actividades')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('reportes.index') }}">Reportes</a></li>
    <li class="breadcrumb-item active">Actividades</li>
@endsection

@php
    $tipoSel = $tipoId ? collect($tipos)->firstWhere('tipoactividadid', (int) $tipoId) : null;
    $loteSel = $loteId ? $lotes->firstWhere('loteid', (int) $loteId) : null;
    $totalTiposChart = $actividadesPorTipo->sum('total') ?: 1;
    $tipoLider = $actividadesPorTipo->first();
    $exportParams = array_filter([
        'tipo' => 'actividades',
        'fecha_desde' => $fechaDesde,
        'fecha_hasta' => $fechaHasta,
        'tipo_id' => $tipoId,
        'lote_id' => $loteId,
    ]);
    $progressColors = ['purple', 'success', 'warning', 'info', 'danger', 'primary', 'secondary'];
@endphp

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        .report-actividades .small-box {
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .report-actividades .small-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.12);
        }
        .report-actividades .small-box .icon { font-size: 72px; }
        .report-actividades .small-box-purple { background: linear-gradient(135deg, #6f42c1, #9775fa) !important; color: #fff; }
        .report-actividades .small-box-green { background: linear-gradient(135deg, #28a745, #20c997) !important; color: #fff; }
        .report-actividades .small-box-orange { background: linear-gradient(135deg, #fd7e14, #ffc107) !important; color: #1a252f; }
        .report-actividades .small-box-blue { background: linear-gradient(135deg, #17a2b8, #6dd5ed) !important; color: #fff; }
        .report-actividades .card { border-radius: 10px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06); }
        .report-actividades .card-header { background: #fff; border-bottom: 1px solid #f1f3f4; }
        .report-actividades .filtros-card .card-body { background: linear-gradient(180deg, #f7f4fd 0%, #fff 100%); }
        .report-actividades .chart-wrap { position: relative; height: 300px; }
        .report-actividades .chart-wrap-sm { position: relative; height: 190px; }
        .report-actividades .products-list .product-img {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .report-actividades .products-list .product-title { font-size: 0.95rem; }
        .report-actividades .legend-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 6px;
        }
    </style>
@endpush

@section('content')
<div class="report-actividades">
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-secondary alert-dismissible shadow-sm mb-0">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5 class="mb-1"><i class="icon fas fa-tasks"></i> Panel de actividades</h5>
                Revisa ejecución de actividades, pendientes por lote y distribución por tipo.
                <div class="mt-2">
                    <a href="{{ route('reportes.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-th-large mr-1"></i> Reportes</a>
                    <a href="{{ route('actividades.index') }}" class="btn btn-sm btn-secondary ml-1"><i class="fas fa-clipboard-check mr-1"></i> Módulo actividades</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-secondary card-outline elevation-2 filtros-card mb-4">
        <div class="card-header">
            <h3 class="card-title text-secondary"><i class="fas fa-sliders-h mr-1"></i> Filtros</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reportes.actividades') }}">
                <div class="row">
                    <div class="col-md-6 col-xl-2 mb-3">
                        <label class="text-muted small text-uppercase mb-1">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control" value="{{ $fechaDesde }}">
                    </div>
                    <div class="col-md-6 col-xl-2 mb-3">
                        <label class="text-muted small text-uppercase mb-1">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control" value="{{ $fechaHasta }}">
                    </div>
                    <div class="col-md-6 col-xl-3 mb-3">
                        <label class="text-muted small text-uppercase mb-1">Tipo</label>
                        <select name="tipo_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach($tipos as $tipo)
                                <option value="{{ $tipo->tipoactividadid }}" @selected($tipoId == $tipo->tipoactividadid)>{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-xl-3 mb-3">
                        <label class="text-muted small text-uppercase mb-1">Lote</label>
                        <select name="lote_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach($lotes as $lote)
                                <option value="{{ $lote->loteid }}" @selected($loteId == $lote->loteid)>{{ $lote->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-xl-2 mb-3 d-flex align-items-end">
                        <div class="w-100">
                            <button type="submit" class="btn btn-secondary btn-block"><i class="fas fa-search mr-1"></i> Buscar</button>
                            <a href="{{ route('reportes.actividades') }}" class="btn btn-default btn-block btn-sm mt-2"><i class="fas fa-redo mr-1"></i> Limpiar</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer bg-white">
            <i class="fas fa-filter text-secondary mr-1"></i>
            <span class="badge badge-secondary elevation-1">{{ \Carbon\Carbon::parse($fechaDesde)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($fechaHasta)->format('d/m/Y') }}</span>
            @if($tipoSel)<span class="badge badge-primary elevation-1">{{ $tipoSel->nombre }}</span>@endif
            @if($loteSel)<span class="badge badge-info elevation-1">{{ $loteSel->nombre }}</span>@endif
            @if($tipoLider)<span class="badge badge-dark elevation-1"><i class="fas fa-crown mr-1"></i>{{ $tipoLider->nombre }} {{ number_format(($tipoLider->total / $totalTiposChart) * 100, 0) }}%</span>@endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-purple">
                <div class="inner"><h3>{{ $stats['total'] }}</h3><p>Total actividades</p></div>
                <div class="icon"><i class="fas fa-tasks"></i></div>
                <a href="#detalle-actividades" class="small-box-footer">Ver detalle <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner"><h3>{{ $stats['completadas'] }}</h3><p>Completadas</p></div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <span class="small-box-footer">Con fecha de fin</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-orange">
                <div class="inner"><h3>{{ $stats['pendientes'] }}</h3><p>Pendientes</p></div>
                <div class="icon"><i class="fas fa-clock"></i></div>
                <span class="small-box-footer">Aún sin completar</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner"><h3>{{ $stats['lotes_activos'] }}</h3><p>Lotes activos</p></div>
                <div class="icon"><i class="fas fa-map-marked-alt"></i></div>
                <span class="small-box-footer">Con actividad registrada</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-secondary card-outline elevation-2">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-bar mr-1 text-secondary"></i> Actividades por día</h3></div>
                <div class="card-body">
                    @if($actividadesPorDia->isEmpty())
                        <div class="text-center text-muted py-5"><i class="fas fa-chart-bar fa-3x mb-3 text-light"></i><p class="mb-0">No hay datos para el gráfico.</p></div>
                    @else
                        <div class="chart-wrap"><canvas id="actividadesDiaChart"></canvas></div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-primary card-outline elevation-2">
                <div class="card-header"><h3 class="card-title"><i class="fas fa-chart-pie mr-1 text-primary"></i> Por tipo</h3></div>
                <div class="card-body">
                    @if($actividadesPorTipo->isEmpty())
                        <div class="text-center text-muted py-4"><i class="fas fa-list-ul fa-2x mb-2 text-light"></i><p class="mb-0 small">Sin datos por tipo.</p></div>
                    @else
                        <div class="chart-wrap-sm mx-auto mb-3" style="max-width: 220px;"><canvas id="actividadesTipoChart"></canvas></div>
                        @foreach($actividadesPorTipo as $idx => $tipo)
                            @php
                                $pct = ($tipo->total / $totalTiposChart) * 100;
                                $color = $progressColors[$idx % count($progressColors)];
                            @endphp
                            <div class="progress-group mb-2">
                                <span class="float-right"><b>{{ number_format($pct, 0) }}%</b></span>
                                <span class="progress-text"><span class="legend-dot bg-{{ $color }}"></span>{{ $tipo->nombre }}</span>
                                <div class="progress progress-sm"><div class="progress-bar bg-{{ $color }}" style="width: {{ $pct }}%"></div></div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card card-primary card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list-alt mr-1 text-primary"></i> Resumen por tipo</h3>
                    <span class="badge badge-primary">Top {{ min($actividadesPorTipo->count(), 8) }}</span>
                </div>
                <div class="card-body p-0">
                    @if($actividadesPorTipo->isEmpty())
                        <p class="text-muted text-center p-4 mb-0">Sin actividades registradas.</p>
                    @else
                        <ul class="products-list product-list-in-card pl-2 pr-2 mb-0">
                            @foreach($actividadesPorTipo->take(8) as $index => $tipo)
                                <li class="item">
                                    <div class="product-img">
                                        <span class="badge badge-secondary elevation-2 p-2" style="font-size: .95rem;">#{{ $index + 1 }}</span>
                                    </div>
                                    <div class="product-info">
                                        <span class="product-title">{{ Str::limit($tipo->nombre, 28) }} <span class="badge badge-primary float-right">{{ $tipo->total }}</span></span>
                                        <span class="product-description">{{ number_format(($tipo->total / $totalTiposChart) * 100, 1) }}% del total</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8" id="detalle-actividades">
            <div class="card card-success card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-table mr-1 text-success"></i> Detalle de actividades</h3>
                    <span class="badge badge-success ml-1">{{ $actividades->count() }}</span>
                    <div class="card-tools">
                        <div class="btn-group btn-group-sm mr-2">
                            <a href="{{ route('reportes.exportar', $exportParams) }}" class="btn btn-success"><i class="fas fa-file-csv"></i> CSV</a>
                            <a href="{{ route('reportes.exportar', array_merge($exportParams, ['formato' => 'pdf'])) }}" class="btn btn-danger"><i class="fas fa-file-pdf"></i> PDF</a>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    @if($actividades->isEmpty())
                        <div class="text-center text-muted py-5"><i class="fas fa-inbox fa-3x mb-3 text-light"></i><p class="mb-2">No hay actividades con estos filtros.</p></div>
                    @else
                        <table id="actividadesTable" class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fecha inicio</th>
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
                                        $estadoClass = $act->fechafin ? 'success' : 'warning';
                                        $estadoLabel = $act->fechafin ? 'Completada' : 'Pendiente';
                                    @endphp
                                    <tr>
                                        <td data-order="{{ $act->fechainicio instanceof \Carbon\Carbon ? $act->fechainicio->format('Y-m-d') : $act->fechainicio }}">
                                            {{ $act->fechainicio instanceof \Carbon\Carbon ? $act->fechainicio->format('d/m/Y') : ($act->fechainicio ?? '-') }}
                                        </td>
                                        <td><span class="badge badge-secondary">{{ $act->tipoActividad->nombre ?? '-' }}</span></td>
                                        <td><strong>{{ $act->lote->nombre ?? '-' }}</strong></td>
                                        <td>{{ $act->usuario->nombre ?? '-' }}</td>
                                        <td><span class="badge badge-{{ $estadoClass }}">{{ $estadoLabel }}</span></td>
                                        <td>{{ Str::limit($act->descripcion ?? '-', 55) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
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
            @if($actividades->isNotEmpty())
                $('#actividadesTable').DataTable({
                    language: { url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json' },
                    order: [[0, 'desc']],
                    pageLength: 10,
                    dom: "<'row'<'col-sm-6'l><'col-sm-6'f>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>"
                });
            @endif

            var actividadesPorDia = @json($actividadesPorDia ?? []);
            var actividadesPorTipo = @json($actividadesPorTipo);
            var chartColors = ['#6f42c1', '#28a745', '#fd7e14', '#17a2b8', '#dc3545', '#007bff', '#6c757d', '#20c997'];

            if (actividadesPorDia.length > 0 && document.getElementById('actividadesDiaChart')) {
                new Chart(document.getElementById('actividadesDiaChart'), {
                    type: 'bar',
                    data: {
                        labels: actividadesPorDia.map(a => a.dia),
                        datasets: [{
                            label: 'Actividades',
                            data: actividadesPorDia.map(a => a.total),
                            backgroundColor: 'rgba(111, 66, 193, 0.85)',
                            borderColor: '#6f42c1',
                            borderWidth: 2,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
                    }
                });
            }

            if (actividadesPorTipo.length > 0 && document.getElementById('actividadesTipoChart')) {
                new Chart(document.getElementById('actividadesTipoChart'), {
                    type: 'doughnut',
                    data: {
                        labels: actividadesPorTipo.map(a => a.nombre),
                        datasets: [{
                            data: actividadesPorTipo.map(a => a.total),
                            backgroundColor: chartColors,
                            borderWidth: 3,
                            borderColor: '#fff',
                            hoverOffset: 6
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { display: false } } }
                });
            }
        });
    </script>
@endpush