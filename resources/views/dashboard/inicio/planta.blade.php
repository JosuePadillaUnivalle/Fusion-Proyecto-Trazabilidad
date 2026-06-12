@extends('layouts.app')

@section('title', 'Inicio | AgroFusion')
@section('page_title', 'Inicio')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Inicio</li>
@endsection

@push('styles')
@include('dashboard.partials.inicio-estilos')
<style>
.inicio-planta {
    --inicio-border: rgba(22, 163, 74, .15);
    --inicio-hero-bg: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 42%, #f8fafc 100%);
    --inicio-title: #14532d;
    --inicio-icon-bg: linear-gradient(135deg, #16a34a, #22c55e);
}
</style>
@endpush

@section('content')
<section class="content px-0 inicio-planta">
    <div class="container-fluid px-0">

        <div class="inicio-dash-hero">
            <div class="inicio-dash-hero__row">
                <div>
                    <div class="inicio-dash-hero__title"><i class="fas fa-chart-area"></i>Resumen de planta</div>
                    <p class="inicio-dash-hero__sub">Indicadores de logística, recepción y distribución · {{ $filtros->etiquetaPeriodo() }}</p>
                </div>
                <a href="{{ route('dashboard.panel-planta') }}" class="inicio-dash-link-panel">
                    <i class="fas fa-industry"></i> Ir al panel operativo
                </a>
            </div>
        </div>

        @include('partials.dashboard-alertas')

        @include('dashboard.partials.filtros', [
            'filtros' => $filtros,
            'actionUrl' => route('dashboard'),
        ])

        <div class="inicio-kpi-row">
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#15803d,#22c55e)">
                <i class="fas fa-boxes inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['pedidos_totales'] }}</div>
                <p class="inicio-kpi__lbl">Pedidos</p>
            </div>
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#1d4ed8,#3b82f6)">
                <i class="fas fa-user-tag inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['asignaciones'] }}</div>
                <p class="inicio-kpi__lbl">Asignaciones</p>
            </div>
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#0369a1,#0ea5e9)">
                <i class="fas fa-route inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['rutas_activas'] }}</div>
                <p class="inicio-kpi__lbl">Rutas activas</p>
            </div>
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#c2410c,#f59e0b)">
                <i class="fas fa-exclamation-triangle inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['incidentes_abiertos'] }}</div>
                <p class="inicio-kpi__lbl">Incidentes</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="inicio-chart-card">
                    <div class="inicio-chart-card__head">
                        <h3><i class="fas fa-chart-line text-success mr-2"></i>Flujo de pedidos y asignaciones · {{ $etiquetaGrafico }}</h3>
                    </div>
                    <div class="inicio-chart-wrap"><canvas id="chartPlantaFlujo"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="inicio-chart-card">
                    <div class="inicio-chart-card__head">
                        <h3><i class="fas fa-chart-pie text-primary mr-2"></i>Estado de asignaciones</h3>
                    </div>
                    <div class="inicio-chart-wrap inicio-chart-wrap--sm"><canvas id="chartPlantaEstados"></canvas></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="inicio-chart-card mb-0">
                    <div class="inicio-chart-card__head">
                        <h3><i class="fas fa-store text-purple mr-2"></i>Pedidos de distribución por estado</h3>
                    </div>
                    <div class="inicio-chart-wrap inicio-chart-wrap--sm"><canvas id="chartPlantaDistribucion"></canvas></div>
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="inicio-chart-card mb-0">
                    <div class="inicio-chart-card__head">
                        <h3><i class="fas fa-info-circle text-muted mr-2"></i>Lectura rápida</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0" style="font-size:.88rem;color:#475569">
                            <li class="mb-2"><i class="fas fa-check text-success mr-2"></i>{{ $stats['documentos'] }} documentos de entrega registrados.</li>
                            <li class="mb-2"><i class="fas fa-truck text-primary mr-2"></i>{{ $charts['incidentesAbiertos'] }} incidente(s) logístico(s) abiertos requieren seguimiento.</li>
                            <li><i class="fas fa-route text-info mr-2"></i>{{ $stats['rutas_activas'] }} ruta(s) en planificación o en curso hacia puntos de venta.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var charts = @json($charts);
    var grid = { color: '#f1f5f9' };
    var baseOpts = { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } } };

    if (charts.pedidosMes.labels.length) {
        new Chart(document.getElementById('chartPlantaFlujo'), {
            type: 'line',
            data: {
                labels: charts.pedidosMes.labels,
                datasets: [
                    { label: charts.pedidosMes.label, data: charts.pedidosMes.data, borderColor: '#22c55e', backgroundColor: '#22c55e22', fill: true, tension: .35, borderWidth: 2 },
                    { label: charts.asignacionesMes.label, data: charts.asignacionesMes.data, borderColor: '#3b82f6', backgroundColor: '#3b82f622', fill: true, tension: .35, borderWidth: 2 },
                ],
            },
            options: Object.assign({}, baseOpts, { scales: { y: { beginAtZero: true, grid: grid }, x: { grid: grid } } }),
        });
    }

    function doughnut(id, pack) {
        if (!pack.labels.length) return;
        new Chart(document.getElementById(id), {
            type: 'doughnut',
            data: { labels: pack.labels, datasets: [{ data: pack.data, backgroundColor: pack.colors, borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
        });
    }
    doughnut('chartPlantaEstados', charts.estadosAsignacion);
    doughnut('chartPlantaDistribucion', charts.distribucionEstados);
});
</script>
@endpush
