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
.admin-metric--transporte { background: linear-gradient(135deg, #0f766e, #14b8a6); }
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
.admin-dash-card--interactive .role-acc-card__head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
}
.admin-dash-card__link {
    font-size: .78rem;
    font-weight: 700;
    color: #059669;
    text-decoration: none;
    white-space: nowrap;
}
.admin-dash-card__link:hover { color: #047857; text-decoration: underline; }
.admin-interactive-row {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .85rem 1.15rem;
    border-bottom: 1px solid #f1f5f9;
    text-decoration: none;
    color: inherit;
    transition: background .15s ease;
}
.admin-interactive-row:last-child { border-bottom: 0; }
.admin-interactive-row:hover {
    background: #f8fafc;
    color: inherit;
    text-decoration: none;
}
.admin-interactive-row:focus-visible {
    outline: 2px solid #34d399;
    outline-offset: -2px;
    background: #f0fdf4;
}
.admin-interactive-row__chevron {
    margin-left: auto;
    color: #cbd5e1;
    font-size: .72rem;
    transition: transform .15s ease, color .15s ease;
}
.admin-interactive-row:hover .admin-interactive-row__chevron {
    color: #059669;
    transform: translateX(2px);
}
.admin-interactive-empty {
    display: block;
    padding: 1.5rem 1.15rem;
    text-align: center;
    text-decoration: none;
    color: inherit;
    transition: background .15s ease;
}
.admin-interactive-empty:hover {
    background: #f8fafc;
    text-decoration: none;
    color: inherit;
}
.admin-interactive-empty--center {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    flex: 1 1 auto;
    min-height: 12rem;
}
.admin-dash-card--fill {
    display: flex;
    flex-direction: column;
}
.admin-dash-card--fill > .card-body {
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
}
.progress-group--link {
    display: block;
    padding: .55rem .65rem;
    margin: 0 -.65rem .85rem;
    border-radius: 10px;
    text-decoration: none;
    color: inherit;
    transition: background .15s ease, box-shadow .15s ease;
}
.progress-group--link:last-child { margin-bottom: 0; }
.progress-group--link:hover {
    background: #f8fafc;
    box-shadow: inset 0 0 0 1px #e2e8f0;
    text-decoration: none;
    color: inherit;
}
.progress-group--link:focus-visible {
    outline: 2px solid #34d399;
    outline-offset: 2px;
}
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
            <a href="{{ route('logistica.asignaciones.listado') }}" class="role-metric admin-metric--transporte text-white text-decoration-none">
                <i class="fas fa-truck role-metric__icon"></i>
                <div class="role-metric__val">Bs.{{ number_format($stats['transporte_costo_mes'] ?? 0, 0) }}</div>
                <p class="role-metric__lbl">Costo transporte ({{ $filtros->etiquetaPeriodo() }})</p>
            </a>
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
                <div class="card role-acc-card h-100 mb-0 admin-dash-card--interactive">
                    <div class="role-acc-card__head">
                        <h3 class="mb-0"><i class="fas fa-history text-secondary mr-2"></i>Actividades recientes</h3>
                        @can('lotes.view')
                        <a href="{{ route('actividades.index') }}" class="admin-dash-card__link">
                            Ver todas <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                        @endcan
                    </div>
                    <div class="card-body p-0">
                        @forelse($actividadesRecientes as $act)
                        @php
                            $uiActividad = \App\Support\DashboardPresentacion::actividadIcono($act->tipoActividad->nombre ?? null);
                            $fechaActividad = \App\Support\DashboardPresentacion::actividadFechaTexto($act->fechainicio);
                            $urlActividad = $act->lote
                                ? route('lotes.trazabilidad', $act->lote).'#historial-eventos'
                                : route('actividades.show', $act);
                        @endphp
                        @can('lotes.view')
                        <a href="{{ $urlActividad }}" class="admin-interactive-row recent-activity-item" title="Ver actividad en trazabilidad">
                        @else
                        <div class="admin-interactive-row recent-activity-item">
                        @endcan
                            <div class="activity-icon {{ $uiActividad['class'] }}">
                                <i class="fas {{ $uiActividad['icon'] }}"></i>
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="font-weight-bold text-truncate" style="font-size:.88rem">{{ $act->tipoActividad->nombre ?? 'Actividad' }} — {{ $act->lote->nombre ?? 'Sin lote' }}</div>
                                <small class="text-muted">{{ $fechaActividad }} · {{ trim(($act->usuario->nombre ?? '').' '.($act->usuario->apellido ?? '')) }}</small>
                            </div>
                            @can('lotes.view')
                            <i class="fas fa-chevron-right admin-interactive-row__chevron" aria-hidden="true"></i>
                            @endcan
                        @can('lotes.view')
                        </a>
                        @else
                        </div>
                        @endcan
                        @empty
                        @can('lotes.update')
                        <a href="{{ route('actividades.create') }}" class="admin-interactive-empty text-muted">
                            <i class="fas fa-plus-circle d-block mb-2"></i>
                            No hay actividades recientes. Registrar una actividad
                        </a>
                        @else
                        <div class="text-center text-muted py-4"><i class="fas fa-inbox d-block mb-2"></i>No hay actividades recientes</div>
                        @endcan
                        @endforelse
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card role-acc-card h-100 mb-0 admin-dash-card--interactive admin-dash-card--fill">
                    <div class="role-acc-card__head">
                        <h3 class="mb-0"><i class="fas fa-bell text-warning mr-2"></i>Alertas del sistema</h3>
                        @can('inventario.view')
                        <a href="{{ route('insumos.index', ['stock' => 'bajo']) }}" class="admin-dash-card__link">
                            Ver inventario <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                        @endcan
                    </div>
                    <div class="card-body p-0">
                        @forelse($insumosStockBajo as $insumo)
                        @php $stockAgotado = (float) $insumo->stock <= 0; @endphp
                        @can('inventario.view')
                        <a href="{{ route('insumos.show', $insumo) }}" class="admin-interactive-row alert-item {{ $stockAgotado ? 'alert-item-agotado' : '' }}" title="Ver insumo">
                        @else
                        <div class="admin-interactive-row alert-item {{ $stockAgotado ? 'alert-item-agotado' : '' }}">
                        @endcan
                            <div class="alert-icon"><i class="fas {{ $stockAgotado ? 'fa-times' : 'fa-exclamation' }}"></i></div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="font-weight-bold text-truncate" style="font-size:.88rem">{{ $stockAgotado ? 'Stock agotado' : 'Stock bajo' }}: {{ $insumo->nombre }}</div>
                                <small class="text-muted">
                                    {{ number_format($insumo->stock, 2) }} {{ $insumo->unidadMedida->abreviatura ?? '' }}
                                    @unless($stockAgotado)(menor a {{ \App\Support\InsumoCatalogo::UMBRAL_ALERTA_STOCK }})@endunless
                                </small>
                            </div>
                            @can('inventario.view')
                            <i class="fas fa-chevron-right admin-interactive-row__chevron" aria-hidden="true"></i>
                            @endcan
                        @can('inventario.view')
                        </a>
                        @else
                        </div>
                        @endcan
                        @empty
                        @can('inventario.view')
                        <a href="{{ route('insumos.index') }}" class="admin-interactive-empty admin-interactive-empty--center text-success">
                            <i class="fas fa-check-circle mb-2" style="font-size:1.35rem"></i>
                            <span>Sin alertas pendientes. Revisar inventario</span>
                        </a>
                        @else
                        <div class="admin-interactive-empty admin-interactive-empty--center text-success">
                            <i class="fas fa-check-circle mb-2" style="font-size:1.35rem"></i>
                            <span>Sin alertas pendientes</span>
                        </div>
                        @endcan
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        @if($topCultivos->isNotEmpty())
        <div class="card role-acc-card mb-0 admin-dash-card--interactive">
            <div class="role-acc-card__head">
                <h3 class="mb-0"><i class="fas fa-trophy text-warning mr-2"></i>Top cultivos por producción</h3>
                @can('lote_produccion.view')
                <a href="{{ route('producciones.index') }}" class="admin-dash-card__link">
                    Ver producciones <i class="fas fa-arrow-right ml-1"></i>
                </a>
                @endcan
            </div>
            <div class="card-body">
                @php $colores = ['success','warning','info','danger','primary']; $maxProduccion = $topCultivos->max('total') ?: 1; @endphp
                @foreach($topCultivos as $index => $cultivo)
                @can('lote_produccion.view')
                <a href="{{ route('producciones.index', ['buscar' => $cultivo->nombre, 'filtros_abiertos' => 1]) }}"
                   class="progress-group progress-group--link"
                   title="Ver cosechas de {{ $cultivo->nombre }}">
                @else
                <div class="progress-group">
                @endcan
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="pg-label">{{ $cultivo->nombre }}</span>
                        <span class="pg-value">{{ number_format($cultivo->total, 0) }} kg</span>
                    </div>
                    <div class="progress" style="height:8px;border-radius:4px">
                        <div class="progress-bar bg-{{ $colores[$index % 5] }}" style="width:{{ ($cultivo->total / $maxProduccion) * 100 }}%"></div>
                    </div>
                @can('lote_produccion.view')
                </a>
                @else
                </div>
                @endcan
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
        var esDiario = chartData.modo === 'diario';
        new Chart(ctx, {
            type: 'line',
            data: { labels: chartData.labels, datasets: chartData.datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: esDiario ? {
                        callbacks: {
                            title: function (items) {
                                return items.length ? 'Día ' + items[0].label : '';
                            }
                        }
                    } : {}
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                    x: {
                        grid: { color: '#f1f5f9' },
                        title: {
                            display: esDiario,
                            text: 'Día del mes',
                            color: '#64748b',
                            font: { size: 11 }
                        },
                        ticks: { maxTicksLimit: esDiario ? 16 : undefined }
                    }
                }
            }
        });
    } else {
        $('#productionChart').parent().html('<div class="text-center text-muted py-5"><i class="fas fa-chart-line fa-2x mb-2 d-block"></i>Sin datos de producción aún</div>');
    }
});
</script>
@endpush
