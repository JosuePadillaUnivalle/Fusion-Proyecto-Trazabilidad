@extends('layouts.app')

@section('title', 'Calendario de Actividades | AgroNexus')
@section('page_title', 'Calendario de Actividades')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('actividades.index') }}" style="color: #2c5530;">Actividades</a></li>
    <li class="breadcrumb-item active">Calendario</li>
@endsection

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
<style>
    :root {
        --primary-color: #2c5530;
        --secondary-color: #4a7c59;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --info-color: #17a2b8;
        --text-dark: #1a252f;
        --text-light: #6c757d;
    }

    /* Estilos del calendario */
    .fc { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .fc-header-toolbar {
        background: white;
        padding: 15px;
        border-radius: 10px 10px 0 0;
        margin-bottom: 0 !important;
    }
    .fc-button {
        background: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
        font-weight: 500;
    }
    .fc-button:hover {
        background: var(--secondary-color) !important;
        border-color: var(--secondary-color) !important;
    }
    .fc-button-active {
        background: var(--secondary-color) !important;
    }
    .fc-today-button {
        background: var(--info-color) !important;
        border-color: var(--info-color) !important;
    }
    .fc-daygrid-day { min-height: 100px; cursor: pointer; }
    .fc-daygrid-day:hover { background: #f8f9fc; }
    .fc-daygrid-day-number { color: var(--text-dark); font-weight: 600; padding: 8px; }
    .fc-daygrid-day.fc-day-today { background: rgba(40, 167, 69, 0.1) !important; }
    .fc-daygrid-day.fc-day-today .fc-daygrid-day-number { color: var(--success-color); font-weight: 700; }

    /* Eventos por tipo - texto siempre visible */
    .fc-event { 
        border-radius: 4px; 
        padding: 3px 6px; 
        font-size: 12px; 
        cursor: pointer; 
        font-weight: 600;
        color: white !important;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }
    .fc-event .fc-event-title { color: white !important; }
    .fc-event .fc-event-time { color: rgba(255,255,255,0.9) !important; }
    
    .fc-event.event-siembra { background: #28a745 !important; border-color: #1e7e34 !important; }
    .fc-event.event-riego { background: #17a2b8 !important; border-color: #117a8b !important; }
    .fc-event.event-cosecha { background: #e67e00 !important; border-color: #cc7000 !important; color: white !important; }
    .fc-event.event-fumigacion, .fc-event.event-fumigación { background: #dc3545 !important; border-color: #bd2130 !important; }
    .fc-event.event-preparacion, .fc-event.event-labranza { background: #6c757d !important; border-color: #545b62 !important; }
    .fc-event.event-fertilizacion, .fc-event.event-fertilización { background: #fd7e14 !important; border-color: #dc6502 !important; }
    .fc-event.event-control { background: #6f42c1 !important; border-color: #5a32a3 !important; }
    .fc-event.event-poda { background: #20c997 !important; border-color: #17a085 !important; }
    
    /* Vista de lista - texto visible */
    .fc-list-event-title { color: #1a252f !important; font-weight: 500; }
    .fc-list-event-time { color: #495057 !important; }

    /* Stats cards */
    .stats-card {
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        color: white;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .stats-card h3 { font-size: 32px; font-weight: 300; margin: 5px 0; }
    .stats-card p { opacity: 0.9; margin-bottom: 0; font-size: 14px; }

    /* Filtros */
    .activity-filters {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .filter-checkbox { margin-right: 15px; margin-bottom: 10px; display: inline-block; }
    .legend-color { width: 15px; height: 15px; display: inline-block; margin-right: 5px; border-radius: 3px; vertical-align: middle; }

    /* Modal */
    .modal-header { background: var(--primary-color); color: white; }
    .modal-header .close { color: white; opacity: 0.8; }
    .modal-header .close:hover { opacity: 1; }

    .activity-detail-item {
        display: flex;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f1f3f4;
    }
    .activity-detail-item:last-child { border-bottom: none; }
    .activity-detail-icon {
        width: 40px; height: 40px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin-right: 15px; font-size: 16px; color: white;
    }
    .activity-detail-info h6 { margin: 0; font-weight: 600; color: var(--text-dark); }
    .activity-detail-info small { color: var(--text-light); }

    /* Botón flotante */
    .fab-button {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: var(--success-color);
        color: white;
        border: none;
        font-size: 24px;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        transition: all 0.3s ease;
        z-index: 1000;
    }
    .fab-button:hover {
        background: #218838;
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(40, 167, 69, 0.6);
    }

    .card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
</style>
@endpush

@section('content')
<!-- Estadísticas -->
<div class="row mb-3">
    <div class="col-md-3 col-sm-6">
        <div class="stats-card">
            <h3>{{ $stats['mes'] }}</h3>
            <p>Actividades este mes</p>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stats-card" style="background: linear-gradient(135deg, var(--info-color), #20c997);">
            <h3>{{ $stats['hoy'] }}</h3>
            <p>Actividades hoy</p>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stats-card" style="background: linear-gradient(135deg, var(--warning-color), #ffca2c);">
            <h3>{{ $stats['pendientes'] }}</h3>
            <p>Pendientes</p>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stats-card" style="background: linear-gradient(135deg, var(--success-color), #34ce57);">
            <h3>{{ $stats['completadas'] }}</h3>
            <p>Completadas</p>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="activity-filters">
    <div class="row">
        <div class="col-md-8">
            <h5 class="mb-3"><i class="fas fa-filter mr-2"></i>Filtrar por Tipo</h5>
            <div class="custom-control custom-checkbox filter-checkbox">
                <input type="checkbox" class="custom-control-input filter-tipo" id="filter-siembra" value="siembra" checked>
                <label class="custom-control-label" for="filter-siembra">
                    <span class="legend-color" style="background: var(--success-color);"></span> Siembra
                </label>
            </div>
            <div class="custom-control custom-checkbox filter-checkbox">
                <input type="checkbox" class="custom-control-input filter-tipo" id="filter-riego" value="riego" checked>
                <label class="custom-control-label" for="filter-riego">
                    <span class="legend-color" style="background: var(--info-color);"></span> Riego
                </label>
            </div>
            <div class="custom-control custom-checkbox filter-checkbox">
                <input type="checkbox" class="custom-control-input filter-tipo" id="filter-cosecha" value="cosecha" checked>
                <label class="custom-control-label" for="filter-cosecha">
                    <span class="legend-color" style="background: var(--warning-color);"></span> Cosecha
                </label>
            </div>
            <div class="custom-control custom-checkbox filter-checkbox">
                <input type="checkbox" class="custom-control-input filter-tipo" id="filter-fumigacion" value="fumigación" checked>
                <label class="custom-control-label" for="filter-fumigacion">
                    <span class="legend-color" style="background: var(--danger-color);"></span> Fumigacion
                </label>
            </div>
            <div class="custom-control custom-checkbox filter-checkbox">
                <input type="checkbox" class="custom-control-input filter-tipo" id="filter-labranza" value="labranza" checked>
                <label class="custom-control-label" for="filter-labranza">
                    <span class="legend-color" style="background: #6c757d;"></span> Labranza
                </label>
            </div>
            <div class="custom-control custom-checkbox filter-checkbox">
                <input type="checkbox" class="custom-control-input filter-tipo" id="filter-fertilizacion" value="fertilización" checked>
                <label class="custom-control-label" for="filter-fertilizacion">
                    <span class="legend-color" style="background: #fd7e14;"></span> Fertilizacion
                </label>
            </div>
        </div>
        <div class="col-md-4">
            <h5 class="mb-3"><i class="fas fa-search mr-2"></i>Buscar</h5>
            <div class="form-group">
                <select class="form-control form-control-sm" id="filter-lote">
                    <option value="">Todos los lotes</option>
                    @foreach($lotes as $lote)
                        <option value="{{ $lote->loteid }}">{{ $lote->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <select class="form-control form-control-sm" id="filter-usuario">
                    <option value="">Todos los responsables</option>
                    @foreach($usuarios as $u)
                        <option value="{{ $u->usuarioid }}">{{ $u->nombre }} {{ $u->apellido }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<!-- Calendario -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-0">
                <div id="calendar"></div>
            </div>
        </div>
    </div>
</div>

@can('lotes.update')
<!-- Botón flotante nueva actividad -->
<button class="fab-button" data-toggle="modal" data-target="#newActivityModal" title="Nueva Actividad">
    <i class="fas fa-plus"></i>
</button>
@endcan

<!-- Modal Detalle de Actividad -->
<div class="modal fade" id="activityModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-info-circle mr-2"></i>Detalles de la Actividad</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="activity-detail-item">
                            <div class="activity-detail-icon" style="background: var(--primary-color);"><i class="fas fa-tasks"></i></div>
                            <div class="activity-detail-info">
                                <h6>Tipo de Actividad</h6>
                                <small id="modal-tipo">-</small>
                            </div>
                        </div>
                        <div class="activity-detail-item">
                            <div class="activity-detail-icon" style="background: var(--info-color);"><i class="fas fa-map-marked-alt"></i></div>
                            <div class="activity-detail-info">
                                <h6>Lote</h6>
                                <small id="modal-lote">-</small>
                            </div>
                        </div>
                        <div class="activity-detail-item">
                            <div class="activity-detail-icon" style="background: var(--secondary-color);"><i class="fas fa-user"></i></div>
                            <div class="activity-detail-info">
                                <h6>Responsable</h6>
                                <small id="modal-responsable">-</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="activity-detail-item">
                            <div class="activity-detail-icon" style="background: var(--warning-color);"><i class="fas fa-calendar"></i></div>
                            <div class="activity-detail-info">
                                <h6>Fecha Inicio</h6>
                                <small id="modal-fecha-inicio">-</small>
                            </div>
                        </div>
                        <div class="activity-detail-item">
                            <div class="activity-detail-icon" style="background: var(--success-color);"><i class="fas fa-calendar-check"></i></div>
                            <div class="activity-detail-info">
                                <h6>Fecha Fin</h6>
                                <small id="modal-fecha-fin">-</small>
                            </div>
                        </div>
                        <div class="activity-detail-item">
                            <div class="activity-detail-icon" style="background: var(--danger-color);"><i class="fas fa-flag"></i></div>
                            <div class="activity-detail-info">
                                <h6>Estado</h6>
                                <small id="modal-estado"><span class="badge badge-warning">Pendiente</span></small>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="form-group">
                    <label><i class="fas fa-comment mr-2"></i>Observaciones</label>
                    <p id="modal-observaciones" class="text-muted">-</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i>Cerrar</button>
                @can('lotes.update')
                <a href="#" id="btn-editar-actividad" class="btn btn-primary"><i class="fas fa-edit mr-1"></i>Editar</a>
                @endcan
            </div>
        </div>
    </div>
</div>

@can('lotes.update')
<!-- Modal Nueva Actividad -->
<div class="modal fade" id="newActivityModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus mr-2"></i>Nueva Actividad</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('actividades.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-tasks mr-1"></i>Tipo de Actividad *</label>
                                <select name="tipoactividadid" class="form-control" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($tiposActividad as $tipo)
                                        <option value="{{ $tipo->tipoactividadid }}">{{ $tipo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-map-marked-alt mr-1"></i>Lote *</label>
                                <select name="loteid" class="form-control" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($lotes as $lote)
                                        <option value="{{ $lote->loteid }}">{{ $lote->nombre }} - {{ $lote->cultivo->nombre ?? 'Sin cultivo' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-calendar mr-1"></i>Fecha Inicio *</label>
                                <input type="date" name="fechainicio" id="new-fecha-inicio" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><i class="fas fa-calendar-check mr-1"></i>Fecha Fin</label>
                                <input type="date" name="fechafin" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-comment mr-1"></i>Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3" placeholder="Detalles adicionales..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i>Guardar Actividad</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan
@endsection

@push('scripts')
<script>window.ACTIVIDADES_PUEDE_EDITAR = @json(auth()->user()->can('lotes.update'));</script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var allEvents = @json($eventos);

    var calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        buttonText: {
            today: 'Hoy',
            month: 'Mes',
            week: 'Semana',
            list: 'Lista'
        },
        events: allEvents,
        eventClick: function(info) {
            var event = info.event;
            var props = event.extendedProps;
            
            document.getElementById('modal-tipo').textContent = props.tipo || '-';
            document.getElementById('modal-lote').textContent = props.lote || '-';
            document.getElementById('modal-responsable').textContent = props.responsable || '-';
            document.getElementById('modal-fecha-inicio').textContent = event.startStr || '-';
            document.getElementById('modal-fecha-fin').textContent = props.fechafin || 'No definida';
            document.getElementById('modal-observaciones').textContent = props.observaciones || 'Sin observaciones';
            
            var estadoBadge = props.fechafin ? 
                '<span class="badge badge-success">Completada</span>' : 
                '<span class="badge badge-warning">Pendiente</span>';
            document.getElementById('modal-estado').innerHTML = estadoBadge;
            
            if (window.ACTIVIDADES_PUEDE_EDITAR) {
                var btnEd = document.getElementById('btn-editar-actividad');
                if (btnEd) btnEd.href = '/actividades/' + props.id + '/edit';
            }
            
            $('#activityModal').modal('show');
        },
        dateClick: function(info) {
            if (!window.ACTIVIDADES_PUEDE_EDITAR) return;
            var elFecha = document.getElementById('new-fecha-inicio');
            if (elFecha) elFecha.value = info.dateStr;
            if (document.getElementById('newActivityModal')) $('#newActivityModal').modal('show');
        },
        eventDidMount: function(info) {
            var tipo = (info.event.extendedProps.tipo || '').toLowerCase()
                .normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            info.el.classList.add('event-' + tipo);
        }
    });

    calendar.render();

    // Filtrar por tipo de actividad
    function aplicarFiltros() {
        var tiposActivos = [];
        document.querySelectorAll('.filter-tipo:checked').forEach(function(cb) {
            tiposActivos.push(cb.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, ""));
        });

        var loteId = document.getElementById('filter-lote').value;
        var usuarioId = document.getElementById('filter-usuario').value;

        var filtrados = allEvents.filter(function(e) {
            var tipoEvento = (e.extendedProps.tipo || '').toLowerCase()
                .normalize("NFD").replace(/[\u0300-\u036f]/g, "");
            
            if (!tiposActivos.includes(tipoEvento)) return false;
            if (loteId && e.extendedProps.loteid != loteId) return false;
            if (usuarioId && e.extendedProps.usuarioid != usuarioId) return false;
            
            return true;
        });

        calendar.removeAllEvents();
        calendar.addEventSource(filtrados);
    }

    document.querySelectorAll('.filter-tipo').forEach(function(cb) {
        cb.addEventListener('change', aplicarFiltros);
    });
    document.getElementById('filter-lote').addEventListener('change', aplicarFiltros);
    document.getElementById('filter-usuario').addEventListener('change', aplicarFiltros);
});
</script>
@endpush