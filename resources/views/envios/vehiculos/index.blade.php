@extends('layouts.app')

@section('title', 'Vehículos | AgroFusion')
@section('page_title', 'Vehículos')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.seguimiento') }}">Envíos</a></li>
    <li class="breadcrumb-item active">Vehículos</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
@endpush

@section('content')
<div class="modulo-env page-env-vehiculos">
    @include('envios.partials.alertas')

    <div class="row mb-2">
        <div class="col-md-4">
            <div class="small-box small-box-orange">
                <div class="inner">
                    <h3>{{ $stats['total'] ?? 0 }}</h3>
                    <p>Vehículos en flota</p>
                </div>
                <div class="icon"><i class="fas fa-truck"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Flota logística"
            icono="fa-truck-moving"
            :registros="$vehiculos->total()"
            filtros-target="#filtrosVehiculos"
            :nuevo-href="route('envios.vehiculos.create')"
            nuevo-text="Nuevo vehículo"
            nuevo-can="vehiculos.create"
        />

        <div id="filtrosVehiculos" class="filtros-panel collapse {{ request()->hasAny(['buscar','estado']) ? 'show' : '' }}">
            <form method="GET" action="{{ route('envios.vehiculos') }}">
                <div class="row align-items-end">
                    <div class="col-lg-5 col-md-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Buscar</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" name="buscar" class="form-control" value="{{ request('buscar') }}" placeholder="Placa, marca o modelo">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($estadosFiltro ?? [] as $estado)
                                <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <x-filtros-form-actions :limpiar-url="route('envios.vehiculos', ['filtros_abiertos' => 1])" />
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th>Placa</th>
                        <th>Marca / modelo</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Capacidad</th>
                        <th style="width:130px" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehiculos as $v)
                    @php
                        $tipo = $v->tipoVehiculo?->nombre ?? '—';
                        $estado = $v->estadoVehiculo?->nombre ?? ($v->activo ? 'Activo' : 'Inactivo');
                        $estadoLower = strtolower($estado);
                        $badgeEstado = str_contains($estadoLower, 'manten')
                            ? 'badge-warning text-dark'
                            : (str_contains($estadoLower, 'activ') ? 'badge-success' : 'badge-secondary');
                        $cap = $v->tipoVehiculo?->capacidad_kg;
                    @endphp
                    <tr>
                        <td><span class="badge badge-dark font-weight-bold">{{ $v->placa }}</span></td>
                        <td>{{ trim(($v->marca ?? '').' '.($v->modelo ?? '')) ?: '—' }}</td>
                        <td>{{ $tipo }}</td>
                        <td><span class="badge badge-estado {{ $badgeEstado }}">{{ $estado }}</span></td>
                        <td>{{ $cap ? number_format((float) $cap, 0).' kg' : '—' }}</td>
                        <td class="text-center">
                            @include('envios.partials.crud-acciones', [
                                'showRoute' => route('envios.vehiculos.show', $v),
                                'editRoute' => route('envios.vehiculos.edit', $v),
                                'destroyRoute' => route('envios.vehiculos.destroy', $v),
                                'entityName' => 'el vehículo '.$v->placa,
                                'readPermission' => 'vehiculos.view',
                                'updatePermission' => 'vehiculos.update',
                                'deletePermission' => 'vehiculos.delete',
                            ])
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            No hay vehículos registrados.
                            <a href="{{ route('envios.vehiculos.create') }}" class="d-block mt-2">Registrar vehículo</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vehiculos->hasPages())
            <div class="card-footer">{{ $vehiculos->links() }}</div>
        @endif
    </div>
</div>
@endsection
