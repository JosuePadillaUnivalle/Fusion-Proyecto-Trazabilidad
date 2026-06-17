@extends('layouts.app')

@section('title', 'Panel Minorista | AgroFusion')
@section('page_title', 'Panel Minorista')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:#2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Panel Minorista</li>
@endsection

@push('styles')
@include('dashboard.partials.inicio-estilos')
@include('dashboard.partials.panel-accesos-styles')
<style>
.role-panel-wrap--minorista {
    --inicio-border: rgba(124, 58, 237, .18);
    --inicio-hero-bg: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 42%, #f8fafc 100%);
    --inicio-title: #5b21b6;
    --inicio-icon-bg: linear-gradient(135deg, #7c3aed, #8b5cf6);
    --rp-border: rgba(124, 58, 237, .18);
    --rp-hero-bg: var(--inicio-hero-bg);
    --rp-glow: radial-gradient(circle, rgba(139, 92, 246, .16) 0%, transparent 70%);
    --rp-title: var(--inicio-title);
    --rp-icon-bg: var(--inicio-icon-bg);
    --rp-tile-hover: #c4b5fd;
}
.min-panel-inv-card {
    border: 0;
    border-radius: 16px;
    background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 55%, #fff 100%);
    box-shadow: 0 8px 28px rgba(15, 23, 42, .08);
    padding: 1.1rem 1.25rem;
    height: 100%;
}
.min-panel-inv-card__val { font-size: 1.65rem; font-weight: 800; color: #047857; line-height: 1.1; }
.min-panel-inv-card__lbl { font-size: .82rem; color: #64748b; margin: .15rem 0 0; }
.min-panel-pedido-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .85rem 1.15rem;
    border-bottom: 1px solid #f1f5f9;
}
.min-panel-pedido-item:last-child { border-bottom: 0; }
.min-panel-pedido-item__id { font-weight: 700; color: #1e293b; font-size: .92rem; }
.min-panel-pedido-item__pdv { font-size: .78rem; color: #64748b; }
</style>
@endpush

@section('content')
<section class="content px-0 role-panel-wrap--minorista inicio-minorista">
    <div class="container-fluid px-0">

        <div class="inicio-dash-hero">
            <div class="inicio-dash-hero__row">
                <div>
                    <div class="inicio-dash-hero__title"><i class="fas fa-shopping-basket"></i>Panel Minorista</div>
                    <p class="inicio-dash-hero__sub">
                        Hola, <strong>{{ auth()->user()->nombre }}</strong> · Pedidos, inventario y puntos de venta · {{ $filtros->etiquetaPeriodo() }}
                    </p>
                </div>
                @can('pedidos_distribucion.create')
                <a href="{{ route('punto-venta.pedidos.create') }}" class="inicio-dash-link-panel">
                    <i class="fas fa-paper-plane"></i> Nueva solicitud
                </a>
                @endcan
            </div>
        </div>

        @include('partials.dashboard-alertas')

        @include('dashboard.partials.filtros', [
            'filtros' => $filtros,
            'actionUrl' => url()->current(),
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
                <p class="inicio-kpi__lbl">En camino</p>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="inicio-chart-card mb-0">
                    <div class="inicio-chart-card__head">
                        <h3><i class="fas fa-chart-line text-purple mr-2"></i>Pedidos solicitados · {{ $etiquetaGrafico }}</h3>
                    </div>
                    <div class="inicio-chart-wrap"><canvas id="chartMinPedidos"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="row h-100">
                    <div class="col-12 mb-3">
                        <div class="inicio-chart-card mb-0">
                            <div class="inicio-chart-card__head">
                                <h3><i class="fas fa-chart-pie text-warning mr-2"></i>Estado de pedidos</h3>
                            </div>
                            <div class="inicio-chart-wrap inicio-chart-wrap--sm"><canvas id="chartMinEstados"></canvas></div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="min-panel-inv-card">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <div class="min-panel-inv-card__val">{{ $inventario['productos'] ?? 0 }}</div>
                                    <p class="min-panel-inv-card__lbl">Productos en inventario</p>
                                </div>
                                <i class="fas fa-boxes text-success" style="font-size:1.6rem;opacity:.35"></i>
                            </div>
                            @if(($inventario['bajo_stock'] ?? 0) > 0)
                            <div class="mt-2 pt-2 border-top">
                                <span class="badge badge-warning"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $inventario['bajo_stock'] }} con stock bajo</span>
                            </div>
                            @endif
                            @can('punto_venta.view')
                            <a href="{{ route('punto-venta.inventario.index') }}" class="btn btn-sm btn-outline-success mt-3">Ver inventario</a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="inicio-chart-card mb-4">
            <div class="inicio-chart-card__head">
                <h3><i class="fas fa-store text-primary mr-2"></i>Pedidos por punto de venta</h3>
            </div>
            <div class="inicio-chart-wrap inicio-chart-wrap--sm"><canvas id="chartMinPuntos"></canvas></div>
        </div>

        @if($pedidosRecientes->isNotEmpty())
        <div class="inicio-chart-card mb-4">
            <div class="inicio-chart-card__head d-flex justify-content-between align-items-center">
                <h3><i class="fas fa-box text-primary mr-2"></i>Últimos pedidos</h3>
                <a href="{{ route('punto-venta.pedidos.index') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
            <div class="p-0">
                @foreach($pedidosRecientes as $pedido)
                @php $badge = \App\Support\PedidoDistribucionCatalogo::badgeEstado($pedido); @endphp
                <div class="min-panel-pedido-item">
                    <div>
                        <div class="min-panel-pedido-item__id">{{ $pedido->numero_solicitud }}</div>
                        <div class="min-panel-pedido-item__pdv">{{ $pedido->puntoVenta?->nombre ?? '—' }}</div>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-{{ $badge['clase'] }}">{{ $badge['etiqueta'] }}</span>
                        <a href="{{ route('punto-venta.pedidos.show', $pedido) }}" class="btn btn-sm btn-outline-secondary ml-2">Ver</a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="card role-acc-card mb-0">
            <div class="role-acc-card__head">
                <h3><i class="fas fa-bolt mr-2" style="color:#7c3aed"></i>Accesos rápidos</h3>
            </div>
            <div class="role-acc-grupo">
                <div class="role-acc-grupo__titulo">Comercialización</div>
                <div class="role-acc-grid">
                    @can('pedidos_distribucion.view')
                    <a href="{{ route('punto-venta.pedidos.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--com"><i class="fas fa-clipboard-list"></i></span>
                        <span>
                            <span class="role-acc-tile__lbl">Mis pedidos</span>
                            <span class="role-acc-tile__sub">Solicitudes a planta</span>
                        </span>
                    </a>
                    @endcan
                    @can('pedidos_distribucion.create')
                    <a href="{{ route('punto-venta.pedidos.create') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--com"><i class="fas fa-plus"></i></span>
                        <span>
                            <span class="role-acc-tile__lbl">Nuevo pedido</span>
                            <span class="role-acc-tile__sub">Solicitar producto de planta</span>
                        </span>
                    </a>
                    @endcan
                    @can('punto_venta.view')
                    <a href="{{ route('punto-venta.puntos.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--com"><i class="fas fa-store"></i></span>
                        <span>
                            <span class="role-acc-tile__lbl">Puntos de venta</span>
                            <span class="role-acc-tile__sub">Locales registrados</span>
                        </span>
                    </a>
                    @endcan
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
