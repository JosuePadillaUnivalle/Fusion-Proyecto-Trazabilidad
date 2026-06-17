@extends('layouts.app')

@section('title', 'Máquinas de planta | Fusion-Proyectos')
@section('page_title', 'Máquinas de planta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Máquinas de planta</li>
@endsection

@push('styles')
@include('partials.modulo-produccion-styles')
@include('maquinas_planta.partials.foto-maquina-styles')
@endpush

@section('content')
<div class="modulo-prod">

    <div class="row mb-2">
        <div class="col-lg-4 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['total'] ?? 0 }}</h3>
                    <p>Máquinas registradas</p>
                </div>
                <div class="icon"><i class="fas fa-cogs"></i></div>
                <span class="small-box-footer">Equipo de planta</span>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $stats['con_codigo'] ?? 0 }}</h3>
                    <p>Con código</p>
                </div>
                <div class="icon"><i class="fas fa-barcode"></i></div>
                <span class="small-box-footer">Identificación interna</span>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            <div class="small-box small-box-teal">
                <div class="inner">
                    <h3>{{ $stats['activas'] ?? 0 }}</h3>
                    <p>Activas</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <span class="small-box-footer">Disponibles en registro</span>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Catálogo de máquinas"
            icono="fa-cogs"
            :registros="$maquinas->total()"
            filtros-target="#filtrosMaquinasPanel"
            :nuevo-href="route('maquinas-planta.create')"
            nuevo-text="Nueva máquina"
        />

        <div id="filtrosMaquinasPanel" class="filtros-panel collapse {{ request()->hasAny(['buscar','estado']) ? 'show' : '' }}">
            <form method="GET" action="{{ route('maquinas-planta.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}" placeholder="Nombre, código o descripción…">
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">Todas</option>
                            <option value="activa" @selected(request('estado') === 'activa')>Activas</option>
                            <option value="mantenimiento" @selected(in_array(request('estado'), ['mantenimiento', 'inactiva'], true))>En mantenimiento</option>
                        </select>
                    </div>
                </div>
                <x-filtros-form-actions :limpiar-url="route('maquinas-planta.index', ['filtros_abiertos' => 1])" />
            </form>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 56px;">Foto</th>
                        <th>Nombre</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th style="width: 100px;">Estado</th>
                        <th style="width: 200px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($maquinas as $maquina)
                    <tr>
                        <td>
                            @include('maquinas_planta.partials.foto-maquina', ['maquina' => $maquina, 'size' => 'thumb'])
                        </td>
                        <td><strong>{{ $maquina->nombre }}</strong></td>
                        <td>
                            @if($maquina->codigo)
                                <span class="badge badge-light border">{{ $maquina->codigo }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $maquina->descripcionMostrar() }}</td>
                        <td>@include('maquinas_planta.partials.estado-maquina', ['maquina' => $maquina])</td>
                        <td class="btn-actions text-nowrap">
                            @include('maquinas_planta.partials.btn-toggle-estado', ['maquina' => $maquina])
                            <a href="{{ route('maquinas-planta.show', $maquina) }}" class="btn btn-sm btn-outline-info" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('maquinas-planta.edit', $maquina) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('maquinas-planta.destroy', $maquina) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar"
                                    data-confirm-modal
                                    data-confirm-title="Eliminar máquina"
                                    data-confirm-message="¿Eliminar «{{ $maquina->nombre }}»? Esta acción no se puede deshacer.">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-cogs fa-2x mb-2 d-block"></i>
                            Sin máquinas registradas.
                            <a href="{{ route('maquinas-planta.create') }}" class="btn btn-success btn-sm mt-2">
                                <i class="fas fa-plus mr-1"></i> Registrar primera máquina
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($maquinas->hasPages())
        <div class="card-footer">{{ $maquinas->links() }}</div>
        @endif
    </div>

    @include('maquinas_planta.partials.modal-foto-maquina')
    @include('partials.modal-confirmar-accion')
</div>
@endsection

