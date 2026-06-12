@extends('layouts.app')

@section('title', 'Inicio | AgroFusion')
@section('page_title', 'Inicio')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Inicio</li>
@endsection

@push('styles')
@include('dashboard.partials.inicio-estilos')
<style>
.inicio-minorista {
    --inicio-border: rgba(124, 58, 237, .18);
    --inicio-hero-bg: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 42%, #f8fafc 100%);
    --inicio-title: #5b21b6;
    --inicio-icon-bg: linear-gradient(135deg, #7c3aed, #8b5cf6);
}
</style>
@endpush

@section('content')
<section class="content px-0 inicio-minorista">
    <div class="container-fluid px-0">

        <div class="inicio-dash-hero">
            <div class="inicio-dash-hero__row">
                <div>
                    <div class="inicio-dash-hero__title"><i class="fas fa-chart-pie"></i>Comercialización</div>
                    <p class="inicio-dash-hero__sub">Hola, <strong>{{ auth()->user()->nombre }}</strong> · Pedidos y abastecimiento desde planta · {{ $filtros->etiquetaPeriodo() }}</p>
                </div>
                <a href="{{ route('dashboard.panel-minorista') }}" class="inicio-dash-link-panel">
                    <i class="fas fa-shopping-basket"></i> Ir al panel operativo
                </a>
            </div>
        </div>

        @include('partials.dashboard-alertas')

        @include('dashboard.partials.filtros', [
            'filtros' => $filtros,
            'actionUrl' => route('dashboard'),
        ])

        <div class="inicio-kpi-row">
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#7c3aed,#8b5cf6)">
                <i class="fas fa-store inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['puntos_venta'] }}</div>
                <p class="inicio-kpi__lbl">Puntos de venta</p>
            </div>
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#2563eb,#3b82f6)">
                <i class="fas fa-clipboard-list inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['pedidos_activos'] }}</div>
                <p class="inicio-kpi__lbl">Pedidos activos</p>
            </div>
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#c2410c,#f59e0b)">
                <i class="fas fa-hourglass-half inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['pendientes_planta'] }}</div>
                <p class="inicio-kpi__lbl">En revisión</p>
            </div>
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#0369a1,#0ea5e9)">
                <i class="fas fa-shipping-fast inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['en_transito'] }}</div>
                <p class="inicio-kpi__lbl">En tránsito</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="inicio-chart-card">
                    <div class="inicio-chart-card__head">
                        <h3><i class="fas fa-chart-line text-purple mr-2"></i>Pedidos solicitados · {{ $etiquetaGrafico }}</h3>
                    </div>
                    <div class="inicio-chart-wrap"><canvas id="chartMinPedidos"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="inicio-chart-card">
                    <div class="inicio-chart-card__head">
                        <h3><i class="fas fa-chart-pie text-warning mr-2"></i>Estado de pedidos</h3>
                    </div>
                    <div class="inicio-chart-wrap inicio-chart-wrap--sm"><canvas id="chartMinEstados"></canvas></div>
                </div>
            </div>
        </div>

        <div class="inicio-chart-card mb-0">
            <div class="inicio-chart-card__head">
                <h3><i class="fas fa-store text-primary mr-2"></i>Pedidos por punto de venta</h3>
            </div>
            <div class="inicio-chart-wrap inicio-chart-wrap--sm"><canvas id="chartMinPuntos"></canvas></div>
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

    new Chart(document.getElementById('chartMinPedidos'), {
        type: 'line',
        data: {
            labels: c.pedidosMes.labels,
            datasets: [{ label: c.pedidosMes.label, data: c.pedidosMes.data, borderColor: '#8b5cf6', backgroundColor: '#8b5cf633', fill: true, tension: .35, borderWidth: 2 }],
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, grid: grid }, x: { grid: grid } } },
    });

    if (c.estadosPedido.labels.length) {
        new Chart(document.getElementById('chartMinEstados'), {
            type: 'doughnut',
            data: { labels: c.estadosPedido.labels, datasets: [{ data: c.estadosPedido.data, backgroundColor: c.estadosPedido.colors, borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
        });
    }

    if (c.porPunto.labels.length) {
        new Chart(document.getElementById('chartMinPuntos'), {
            type: 'bar',
            data: { labels: c.porPunto.labels, datasets: [{ data: c.porPunto.data, backgroundColor: '#3b82f6cc', borderRadius: 8 }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: grid } } },
        });
    }
});
</script>
@endpush
