@extends('layouts.app')

@section('title', 'Calendario de actividades | AgroFusion')
@section('page_title', 'Calendario de actividades')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('actividades.index') }}">Actividades</a></li>
    <li class="breadcrumb-item active">Calendario</li>
@endsection

@php
    use Illuminate\Support\Str;
    $pctCompletadas = $stats['total'] > 0
        ? round(($stats['completadas'] / $stats['total']) * 100)
        : 0;
    $coloresTipo = [
        'siembra' => '#28a745',
        'riego' => '#17a2b8',
        'cosecha' => '#e67e00',
        'fumigacion' => '#dc3545',
        'fertilizacion' => '#fd7e14',
        'labranza' => '#6c757d',
        'control-de-plagas' => '#6f42c1',
        'poda' => '#20c997',
        'auditoria-de-calidad' => '#5a6268',
        'capacitacion-de-personal' => '#007bff',
        'monitoreo-iot' => '#6610f2',
    ];
    $iconosTipo = [
        'siembra' => 'fa-seedling',
        'riego' => 'fa-tint',
        'fertilizacion' => 'fa-flask',
        'cosecha' => 'fa-box-open',
        'fumigacion' => 'fa-spray-can',
        'labranza' => 'fa-tractor',
        'control-de-plagas' => 'fa-bug',
        'poda' => 'fa-cut',
        'auditoria-de-calidad' => 'fa-clipboard-check',
        'capacitacion-de-personal' => 'fa-chalkboard-teacher',
        'monitoreo-iot' => 'fa-satellite-dish',
    ];
@endphp

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet" />
@include('partials.modulo-lotes-actividades-styles')
<style>
.page-calendario .card { border-radius: 10px; box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06); }
.page-calendario .legend-dot {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 3px;
    margin-right: 6px;
    vertical-align: middle;
}
.page-calendario .fc {
    font-family: inherit;
}
.page-calendario .fc .fc-button-primary {
    background-color: #2c5530 !important;
    border-color: #2c5530 !important;
}
.page-calendario .fc .fc-button-primary:hover,
.page-calendario .fc .fc-button-primary:not(:disabled).fc-button-active {
    background-color: #4a7c59 !important;
    border-color: #4a7c59 !important;
}
.page-calendario .fc .fc-today-button {
    background-color: #17a2b8 !important;
    border-color: #17a2b8 !important;
}
/* Vista mes: cuadricula limpia, sin barras de eventos */
.page-calendario .fc-dayGridMonth-view .fc-daygrid-day-events,
.page-calendario .fc-dayGridMonth-view .fc-daygrid-event-harness,
.page-calendario .fc-dayGridMonth-view .fc-more-link {
    display: none !important;
}
.page-calendario .fc-daygrid-day {
    cursor: pointer;
}
.page-calendario .fc-daygrid-day:hover .fc-daygrid-day-frame {
    background: #f8fbf8;
}
.page-calendario .dia-badge-calendario {
    position: absolute;
    bottom: 6px;
    left: 50%;
    transform: translateX(-50%);
    min-width: 26px;
    height: 26px;
    line-height: 26px;
    padding: 0 6px;
    border-radius: 13px;
    font-size: 0.75rem;
    font-weight: 700;
    text-align: center;
    background: #2c5530;
    color: #fff;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
    pointer-events: none;
}
.page-calendario .dia-badge-calendario.tiene-pendientes {
    background: #f39c12;
}
.page-calendario .fc-daygrid-day-frame {
    position: relative;
    min-height: 88px;
}
.page-calendario .fc-daygrid-day-number {
    font-size: 0.9rem;
    padding: 6px 8px;
    color: #495057;
}
.page-calendario .fc-day-today .fc-daygrid-day-number {
    background: transparent;
    color: #17a2b8;
    border: 2px solid #17a2b8;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    line-height: 24px;
    text-align: center;
    padding: 0;
    margin: 4px;
    font-weight: 700;
    box-shadow: 0 0 0 2px rgba(23, 162, 184, 0.15);
}
.page-calendario .dia-label-hoy {
    position: absolute;
    bottom: 6px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 0.62rem;
    font-weight: 700;
    color: #17a2b8;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    pointer-events: none;
}
.page-calendario .fc-daygrid-day-frame:has(.dia-badge-calendario) .dia-label-hoy {
    display: none;
}
/* Vista agenda: filas blancas, borde de color (no bloques saturados) */
.page-calendario .fc-list-event {
    background: #fff !important;
    border: 1px solid #e9ecef !important;
    border-left-width: 4px !important;
    margin-bottom: 6px !important;
    border-radius: 4px !important;
    cursor: pointer;
}
.page-calendario .fc-list-event .fc-list-event-title,
.page-calendario .fc-list-event .fc-event-title {
    color: #1a252f !important;
    font-weight: 500 !important;
    font-size: 0.9rem !important;
}
.page-calendario .fc-list-event .fc-list-event-dot,
.page-calendario .fc-list-event .fc-event-time {
    display: none !important;
}
.page-calendario .fc-list-event.event-siembra { border-left-color: #28a745 !important; }
.page-calendario .fc-list-event.event-riego { border-left-color: #17a2b8 !important; }
.page-calendario .fc-list-event.event-cosecha { border-left-color: #e67e00 !important; }
.page-calendario .fc-list-event.event-fumigacion { border-left-color: #dc3545 !important; }
.page-calendario .fc-list-event.event-fertilizacion { border-left-color: #fd7e14 !important; }
.page-calendario .fc-list-event.event-labranza { border-left-color: #6c757d !important; }
.page-calendario .fc-list-event.event-control { border-left-color: #6f42c1 !important; }
.page-calendario .fc-list-event.event-pendiente { border-left-style: dashed !important; }
.page-calendario .fc-list-day-cushion {
    background: #f4f6f9 !important;
    font-weight: 600;
    color: #2c5530;
}
.page-calendario .filtros-tipos .btn-tipo-filtro {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.4rem 0.7rem;
    margin: 0 4px 6px 0;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border: 2px solid var(--tipo-color, #6c757d);
    background: #fff;
    color: var(--tipo-color, #6c757d);
    transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease, transform 0.1s ease;
    opacity: 0.55;
}
.page-calendario .filtros-tipos .btn-tipo-filtro:hover {
    opacity: 0.85;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
}
.page-calendario .filtros-tipos .btn-tipo-filtro:active {
    transform: scale(0.94);
}
.page-calendario .filtros-tipos .btn-tipo-filtro.active {
    opacity: 1;
    background: var(--tipo-color, #6c757d) !important;
    border-color: var(--tipo-color, #6c757d) !important;
    color: #fff !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}
.page-calendario .filtros-tipos .btn-tipo-filtro .tipo-icono {
    width: 1.1em;
    text-align: center;
    font-size: 0.9rem;
}
.page-calendario .filtros-tipos .btn-tipo-filtro.active .tipo-icono {
    color: #fff !important;
}
.page-calendario .filtros-tipos .btn-tipo-filtro .tipo-check {
    display: none;
    font-size: 0.7rem;
}
.page-calendario .filtros-tipos .btn-tipo-filtro.active .tipo-check {
    display: inline-block;
}
.page-calendario #filtro-contador {
    font-size: 0.85rem;
    color: #495057;
    padding: 0.35rem 0.5rem;
    border-radius: 4px;
    transition: background 0.3s ease;
}
.page-calendario #filtro-contador.filtro-actualizado {
    background: #e8f5e9;
}
.page-calendario #filtro-contador.filtro-alerta {
    background: #fff3cd;
    color: #856404;
}
.page-calendario #calendar.calendario-actualizado {
    animation: calPulse 0.45s ease;
}
@keyframes calPulse {
    0% { box-shadow: 0 0 0 0 rgba(44, 85, 48, 0.35); }
    70% { box-shadow: 0 0 0 8px rgba(44, 85, 48, 0); }
    100% { box-shadow: none; }
}
.page-calendario .btn-tipos-ctrl.active {
    background: #2c5530;
    color: #fff;
    border-color: #2c5530;
}
.page-calendario #lista-dia-actividades .list-group-item {
    padding: 0.6rem 0.85rem;
    font-size: 0.9rem;
}
.page-calendario .fc-daygrid-day.fc-day-today .fc-daygrid-day-frame {
    background: rgba(23, 162, 184, 0.08) !important;
    box-shadow: inset 0 0 0 1px rgba(23, 162, 184, 0.25);
}
/* Modal fuera de .page-calendario: estilos por #activityModal */
#activityModal .actividad-detalle-modal {
    border-radius: 18px;
    overflow: hidden;
}
#activityModal .actividad-detalle-hero {
    position: relative;
    padding: 1.85rem 1.65rem 1.45rem;
    color: #fff;
    background: linear-gradient(135deg, #2c5530, #4a7c59);
}
#activityModal .actividad-detalle-hero .close {
    position: absolute;
    top: 1rem;
    right: 1.15rem;
    opacity: 0.92;
    text-shadow: none;
    font-size: 1.4rem;
    padding: 0.15rem 0.35rem;
}
#activityModal .actividad-detalle-hero-icon {
    width: 58px;
    height: 58px;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.22);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.45rem;
    margin-bottom: 0.9rem;
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}
#activityModal .actividad-detalle-hero h4 {
    font-size: 1.45rem;
    font-weight: 700;
    margin: 0 0 0.25rem;
    line-height: 1.25;
    padding-right: 1.5rem;
}
#activityModal .actividad-detalle-hero-lote {
    font-size: 0.9rem;
    opacity: 0.94;
    margin: 0 0 0.85rem;
    padding-right: 0.5rem;
}
#activityModal .actividad-detalle-estado {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.38rem 0.8rem;
    border-radius: 22px;
    background: rgba(255, 255, 255, 0.22);
    border: 1px solid rgba(255, 255, 255, 0.35);
}
#activityModal .actividad-detalle-estado.completada {
    background: rgba(255, 255, 255, 0.96);
    color: #1e7e34;
    border-color: transparent;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
}
#activityModal .actividad-detalle-estado.pendiente {
    background: rgba(255, 193, 7, 0.96);
    color: #856404;
    border-color: transparent;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
#activityModal .actividad-detalle-body {
    padding: 1.4rem 1.55rem 1.3rem !important;
    background: #f4f7f5;
}
#activityModal .actividad-detalle-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.85rem;
}
#activityModal .actividad-detalle-campo {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: #fff;
    border: 1px solid #e3ebe5;
    border-radius: 14px;
    padding: 1rem 1.15rem;
    box-shadow: 0 2px 8px rgba(44, 85, 48, 0.06);
}
#activityModal .campo-icon-wrap {
    width: 46px;
    height: 46px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.1rem;
    color: #fff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
}
#activityModal .campo-icon-responsable { background: linear-gradient(135deg, #2c5530, #4a7c59); }
#activityModal .campo-icon-lote { background: linear-gradient(135deg, #17a2b8, #20c997); }
#activityModal .campo-icon-inicio { background: linear-gradient(135deg, #f39c12, #ffc107); }
#activityModal .campo-icon-fin { background: linear-gradient(135deg, #6f42c1, #8e64e8); }
#activityModal .campo-icon-obs { background: linear-gradient(135deg, #495057, #6c757d); }
#activityModal .campo-texto {
    flex: 1;
    min-width: 0;
}
#activityModal .actividad-detalle-campo .campo-label,
#activityModal .actividad-detalle-obs .campo-label {
    display: block;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #7a8794;
    margin-bottom: 0.3rem;
}
#activityModal .actividad-detalle-campo .campo-valor {
    display: block;
    font-size: 0.95rem;
    font-weight: 600;
    color: #1a252f;
    line-height: 1.4;
    word-break: break-word;
}
#activityModal .actividad-detalle-obs {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-top: 0.95rem;
    background: #fff;
    border: 1px solid #e3ebe5;
    border-radius: 14px;
    padding: 1.05rem 1.15rem;
    box-shadow: 0 2px 8px rgba(44, 85, 48, 0.06);
}
#activityModal .actividad-detalle-obs p {
    margin: 0;
    font-size: 0.9rem;
    color: #495057;
    line-height: 1.55;
}
#activityModal .actividad-detalle-footer {
    background: #fff;
    border-top: 1px solid #e9ecef;
    padding: 1.05rem 1.55rem 1.2rem !important;
    gap: 0.75rem;
}
.page-calendario .fab-button {
    position: fixed;
    bottom: 28px;
    right: 28px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #28a745;
    color: #fff;
    border: none;
    font-size: 22px;
    box-shadow: 0 4px 16px rgba(40, 167, 69, 0.45);
    z-index: 1050;
    transition: transform 0.2s ease;
}
.page-calendario .fab-button:hover {
    transform: scale(1.08);
    color: #fff;
    background: #218838;
}
</style>
@endpush

@section('content')
<div class="modulo-la page-calendario">

    <div class="row mb-2">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['mes'] }}</h3>
                    <p>Este mes</p>
                </div>
                <div class="icon"><i class="fas fa-calendar"></i></div>
                <span class="small-box-footer">{{ now()->translatedFormat('F Y') }}</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $stats['hoy'] }}</h3>
                    <p>Programadas hoy</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-day"></i></div>
                <span class="small-box-footer">{{ now()->format('d/m/Y') }}</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3>{{ $stats['pendientes'] }}</h3>
                    <p>Pendientes</p>
                </div>
                <div class="icon"><i class="fas fa-clock"></i></div>
                <span class="small-box-footer">Por realizar</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-teal">
                <div class="inner">
                    <h3>{{ $stats['completadas'] }}</h3>
                    <p>Completadas</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <span class="small-box-footer">{{ $pctCompletadas }}% del total</span>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Calendario"
            icono="fa-calendar-alt"
            :registros="$eventos->count()"
            registros-label="eventos"
            filtros-target="#filtrosCalendarioPanel"
        >
            <x-slot:tools>
                @can('lotes.update')
                <button type="button" class="btn btn-success btn-sm ml-1" data-toggle="modal" data-target="#newActivityModal">
                    <i class="fas fa-plus mr-1"></i> Nueva Actividad
                </button>
                @endcan
            </x-slot:tools>
        </x-modulo-index-header>

        <div id="filtrosCalendarioPanel" class="filtros-panel collapse show">
            <div class="row align-items-end">
                <div class="col-lg-3 col-md-4 col-6 mb-2">
                    <label class="small text-muted mb-1">Estado</label>
                    <select id="filter-estado" class="form-control form-control-sm">
                        <option value="">Todas</option>
                        <option value="pendiente">Pendientes</option>
                        <option value="completada">Completadas</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-4 col-6 mb-2">
                    <label class="small text-muted mb-1">Lote</label>
                    <select id="filter-lote" class="form-control form-control-sm">
                        <option value="">Todos los lotes</option>
                        @foreach($lotes as $lote)
                            <option value="{{ $lote->loteid }}">{{ $lote->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-4 col-6 mb-2">
                    <label class="small text-muted mb-1">Responsable</label>
                    <select id="filter-usuario" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach($usuarios as $u)
                            <option value="{{ $u->usuarioid }}">{{ $u->nombre }} {{ $u->apellido }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-3 col-md-4 col-6 mb-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" id="btn-limpiar-filtros-cal">
                        <i class="fas fa-eraser mr-1"></i> Limpiar
                    </button>
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center mb-2">
                <span class="small text-muted mr-2">Tipos:</span>
                <button type="button" class="btn btn-outline-secondary btn-sm mr-1 mb-1 btn-tipos-ctrl" id="btn-tipos-todos">
                    <i class="fas fa-check-double mr-1"></i> Todos
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm mr-2 mb-1 btn-tipos-ctrl" id="btn-tipos-ninguno">
                    <i class="fas fa-times mr-1"></i> Ninguno
                </button>
                <span class="small text-muted mb-1">Clic en un tipo para activar o desactivar</span>
            </div>
            <div class="filtros-tipos d-flex flex-wrap">
                @php
                    $tiposPrincipales = ['siembra', 'riego', 'cosecha', 'fumigacion', 'fertilizacion', 'labranza', 'control-de-plagas', 'poda'];
                @endphp
                @foreach($tiposActividad as $tipo)
                    @php
                        $slug = Str::slug($tipo->nombre, '-', 'es');
                        $color = $coloresTipo[$slug] ?? '#6c757d';
                        $icono = $iconosTipo[$slug] ?? 'fa-tasks';
                        $activoDefecto = in_array($slug, $tiposPrincipales, true);
                    @endphp
                    <button type="button"
                        class="btn btn-tipo-filtro {{ $activoDefecto ? 'active' : '' }}"
                        data-tipo-slug="{{ $slug }}"
                        data-color="{{ $color }}"
                        title="{{ $activoDefecto ? 'Activo - clic para ocultar' : 'Inactivo - clic para mostrar' }}"
                        style="--tipo-color: {{ $color }};"
                        aria-pressed="{{ $activoDefecto ? 'true' : 'false' }}">
                        <i class="fas {{ $icono }} tipo-icono"></i>
                        {{ $tipo->nombre }}
                        <i class="fas fa-check tipo-check"></i>
                    </button>
                @endforeach
            </div>
            <div id="filtro-contador" class="mt-1"></div>
            <template id="tpl-alerta-tipos-vacio">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <strong>Ning&uacute;n tipo seleccionado</strong>
                &mdash; activa al menos uno o pulsa &laquo;Todos&raquo;
            </template>
        </div>

        <div class="card-body p-2">
            <div id="calendar" class="calendario-wrap"></div>
        </div>
        <div class="card-footer py-2 d-flex flex-wrap align-items-center" style="gap: 12px;">
            <small class="text-muted">
                <i class="fas fa-mouse-pointer mr-1"></i>
                <strong>Mes:</strong> clic en un d&iacute;a (n&uacute;mero = cantidad de actividades).
            </small>
            <small class="text-muted">
                <span class="dia-badge-calendario d-inline-block" style="position:static;transform:none;min-width:22px;height:22px;line-height:22px;font-size:0.7rem;"></span>
                Total del d&iacute;a
            </small>
            <small class="text-muted">
                <span class="dia-badge-calendario tiene-pendientes d-inline-block" style="position:static;transform:none;min-width:22px;height:22px;line-height:22px;font-size:0.7rem;"></span>
                Con pendientes
            </small>
            <small class="text-muted">
                <span class="d-inline-block mr-1" style="width:22px;height:22px;border:2px solid #17a2b8;border-radius:50%;vertical-align:middle;"></span>
                D&iacute;a actual
            </small>
        </div>
    </div>

</div>

@can('lotes.update')
<button type="button" class="fab-button" data-toggle="modal" data-target="#newActivityModal" title="Nueva actividad">
    <i class="fas fa-plus"></i>
</button>
@endcan

<div class="modal fade" id="dayActivitiesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white py-2">
                <h5 class="modal-title mb-0"><i class="fas fa-calendar-day mr-2"></i> <span id="dia-modal-titulo">Actividades del d&iacute;a</span></h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <div id="lista-dia-actividades" class="list-group list-group-flush"></div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="activityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content actividad-detalle-modal border-0 shadow-lg">
            <div class="actividad-detalle-hero" id="modal-hero">
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar"><span>&times;</span></button>
                <div class="actividad-detalle-hero-icon">
                    <i class="fas fa-tasks" id="modal-hero-icon"></i>
                </div>
                <h4 id="modal-hero-tipo">Actividad</h4>
                <p class="actividad-detalle-hero-lote" id="modal-hero-lote"></p>
                <span class="actividad-detalle-estado pendiente" id="modal-hero-estado">
                    <i class="fas fa-clock"></i> Pendiente
                </span>
            </div>
            <div class="modal-body actividad-detalle-body">
                <div class="actividad-detalle-grid">
                    <div class="actividad-detalle-campo">
                        <div class="campo-icon-wrap campo-icon-responsable"><i class="fas fa-user-check"></i></div>
                        <div class="campo-texto">
                            <span class="campo-label">Responsable</span>
                            <span class="campo-valor" id="modal-responsable">-</span>
                        </div>
                    </div>
                    <div class="actividad-detalle-campo">
                        <div class="campo-icon-wrap campo-icon-lote"><i class="fas fa-map-marked-alt"></i></div>
                        <div class="campo-texto">
                            <span class="campo-label">Lote</span>
                            <span class="campo-valor" id="modal-lote">-</span>
                        </div>
                    </div>
                    <div class="actividad-detalle-campo">
                        <div class="campo-icon-wrap campo-icon-inicio"><i class="fas fa-play-circle"></i></div>
                        <div class="campo-texto">
                            <span class="campo-label">Inicio</span>
                            <span class="campo-valor" id="modal-fecha-inicio">-</span>
                        </div>
                    </div>
                    <div class="actividad-detalle-campo">
                        <div class="campo-icon-wrap campo-icon-fin"><i class="fas fa-flag-checkered"></i></div>
                        <div class="campo-texto">
                            <span class="campo-label">Fin</span>
                            <span class="campo-valor" id="modal-fecha-fin">-</span>
                        </div>
                    </div>
                </div>
                <div class="actividad-detalle-obs">
                    <div class="campo-icon-wrap campo-icon-obs"><i class="fas fa-sticky-note"></i></div>
                    <div class="campo-texto">
                        <span class="campo-label">Observaciones</span>
                        <p id="modal-observaciones">Sin observaciones</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer actividad-detalle-footer d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                @can('lotes.update')
                <a href="#" id="btn-editar-actividad" class="btn btn-success btn-sm">
                    <i class="fas fa-edit mr-1"></i> Editar actividad
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>

@can('lotes.update')
<div class="modal fade" id="newActivityModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-plus mr-2"></i> Nueva actividad</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('actividades.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small text-muted">Tipo de actividad *</label>
                                <select name="tipoactividadid" class="form-control form-control-sm" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($tiposActividad as $tipo)
                                        <option value="{{ $tipo->tipoactividadid }}">{{ $tipo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small text-muted">Lote *</label>
                                <select name="loteid" id="new-loteid" class="form-control form-control-sm" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($lotes as $lote)
                                        <option value="{{ $lote->loteid }}" data-usuarioid="{{ $lote->usuarioid }}">
                                            {{ $lote->nombre }} &mdash; {{ $lote->cultivo->nombre ?? 'Sin cultivo' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            @if(!empty($puedeDesignarResponsable))
                                <div class="form-group">
                                    <label class="small text-muted">Responsable *</label>
                                    <select name="usuarioid" id="new-usuarioid" class="form-control form-control-sm" required>
                                        <option value="">Seleccionar agricultor...</option>
                                        @foreach($usuarios as $u)
                                            <option value="{{ $u->usuarioid }}">{{ $u->nombre }} {{ $u->apellido }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">
                                        @if(!empty($puedeDesignarResponsable) && !empty($esJefeAgricultorDesignando))
                                            Asigne un agricultor de su equipo; él recibirá la alerta.
                                        @else
                                            El responsable recibirá una alerta en su panel.
                                        @endif
                                    </small>
                                </div>
                            @else
                                <input type="hidden" name="usuarioid" value="{{ auth()->user()->usuarioid }}">
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small text-muted">Fecha inicio *</label>
                                <input type="date" name="fechainicio" id="new-fecha-inicio" class="form-control form-control-sm" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="small text-muted">Fecha fin</label>
                                <input type="date" name="fechafin" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="small text-muted">Observaciones</label>
                        <textarea name="observaciones" class="form-control form-control-sm" rows="3" placeholder="Detalles adicionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-save mr-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>window.ACTIVIDADES_PUEDE_EDITAR = @json(auth()->user()->can('lotes.update'));</script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var calendarEl = document.getElementById('calendar');
    var allEvents = @json($eventos);
    var editUrlTemplate = @json(route('actividades.edit', ['actividad' => '__ID__']));
    var coloresTipoMap = @json($coloresTipo);
    var iconosTipoMap = @json($iconosTipo);
    var contadorEl = document.getElementById('filtro-contador');
    var conteosPorDia = {};

    function tiposActivos() {
        var slugs = [];
        document.querySelectorAll('.btn-tipo-filtro.active').forEach(function (btn) {
            slugs.push(btn.getAttribute('data-tipo-slug'));
        });
        return slugs;
    }

    function filtrarEventos() {
        var slugs = tiposActivos();
        var loteId = document.getElementById('filter-lote').value;
        var usuarioId = document.getElementById('filter-usuario').value;
        var estado = document.getElementById('filter-estado').value;

        return allEvents.filter(function (e) {
            var props = e.extendedProps || {};
            if (slugs.length === 0) return false;
            if (!slugs.includes(props.tipoSlug)) return false;
            if (loteId && String(props.loteid) !== String(loteId)) return false;
            if (usuarioId && String(props.usuarioid) !== String(usuarioId)) return false;
            if (estado === 'pendiente' && !props.pendiente) return false;
            if (estado === 'completada' && props.pendiente) return false;
            return true;
        });
    }

    function rebuildConteos() {
        conteosPorDia = {};
        filtrarEventos().forEach(function (e) {
            var fecha = (e.start || '').substring(0, 10);
            if (!fecha) return;
            if (!conteosPorDia[fecha]) {
                conteosPorDia[fecha] = { total: 0, pendientes: 0 };
            }
            conteosPorDia[fecha].total++;
            if (e.extendedProps && e.extendedProps.pendiente) conteosPorDia[fecha].pendientes++;
        });
    }

    function agruparDuplicados(items) {
        var mapa = {};
        items.forEach(function (e) {
            var p = e.extendedProps || {};
            var key = (p.tipoSlug || '') + '|' + (p.loteid || '') + '|' + (p.pendiente ? 'p' : 'c');
            if (!mapa[key]) {
                mapa[key] = {
                    representante: p,
                    tipo: p.tipo,
                    lote: p.lote,
                    pendiente: p.pendiente,
                    cantidad: 0,
                };
            }
            mapa[key].cantidad++;
        });
        return Object.values(mapa);
    }

    function claseTipoEvento(slug) {
        return {
            'control-de-plagas': 'event-control',
            'fumigacion': 'event-fumigacion',
            'fertilizacion': 'event-fertilizacion'
        }[slug] || ('event-' + (slug || '').replace(/-/g, ''));
    }

    function eventosParaAgenda() {
        var grupos = {};
        filtrarEventos().forEach(function (e) {
            var props = e.extendedProps || {};
            var key = (e.start || '') + '|' + props.tipoSlug + '|' + props.loteid + '|' + (props.pendiente ? 'p' : 'c');
            if (!grupos[key]) grupos[key] = { event: e, count: 0 };
            grupos[key].count++;
        });
        return Object.keys(grupos).map(function (key) {
            var g = grupos[key];
            var e = g.event;
            var props = e.extendedProps || {};
            var sufijo = g.count > 1 ? ' (' + g.count + ')' : '';
            return Object.assign({}, e, {
                title: (props.tipo || 'Actividad') + ' - ' + (props.lote || 'N/D') + sufijo,
                classNames: [claseTipoEvento(props.tipoSlug)].concat(props.pendiente ? ['event-pendiente'] : []),
            });
        });
    }

    function pintarBadgesMes() {
        document.querySelectorAll('.dia-badge-calendario, .dia-label-hoy').forEach(function (el) { el.remove(); });
        if (calendar.view.type !== 'dayGridMonth') return;

        document.querySelectorAll('.fc-daygrid-day[data-date]').forEach(function (celda) {
            var fecha = celda.getAttribute('data-date');
            var frame = celda.querySelector('.fc-daygrid-day-frame');
            if (!frame) return;
            var info = conteosPorDia[fecha];
            var esHoy = celda.classList.contains('fc-day-today');

            if (info && info.total > 0) {
                var badge = document.createElement('div');
                badge.className = 'dia-badge-calendario' + (info.pendientes > 0 ? ' tiene-pendientes' : '');
                badge.textContent = info.total;
                badge.title = info.total + ' actividad' + (info.total !== 1 ? 'es' : '');
                frame.appendChild(badge);
            } else if (esHoy) {
                var label = document.createElement('span');
                label.className = 'dia-label-hoy';
                label.textContent = 'Hoy';
                frame.appendChild(label);
            }
        });
    }

    function actualizarContador(n) {
        if (!contadorEl) return;
        var slugs = tiposActivos();
        var html = '<i class="fas fa-filter mr-1"></i> Mostrando <strong>' + n + '</strong> de ' + allEvents.length + ' actividades';
        if (slugs.length === 0) {
            contadorEl.className = 'mt-1 filtro-alerta';
            var tplAlerta = document.getElementById('tpl-alerta-tipos-vacio');
            html = tplAlerta ? tplAlerta.innerHTML : '<strong>Ningun tipo seleccionado</strong> - activa al menos uno o pulsa Todos';
        } else {
            contadorEl.className = 'mt-1 filtro-actualizado';
            setTimeout(function () {
                contadorEl.classList.remove('filtro-actualizado');
            }, 1200);
        }
        contadorEl.innerHTML = html;
    }

    function animarCalendario() {
        var cal = document.getElementById('calendar');
        if (!cal) return;
        cal.classList.remove('calendario-actualizado');
        void cal.offsetWidth;
        cal.classList.add('calendario-actualizado');
    }

    function formatearNombreLote(nombre) {
        if (!nombre) return '-';
        return String(nombre).replace(/^Lote\s+/i, '');
    }

    function colorHeroSecundario(hex) {
        if (!hex || hex.charAt(0) !== '#') return '#4a7c59';
        var r = Math.max(0, parseInt(hex.slice(1, 3), 16) - 28);
        var g = Math.max(0, parseInt(hex.slice(3, 5), 16) - 28);
        var b = Math.max(0, parseInt(hex.slice(5, 7), 16) - 28);
        return 'rgb(' + r + ',' + g + ',' + b + ')';
    }

    function abrirDetalleActividad(props) {
        var slug = props.tipoSlug || '';
        var color = coloresTipoMap[slug] || '#2c5530';
        var icono = iconosTipoMap[slug] || 'fa-tasks';
        var hero = document.getElementById('modal-hero');
        var heroIcon = document.getElementById('modal-hero-icon');
        var heroEstado = document.getElementById('modal-hero-estado');

        if (hero) {
            hero.style.background = 'linear-gradient(135deg, ' + color + ', ' + colorHeroSecundario(color) + ')';
        }
        if (heroIcon) {
            heroIcon.className = 'fas ' + icono;
        }
        var loteFmt = formatearNombreLote(props.lote);
        document.getElementById('modal-hero-tipo').textContent = props.tipo || 'Actividad';
        document.getElementById('modal-hero-lote').textContent = loteFmt !== '-' ? loteFmt : 'Sin lote';
        document.getElementById('modal-responsable').textContent = props.responsable || '-';
        document.getElementById('modal-lote').textContent = loteFmt;
        document.getElementById('modal-fecha-inicio').textContent = props.fechainicioFmt || '-';
        document.getElementById('modal-fecha-fin').textContent = props.pendiente
            ? 'Pendiente (se registra al completar)'
            : (props.fechafin || '-');
        document.getElementById('modal-observaciones').textContent = props.observaciones || 'Sin observaciones';

        if (heroEstado) {
            if (props.pendiente) {
                heroEstado.className = 'actividad-detalle-estado pendiente';
                heroEstado.innerHTML = '<i class="fas fa-clock"></i> Pendiente';
            } else {
                heroEstado.className = 'actividad-detalle-estado completada';
                heroEstado.innerHTML = '<i class="fas fa-check-circle"></i> Completada';
            }
        }

        if (window.ACTIVIDADES_PUEDE_EDITAR) {
            var btnEd = document.getElementById('btn-editar-actividad');
            if (btnEd) btnEd.href = editUrlTemplate.replace('__ID__', props.id);
        }
        $('#activityModal').modal('show');
    }

    function abrirListaDia(fecha) {
        var lista = document.getElementById('lista-dia-actividades');
        var titulo = document.getElementById('dia-modal-titulo');
        if (!lista) return;
        var partes = fecha.split('-');
        var items = filtrarEventos().filter(function (e) {
            return (e.start || '').substring(0, 10) === fecha;
        });
        var grupos = agruparDuplicados(items);

        titulo.textContent = 'Actividades del ' + partes[2] + '/' + partes[1] + '/' + partes[0];
        lista.innerHTML = '';

        if (!grupos.length) {
            lista.innerHTML = '<div class="p-3 text-muted text-center">Sin actividades este d&iacute;a</div>';
        } else {
            grupos.forEach(function (g) {
                var li = document.createElement('button');
                li.type = 'button';
                li.className = 'list-group-item list-group-item-action text-left d-flex justify-content-between align-items-center';
                var texto = '<span><strong>' + g.tipo + '</strong> <span class="text-muted">- ' + g.lote + '</span></span>';
                var badges = g.pendiente
                    ? '<span class="badge badge-warning">Pendiente</span>'
                    : '<span class="badge badge-success">Hecha</span>';
                if (g.cantidad > 1) {
                    badges = '<span class="badge badge-secondary mr-1">x' + g.cantidad + '</span>' + badges;
                }
                li.innerHTML = texto + badges;
                li.addEventListener('click', function () {
                    $('#dayActivitiesModal').modal('hide');
                    abrirDetalleActividad(g.representante);
                });
                lista.appendChild(li);
            });
        }
        $('#dayActivitiesModal').modal('show');
    }

    function refrescarCalendario() {
        rebuildConteos();
        calendar.removeAllEvents();
        if (calendar.view.type === 'dayGridMonth') {
            setTimeout(pintarBadgesMes, 0);
        } else {
            calendar.addEventSource(eventosParaAgenda());
        }
        actualizarContador(filtrarEventos().length);
        animarCalendario();
        var badge = document.querySelector('.badge-registros');
        if (badge) {
            var n = filtrarEventos().length;
            badge.textContent = n + ' eventos';
        }
    }

    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        height: 'auto',
        firstDay: 1,
        fixedWeekCount: false,
        displayEventTime: false,
        noEventsText: 'No hay actividades con estos filtros',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,listMonth'
        },
        buttonText: { today: 'Hoy', month: 'Mes', list: 'Agenda' },
        listDayFormat: { weekday: 'long', day: 'numeric', month: 'long' },
        listDaySideFormat: false,
        events: [],
        datesSet: function () {
            refrescarCalendario();
        },
        dayCellDidMount: function () {
            if (calendar.view.type === 'dayGridMonth') {
                pintarBadgesMes();
            }
        },
        eventClick: function (info) {
            abrirDetalleActividad(info.event.extendedProps || {});
        },
        dateClick: function (info) {
            var fecha = info.dateStr;
            var delDia = conteosPorDia[fecha];
            if (delDia && delDia.total > 0) {
                abrirListaDia(fecha);
                return;
            }
            if (!window.ACTIVIDADES_PUEDE_EDITAR) return;
            var elFecha = document.getElementById('new-fecha-inicio');
            if (elFecha) elFecha.value = fecha;
            $('#newActivityModal').modal('show');
        },
        eventDidMount: function (info) {
            var props = info.event.extendedProps || {};
            (info.event.classNames || []).forEach(function (c) {
                info.el.classList.add(c);
            });
            if (props.pendiente) info.el.classList.add('event-pendiente');
        }
    });

    calendar.render();
    rebuildConteos();
    actualizarContador(filtrarEventos().length);
    setTimeout(pintarBadgesMes, 50);

    function aplicarFiltros() {
        refrescarCalendario();
    }

    function setBtnTipoActivo(btn, activo) {
        btn.classList.toggle('active', activo);
        btn.setAttribute('aria-pressed', activo ? 'true' : 'false');
        btn.title = activo ? 'Activo - clic para ocultar' : 'Inactivo - clic para mostrar';
    }

    document.querySelectorAll('.btn-tipo-filtro').forEach(function (btn) {
        btn.addEventListener('click', function () {
            setBtnTipoActivo(btn, !btn.classList.contains('active'));
            aplicarFiltros();
        });
    });

    document.getElementById('btn-tipos-todos').addEventListener('click', function () {
        document.querySelectorAll('.btn-tipo-filtro').forEach(function (btn) {
            setBtnTipoActivo(btn, true);
        });
        aplicarFiltros();
    });

    document.getElementById('btn-tipos-ninguno').addEventListener('click', function () {
        document.querySelectorAll('.btn-tipo-filtro').forEach(function (btn) {
            setBtnTipoActivo(btn, false);
        });
        aplicarFiltros();
    });

    document.getElementById('filter-lote').addEventListener('change', aplicarFiltros);
    document.getElementById('filter-usuario').addEventListener('change', aplicarFiltros);
    document.getElementById('filter-estado').addEventListener('change', aplicarFiltros);

    document.getElementById('btn-limpiar-filtros-cal').addEventListener('click', function () {
        document.getElementById('filter-lote').value = '';
        document.getElementById('filter-usuario').value = '';
        document.getElementById('filter-estado').value = '';
        var principales = ['siembra', 'riego', 'cosecha', 'fumigacion', 'fertilizacion', 'labranza', 'control-de-plagas', 'poda'];
        document.querySelectorAll('.btn-tipo-filtro').forEach(function (btn) {
            var esPrincipal = principales.includes(btn.getAttribute('data-tipo-slug'));
            setBtnTipoActivo(btn, esPrincipal);
        });
        aplicarFiltros();
    });

    var newLote = document.getElementById('new-loteid');
    var newUsuario = document.getElementById('new-usuarioid');
    var idsResponsablesPermitidos = @json($usuarios->pluck('usuarioid')->map(fn ($id) => (int) $id)->values());
    if (newLote && newUsuario) {
        newLote.addEventListener('change', function () {
            var opt = newLote.options[newLote.selectedIndex];
            var uid = opt && opt.getAttribute('data-usuarioid');
            if (uid && !newUsuario.value && idsResponsablesPermitidos.includes(Number(uid))) {
                newUsuario.value = uid;
            }
        });
    }
});
</script>
@endpush
