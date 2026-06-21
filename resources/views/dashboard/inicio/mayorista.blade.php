@extends('layouts.app')



@section('title', 'Inicio | AgroFusion')

@section('page_title', 'Inicio')



@section('breadcrumbs')

    <li class="breadcrumb-item active">Inicio</li>

@endsection



@push('styles')

@include('dashboard.partials.inicio-estilos')

<style>

.inicio-mayorista { --inicio-accent: #0d9488; }

</style>

@endpush



@section('content')

<section class="content px-0 inicio-mayorista">

    <div class="container-fluid px-0">



        <div class="inicio-dash-hero">

            <div class="inicio-dash-hero__row">

                <div>

                    <div class="inicio-dash-hero__title"><i class="fas fa-warehouse"></i>Centro mayorista</div>

                    <p class="inicio-dash-hero__sub">

                        Hola, <strong>{{ auth()->user()->nombre }}</strong> · Almacenes, pedidos de puntos de venta y distribución · {{ $filtros->etiquetaPeriodo() }}

                    </p>

                </div>

                <a href="{{ route('dashboard.panel-mayorista') }}" class="inicio-dash-link-panel">

                    <i class="fas fa-th-large"></i> Ir al panel operativo

                </a>

            </div>

        </div>



        @include('partials.dashboard-alertas')



        @include('dashboard.partials.filtros', [

            'filtros' => $filtros,

            'actionUrl' => route('dashboard'),

        ])



        <div class="inicio-kpi-row">

            <div class="inicio-kpi dash-kpi--teal">

                <i class="fas fa-warehouse inicio-kpi__icon"></i>

                <div class="inicio-kpi__val">{{ $stats['almacenes'] }}</div>

                <p class="inicio-kpi__lbl">Almacenes</p>

            </div>

            <div class="inicio-kpi dash-kpi--amber">

                <i class="fas fa-inbox inicio-kpi__icon"></i>

                <div class="inicio-kpi__val">{{ $stats['pedidos_pendientes'] }}</div>

                <p class="inicio-kpi__lbl">Pedidos por revisar</p>

            </div>

            <div class="inicio-kpi dash-kpi--blue">

                <i class="fas fa-clipboard-list inicio-kpi__icon"></i>

                <div class="inicio-kpi__val">{{ $stats['pedidos_activos'] }}</div>

                <p class="inicio-kpi__lbl">Pedidos activos</p>

            </div>

            <div class="inicio-kpi dash-kpi--green">

                <i class="fas fa-boxes inicio-kpi__icon"></i>

                <div class="inicio-kpi__val">{{ $stats['productos_stock'] }}</div>

                <p class="inicio-kpi__lbl">Productos en stock</p>

            </div>

        </div>



        <div class="row">

            <div class="col-lg-8 mb-4">

                <div class="inicio-chart-card">

                    <div class="inicio-chart-card__head">

                        <h3><i class="fas fa-chart-line text-teal mr-2"></i>Pedidos de minoristas · {{ $etiquetaGrafico }}</h3>

                    </div>

                    <div class="inicio-chart-wrap"><canvas id="chartMayPedidos"></canvas></div>

                </div>

            </div>

            <div class="col-lg-4 mb-4">

                <div class="inicio-chart-card">

                    <div class="inicio-chart-card__head">

                        <h3><i class="fas fa-chart-pie text-warning mr-2"></i>Estado de pedidos</h3>

                    </div>

                    <div class="inicio-chart-wrap inicio-chart-wrap--sm"><canvas id="chartMayEstados"></canvas></div>

                </div>

            </div>

        </div>



        <div class="inicio-chart-card mb-4">

            <div class="inicio-chart-card__head">

                <h3><i class="fas fa-store text-primary mr-2"></i>Pedidos por punto de venta</h3>

            </div>

            <div class="inicio-chart-wrap inicio-chart-wrap--sm"><canvas id="chartMayPdv"></canvas></div>

        </div>



        @if($pedidosRecientes->isNotEmpty())

        <div class="inicio-chart-card mb-0">

            <div class="inicio-chart-card__head d-flex justify-content-between align-items-center">

                <h3><i class="fas fa-shopping-cart text-teal mr-2"></i>Últimos pedidos de minoristas</h3>

                <a href="{{ route('punto-venta.pedidos.index') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>

            </div>

            <div class="p-0">

                @foreach($pedidosRecientes as $pedido)

                @php $badge = \App\Support\PedidoDistribucionCatalogo::badgeEstado($pedido); @endphp

                <div class="d-flex align-items-center justify-content-between px-3 py-3 border-bottom" style="gap:.75rem">

                    <div>

                        <div class="font-weight-bold">{{ $pedido->numero_solicitud }}</div>

                        <div class="small text-muted">{{ $pedido->puntoVenta?->nombre ?? 'Punto de venta' }}</div>

                    </div>

                    <div class="text-right">

                        <span class="badge badge-{{ $badge['clase'] }}">{{ $badge['etiqueta'] }}</span>

                        <a href="{{ route('punto-venta.pedidos.show', $pedido) }}" class="btn btn-sm btn-outline-secondary ml-2">Revisar</a>

                    </div>

                </div>

                @endforeach

            </div>

        </div>

        @endif



    </div>

</section>

@endsection



@push('scripts')

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    var c = @json($charts);

    var grid = { color: '#f1f5f9' };



    new Chart(document.getElementById('chartMayPedidos'), {

        type: 'line',

        data: {

            labels: c.pedidosMes.labels,

            datasets: [

                { label: c.pedidosMes.label, data: c.pedidosMes.data, borderColor: '#0d9488', backgroundColor: '#0d948833', fill: true, tension: .35, borderWidth: 2, pointRadius: 4, pointHoverRadius: 6 },

                { label: c.enTransitoMes.label, data: c.enTransitoMes.data, borderColor: '#22c55e', backgroundColor: '#22c55e22', fill: false, tension: .35, borderWidth: 2, pointRadius: 4, pointHoverRadius: 6 },

            ],

        },

        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: { beginAtZero: true, grid: grid, ticks: { precision: 0 } },
                x: { grid: grid, title: { display: (c.pedidosMes.modo === 'diario'), text: 'Día del mes' } },
            },
        },

    });



    if (c.estadosPedido.labels.length) {

        new Chart(document.getElementById('chartMayEstados'), {

            type: 'doughnut',

            data: { labels: c.estadosPedido.labels, datasets: [{ data: c.estadosPedido.data, backgroundColor: c.estadosPedido.colors, borderWidth: 0 }] },

            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },

        });

    }



    if (c.porPdv.labels.length) {

        new Chart(document.getElementById('chartMayPdv'), {

            type: 'bar',

            data: { labels: c.porPdv.labels, datasets: [{ data: c.porPdv.data, backgroundColor: '#0d9488cc', borderRadius: 8 }] },

            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: grid } } },

        });

    }

});

</script>

@endpush

