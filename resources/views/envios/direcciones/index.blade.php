@extends('layouts.app')

@section('title', 'Direcciones | AgroFusion')
@section('page_title', 'Direcciones de envíos')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.seguimiento') }}">Envíos</a></li>
    <li class="breadcrumb-item active">Direcciones</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
@endpush

@section('content')
<div class="modulo-env page-env-direcciones">
    @include('envios.partials.alertas')

    <div class="row mb-2">
        <div class="col-md-4">
            <div class="small-box small-box-green">
                <div class="inner"><h3>{{ $stats['total'] ?? 0 }}</h3><p>Puntos logísticos</p></div>
                <div class="icon"><i class="fas fa-map-marked-alt"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box small-box-teal">
                <div class="inner"><h3>{{ $stats['origenes'] ?? 0 }}</h3><p>Orígenes</p></div>
                <div class="icon"><i class="fas fa-arrow-up"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box small-box-blue">
                <div class="inner"><h3>{{ $stats['destinos'] ?? 0 }}</h3><p>Destinos</p></div>
                <div class="icon"><i class="fas fa-arrow-down"></i></div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Direcciones de origen y destino"
            icono="fa-map-pin"
            :registros="$direcciones->total()"
            filtros-target="#filtrosDirecciones"
            :nuevo-href="route('envios.direcciones.create')"
            nuevo-text="Nueva dirección"
            nuevo-can="direcciones.create"
        />

        <div id="filtrosDirecciones" class="filtros-panel collapse {{ request()->hasAny(['buscar','tipo']) ? 'show' : '' }}">
            <form method="GET" action="{{ route('envios.direcciones') }}">
                <div class="row align-items-end">
                    <div class="col-lg-5 col-md-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Buscar</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" name="buscar" class="form-control" value="{{ request('buscar') }}" placeholder="Nombre, ciudad o dirección">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Tipo de punto</label>
                        <select name="tipo" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="origen" @selected(request('tipo') === 'origen')>Origen</option>
                            <option value="destino" @selected(request('tipo') === 'destino')>Destino</option>
                            <option value="hub" @selected(request('tipo') === 'hub')>Hub / punto</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <x-filtros-form-actions :limpiar-url="route('envios.direcciones', ['filtros_abiertos' => 1])" />
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body table-responsive p-0">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Ciudad</th>
                        <th style="width:130px" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($direcciones as $d)
                    @php
                        $tipo = strtolower((string) ($d->tipo_punto ?? ''));
                        $badgeTipo = match ($tipo) {
                            'origen' => 'badge-success',
                            'destino' => 'badge-danger',
                            default => 'badge-info',
                        };
                    @endphp
                    <tr>
                        <td><span class="badge badge-estado {{ $badgeTipo }}">{{ $d->etiquetaTipo() }}</span></td>
                        <td class="font-weight-bold">{{ $d->nombre }}</td>
                        <td>{{ $d->direccion_completa }}</td>
                        <td>{{ $d->ciudad }}</td>
                        <td class="text-center">
                            @include('envios.partials.crud-acciones', [
                                'showRoute' => route('envios.direcciones.show', $d),
                                'editRoute' => route('envios.direcciones.edit', $d),
                                'destroyRoute' => route('envios.direcciones.destroy', $d),
                                'entityName' => $d->nombre,
                                'readPermission' => 'direcciones.view',
                                'updatePermission' => 'direcciones.update',
                                'deletePermission' => 'direcciones.delete',
                            ])
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            No hay direcciones registradas.
                            <a href="{{ route('envios.direcciones.create') }}" class="d-block mt-2">Registrar dirección</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($direcciones->hasPages())
            <div class="card-footer">{{ $direcciones->links() }}</div>
        @endif
    </div>
</div>
@endsection
