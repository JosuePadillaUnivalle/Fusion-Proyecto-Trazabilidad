@extends('layouts.app')

@section('title', 'Panel Mayorista | AgroFusion')
@section('page_title', 'Panel Mayorista')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:#2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Panel Mayorista</li>
@endsection

@push('styles')
@include('dashboard.partials.inicio-estilos')
@include('dashboard.partials.panel-accesos-styles')
<style>
.role-panel-wrap--mayorista { --rp-accent: #0d9488; }
.may-pedido-item {
    display: flex; align-items: center; justify-content: space-between;
    gap: .75rem; padding: .85rem 1.15rem; border-bottom: 1px solid #f1f5f9;
}
.may-pedido-item:last-child { border-bottom: 0; }
.may-almacen-item {
    display: flex; align-items: center; justify-content: space-between;
    gap: .75rem; padding: .75rem 1rem; border-bottom: 1px solid #f1f5f9;
}
.may-almacen-item:last-child { border-bottom: 0; }
</style>
@endpush

@section('content')
<section class="content px-0 role-panel-wrap--mayorista">
    <div class="container-fluid px-0">

        <div class="role-panel-hero position-relative" style="z-index:1">
            <div class="role-panel-hero__title">
                <i class="fas fa-warehouse"></i>Panel Mayorista
            </div>
            <p class="role-panel-hero__sub">
                Hola, <strong>{{ auth()->user()->nombre }}</strong> · Gestione sus almacenes y revise lo que solicitan los puntos de venta · {{ $filtros->etiquetaPeriodo() }}
            </p>
        </div>

        @include('partials.dashboard-alertas')

        @include('dashboard.partials.filtros', [
            'filtros' => $filtros,
            'actionUrl' => url()->current(),
        ])

        <div class="role-metrics">
            <a href="{{ route('almacen-mayorista.index') }}" class="role-metric dash-kpi--teal text-decoration-none">
                <i class="fas fa-warehouse role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['almacenes'] }}</div>
                <p class="role-metric__lbl">Almacenes</p>
            </a>
            <a href="{{ route('punto-venta.pedidos.index', ['estado' => 'pendiente']) }}" class="role-metric dash-kpi--amber text-decoration-none">
                <i class="fas fa-inbox role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['pedidos_pendientes'] }}</div>
                <p class="role-metric__lbl">Por revisar</p>
            </a>
            <a href="{{ route('punto-venta.pedidos.index') }}" class="role-metric dash-kpi--blue text-decoration-none">
                <i class="fas fa-clipboard-list role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['pedidos_activos'] }}</div>
                <p class="role-metric__lbl">Pedidos activos</p>
            </a>
            <div class="role-metric dash-kpi--green">
                <i class="fas fa-shipping-fast role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['en_transito'] }}</div>
                <p class="role-metric__lbl">En tránsito</p>
            </div>
            <div class="role-metric dash-kpi--slate">
                <i class="fas fa-boxes role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['productos_stock'] }}</div>
                <p class="role-metric__lbl">Productos en stock</p>
                @if(($stats['stock_bajo'] ?? 0) > 0)
                <div class="role-metric__sub text-warning">{{ $stats['stock_bajo'] }} con stock bajo</div>
                @endif
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="inicio-chart-card mb-0">
                    <div class="inicio-chart-card__head">
                        <h3><i class="fas fa-chart-line text-teal mr-2"></i>Pedidos de minoristas · {{ $etiquetaGrafico }}</h3>
                    </div>
                    <div class="inicio-chart-wrap"><canvas id="chartMayPedidos"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="inicio-chart-card mb-0">
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

        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="role-block-card mb-0">
                    <div class="role-block-card__head">
                        <h3><i class="fas fa-shopping-cart mr-2 text-muted"></i>Pedidos de minoristas</h3>
                        <a href="{{ route('punto-venta.pedidos.index') }}" class="btn btn-sm btn-outline-primary">Ver bandeja</a>
                    </div>
                    <div class="p-0">
                        @forelse($pedidosRecientes as $pedido)
                        @php $badge = \App\Support\PedidoDistribucionCatalogo::badgeEstado($pedido); @endphp
                        <div class="may-pedido-item">
                            <div>
                                <div class="font-weight-bold">{{ $pedido->numero_solicitud }}</div>
                                <div class="small text-muted">{{ $pedido->puntoVenta?->nombre ?? '—' }} · {{ $pedido->fechapedido?->format('d/m/Y H:i') ?? '—' }}</div>
                            </div>
                            <div>
                                <span class="badge badge-{{ $badge['clase'] }}">{{ $badge['etiqueta'] }}</span>
                                <a href="{{ route('punto-venta.pedidos.show', $pedido) }}" class="btn btn-sm btn-outline-secondary ml-1">Revisar</a>
                            </div>
                        </div>
                        @empty
                        <div class="p-4 text-center text-muted">No hay pedidos recientes en este periodo.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-4">
                <div class="role-acc-card mb-3">
                    <div class="role-acc-card__head">
                        <h3><i class="fas fa-bolt mr-2 text-muted"></i>Accesos rápidos</h3>
                    </div>
                    <div class="role-acc-grupo">
                        <div class="role-acc-grid" style="grid-template-columns:1fr">
                            @can('inventario.create')
                            <a href="{{ route('almacen-mayorista.create') }}" class="role-acc-tile">
                                <span class="role-acc-tile__icon"><i class="fas fa-plus"></i></span>
                                <span><span class="role-acc-tile__lbl">Nuevo almacén</span><span class="role-acc-tile__sub">Registrar centro de distribución</span></span>
                            </a>
                            @endcan
                            <a href="{{ route('almacen-mayorista.index') }}" class="role-acc-tile">
                                <span class="role-acc-tile__icon"><i class="fas fa-warehouse"></i></span>
                                <span><span class="role-acc-tile__lbl">Mis almacenes</span><span class="role-acc-tile__sub">Inventario y movimientos</span></span>
                            </a>
                            <a href="{{ route('punto-venta.pedidos.index') }}" class="role-acc-tile">
                                <span class="role-acc-tile__icon"><i class="fas fa-inbox"></i></span>
                                <span><span class="role-acc-tile__lbl">Pedidos minoristas</span><span class="role-acc-tile__sub">Aceptar y coordinar entregas</span></span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="role-block-card mb-0">
                    <div class="role-block-card__head">
                        <h3><i class="fas fa-map-marker-alt mr-2 text-muted"></i>Mis almacenes</h3>
                    </div>
                    <div class="p-0">
                        @forelse($almacenesResumen as $almacen)
                        <div class="may-almacen-item">
                            <div>
                                <div class="font-weight-bold">{{ $almacen->nombre }}</div>
                                <div class="small text-muted">{{ $almacen->ubicacion ?: 'Sin ubicación' }}</div>
                            </div>
                            <a href="{{ route('almacen-mayorista.show', $almacen) }}" class="btn btn-sm btn-outline-secondary">Abrir</a>
                        </div>
                        @empty
                        <div class="p-4 text-center text-muted">
                            Aún no tiene almacenes asignados.
                            @can('inventario.create')
                            <a href="{{ route('almacen-mayorista.create') }}" class="d-block mt-2">Crear el primero</a>
                            @endcan
                        </div>
                        @endforelse
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
