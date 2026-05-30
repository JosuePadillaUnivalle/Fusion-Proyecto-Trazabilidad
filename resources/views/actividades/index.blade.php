@extends('layouts.app')

@section('title', 'Actividades | AgroNexus')
@section('page_title', 'Gestión de actividades')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.index') }}">Lotes</a></li>
    <li class="breadcrumb-item active">Actividades</li>
@endsection

@php
    $filtrosActivos = collect($filtros ?? [])->filter(fn ($v) => $v !== null && $v !== '');
    $prioridadBadge = fn ($nombre) => match (strtolower($nombre ?? '')) {
        'alta' => 'danger',
        'media' => 'warning',
        default => 'secondary',
    };
    $pctCompletadas = $stats['total'] > 0
        ? round(($stats['completadas'] / $stats['total']) * 100)
        : 0;
@endphp

@push('styles')
@include('partials.modulo-lotes-actividades-styles')
<style>
.page-actividades .table-actividades thead th {
    background: #f4f6f9;
    border-bottom: 2px solid #dee2e6;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    color: #6c757d;
    font-weight: 600;
    white-space: nowrap;
}
.page-actividades .table-actividades tbody td {
    vertical-align: middle;
    padding: 0.85rem 0.75rem;
    font-size: 0.9rem;
}
.page-actividades .table-actividades tbody tr:hover { background: #f8fbf8; }
.page-actividades .act-tipo {
    font-weight: 600;
    color: #2c5530;
}
.page-actividades .act-tipo:hover { color: #1e3d22; text-decoration: none; }
.page-actividades .meta-chip {
    display: inline-block;
    font-size: 0.75rem;
    color: #6c757d;
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 2px 8px;
    margin: 2px 4px 2px 0;
}
.page-actividades .act-row-card {
    display: flex;
    align-items: center;
    padding: 0.85rem 1.25rem;
    border-bottom: 1px solid #f1f3f4;
    transition: background 0.15s ease;
}
.page-actividades .act-row-card:hover { background: #f8fbf8; }
.page-actividades .act-avatar {
    width: 42px;
    height: 42px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin-right: 1rem;
}
.page-actividades .act-avatar.pendiente { background: #fff8e1; color: #f39c12; }
.page-actividades .act-avatar.completada { background: #e8f5e9; color: #28a745; }
.page-actividades .btn-actions .btn {
    padding: 0.2rem 0.45rem;
    line-height: 1.2;
}
.page-actividades .btn-realizada {
    background: #28a745;
    border: none;
    color: #fff;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}
.page-actividades .btn-realizada:hover { color: #fff; background: #218838; }
</style>
@endpush

@section('content')
<div class="modulo-la page-actividades">

    <div class="row mb-2">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner"><h3>{{ $stats['total'] }}</h3><p>Total actividades</p></div>
                <div class="icon"><i class="fas fa-tasks"></i></div>
                <span class="small-box-footer">Historial completo</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner"><h3>{{ $stats['pendientes'] }}</h3><p>Pendientes</p></div>
                <div class="icon"><i class="fas fa-clock"></i></div>
                <span class="small-box-footer">Por completar</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner"><h3>{{ $stats['completadas'] }}</h3><p>Completadas</p></div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <span class="small-box-footer">{{ $pctCompletadas }}% del total</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-red">
                <div class="inner"><h3>{{ $stats['hoy'] }}</h3><p>Hoy</p></div>
                <div class="icon"><i class="fas fa-calendar-day"></i></div>
                <a href="{{ route('actividades.calendario') }}" class="small-box-footer">
                    Calendario <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Actividades"
            icono="fa-tasks"
            :registros="$actividades->total()"
            filtros-target="#filtrosActividadesPanel"
            :view-toggle="true"
            view-default="table"
            :nuevo-href="route('actividades.create')"
            nuevo-text="Nueva"
            nuevo-can="lotes.update"
        />

        <div id="filtrosActividadesPanel" class="filtros-panel collapse {{ $filtrosActivos->isNotEmpty() ? 'show' : '' }}">
            <form method="GET" action="{{ route('actividades.index') }}">
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-2">
                        <label class="small text-muted mb-1">Buscar</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" name="q" class="form-control"
                                value="{{ $filtros['q'] ?? '' }}" placeholder="Tipo, lote, responsable o descripción">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6 mb-2">
                        <label class="small text-muted mb-1">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="pendiente" @selected(($filtros['estado'] ?? '') === 'pendiente')>Pendientes</option>
                            <option value="completada" @selected(($filtros['estado'] ?? '') === 'completada')>Completadas</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-3 col-6 mb-2">
                        <label class="small text-muted mb-1">Lote</label>
                        <select name="loteid" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($lotes as $lote)
                                <option value="{{ $lote->loteid }}" @selected(($filtros['loteid'] ?? '') == $lote->loteid)>{{ $lote->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-3 col-6 mb-2">
                        <label class="small text-muted mb-1">Tipo</label>
                        <select name="tipoactividadid" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($tiposActividad as $tipo)
                                <option value="{{ $tipo->tipoactividadid }}" @selected(($filtros['tipoactividadid'] ?? '') == $tipo->tipoactividadid)>{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <x-filtros-form-actions
                    :limpiar-url="route('actividades.index', ['filtros_abiertos' => 1])"
                    :resultados="$filtrosActivos->isNotEmpty() ? $actividades->total() : null"
                />
            </form>
        </div>

        {{-- Tabla (por defecto) --}}
        <div id="tableView" class="table-responsive">
            <table class="table table-actividades table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Lote</th>
                        <th>Responsable</th>
                        <th>Inicio</th>
                        <th title="La fecha fin se registra al marcar la actividad como realizada">Fin</th>
                        <th>Estado</th>
                        <th class="text-center" style="min-width: 140px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($actividades as $act)
                        @php $esCompletada = $act->fechafin !== null; @endphp
                        <tr>
                            <td>
                                <a href="{{ route('actividades.show', $act) }}" class="act-tipo d-block">
                                    {{ $act->tipoActividad->nombre ?? '—' }}
                                </a>
                                @if($act->prioridad)
                                <span class="badge badge-{{ $prioridadBadge($act->prioridad->nombre) }} badge-sm">
                                    {{ $act->prioridad->nombre }}
                                </span>
                                @endif
                            </td>
                            <td>
                                <span class="text-dark">{{ $act->lote->nombre ?? '—' }}</span>
                                @if($act->lote?->cultivo)
                                <br><small class="text-muted">{{ $act->lote->cultivo->nombre }}</small>
                                @endif
                            </td>
                            <td class="text-muted">{{ $act->usuario->nombre ?? '—' }}</td>
                            <td>{{ $act->fechainicio ? \Carbon\Carbon::parse($act->fechainicio)->format('d/m/Y') : '—' }}</td>
                            <td>
                                @if($esCompletada)
                                    {{ \Carbon\Carbon::parse($act->fechafin)->format('d/m/Y') }}
                                @else
                                    <span class="badge badge-light border text-muted font-weight-normal">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $esCompletada ? 'success' : 'warning' }}">
                                    {{ $esCompletada ? 'Completada' : 'Pendiente' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm btn-actions">
                                    @if(!$esCompletada)
                                        @can('lotes.update')
                                        <form action="{{ route('actividades.marcar-realizada', $act) }}" method="POST" class="d-inline form-realizada">
                                            @csrf
                                            <button type="button" class="btn btn-realizada btn-marcar-realizada"
                                                data-tipo="{{ $act->tipoActividad->nombre ?? 'Actividad' }}"
                                                data-lote="{{ $act->lote->nombre ?? 'Sin lote' }}"
                                                title="Marcar realizada">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    @endif
                                    <a href="{{ route('actividades.show', $act) }}" class="btn btn-default" title="Ver">
                                        <i class="fas fa-eye text-info"></i>
                                    </a>
                                    @can('lotes.update')
                                    <a href="{{ route('actividades.edit', $act) }}" class="btn btn-default" title="Editar">
                                        <i class="fas fa-edit text-warning"></i>
                                    </a>
                                    <form action="{{ route('actividades.destroy', $act) }}" method="POST" class="d-inline form-eliminar">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-default btn-eliminar" title="Eliminar">
                                            <i class="fas fa-trash text-danger"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @if($act->descripcion)
                        <tr class="bg-light">
                            <td colspan="7" class="py-2 pl-4 text-muted small border-0">
                                <i class="fas fa-comment-alt mr-1"></i>{{ Str::limit($act->descripcion, 120) }}
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="fas fa-tasks fa-2x mb-2 text-light d-block"></i>
                                No hay actividades que coincidan.
                                @can('lotes.update')
                                <a href="{{ route('actividades.create') }}" class="d-block mt-2">Registrar actividad</a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Tarjetas (alternativa) --}}
        <div id="cardView" style="display: none;">
            @forelse($actividades as $act)
                @php $esCompletada = $act->fechafin !== null; @endphp
                <div class="act-row-card">
                    <div class="act-avatar {{ $esCompletada ? 'completada' : 'pendiente' }}">
                        <i class="fas fa-{{ $esCompletada ? 'check' : 'clock' }}"></i>
                    </div>
                    <div class="flex-grow-1 min-width-0 mr-2">
                        <a href="{{ route('actividades.show', $act) }}" class="act-tipo">
                            {{ $act->tipoActividad->nombre ?? 'Sin tipo' }}
                        </a>
                        <div class="mt-1">
                            <span class="meta-chip">{{ $act->lote->nombre ?? '—' }}</span>
                            @if($act->lote?->cultivo)
                            <span class="meta-chip">{{ $act->lote->cultivo->nombre }}</span>
                            @endif
                            <span class="meta-chip">{{ $act->usuario->nombre ?? '—' }}</span>
                            <span class="meta-chip">
                                {{ $act->fechainicio ? \Carbon\Carbon::parse($act->fechainicio)->format('d/m/Y') : '—' }}
                            </span>
                        </div>
                        @if($act->descripcion)
                        <small class="text-muted d-block mt-1">{{ Str::limit($act->descripcion, 80) }}</small>
                        @endif
                    </div>
                    <span class="badge badge-{{ $esCompletada ? 'success' : 'warning' }} mr-2 d-none d-md-inline">
                        {{ $esCompletada ? 'Completada' : 'Pendiente' }}
                    </span>
                    <div class="btn-group btn-group-sm btn-actions flex-shrink-0">
                        @if(!$esCompletada)
                            @can('lotes.update')
                            <form action="{{ route('actividades.marcar-realizada', $act) }}" method="POST" class="d-inline form-realizada">
                                @csrf
                                <button type="button" class="btn btn-realizada btn-marcar-realizada"
                                    data-tipo="{{ $act->tipoActividad->nombre ?? 'Actividad' }}"
                                    data-lote="{{ $act->lote->nombre ?? 'Sin lote' }}">
                                    <i class="fas fa-check mr-1"></i> Hecha
                                </button>
                            </form>
                            @endcan
                        @endif
                        <a href="{{ route('actividades.show', $act) }}" class="btn btn-default"><i class="fas fa-eye text-info"></i></a>
                        @can('lotes.update')
                        <a href="{{ route('actividades.edit', $act) }}" class="btn btn-default"><i class="fas fa-edit text-warning"></i></a>
                        <form action="{{ route('actividades.destroy', $act) }}" method="POST" class="d-inline form-eliminar">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-default btn-eliminar"><i class="fas fa-trash text-danger"></i></button>
                        </form>
                        @endcan
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-5">No hay actividades registradas.</div>
            @endforelse
        </div>

        @if($actividades->hasPages())
        <div class="card-footer bg-white d-flex justify-content-end py-2">
            {{ $actividades->links() }}
        </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(function () {
    $('#btnCardView').on('click', function () {
        $(this).addClass('active').siblings().removeClass('active');
        $('#cardView').show();
        $('#tableView').hide();
    });
    $('#btnTableView').on('click', function () {
        $(this).addClass('active').siblings().removeClass('active');
        $('#tableView').show();
        $('#cardView').hide();
    });

    $(document).on('click', '.btn-marcar-realizada', function (e) {
        e.preventDefault();
        var form = $(this).closest('form');
        var tipo = $(this).data('tipo');
        var lote = $(this).data('lote');
        Swal.fire({
            title: '¿Marcar como realizada?',
            html: '<p class="mb-1"><strong>' + tipo + '</strong></p><p class="text-muted small mb-0">Lote: ' + lote + '</p>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, marcar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: function () { Swal.showLoading(); }
                });
                form.submit();
            }
        });
    });

    $(document).on('click', '.btn-eliminar', function (e) {
        e.preventDefault();
        var form = $(this).closest('form');
        Swal.fire({
            title: '¿Eliminar actividad?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function (result) {
            if (result.isConfirmed) form.submit();
        });
    });

@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: @json(session('error')),
        confirmButtonColor: '#dc3545'
    });
    @endif
});
</script>
@endpush
