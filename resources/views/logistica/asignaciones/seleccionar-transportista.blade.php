@extends('layouts.app')

@section('title', 'Elegir transportista | AgroFusion')
@section('page_title', 'Elegir transportista')

@push('styles')
@include('logistica.partials.ayuda-logistica-styles')
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Elegir transportista</h1>
        <p class="text-muted mb-0">Busque por nombre, correo o placa del vehículo y seleccione quién llevará los envíos.</p>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card x-card mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title mb-0 h5"><i class="fas fa-filter mr-1 text-success"></i> Filtros</h3>
                <a href="{{ route('logistica.asignaciones.create') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Volver sin elegir
                </a>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('logistica.asignaciones.seleccionar-transportista') }}">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="small text-muted mb-1">Buscar</label>
                            <input type="text" name="buscar" class="form-control" value="{{ request('buscar') }}"
                                   placeholder="Nombre, correo o teléfono">
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="small text-muted mb-1">Placa del vehículo</label>
                            <input type="text" name="placa" class="form-control" value="{{ request('placa') }}"
                                   placeholder="Ej: 1234-ABC">
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="small text-muted mb-1">Estado</label>
                            <select name="estado" class="form-control">
                                <option value="activo" @selected(request('estado', 'activo') === 'activo')>Solo activos</option>
                                <option value="todos" @selected(request('estado') === 'todos')>Todos</option>
                                <option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivos</option>
                            </select>
                        </div>
                        <div class="col-md-2 mb-2 mb-md-0">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search mr-1"></i> Buscar
                            </button>
                        </div>
                    </div>
                    @if(request()->hasAny(['buscar', 'placa', 'estado']))
                        <div class="mt-2">
                            <a href="{{ route('logistica.asignaciones.seleccionar-transportista') }}" class="small">Limpiar filtros</a>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        <div class="card x-card">
            <div class="card-header bg-light">
                <h3 class="card-title mb-0 h5">
                    <i class="fas fa-id-card mr-1 text-success"></i>
                    {{ $transportistas->total() }} transportista(s) encontrado(s)
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Vehículo</th>
                            <th>Estado</th>
                            <th style="width:140px" class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transportistas as $t)
                            @php
                                $vehiculo = $t->perfilTransportista?->vehiculo;
                                $vehiculoLabel = $vehiculo
                                    ? trim(collect([$vehiculo->placa, $vehiculo->marca, $vehiculo->modelo])->filter()->implode(' · '))
                                    : '—';
                            @endphp
                            <tr>
                                <td class="font-weight-bold">{{ trim($t->nombre.' '.($t->apellido ?? '')) }}</td>
                                <td>{{ $t->email ?? '—' }}</td>
                                <td>{{ $t->telefono ?? '—' }}</td>
                                <td>{{ $vehiculoLabel }}</td>
                                <td>
                                    @if($t->activo)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('logistica.asignaciones.create', ['transportista' => $t->usuarioid]) }}"
                                       class="btn btn-success btn-sm">
                                        <i class="fas fa-check mr-1"></i> Seleccionar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No hay transportistas con esos filtros.
                                    <a href="{{ route('envios.transportistas.create') }}" class="d-block mt-2">Registrar transportista</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($transportistas->hasPages())
                <div class="card-footer">{{ $transportistas->links() }}</div>
            @endif
        </div>
    </div>
</section>
@endsection
