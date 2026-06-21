@extends('layouts.app')

@section('title', 'Vehículo '.$vehiculo->placa.' | AgroFusion')
@section('page_title', 'Detalle del vehículo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.vehiculos') }}">Vehículos</a></li>
    <li class="breadcrumb-item active">{{ $vehiculo->placa }}</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
@endpush

@section('content')
@php
    $capSvc = app(\App\Services\TransporteCapacidadService::class);
    $cap = $capSvc->capacidadEfectiva($vehiculo);
    $ambitoV = $vehiculo->ambito_flota ?? \App\Support\TransportistaFlotaCatalogo::AGRICOLA;
    $estadoVisual = $estadoVisual ?? 'operativo';
    $estadoLabel = $estadoLabel ?? 'Operativo';
    $badgeEstado = $badgeEstado ?? 'badge badge-success badge-estado';
    $enMantenimiento = $estadoVisual === 'mantenimiento';
    $enRuta = $estadoVisual === 'en_ruta';
@endphp
<div class="modulo-env page-vehiculo-detalle">
    @include('envios.partials.alertas')

    <div class="veh-det-toolbar d-flex flex-wrap justify-content-between align-items-center mb-3">
        <a href="{{ route('envios.vehiculos') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>
        <div class="veh-det-toolbar__acciones d-flex flex-wrap align-items-center">
            @if($enRuta && ($rutaTiempoReal['url'] ?? null))
            <a href="{{ $rutaTiempoReal['url'] }}" class="btn btn-primary btn-sm mr-1 mb-1">
                <i class="fas fa-satellite-dish mr-1"></i> Ver ruta en tiempo real
            </a>
            @endif
            @can('vehiculos.update')
            @include('envios.vehiculos.partials.btn-toggle-mantenimiento', ['vehiculo' => $vehiculo])
            <a href="{{ route('envios.vehiculos.edit', $vehiculo) }}" class="btn btn-success btn-sm">
                <i class="fas fa-edit mr-1"></i> Editar
            </a>
            @endcan
            @can('vehiculos.delete')
            <form method="POST" action="{{ route('envios.vehiculos.destroy', $vehiculo) }}" class="d-inline">
                @csrf @method('DELETE')
                <button type="submit"
                        class="btn btn-outline-danger btn-sm"
                        data-confirm-modal
                        data-confirm-tone="danger"
                        data-confirm-title="Eliminar vehículo"
                        data-confirm-message="¿Eliminar el vehículo {{ $vehiculo->placa }}? Esta acción no se puede deshacer.">
                    <i class="fas fa-trash mr-1"></i> Eliminar
                </button>
            </form>
            @endcan
        </div>
    </div>

    <div class="card veh-det-hero mb-3">
        <div class="veh-det-hero__body">
            <div class="veh-det-hero__icon"><i class="fas fa-truck"></i></div>
            <div>
                <span class="veh-det-hero__placa">{{ $vehiculo->placa }}</span>
                <div class="veh-det-hero__meta mt-1">
                    <span class="badge {{ \App\Support\TransportistaFlotaCatalogo::badgeClase($ambitoV) }}">
                        {{ \App\Support\TransportistaFlotaCatalogo::categoriaCorta($ambitoV) }}
                    </span>
                    <span class="{{ $badgeEstado }} ml-1">{{ $estadoLabel }}</span>
                </div>
                @if($enRuta)
                <p class="mb-0 mt-2 small text-white-50">
                    <i class="fas fa-route mr-1"></i> Simulación activa en ruta en tiempo real.
                    @if($rutaTiempoReal['codigo'] ?? null)
                        Seguimiento: <strong>{{ $rutaTiempoReal['codigo'] }}</strong>
                    @endif
                </p>
                @elseif($enMantenimiento)
                <p class="mb-0 mt-2 small text-warning">
                    <i class="fas fa-wrench mr-1"></i> Este vehículo no puede asignarse a envíos ni rutas mientras esté en mantenimiento.
                </p>
                @endif
                <p class="mb-0 mt-2 text-white-50">
                    {{ trim(($vehiculo->marca ?? '').' '.($vehiculo->modelo ?? '')) ?: 'Sin marca/modelo' }}
                    @if($vehiculo->anio)<span class="mx-1">·</span>{{ $vehiculo->anio }}@endif
                    @if($vehiculo->color)<span class="mx-1">·</span>{{ $vehiculo->color }}@endif
                </p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card veh-det-panel h-100">
                <div class="card-header"><i class="fas fa-info-circle mr-1 text-success"></i> Identificación</div>
                <div class="card-body">
                    <div class="veh-det-grid">
                        <div class="veh-det-item">
                            <span class="veh-det-item__label">Tipo</span>
                            <span class="veh-det-item__value">{{ $vehiculo->tipoVehiculo?->nombre ?? '—' }}</span>
                        </div>
                        <div class="veh-det-item veh-det-item--transporte">
                            <span class="veh-det-item__label">Transporte</span>
                            <span class="veh-det-item__value">
                                @php
                                    $ttVeh = $vehiculo->tiposTransporteEfectivos()->first();
                                @endphp
                                @if($ttVeh)
                                    @include('envios.vehiculos.partials.transporte-badge', [
                                        'nombre' => $ttVeh->nombre,
                                        'codigo' => $ttVeh->codigo,
                                        'size' => 'md',
                                    ])
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </span>
                        </div>
                        <div class="veh-det-item">
                            <span class="veh-det-item__label">Marca</span>
                            <span class="veh-det-item__value">{{ $vehiculo->marca ?? '—' }}</span>
                        </div>
                        <div class="veh-det-item">
                            <span class="veh-det-item__label">Modelo</span>
                            <span class="veh-det-item__value">{{ $vehiculo->modelo ?? '—' }}</span>
                        </div>
                        <div class="veh-det-item">
                            <span class="veh-det-item__label">Año</span>
                            <span class="veh-det-item__value">{{ $vehiculo->anio ?? '—' }}</span>
                        </div>
                        <div class="veh-det-item">
                            <span class="veh-det-item__label">Color</span>
                            <span class="veh-det-item__value">{{ $vehiculo->color ?? '—' }}</span>
                        </div>
                        <div class="veh-det-item">
                            <span class="veh-det-item__label">Estado operativo</span>
                            <span class="veh-det-item__value">
                                <span class="{{ $badgeEstado }}">{{ $estadoLabel }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="card veh-det-panel h-100">
                <div class="card-header"><i class="fas fa-weight-hanging mr-1 text-success"></i> Capacidad y licencia</div>
                <div class="card-body">
                    @if($cap['kg'] > 0 || $cap['m3'] > 0)
                    <div class="veh-det-capacidad mb-3">
                        @if($cap['kg'] > 0)
                        <div class="veh-det-capacidad__chip">
                            <i class="fas fa-weight-hanging"></i>
                            <div>
                                <strong>{{ number_format($cap['kg'], 0) }}</strong>
                                <span>kg máx.</span>
                            </div>
                        </div>
                        @endif
                        @if($cap['m3'] > 0)
                        <div class="veh-det-capacidad__chip">
                            <i class="fas fa-cube"></i>
                            <div>
                                <strong>{{ number_format($cap['m3'], 1) }}</strong>
                                <span>m³ máx.</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    @if($cap['usa_override'])
                        <p class="small text-info mb-2"><i class="fas fa-sliders-h mr-1"></i> Capacidad ajustada para esta unidad.</p>
                    @else
                        <p class="small text-muted mb-2">Heredada del tipo de vehículo.</p>
                    @endif
                    @else
                        <p class="text-muted mb-2">Sin capacidad registrada.</p>
                    @endif
                    <div class="veh-det-licencia">
                        <span class="veh-det-item__label d-block mb-1">Licencia mínima del conductor</span>
                        @if($cap['licencia_requerida'])
                            <span class="badge badge-info badge-lg">{{ $cap['licencia_requerida'] }}</span>
                            <span class="ml-1">{{ \App\Support\TiposLicenciaBolivia::etiqueta($cap['licencia_requerida']) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($capacidadResumen))
        @include('envios.vehiculos.partials.tabla-empaques-capacidad', [
            'capacidadResumen' => $capacidadResumen,
            'vehiculo' => $vehiculo,
        ])
    @endif

    @include('envios.partials.tipos-vehiculo-catalogo', ['tipos' => $tiposCatalogo ?? collect()])
</div>

@include('partials.modal-confirmar-accion')
@endsection
