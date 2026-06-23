@extends('layouts.app')

@section('title', 'Inicio | AgroFusion')
@section('page_title', 'Inicio')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Inicio</li>
@endsection

@push('styles')
@include('dashboard.partials.inicio-estilos')
<style>
.inicio-transportista {
    --inicio-border: rgba(234, 88, 12, .18);
    --inicio-hero-bg: linear-gradient(135deg, #fff7ed 0%, #ffedd5 42%, #f8fafc 100%);
    --inicio-title: #9a3412;
    --inicio-icon-bg: linear-gradient(135deg, #ea580c, #f59e0b);
}
.inicio-pendientes-card {
    border-radius: 14px;
    border: 1px solid #fcd34d;
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    box-shadow: 0 4px 16px rgba(234, 88, 12, 0.1);
    margin-bottom: 1.25rem;
    overflow: hidden;
}
.inicio-pendientes-card--asignado {
    border-color: #86efac;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    box-shadow: 0 4px 16px rgba(22, 163, 74, 0.1);
}
.inicio-pendientes-card__head {
    display: flex;
    align-items: flex-start;
    gap: .85rem;
    padding: 1rem 1.15rem .85rem;
}
.inicio-pendientes-card__icon {
    width: 42px;
    height: 42px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.05rem;
    color: #fff;
    background: linear-gradient(135deg, #ea580c, #f59e0b);
    box-shadow: 0 3px 10px rgba(234, 88, 12, 0.25);
}
.inicio-pendientes-card--asignado .inicio-pendientes-card__icon {
    background: linear-gradient(135deg, #16a34a, #22c55e);
    box-shadow: 0 3px 10px rgba(22, 163, 74, 0.25);
}
.inicio-pendientes-card__title {
    font-size: .95rem;
    font-weight: 800;
    color: #9a3412;
    margin-bottom: .15rem;
}
.inicio-pendientes-card--asignado .inicio-pendientes-card__title { color: #14532d; }
.inicio-pendientes-card__sub {
    font-size: .82rem;
    color: #78716c;
    margin: 0;
    line-height: 1.4;
}
.inicio-pendientes-lista {
    list-style: none;
    margin: 0;
    padding: 0 .85rem .85rem;
    display: flex;
    flex-direction: column;
    gap: .55rem;
}
.inicio-pendiente-item {
    display: flex;
    align-items: center;
    gap: .75rem;
    padding: .75rem .9rem;
    border-radius: 11px;
    background: #fff;
    border: 1px solid #fde68a;
    border-left: 4px solid #f59e0b;
    text-decoration: none !important;
    color: inherit;
    transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
}
.inicio-pendientes-card--asignado .inicio-pendiente-item {
    border-color: #bbf7d0;
    border-left-color: #22c55e;
}
.inicio-pendiente-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
    border-color: #f59e0b;
}
.inicio-pendientes-card--asignado .inicio-pendiente-item:hover { border-color: #22c55e; }
.inicio-pendiente-item__body { flex: 1; min-width: 0; }
.inicio-pendiente-item__codigo {
    display: block;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-weight: 700;
    font-size: .88rem;
    color: #92400e;
    margin-bottom: .1rem;
}
.inicio-pendientes-card--asignado .inicio-pendiente-item__codigo { color: #14532d; }
.inicio-pendiente-item__meta {
    display: block;
    font-size: .8rem;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.inicio-pendiente-item__arrow {
    color: #d97706;
    font-size: .85rem;
    flex-shrink: 0;
}
.inicio-pendientes-card--asignado .inicio-pendiente-item__arrow { color: #16a34a; }
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

        @if(($envios_pendientes_accion ?? collect())->isNotEmpty())
            <div class="inicio-pendientes-card inicio-pendientes-card--asignado" role="alert">
                <div class="inicio-pendientes-card__head">
                    <div class="inicio-pendientes-card__icon"><i class="fas fa-truck-loading"></i></div>
                    <div>
                        <div class="inicio-pendientes-card__title">
                            {{ $envios_pendientes_accion->count() }} envío(s) listo(s) para recoger
                        </div>
                        <p class="inicio-pendientes-card__sub">Seleccione un envío para revisar condiciones y comenzar el cierre operativo.</p>
                    </div>
                </div>
                <ul class="inicio-pendientes-lista">
                    @foreach($envios_pendientes_accion as $envio)
                    <li>
                        <a href="{{ route('logistica.asignaciones.show', $envio) }}" class="inicio-pendiente-item">
                            <div class="inicio-pendiente-item__body">
                                <span class="inicio-pendiente-item__codigo">{{ $envio->externo_envio_id ?? $envio->pedido?->numero_solicitud ?? '#'.$envio->envioasignacionmultipleid }}</span>
                                <span class="inicio-pendiente-item__meta">{{ $envio->pedido?->detalles?->first()?->cultivo_personalizado ?? 'Producto agrícola' }}</span>
                            </div>
                            <i class="fas fa-arrow-right inicio-pendiente-item__arrow"></i>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(($rutas_pendientes_salida ?? collect())->isNotEmpty())
            <div class="inicio-pendientes-card" role="alert">
                <div class="inicio-pendientes-card__head">
                    <div class="inicio-pendientes-card__icon"><i class="fas fa-exclamation-circle"></i></div>
                    <div>
                        <div class="inicio-pendientes-card__title">
                            {{ $rutas_pendientes_salida->count() }} envío(s) pendiente(s) de salida
                        </div>
                        <p class="inicio-pendientes-card__sub">Debe aceptar la solicitud e iniciar el cierre operativo de cada envío.</p>
                    </div>
                </div>
                <ul class="inicio-pendientes-lista">
                    @foreach($rutas_pendientes_salida as $ruta)
                    <li>
                        <a href="{{ \App\Support\RutaDistribucionNavegacion::urlVer($ruta) }}" class="inicio-pendiente-item">
                            <div class="inicio-pendiente-item__body">
                                <span class="inicio-pendiente-item__codigo">{{ $ruta->codigo }}</span>
                                <span class="inicio-pendiente-item__meta">{{ \App\Support\RutaDistribucionNavegacion::resumenProducto($ruta) }}</span>
                            </div>
                            <i class="fas fa-arrow-right inicio-pendiente-item__arrow"></i>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('dashboard.partials.filtros', [
            'filtros' => $filtros,
            'actionUrl' => route('dashboard'),
        ])

        <div class="inicio-kpi-row">
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#15803d,#22c55e)">
                <i class="fas fa-clipboard-check inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['asignados'] }}</div>
                <p class="inicio-kpi__lbl">Asignados</p>
            </div>
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#c2410c,#f59e0b)">
                <i class="fas fa-box inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['por_recoger'] }}</div>
                <p class="inicio-kpi__lbl">Por recoger</p>
            </div>
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#0369a1,#0ea5e9)">
                <i class="fas fa-shipping-fast inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['en_camino'] }}</div>
                <p class="inicio-kpi__lbl">En camino</p>
            </div>
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#7c3aed,#a855f7)">
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
