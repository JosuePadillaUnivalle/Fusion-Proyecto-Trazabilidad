@extends('layouts.app')

@section('title', 'Historial de estados de lote | Fusion-Proyectos')
@section('page_title', 'Historial de estados de lote')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.index') }}">Lotes</a></li>
    <li class="breadcrumb-item active">Historial de estados</li>
@endsection

@push('styles')
@include('partials.modulo-catalogos-styles')
@endpush

@section('content')
<div class="modulo-cat">

<div class="row mb-2">
        <div class="col-lg-4 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $historial->total() }}</h3>
                    <p>Registros de cambio</p>
                </div>
                <div class="icon"><i class="fas fa-history"></i></div>
                <span class="small-box-footer">Trazabilidad de estados por lote</span>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1 mb-3">
        <x-modulo-index-header
            titulo="Historial de estados de lote"
            icono="fa-history"
            :registros="$historial->total()"
            filtros-target="#filtrosHistorialPanel"
            :nuevo-href="route('historial-estados-lote.create')"
            nuevo-text="Nuevo registro"
        />

        <div id="filtrosHistorialPanel" class="filtros-panel collapse {{ request()->hasAny(['buscar','loteid','estadolotetipoid','fecha_desde','fecha_hasta']) ? 'show' : '' }}">
            <form method="GET" action="{{ route('historial-estados-lote.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}"
                            placeholder="Lote, estado u observaciones…">
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Lote</label>
                        <select name="loteid" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($lotes as $lote)
                                <option value="{{ $lote->loteid }}" @selected(request('loteid') == $lote->loteid)>{{ $lote->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Estado</label>
                        <select name="estadolotetipoid" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($tiposEstado as $tipo)
                                <option value="{{ $tipo->estadolotetipoid }}" @selected(request('estadolotetipoid') == $tipo->estadolotetipoid)>{{ $tipo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ request('fecha_hasta') }}">
                    </div>
                </div>
                <x-filtros-form-actions :limpiar-url="route('historial-estados-lote.index', ['filtros_abiertos' => 1])" />
            </form>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 55px">#</th>
                        <th>Lote</th>
                        <th>Estado</th>
                        <th>Fecha cambio</th>
                        <th>Usuario</th>
                        <th>Observaciones</th>
                        <th style="width: 130px" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historial as $registro)
                    <tr>
                        <td class="text-muted font-weight-bold">#{{ $registro->historial_estado_id }}</td>
                        <td><strong>{{ $registro->lote->nombre ?? '—' }}</strong></td>
                        <td>
                            <span class="badge badge-light border">{{ $registro->estadoTipo->nombre ?? '—' }}</span>
                        </td>
                        <td class="text-muted">{{ $registro->fecha_cambio ? $registro->fecha_cambio->format('d/m/Y H:i') : '—' }}</td>
                        <td>{{ $registro->usuario ? $registro->usuario->nombre.' '.$registro->usuario->apellido : '—' }}</td>
                        <td class="text-muted small">{{ Str::limit($registro->observaciones, 60) ?: '—' }}</td>
                        <td class="text-center btn-actions">
                            <a href="{{ route('historial-estados-lote.show', $registro) }}" class="btn btn-sm btn-outline-info" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('historial-estados-lote.edit', $registro) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('historial-estados-lote.destroy', $registro) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('¿Eliminar este registro?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-history fa-3x mb-3 d-block"></i>
                            No hay registros que coincidan con los filtros.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($historial->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
            <small class="text-muted mb-2 mb-md-0">
                Mostrando {{ $historial->firstItem() }}–{{ $historial->lastItem() }} de {{ $historial->total() }}
            </small>
            {{ $historial->links() }}
        </div>
        @endif
    </div>

    <a href="{{ route('lotes.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Volver a Lotes
    </a>
</div>
@endsection
