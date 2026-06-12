@extends('layouts.app')

@section('title', 'Inicio | AgroFusion')
@section('page_title', 'Inicio')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Inicio</li>
@endsection

@push('styles')
@include('dashboard.partials.panel-accesos-styles')
<style>
.admin-home-wrap {
    --rp-border: rgba(30, 64, 175, .15);
    --rp-hero-bg: linear-gradient(135deg, #eff6ff 0%, #dbeafe 42%, #f8fafc 100%);
    --rp-glow: radial-gradient(circle, rgba(59, 130, 246, .14) 0%, transparent 70%);
    --rp-title: #1e3a8a;
    --rp-icon-bg: linear-gradient(135deg, #2563eb, #3b82f6);
    --rp-tile-hover: #93c5fd;
}
.admin-metric--lotes { background: linear-gradient(135deg, #15803d, #22c55e); }
.admin-metric--prod { background: linear-gradient(135deg, #0369a1, #0ea5e9); }
.admin-metric--inv { background: linear-gradient(135deg, #c2410c, #f59e0b); }
.admin-metric--ventas { background: linear-gradient(135deg, #7c3aed, #8b5cf6); }
.admin-chart-card { border: 0; border-radius: 16px; box-shadow: 0 8px 28px rgba(15,23,42,.08); margin-bottom: 1.25rem; }
.admin-chart-card .card-header { background: #fafbfc; border-bottom: 1px solid #e8edf2; padding: .9rem 1.25rem; }
.admin-chart-card .card-title { font-size: .95rem; font-weight: 700; margin: 0; color: #1e293b; }
.production-chart-container { position: relative; height: 320px; }
.admin-weather {
    background: linear-gradient(135deg, #0ea5e9, #2563eb);
    color: #fff; border-radius: 14px; padding: 1.25rem;
    margin-bottom: 1rem; text-align: center;
}
.admin-weather .weather-temp { font-size: 2.2rem; font-weight: 800; line-height: 1; }
.admin-weather .weather-desc { opacity: .9; font-size: .9rem; }
.admin-weather .weather-details { display: flex; justify-content: center; gap: 1.25rem; margin-top: .75rem; font-size: .8rem; opacity: .9; }
.recent-activity-item {
    display: flex; align-items: center; gap: .75rem;
    padding: .85rem 1.15rem; border-bottom: 1px solid #f1f5f9;
}
.recent-activity-item:last-child { border-bottom: 0; }
.activity-icon {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center; color: #fff; flex-shrink: 0;
}
.activity-siembra { background: #22c55e; }
.activity-riego { background: #0ea5e9; }
.activity-cosecha { background: #f59e0b; }
.activity-plagas { background: #ef4444; }
.activity-fumigacion { background: #ef4444; }
.activity-labranza { background: #64748b; }
.activity-fertilizacion { background: #8b5cf6; }
.activity-poda { background: #84cc16; }
.activity-monitoreo { background: #6366f1; }
.activity-default { background: #94a3b8; }
.alert-item {
    display: flex; gap: .75rem; padding: .85rem 1.15rem;
    border-bottom: 1px solid #f1f5f9; align-items: flex-start;
}
.alert-item:last-child { border-bottom: 0; }
.alert-icon {
    width: 34px; height: 34px; border-radius: 8px; background: #fef3c7; color: #d97706;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.alert-item-agotado .alert-icon { background: #fee2e2; color: #dc2626; }
.progress-group { margin-bottom: .85rem; }
.progress-group:last-child { margin-bottom: 0; }
.pg-label { font-size: .85rem; font-weight: 600; color: #334155; }
.pg-value { font-size: .78rem; color: #64748b; }
</style>
@endpush

@section('content')
<section class="content px-0 admin-home-wrap">
    <div class="container-fluid px-0">

        <div class="role-panel-hero position-relative" style="z-index:1">
            <div class="role-panel-hero__title">
                <i class="fas fa-chart-pie"></i>Centro de control
            </div>
            <p class="role-panel-hero__sub">
                Vista ejecutiva de AgroFusion · métricas filtrables por periodo y cultivo.
            </p>
        </div>

        @include('partials.dashboard-alertas')

        @include('dashboard.partials.filtros', [
            'filtros' => $filtros,
            'cultivos' => $cultivos,
            'lotes' => $lotes ?? collect(),
            'estadosLote' => $estadosLote ?? collect(),
            'mostrarCultivo' => true,
            'mostrarLote' => true,
            'mostrarEstadoLote' => true,
            'mostrarRangoFechas' => true,
            'actionUrl' => route('dashboard'),
        ])

        <div class="role-metrics">
            <a href="{{ route('lotes.index') }}" class="role-metric admin-metric--lotes text-white text-decoration-none">
                <i class="fas fa-map-marked-alt role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['lotes_activos'] ?? 0 }}</div>
                <p class="role-metric__lbl">Lotes activos</p>
            </a>
            <a href="{{ route('producciones.index') }}" class="role-metric admin-metric--prod text-white text-decoration-none">
                <i class="fas fa-seedling role-metric__icon"></i>
                <div class="role-metric__val">{{ number_format($stats['produccion_mes_kg'] ?? 0, 0) }}<span style="font-size:.9rem"> kg</span></div>
                <p class="role-metric__lbl">Producción ({{ $filtros->etiquetaPeriodo() }})</p>
            </a>
            <a href="{{ route('insumos.index') }}" class="role-metric admin-metric--inv text-white text-decoration-none">
                <i class="fas fa-exclamation-triangle role-metric__icon"></i>
                <div class="role-metric__val">{{ $stats['insumos_stock_bajo'] ?? 0 }}</div>
                <p class="role-metric__lbl">Alertas inventario</p>
            </a>
            @can('ventas.view')
            <a href="{{ route('ventas.index') }}" class="role-metric admin-metric--ventas text-white text-decoration-none">
                <i class="fas fa-dollar-sign role-metric__icon"></i>
                <div class="role-metric__val">Bs.{{ number_format($stats['ventas_mes'] ?? 0, 0) }}</div>
                <p class="role-metric__lbl">Ventas ({{ $filtros->etiquetaPeriodo() }})</p>
            </a>
            @else
            <div class="role-metric admin-metric--ventas">
                <i class="fas fa-dollar-sign role-metric__icon"></i>
                <div class="role-metric__val">Bs.{{ number_format($stats['ventas_mes'] ?? 0, 0) }}</div>
                <p class="role-metric__lbl">Ventas ({{ $filtros->etiquetaPeriodo() }})</p>
            </div>
            @endcan
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="card admin-chart-card h-100">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-line text-primary mr-2"></i>Producción · {{ $etiquetaGrafico ?? $filtros->etiquetaGrafico() }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="production-chart-container">
                            <canvas id="productionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="admin-weather" id="weatherWidget">
                    <h5 class="mb-2" style="font-size:.9rem;font-weight:600"><i class="fas fa-map-marker-alt mr-1"></i><span id="weatherCity">Santa Cruz, Bolivia</span></h5>
                    <div class="weather-temp" id="weatherTemp">--°C</div>
                    <div class="weather-desc" id="weatherDesc">Cargando…</div>
                    <div class="weather-details">
                        <div><i class="fas fa-tint mr-1"></i><span id="weatherHumedad">--%</span> Humedad</div>
                        <div><i class="fas fa-wind mr-1"></i><span id="weatherViento">--</span> km/h</div>
                    </div>
                </div>

                <div class="card role-acc-card">
                    <div class="role-acc-card__head">
                        <h3><i class="fas fa-bolt text-warning mr-2"></i>Acciones rápidas</h3>
                    </div>
                    <div class="role-acc-grupo">
                        <div class="role-acc-grid" style="grid-template-columns:1fr">
                            @can('lotes.create')
                            <a href="{{ route('lotes.create') }}" class="role-acc-tile">
                                <span class="role-acc-tile__icon role-acc-tile__icon--prod"><i class="fas fa-plus"></i></span>
                                <span><span class="role-acc-tile__lbl">Nuevo lote</span><span class="role-acc-tile__sub">Registrar parcela en campo</span></span>
                            </a>
                            @endcan
                            @can('lotes.update')
                            <a href="{{ route('actividades.create') }}" class="role-acc-tile">
                                <span class="role-acc-tile__icon role-acc-tile__icon--prod"><i class="fas fa-tasks"></i></span>
                                <span><span class="role-acc-tile__lbl">Registrar actividad</span><span class="role-acc-tile__sub">Siembra, riego, cosecha…</span></span>
                            </a>
                            @endcan
                            @can('inventario.view')
                            <a href="{{ route('insumos.index') }}" class="role-acc-tile">
                                <span class="role-acc-tile__icon role-acc-tile__icon--adm"><i class="fas fa-flask"></i></span>
                                <span><span class="role-acc-tile__lbl">Gestionar inventario</span><span class="role-acc-tile__sub">Insumos y stock</span></span>
                            </a>
                            @endcan
                            @can('lote_produccion.create')
                            <a href="{{ route('procesamiento.index') }}" class="role-acc-tile">
                                <span class="role-acc-tile__icon role-acc-tile__icon--com"><i class="fas fa-flask"></i></span>
                                <span><span class="role-acc-tile__lbl">Procesamiento de lote</span><span class="role-acc-tile__sub">Industrialización en planta</span></span>
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card role-acc-card h-100 mb-0">
                    <div class="role-acc-card__head">
                        <h3><i class="fas fa-history text-secondary mr-2"></i>Actividades recientes</h3>
                    </div>
                    <div class="card-body p-0">
                        @forelse($actividadesRecientes as $act)
                        @php
                            $uiActividad = \App\Support\DashboardPresentacion::actividadIcono($act->tipoActividad->nombre ?? null);
                            $fechaActividad = \App\Support\DashboardPresentacion::actividadFechaTexto($act->fechainicio);
                        @endphp
                        <div class="recent-activity-item">
                            <div class="activity-icon {{ $uiActividad['class'] }}">
                                <i class="fas {{ $uiActividad['icon'] }}"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold" style="font-size:.88rem">{{ $act->tipoActividad->nombre ?? 'Actividad' }} — {{ $act->lote->nombre ?? 'Sin lote' }}</div>
                                <small class="text-muted">{{ $fechaActividad }} · {{ trim(($act->usuario->nombre ?? '').' '.($act->usuario->apellido ?? '')) }}</small>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-muted py-4"><i class="fas fa-inbox d-block mb-2"></i>No hay actividades recientes</div>
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card role-acc-card h-100 mb-0">
                    <div class="role-acc-card__head">
                        <h3><i class="fas fa-bell text-warning mr-2"></i>Alertas del sistema</h3>
                    </div>
                    <div class="card-body p-0">
                        @forelse($insumosStockBajo as $insumo)
                        @php $stockAgotado = (float) $insumo->stock <= 0; @endphp
                        <div class="alert-item {{ $stockAgotado ? 'alert-item-agotado' : '' }}">
                            <div class="alert-icon"><i class="fas {{ $stockAgotado ? 'fa-times' : 'fa-exclamation' }}"></i></div>
                            <div>
                                <div class="font-weight-bold" style="font-size:.88rem">{{ $stockAgotado ? 'Stock agotado' : 'Stock bajo' }}: {{ $insumo->nombre }}</div>
                                <small class="text-muted">
                                    {{ number_format($insumo->stock, 2) }} {{ $insumo->unidadMedida->abreviatura ?? '' }}
                                    @unless($stockAgotado)(menor a {{ \App\Support\InsumoCatalogo::UMBRAL_ALERTA_STOCK }})@endunless
                                </small>
                            </div>
                        </div>
                        @empty
                        <div class="text-center text-success py-4"><i class="fas fa-check-circle d-block mb-2"></i>Sin alertas pendientes</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        @if($topCultivos->isNotEmpty())
        <div class="card role-acc-card mb-0">
            <div class="role-acc-card__head">
                <h3><i class="fas fa-trophy text-warning mr-2"></i>Top cultivos por producción</h3>
            </div>
            <div class="card-body">
                @php $colores = ['success','warning','info','danger','primary']; $maxProduccion = $topCultivos->max('total') ?: 1; @endphp
                @foreach($topCultivos as $index => $cultivo)
                <div class="progress-group">
                    <div class="d-flex justify-content-between"><span class="pg-label">{{ $cultivo->nombre }}</span><span class="pg-value">{{ number_format($cultivo->total, 0) }} kg</span></div>
                    <div class="progress" style="height:8px;border-radius:4px">
                        <div class="progress-bar bg-{{ $colores[$index % 5] }}" style="width:{{ ($cultivo->total / $maxProduccion) * 100 }}%"></div>
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
$(function () {
    function cargarClima() {
        $.ajax({
            url: '{{ route("api.clima") }}',
            method: 'GET',
            dataType: 'json',
            timeout: 10000,
        }).done(function (data) {
            if (!data || !data.success) {
                $('#weatherDesc').text('Datos no disponibles');
                return;
            }
            $('#weatherTemp').text(data.temperatura + '°C');
            $('#weatherDesc').text(data.descripcion);
            $('#weatherHumedad').text(data.humedad + '%');
            $('#weatherViento').text(data.viento);
            $('#weatherCity').text((data.ciudad || 'Santa Cruz') + ', Bolivia');
        }).fail(function () {
            $('#weatherTemp').text('28°C');
            $('#weatherDesc').text('Parcialmente nublado');
            $('#weatherHumedad').text('62%');
            $('#weatherViento').text('14');
            $('#weatherCity').text('Santa Cruz, Bolivia');
        });
    }
    cargarClima();

    var chartData = @json($chartData);
    if (chartData.labels && chartData.labels.length > 0) {
        var ctx = document.getElementById('productionChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: { labels: chartData.labels, datasets: chartData.datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: { grid: { color: '#f1f5f9' } }
                }
            }
        });
    } else {
        $('#productionChart').parent().html('<div class="text-center text-muted py-5"><i class="fas fa-chart-line fa-2x mb-2 d-block"></i>Sin datos de producción aún</div>');
    }
});
</script>
@endpush
