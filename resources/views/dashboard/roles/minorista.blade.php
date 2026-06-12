@extends('layouts.app')

@section('title', 'Panel Minorista | AgroFusion')
@section('page_title', 'Panel Minorista')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:#2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Panel Minorista</li>
@endsection

@push('styles')
@include('dashboard.partials.panel-accesos-styles')
<style>
.role-panel-wrap--minorista {
    --rp-border: rgba(124, 58, 237, .18);
    --rp-hero-bg: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 42%, #f8fafc 100%);
    --rp-glow: radial-gradient(circle, rgba(139, 92, 246, .16) 0%, transparent 70%);
    --rp-title: #5b21b6;
    --rp-icon-bg: linear-gradient(135deg, #7c3aed, #8b5cf6);
    --rp-tile-hover: #c4b5fd;
}
</style>
@endpush

@section('content')
<section class="content px-0 role-panel-wrap--minorista">
    <div class="container-fluid px-0">

        <div class="role-panel-hero position-relative" style="z-index:1">
            <div class="role-panel-hero__title">
                <i class="fas fa-shopping-basket"></i>Panel Minorista
            </div>
            <p class="role-panel-hero__sub">
                Hola, <strong>{{ auth()->user()->nombre }}</strong> · Gestión de puntos de venta y pedidos a planta.
            </p>
        </div>

        @include('partials.dashboard-alertas')

        @include('dashboard.partials.filtros', [
            'filtros' => $filtros,
            'actionUrl' => url()->current(),
        ])

        <div class="role-metrics">
            <div class="role-metric" style="background:linear-gradient(135deg,#7c3aed,#8b5cf6)">
                <i class="fas fa-store role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['puntos_venta'] }}</div>
                <p class="role-metric__lbl">Puntos de venta</p>
            </div>
            <div class="role-metric" style="background:linear-gradient(135deg,#2563eb,#3b82f6)">
                <i class="fas fa-clipboard-list role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['pedidos_activos'] }}</div>
                <p class="role-metric__lbl">Pedidos activos</p>
            </div>
            <div class="role-metric" style="background:linear-gradient(135deg,#c2410c,#f59e0b)">
                <i class="fas fa-hourglass-half role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['pendientes_planta'] }}</div>
                <p class="role-metric__lbl">En revisión</p>
            </div>
            <div class="role-metric" style="background:linear-gradient(135deg,#0369a1,#0ea5e9)">
                <i class="fas fa-shipping-fast role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['en_transito'] }}</div>
                <p class="role-metric__lbl">En tránsito</p>
            </div>
        </div>

        @if($pedidosRecientes->isNotEmpty())
        <div class="role-block-card">
            <div class="role-block-card__head">
                <h3><i class="fas fa-box text-primary mr-2"></i>Últimos pedidos</h3>
                <a href="{{ route('punto-venta.pedidos.index') }}" class="btn btn-sm btn-outline-primary">Ver todos</a>
            </div>
            <ul class="list-group list-group-flush">
                @foreach($pedidosRecientes as $pedido)
                @php $badge = \App\Support\PedidoDistribucionCatalogo::badgeEstado($pedido); @endphp
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $pedido->numero_solicitud }}</strong>
                        <div class="small text-muted">{{ $pedido->puntoVenta?->nombre ?? '—' }}</div>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-{{ $badge['clase'] }}">{{ $badge['etiqueta'] }}</span>
                        <a href="{{ route('punto-venta.pedidos.show', $pedido) }}" class="btn btn-sm btn-outline-secondary ml-2">Ver</a>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="card role-acc-card">
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
