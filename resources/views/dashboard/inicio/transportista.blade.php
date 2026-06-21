@extends('layouts.app')

@section('title', 'Inicio | AgroFusion')
@section('page_title', 'Inicio')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Inicio</li>
@endsection

@push('styles')
@include('dashboard.partials.inicio-estilos')
<style>
.inicio-transportista { --inicio-accent: #d97706; }
</style>
@endpush

@section('content')
<section class="content px-0 inicio-transportista">
    <div class="container-fluid px-0">

        <div class="inicio-dash-hero">
            <div class="inicio-dash-hero__row">
                <div>
                    <div class="inicio-dash-hero__title"><i class="fas fa-chart-line"></i>Tu desempeño en ruta</div>
                    <p class="inicio-dash-hero__sub">Hola, <strong>{{ auth()->user()->nombre }}</strong> · Entregas y productividad · {{ $filtros->etiquetaPeriodo() }}</p>
                </div>
                <a href="{{ route('dashboard.panel-transportista') }}" class="inicio-dash-link-panel">
                    <i class="fas fa-truck-moving"></i> Ir al panel operativo
                </a>
            </div>
        </div>

        @include('partials.dashboard-alertas')

        @include('dashboard.partials.filtros', [
            'filtros' => $filtros,
            'actionUrl' => route('dashboard'),
        ])

        <div class="inicio-kpi-row">
            <div class="inicio-kpi" class="inicio-kpi dash-kpi--green">
                <i class="fas fa-clipboard-check inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['asignados'] }}</div>
                <p class="inicio-kpi__lbl">Asignados</p>
            </div>
            <div class="inicio-kpi dash-kpi--amber">
                <i class="fas fa-box inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['por_recoger'] }}</div>
                <p class="inicio-kpi__lbl">Por recoger</p>
            </div>
            <div class="inicio-kpi dash-kpi--blue">
                <i class="fas fa-shipping-fast inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['en_camino'] }}</div>
                <p class="inicio-kpi__lbl">En camino</p>
            </div>
            <div class="inicio-kpi dash-kpi--purple">
                <i class="fas fa-tachometer-alt inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['productividad'] }}%</div>
                <p class="inicio-kpi__lbl">Productividad</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="inicio-chart-card">
                    <div class="inicio-chart-card__head">
                        <h3><i class="fas fa-chart-bar text-warning mr-2"></i>Envíos vs entregas · {{ $etiquetaGrafico }}</h3>
                    </div>
                    <div class="inicio-chart-wrap"><canvas id="chartTransBarras"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="inicio-chart-card">
                    <div class="inicio-chart-card__head">
                        <h3><i class="fas fa-chart-pie text-success mr-2"></i>Estado actual</h3>
                    </div>
                    <div class="inicio-chart-wrap inicio-chart-wrap--sm"><canvas id="chartTransEstados"></canvas></div>
                </div>
            </div>
        </div>

        <div class="inicio-chart-card mb-0">
            <div class="inicio-chart-card__head">
                <h3><i class="fas fa-percentage text-purple mr-2"></i>Tasa de entrega mensual (%)</h3>
            </div>
            <div class="inicio-chart-wrap inicio-chart-wrap--sm"><canvas id="chartTransProductividad"></canvas></div>
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

    new Chart(document.getElementById('chartTransBarras'), {
        type: 'bar',
        data: {
            labels: c.asignacionesMes.labels,
            datasets: [
                { label: c.asignacionesMes.label, data: c.asignacionesMes.data, backgroundColor: '#f59e0bcc', borderRadius: 6 },
                { label: c.entregasMes.label, data: c.entregasMes.data, backgroundColor: '#22c55ecc', borderRadius: 6 },
            ],
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true, grid: grid }, x: { grid: grid } } },
    });

    if (c.estadosAsignacion.labels.length) {
        new Chart(document.getElementById('chartTransEstados'), {
            type: 'doughnut',
            data: { labels: c.estadosAsignacion.labels, datasets: [{ data: c.estadosAsignacion.data, backgroundColor: c.estadosAsignacion.colors, borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
        });
    }

    new Chart(document.getElementById('chartTransProductividad'), {
        type: 'line',
        data: {
            labels: c.productividadMes.labels,
            datasets: [{ label: c.productividadMes.label, data: c.productividadMes.data, borderColor: '#7c3aed', backgroundColor: '#7c3aed22', fill: true, tension: .35 }],
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100, grid: grid }, x: { grid: grid } } },
    });
});
</script>
@endpush
