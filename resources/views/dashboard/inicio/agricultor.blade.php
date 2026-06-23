@extends('layouts.app')

@section('title', 'Inicio | AgroFusion')
@section('page_title', 'Inicio')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Inicio</li>
@endsection

@push('styles')
@include('dashboard.partials.inicio-estilos')
@include('dashboard.partials.panel-accesos-styles')
<style>
.inicio-agricultor {
    --inicio-border: rgba(22, 163, 74, .15);
    --inicio-hero-bg: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 42%, #f8fafc 100%);
    --inicio-title: #166534;
    --inicio-icon-bg: linear-gradient(135deg, #16a34a, #4ade80);
}
</style>
@endpush

@section('content')
<section class="content px-0 inicio-agricultor">
    <div class="container-fluid px-0">

        <div class="inicio-dash-hero">
            <div class="inicio-dash-hero__row">
                <div>
                    <div class="inicio-dash-hero__title"><i class="fas fa-hard-hat"></i>Mi trabajo en campo</div>
                    <p class="inicio-dash-hero__sub">Hola, <strong>{{ auth()->user()->nombre }}</strong> · Avance de tus actividades · {{ $filtros->etiquetaPeriodo() }}</p>
                </div>
                <a href="{{ route('actividades.index') }}" class="inicio-dash-link-panel">
                    <i class="fas fa-tasks"></i> Ver mis actividades
                </a>
            </div>
        </div>

        @include('partials.dashboard-alertas')

        @include('dashboard.partials.filtros', [
            'filtros' => $filtros,
            'lotes' => $lotes ?? collect(),
            'mostrarLote' => true,
            'actionUrl' => route('dashboard'),
        ])

        <div class="inicio-kpi-row">
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#15803d,#22c55e)">
                <i class="fas fa-map-marked-alt inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['lotes_asignados'] }}</div>
                <p class="inicio-kpi__lbl">Lotes</p>
            </div>
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#c2410c,#f59e0b)">
                <i class="fas fa-clock inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['actividades_pendientes'] }}</div>
                <p class="inicio-kpi__lbl">Pendientes</p>
            </div>
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#0369a1,#0ea5e9)">
                <i class="fas fa-calendar-day inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['actividades_hoy'] }}</div>
                <p class="inicio-kpi__lbl">Hoy</p>
            </div>
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#059669,#10b981)">
                <i class="fas fa-check-circle inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['completadas_mes'] }}</div>
                <p class="inicio-kpi__lbl">Completadas</p>
            </div>
        </div>

        <div class="card role-acc-card mb-4">
            <div class="role-acc-card__head">
                <h3><i class="fas fa-bolt text-success mr-2"></i>Atajos rápidos</h3>
            </div>
            <div class="role-acc-grupo">
                <div class="role-acc-grupo__titulo">Mi trabajo en campo</div>
                <div class="role-acc-grid">
                    @can('lotes.view')
                    <a href="{{ route('lotes.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--prod"><i class="fas fa-map"></i></span>
                        <span><span class="role-acc-tile__lbl">Mis lotes</span><span class="role-acc-tile__sub">Parcelas asignadas</span></span>
                    </a>
                    <a href="{{ route('lotes.mapa') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--prod"><i class="fas fa-map-marked-alt"></i></span>
                        <span><span class="role-acc-tile__lbl">Mapa de lotes</span><span class="role-acc-tile__sub">Ubicación geográfica</span></span>
                    </a>
                    @endcan
                    <a href="{{ route('actividades.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--prod"><i class="fas fa-tasks"></i></span>
                        <span><span class="role-acc-tile__lbl">Actividades</span><span class="role-acc-tile__sub">Tareas del lote</span></span>
                    </a>
                    <a href="{{ route('actividades.calendario') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--prod"><i class="fas fa-calendar-alt"></i></span>
                        <span><span class="role-acc-tile__lbl">Calendario</span><span class="role-acc-tile__sub">Agenda semanal</span></span>
                    </a>
                    <a href="{{ route('producciones.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--prod"><i class="fas fa-tractor"></i></span>
                        <span><span class="role-acc-tile__lbl">Mis cosechas</span><span class="role-acc-tile__sub">Producción registrada</span></span>
                    </a>
                    @can('certificaciones.view')
                    <a href="{{ route('certificaciones.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--com"><i class="fas fa-certificate"></i></span>
                        <span><span class="role-acc-tile__lbl">Certificaciones</span><span class="role-acc-tile__sub">Calidad de mi lote</span></span>
                    </a>
                    @endcan
                    @can('inventario.view')
                    <a href="{{ route('insumos.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--adm"><i class="fas fa-flask"></i></span>
                        <span><span class="role-acc-tile__lbl">Insumos</span><span class="role-acc-tile__sub">Consulta de catálogo</span></span>
                    </a>
                    <a href="{{ route('almacen-agricola.index') }}" class="role-acc-tile">
                        <span class="role-acc-tile__icon role-acc-tile__icon--log"><i class="fas fa-warehouse"></i></span>
                        <span><span class="role-acc-tile__lbl">Almacenes</span><span class="role-acc-tile__sub">Destinos de cosecha</span></span>
                    </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="inicio-chart-card">
                    <div class="inicio-chart-card__head">
                        <h3><i class="fas fa-chart-bar text-success mr-2"></i>Actividades completadas vs pendientes · {{ $etiquetaGrafico }}</h3>
                    </div>
                    <div class="inicio-chart-wrap"><canvas id="chartAgriAct"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4 mb-4">
                <div class="inicio-chart-card">
                    <div class="inicio-chart-card__head">
                        <h3><i class="fas fa-chart-pie text-primary mr-2"></i>Mis lotes por estado</h3>
                    </div>
                    <div class="inicio-chart-wrap inicio-chart-wrap--sm"><canvas id="chartAgriLotes"></canvas></div>
                </div>
            </div>
        </div>

        <div class="inicio-chart-card mb-0">
            <div class="inicio-chart-card__head">
                <h3><i class="fas fa-list text-muted mr-2"></i>Tipos de actividad más frecuentes</h3>
            </div>
            <div class="inicio-chart-wrap inicio-chart-wrap--sm"><canvas id="chartAgriTipos"></canvas></div>
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

    new Chart(document.getElementById('chartAgriAct'), {
        type: 'bar',
        data: {
            labels: c.actividadesMes.labels,
            datasets: [
                { label: 'Completadas', data: c.actividadesMes.completadas, backgroundColor: '#22c55ecc', borderRadius: 6 },
                { label: 'Pendientes', data: c.actividadesMes.pendientes, backgroundColor: '#f59e0bcc', borderRadius: 6 },
            ],
        },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, stacked: true, grid: grid }, x: { stacked: true, grid: grid } } },
    });

    if (c.lotesEstado.labels.length) {
        new Chart(document.getElementById('chartAgriLotes'), {
            type: 'doughnut',
            data: { labels: c.lotesEstado.labels, datasets: [{ data: c.lotesEstado.data, backgroundColor: c.lotesEstado.colors, borderWidth: 0 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
        });
    }

    if (c.tiposActividad.labels.length) {
        new Chart(document.getElementById('chartAgriTipos'), {
            type: 'bar',
            data: { labels: c.tiposActividad.labels, datasets: [{ data: c.tiposActividad.data, backgroundColor: '#0ea5e9cc', borderRadius: 8 }] },
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, grid: grid } } },
        });
    }
});
</script>
@endpush
