@extends('layouts.app')

@section('title', 'Panel Planta | AgroFusion')
@section('page_title', 'Panel Planta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Panel Planta</li>
@endsection

@push('styles')
<style>
.planta-panel-hero {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 42%, #f8fafc 100%);
    border: 1px solid rgba(22, 163, 74, .15);
    border-radius: 16px;
    padding: 1.35rem 1.5rem;
    margin-bottom: 1.25rem;
    position: relative;
    overflow: hidden;
}
.planta-panel-hero::after {
    content: '';
    position: absolute;
    top: -50px; right: -40px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(34, 197, 94, .16) 0%, transparent 70%);
    pointer-events: none;
}
.planta-panel-hero__title {
    font-size: 1.4rem;
    font-weight: 800;
    color: #14532d;
    margin-bottom: .2rem;
}
.planta-panel-hero__title i {
    display: inline-flex;
    align-items: center; justify-content: center;
    width: 36px; height: 36px;
    border-radius: 10px;
    background: linear-gradient(135deg, #16a34a, #22c55e);
    color: #fff;
    font-size: .95rem;
    margin-right: .55rem;
    box-shadow: 0 4px 12px rgba(22, 163, 74, .28);
    vertical-align: middle;
}
.planta-panel-hero__sub { color: #4b5563; font-size: .9rem; margin: 0; }

.planta-metrics {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: .75rem;
    margin-bottom: 1.25rem;
}
@media (max-width: 1200px) { .planta-metrics { grid-template-columns: repeat(3, 1fr); } }
@media (max-width: 576px) { .planta-metrics { grid-template-columns: repeat(2, 1fr); } }
.planta-metric {
    border-radius: 14px;
    padding: 1rem 1.1rem;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 6px 18px rgba(15, 23, 42, .1);
}
.planta-metric__val { font-size: 1.65rem; font-weight: 800; line-height: 1; margin-bottom: .15rem; }
.planta-metric__lbl { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; opacity: .92; margin: 0; }
.planta-metric__icon {
    position: absolute; right: 12px; top: 10px;
    font-size: 1.6rem; opacity: .22;
}
.planta-metric--pedidos { background: linear-gradient(135deg, #15803d, #22c55e); }
.planta-metric--asig { background: linear-gradient(135deg, #1d4ed8, #3b82f6); }
.planta-metric--rutas { background: linear-gradient(135deg, #0369a1, #0ea5e9); }
.planta-metric--inc { background: linear-gradient(135deg, #c2410c, #f59e0b); }
.planta-metric--doc { background: linear-gradient(135deg, #475569, #64748b); }

.planta-acc-card {
    border: 0;
    border-radius: 16px;
    box-shadow: 0 8px 28px rgba(15, 23, 42, .08);
    overflow: hidden;
    background: #fff;
}
.planta-acc-card__head {
    background: #fafbfc;
    border-bottom: 1px solid #e8edf2;
    padding: .9rem 1.25rem;
}
.planta-acc-card__head h3 {
    font-size: .95rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}
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
    border-color: #86efac;
    box-shadow: 0 4px 14px rgba(22, 163, 74, .12);
    transform: translateY(-1px);
    color: #14532d;
}
.planta-acc-tile__icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: .95rem; flex-shrink: 0; color: #fff;
}
.planta-acc-tile__icon--log { background: linear-gradient(135deg, #2563eb, #3b82f6); }
.planta-acc-tile__icon--prod { background: linear-gradient(135deg, #15803d, #22c55e); }
.planta-acc-tile__icon--com { background: linear-gradient(135deg, #7c3aed, #8b5cf6); }
.planta-acc-tile__icon--adm { background: linear-gradient(135deg, #475569, #64748b); }
.planta-acc-tile__icon--warn { background: linear-gradient(135deg, #c2410c, #f59e0b); }
.planta-acc-tile__icon--dark { background: linear-gradient(135deg, #1e293b, #334155); }
.planta-acc-tile__lbl { font-size: .88rem; font-weight: 700; color: #1e293b; line-height: 1.2; }
.planta-acc-tile__sub { font-size: .72rem; color: #94a3b8; margin-top: .15rem; display: block; }
</style>
@endpush

@section('content')
<section class="content px-0">
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
            <div class="planta-metric planta-metric--pedidos">
                <i class="fas fa-boxes planta-metric__icon"></i>
                <div class="planta-metric__val">{{ $stats['pedidos_totales'] }}</div>
                <p class="planta-metric__lbl">Pedidos</p>
            </div>
            <div class="planta-metric planta-metric--asig">
                <i class="fas fa-user-tag planta-metric__icon"></i>
                <div class="planta-metric__val">{{ $stats['asignaciones'] }}</div>
                <p class="planta-metric__lbl">Asignaciones</p>
            </div>
            <div class="planta-metric planta-metric--rutas">
                <i class="fas fa-route planta-metric__icon"></i>
                <div class="planta-metric__val">{{ $stats['rutas_activas'] }}</div>
                <p class="planta-metric__lbl">Rutas activas</p>
            </div>
            <div class="planta-metric planta-metric--inc">
                <i class="fas fa-exclamation-triangle planta-metric__icon"></i>
                <div class="planta-metric__val">{{ $stats['incidentes_abiertos'] }}</div>
                <p class="planta-metric__lbl">Incidentes</p>
            </div>
            <div class="planta-metric planta-metric--doc">
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

            @canany(['documentos.view', 'incidentes.view', 'ventas.view'])
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
                    @can('ventas.view')
                    <a href="{{ route('ventas.index') }}" class="planta-acc-tile">
                        <span class="planta-acc-tile__icon planta-acc-tile__icon--dark"><i class="fas fa-receipt"></i></span>
                        <span>
                            <span class="planta-acc-tile__lbl">Notas de venta</span>
                            <span class="planta-acc-tile__sub">Registro comercial</span>
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
