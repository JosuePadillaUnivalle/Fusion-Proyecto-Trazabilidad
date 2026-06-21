@extends('layouts.app')

@section('title', 'Panel Planta | AgroFusion')
@section('page_title', 'Panel Planta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Panel Planta</li>
@endsection

@push('styles')
@include('dashboard.partials.panel-accesos-styles')
<style>
.planta-panel-wrap { --rp-accent: #2c5530; }
.planta-metrics {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: .75rem;
    margin-bottom: 1.25rem;
}
@media (max-width: 1200px) { .planta-metrics { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 576px) { .planta-metrics { grid-template-columns: repeat(2, 1fr); } }

.planta-acc-grupo { padding: 1rem 1.25rem 1.1rem; }
.planta-acc-grupo + .planta-acc-grupo {
    border-top: 1px dashed #e2e8f0;
    padding-top: 1.1rem;
}
.planta-acc-grupo__titulo {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #94a3b8;
    margin-bottom: .65rem;
}
.planta-acc-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: .65rem;
}
.planta-acc-tile {
    display: flex;
    align-items: flex-start;
    gap: .7rem;
    padding: .85rem .95rem;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
    text-decoration: none !important;
    color: #334155;
    transition: border-color .15s, box-shadow .15s, transform .15s;
}
.planta-acc-tile:hover {
    border-color: #cbd5e1;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .05);
    transform: none;
    color: #1e293b;
}
.planta-acc-tile__lbl { font-size: .88rem; font-weight: 700; color: #1e293b; line-height: 1.2; }
.planta-acc-tile__sub { font-size: .72rem; color: #94a3b8; margin-top: .15rem; display: block; }
</style>
@endpush

@section('content')
<section class="content px-0 planta-panel-wrap">
    <div class="container-fluid px-0">

        <div class="planta-panel-hero position-relative" style="z-index:1">
            <div class="planta-panel-hero__title">
                <i class="fas fa-industry"></i>Panel Planta
            </div>
            <p class="planta-panel-hero__sub">
                Resumen operativo y accesos directos a logística, producción y comercialización.
            </p>
        </div>

        @include('partials.dashboard-alertas')

        @include('dashboard.partials.filtros', [
            'filtros' => $filtros,
            'actionUrl' => url()->current(),
        ])

        @if(auth()->user() && \App\Support\UsuarioRol::esOperarioPlanta(auth()->user()) && ($tareasPendientesCount ?? 0) > 0)
        <div class="alert alert-warning border-0 shadow-sm d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:.75rem;">
            <div>
                <strong><i class="fas fa-industry mr-1"></i>Tiene {{ $tareasPendientesCount }} tarea(s) de transformación pendiente(s)</strong>
                <span class="d-block small text-muted">El jefe de planta le asignó trabajo en maquinaria. Revise el detalle y márquelas como completadas.</span>
            </div>
            <a href="{{ route('tareas-planta.index') }}" class="btn btn-warning btn-sm font-weight-bold">
                <i class="fas fa-tasks mr-1"></i>Ver mis tareas
            </a>
        </div>
        @endif

        <div class="planta-metrics">
            <div class="planta-metric planta-metric--pedidos dash-kpi--green">
                <i class="fas fa-boxes planta-metric__icon"></i>
                <div class="planta-metric__val">{{ $stats['pedidos_totales'] }}</div>
                <p class="planta-metric__lbl">Pedidos</p>
            </div>
            <div class="planta-metric planta-metric--asig dash-kpi--blue">
                <i class="fas fa-user-tag planta-metric__icon"></i>
                <div class="planta-metric__val">{{ $stats['asignaciones'] }}</div>
                <p class="planta-metric__lbl">Asignaciones</p>
            </div>
            <div class="planta-metric planta-metric--rutas dash-kpi--teal">
                <i class="fas fa-route planta-metric__icon"></i>
                <div class="planta-metric__val">{{ $stats['rutas_activas'] }}</div>
                <p class="planta-metric__lbl">Rutas activas</p>
            </div>
            <div class="planta-metric planta-metric--inc dash-kpi--amber">
                <i class="fas fa-exclamation-triangle planta-metric__icon"></i>
                <div class="planta-metric__val">{{ $stats['incidentes_abiertos'] }}</div>
                <p class="planta-metric__lbl">Incidentes</p>
            </div>
            <div class="planta-metric planta-metric--doc dash-kpi--slate">
                <i class="fas fa-file-signature planta-metric__icon"></i>
                <div class="planta-metric__val">{{ $stats['documentos'] }}</div>
                <p class="planta-metric__lbl">Documentos</p>
            </div>
        </div>

        <div class="card planta-acc-card">
            <div class="planta-acc-card__head">
                <h3><i class="fas fa-bolt text-success mr-2"></i>Accesos rápidos</h3>
            </div>

            @php
                $userPanel = auth()->user();
                $isAdminPanel = $userPanel && ($userPanel->hasRole('Admin') || $userPanel->hasRole('admin'));
            @endphp
            <div class="planta-acc-grupo">
                <div class="planta-acc-grupo__titulo">Logística</div>
                <div class="planta-acc-grid">
                    @if($userPanel?->can('asignaciones.view'))
                    <a href="{{ route('logistica.asignaciones.listado') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--log"><i class="fas fa-truck"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Envíos</span>
                            <span class="planta-acc-tile__sub">Asignaciones y recepción en planta</span>
                        </span>
                    </a>
                    @elseif($userPanel?->can('pedidos.view'))
                    <a href="{{ route('pedidos.index') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--log"><i class="fas fa-truck"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Envíos</span>
                            <span class="planta-acc-tile__sub">Pedidos y recepción desde agrícola</span>
                        </span>
                    </a>
                    @endif
                    @unless($userPanel?->hasRole('transportista'))
                    @if($isAdminPanel || $userPanel?->can('transportistas.view'))
                    <a href="{{ route('envios.transportistas') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--log"><i class="fas fa-id-card"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Transportistas</span>
                            <span class="planta-acc-tile__sub">Choferes y perfiles de flota</span>
                        </span>
                    </a>
                    @endif
                    @if($isAdminPanel || $userPanel?->can('vehiculos.view'))
                    <a href="{{ route('envios.vehiculos') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--log"><i class="fas fa-truck-moving"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Vehículos</span>
                            <span class="planta-acc-tile__sub">Unidades disponibles para envío</span>
                        </span>
                    </a>
                    @endif
                    @endunless
                </div>
            </div>

            <div class="planta-acc-grupo">
                <div class="planta-acc-grupo__titulo">Producción</div>
                <div class="planta-acc-grid">
                    @can('lote_produccion.view')
                    <a href="{{ route('procesamiento.index') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--prod"><i class="fas fa-flask"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Procesamiento de lote</span>
                            <span class="planta-acc-tile__sub">Transformación y certificación</span>
                        </span>
                    </a>
                    @endcan
                    @if($userPanel && \App\Support\UsuarioRol::esPlantaOperativo($userPanel))
                    <a href="{{ route('procesos-planta.index') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--prod"><i class="fas fa-cogs"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Procesos de planta</span>
                            <span class="planta-acc-tile__sub">Etapas y flujo industrial</span>
                        </span>
                    </a>
                    <a href="{{ route('plantillas-transformacion.index') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--prod"><i class="fas fa-project-diagram"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Procesos de transformación</span>
                            <span class="planta-acc-tile__sub">Plantillas para lotes</span>
                        </span>
                    </a>
                    <a href="{{ route('maquinas-planta.index') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--prod"><i class="fas fa-industry"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Máquinas de planta</span>
                            <span class="planta-acc-tile__sub">Equipos de producción</span>
                        </span>
                    </a>
                    @endif
                    @if($userPanel && \App\Support\UsuarioRol::esOperarioPlanta($userPanel))
                    <a href="{{ route('tareas-planta.index') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--warn"><i class="fas fa-tasks"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Mis tareas</span>
                            <span class="planta-acc-tile__sub">Etapas asignadas en maquinaria</span>
                        </span>
                    </a>
                    @endif
                </div>
            </div>

            @can('almacen.movimientos.view')
            <div class="planta-acc-grupo">
                <div class="planta-acc-grupo__titulo">Almacén de planta</div>
                <div class="planta-acc-grid">
                    <a href="{{ route('almacen-planta.index') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--prod"><i class="fas fa-warehouse"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Almacenes</span>
                            <span class="planta-acc-tile__sub">Depósitos y capacidad</span>
                        </span>
                    </a>
                    <a href="{{ route('almacen-planta.movimientos.index') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--prod"><i class="fas fa-exchange-alt"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Movimientos</span>
                            <span class="planta-acc-tile__sub">Ingresos y salidas de stock</span>
                        </span>
                    </a>
                    @can('almacen.reportes.view')
                    <a href="{{ route('almacen-planta.movimientos.reportes') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--prod"><i class="fas fa-chart-bar"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Reportes</span>
                            <span class="planta-acc-tile__sub">Resumen de almacén</span>
                        </span>
                    </a>
                    @endcan
                </div>
            </div>
            @endcan

            @canany(['pedidos_distribucion.view', 'pedidos_distribucion.update'])
            <div class="planta-acc-grupo">
                <div class="planta-acc-grupo__titulo">Comercialización</div>
                <div class="planta-acc-grid">
                    @can('pedidos_distribucion.view')
                    <a href="{{ route('punto-venta.pedidos.index') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--com"><i class="fas fa-store"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Pedidos de distribución</span>
                            <span class="planta-acc-tile__sub">Solicitudes de puntos de venta</span>
                        </span>
                    </a>
                    @endcan
                    @if(\App\Support\UsuarioRol::puedeGestionarDistribucionPlanta(auth()->user()))
                    <a href="{{ route('punto-venta.rutas.index') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--com"><i class="fas fa-route"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Planificar distribución</span>
                            <span class="planta-acc-tile__sub">Rutas planta → minoristas</span>
                        </span>
                    </a>
                    @endif
                </div>
            </div>
            @endcanany

            @can('usuarios.view')
            <div class="planta-acc-grupo">
                <div class="planta-acc-grupo__titulo">Equipo</div>
                <div class="planta-acc-grid">
                    <a href="{{ route('gestion.index') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--adm"><i class="fas fa-users-cog"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Gestión de usuarios</span>
                            <span class="planta-acc-tile__sub">Operarios y accesos del equipo</span>
                        </span>
                    </a>
                </div>
            </div>
            @endcan

            @canany(['documentos.view', 'incidentes.view'])
            <div class="planta-acc-grupo">
                <div class="planta-acc-grupo__titulo">Logística avanzada</div>
                <div class="planta-acc-grid">
                    @can('documentos.view')
                    <a href="{{ route('logistica.documentos.index') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--adm"><i class="fas fa-file-alt"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Documentos</span>
                            <span class="planta-acc-tile__sub">Notas y comprobantes de entrega</span>
                        </span>
                    </a>
                    @endcan
                    @can('incidentes.view')
                    <a href="{{ route('logistica.incidentes.index') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--warn"><i class="fas fa-exclamation-circle"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Incidentes</span>
                            <span class="planta-acc-tile__sub">{{ $stats['incidentes_abiertos'] }} abierto(s)</span>
                        </span>
                    </a>
                    @endcan
                </div>
            </div>
            @endcanany
        </div>
    </div>
</section>
@endsection
