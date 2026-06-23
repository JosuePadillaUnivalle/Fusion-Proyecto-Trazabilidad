@extends('layouts.app')

@section('title', 'Inicio | AgroFusion')
@section('page_title', 'Inicio')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Inicio</li>
@endsection

@push('styles')
@include('dashboard.partials.inicio-estilos')
<style>
.inicio-agricola {
    --inicio-border: rgba(22, 163, 74, .15);
    --inicio-hero-bg: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 42%, #f8fafc 100%);
    --inicio-title: #14532d;
    --inicio-icon-bg: linear-gradient(135deg, #16a34a, #22c55e);
}
</style>
@endpush

@section('content')
<section class="content px-0 inicio-agricola">
    <div class="container-fluid px-0">

        <div class="inicio-dash-hero">
            <div class="inicio-dash-hero__row">
                <div>
                    <div class="inicio-dash-hero__title"><i class="fas fa-seedling"></i>Producción en campo</div>
                    <p class="inicio-dash-hero__sub">Cosechas, lotes y actividades del área agrícola · {{ $filtros->etiquetaPeriodo() }}</p>
                </div>
                <a href="{{ route('dashboard.panel-agricola') }}" class="inicio-dash-link-panel">
                    <i class="fas fa-tractor"></i> Ir al panel operativo
                </a>
            </div>
        </div>

        @include('partials.dashboard-alertas')

        @include('dashboard.partials.filtros', [
            'filtros' => $filtros,
            'cultivos' => $cultivos ?? collect(),
            'lotes' => $lotes ?? collect(),
            'mostrarCultivo' => true,
            'mostrarLote' => true,
            'actionUrl' => route('dashboard'),
        ])

        <div class="inicio-kpi-row">
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#15803d,#22c55e)">
                <i class="fas fa-map-marked-alt inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['lotes'] }}</div>
                <p class="inicio-kpi__lbl">Lotes</p>
            </div>
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#c2410c,#f59e0b)">
                <i class="fas fa-tasks inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['actividades_pendientes'] }}</div>
                <p class="inicio-kpi__lbl">Actividades pend.</p>
            </div>
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#0369a1,#0ea5e9)">
                <i class="fas fa-weight inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ number_format($stats['cosechas_mes'], 0) }}</div>
                <p class="inicio-kpi__lbl">Cosecha kg</p>
            </div>
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#7c3aed,#8b5cf6)">
                <i class="fas fa-truck inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['pedidos_pendientes'] }}</div>
                <p class="inicio-kpi__lbl">Envíos por aprobar</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="inicio-chart-card">
                    <div class="inicio-chart-card__head">
                        <h3><i class="fas fa-chart-area text-success mr-2"></i>Cosecha y actividades · {{ $etiquetaGrafico }}</h3>
                    </div>
                    <div class="inicio-chart-wrap"><canvas id="chartAgrFlujo"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="inicio-chart-card">
                    <div class="inicio-chart-card__head">
                        <h3><i class="fas fa-chart-pie text-warning mr-2"></i>Lotes por estado</h3>
                    </div>
                    <div class="inicio-chart-wrap inicio-chart-wrap--sm"><canvas id="chartAgrLotes"></canvas></div>
                </div>
            </div>
        </div>

        <div class="inicio-chart-card mb-0">
            <div class="inicio-chart-card__head">
                <h3><i class="fas fa-trophy text-warning mr-2"></i>Top cultivos por producción (kg)</h3>
            </div>
            <div class="inicio-chart-wrap inicio-chart-wrap--sm"><canvas id="chartAgrTop"></canvas></div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var c = @json($charts);
    var grid = { color: '#f1f5f9' };

    new Chart(document.getElementById('chartAgrFlujo'), {
        type: 'line',
        data: {
            labels: c.cosechasMes.labels,
            datasets: [
                { label: c.cosechasMes.label, data: c.cosechasMes.data, borderColor: '#22c55e', backgroundColor: '#22c55e33', fill: true, tension: .4, yAxisID: 'y' },
                { label: c.actividadesMes.label, data: c.actividadesMes.data, borderColor: '#0ea5e9', backgroundColor: '#0ea5e933', fill: true, tension: .4, yAxisID: 'y1' },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, position: 'left', grid: grid, title: { display: true, text: 'kg' } },
                y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, title: { display: true, text: 'Actividades' } },
                x: { grid: grid },
            },
        },
    });

    if (c.lotesEstado.labels.length) {
        new Chart(document.getElementById('chartAgrLotes'), {
            type: 'doughnut',
            data: { labels: c.lotesEstado.labels, datasets: [{ data: c.lotesEstado.data, backgroundColor: c.lotesEstado.colors, borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
        });
    }

    if (c.topCultivos.labels.length) {
        new Chart(document.getElementById('chartAgrTop'), {
            type: 'bar',
            data: { labels: c.topCultivos.labels, datasets: [{ data: c.topCultivos.data, backgroundColor: '#22c55ecc', borderRadius: 8 }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: grid } } },
        });
    }
});
</script>
@endpush
