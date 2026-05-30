@extends('layouts.app')

@section('title', 'Información del lote | AgroFusion')
@section('page_title', $lote->nombre)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.index') }}">Lotes</a></li>
    <li class="breadcrumb-item active">{{ $lote->nombre }}</li>
@endsection

@push('styles')
    @include('lotes.partials.detalle-styles')
@endpush

@section('content')
    @include('lotes.partials.detalle-header')
    @include('lotes.partials.detalle-stats')
    @include('lotes.partials.detalle-nav')

    <div class="card lote-section-card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="mb-3"><i class="fas fa-clipboard-list mr-2 text-success"></i>Datos del lote</h5>
                    <table class="table info-table">
                        <tr>
                            <td>ID</td>
                            <td><strong>#{{ $lote->loteid }}</strong></td>
                        </tr>
                        <tr>
                            <td>Código trazabilidad</td>
                            <td><code>{{ $lote->codigo_trazabilidad ?? '—' }}</code></td>
                        </tr>
                        <tr>
                            <td>Propietario</td>
                            <td>{{ $lote->usuario->nombre ?? '-' }} {{ $lote->usuario->apellido ?? '' }}</td>
                        </tr>
                        <tr>
                            <td>Cultivo</td>
                            <td>
                                @if($lote->cultivo)
                                    <span class="badge badge-success">{{ $lote->cultivo->nombre }}</span>
                                @else
                                    <span class="text-muted">Sin cultivo</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Estado</td>
                            <td>
                                <span class="badge {{ $estadoClass }}">
                                    {{ ucfirst($lote->estadoTipo->nombre ?? 'Sin estado') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Superficie</td>
                            <td><strong>{{ $lote->superficie }}</strong> ha</td>
                        </tr>
                        <tr>
                            <td>Fecha siembra</td>
                            <td>
                                @if($lote->fechasiembra)
                                    {{ \Carbon\Carbon::parse($lote->fechasiembra)->format('d/m/Y') }}
                                    <small class="text-muted">({{ $estadisticas['dias_desde_siembra'] }} días)</small>
                                @else
                                    <span class="text-muted">No registrada</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>Ubicación</td>
                            <td>{{ $lote->ubicacion ?? 'No especificada' }}</td>
                        </tr>
                        <tr>
                            <td>Coordenadas</td>
                            <td>
                                @if($lote->latitud && $lote->longitud)
                                    <code>{{ $lote->latitud }}, {{ $lote->longitud }}</code>
                                    <a href="{{ route('lotes.ubicacion', $lote) }}" class="btn btn-outline-success btn-xs btn-sm ml-2">
                                        Ver en mapa
                                    </a>
                                @else
                                    <span class="text-muted">No registradas</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    @if($lote->imagenurl)
                        <h5 class="mb-3"><i class="fas fa-image mr-2 text-success"></i>Imagen</h5>
                        <div class="text-center mb-4">
                            <img src="{{ $lote->imagenurl }}" alt="Lote" class="img-fluid rounded shadow-sm" style="max-height: 300px;">
                        </div>
                    @endif
                    <h5 class="mb-3"><i class="fas fa-chart-pie mr-2 text-success"></i>Resumen operativo</h5>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded text-center">
                                <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                <h4 class="mb-0">{{ $estadisticas['actividades_completadas'] }}</h4>
                                <small class="text-muted">Actividades completadas</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded text-center">
                                <i class="fas fa-clock text-warning fa-2x mb-2"></i>
                                <h4 class="mb-0">{{ $estadisticas['actividades_pendientes'] }}</h4>
                                <small class="text-muted">Pendientes</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded text-center">
                                <i class="fas fa-flask text-info fa-2x mb-2"></i>
                                <h4 class="mb-0">{{ $estadisticas['total_insumos'] }}</h4>
                                <small class="text-muted">Aplicaciones</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded text-center">
                                <i class="fas fa-leaf text-success fa-2x mb-2"></i>
                                <h4 class="mb-0">{{ number_format($estadisticas['produccion_total'], 0) }}</h4>
                                <small class="text-muted">Kg producidos</small>
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('lotes.trazabilidad', $lote) }}" class="btn btn-success btn-block">
                        <i class="fas fa-project-diagram mr-1"></i> Ver trazabilidad
                    </a>
                </div>
            </div>
        </div>
    </div>

    @include('lotes.partials.detalle-actions')
@endsection
