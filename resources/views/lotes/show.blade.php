@extends('layouts.app')

@section('title', 'Detalle Lote | AgroNexus')
@section('page_title', $lote->nombre)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.index') }}" style="color: #2c5530;">Lotes</a></li>
    <li class="breadcrumb-item active">{{ $lote->nombre }}</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        :root {
            --primary-color: #2c5530;
            --secondary-color: #4a7c59;
        }

        .lote-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
        }

        .lote-header h2 {
            margin: 0;
            font-weight: 700;
        }

        .estado-badge {
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Cards estilo dashboard */
        .small-box {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }

        .small-box:hover {
            transform: translateY(-2px);
        }

        .small-box .icon {
            font-size: 70px !important;
        }

        .small-box-green {
            background: linear-gradient(135deg, #28a745, #34ce57) !important;
        }

        .small-box-blue {
            background: linear-gradient(135deg, #17a2b8, #20c997) !important;
        }

        .small-box-yellow {
            background: linear-gradient(135deg, #ffc107, #ffca2c) !important;
        }

        .small-box-red {
            background: linear-gradient(135deg, #dc3545, #e74a3b) !important;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border: none;
            margin-bottom: 20px;
        }

        .card-header {
            background: white;
            border-bottom: 2px solid #f1f3f4;
            font-weight: 600;
            padding: 15px 20px;
        }

        #map {
            height: 340px;
            width: 100%;
            border-radius: 0 0 10px 10px;
            z-index: 1;
        }

        .ficha-chip {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 14px;
            background: #f8f9fc;
            border-radius: 10px;
            border: 1px solid #eef0f2;
            margin-bottom: 10px;
        }

        .ficha-chip i {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary-color);
            color: #fff;
            flex-shrink: 0;
        }

        .ficha-chip .chip-label {
            font-size: .75rem;
            text-transform: uppercase;
            color: #6c757d;
            letter-spacing: .03em;
        }

        .ficha-chip .chip-value {
            font-weight: 600;
            color: #1a252f;
        }

        .resumen-mini {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 1rem;
        }

        .resumen-mini .item {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 12px;
            text-align: center;
            border: 1px solid #eef0f2;
        }

        .resumen-mini .item h4 {
            margin: 0;
            font-weight: 700;
            color: var(--primary-color);
        }

        .timeline-panel {
            max-height: 420px;
            overflow-y: auto;
            padding-right: 8px;
        }

        .lote-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        /* Timeline */
        .timeline {
            position: relative;
            padding: 20px 0;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: linear-gradient(to bottom, var(--primary-color), #e9ecef);
        }

        .timeline-item {
            position: relative;
            padding-left: 60px;
            padding-bottom: 25px;
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        .timeline-icon {
            position: absolute;
            left: 5px;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            z-index: 1;
        }

        .timeline-icon.success {
            background: #28a745;
        }

        .timeline-icon.info {
            background: #17a2b8;
        }

        .timeline-icon.warning {
            background: #ffc107;
            color: #1a252f;
        }

        .timeline-icon.primary {
            background: var(--primary-color);
        }

        .timeline-content {
            background: #f8f9fc;
            border-radius: 10px;
            padding: 15px;
            border-left: 3px solid #dee2e6;
        }

        .timeline-content.success {
            border-left-color: #28a745;
        }

        .timeline-content.info {
            border-left-color: #17a2b8;
        }

        .timeline-content.warning {
            border-left-color: #ffc107;
        }

        .timeline-content.primary {
            border-left-color: var(--primary-color);
        }

        .timeline-date {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .timeline-title {
            font-weight: 600;
            color: #1a252f;
            margin-bottom: 5px;
        }

        .timeline-desc {
            font-size: 0.9rem;
            color: #495057;
            margin: 0;
        }

        .timeline-user {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 5px;
        }

        .empty-timeline {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }

        .empty-timeline i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .btn-action {
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 500;
        }

        .map-card .card-header {
            background: linear-gradient(135deg, #f8f9fc, #fff);
        }
    </style>
@endpush

@section('content')
    <!-- Header -->
    <div class="lote-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-map-marked-alt mr-2"></i>{{ $lote->nombre }}</h2>
                <p class="mb-0 mt-2">
                    <i class="fas fa-user mr-1"></i> {{ $lote->usuario->nombre ?? 'Sin asignar' }}
                    {{ $lote->usuario->apellido ?? '' }}
                    <span class="mx-2">|</span>
                    <i class="fas fa-map-marker-alt mr-1"></i> {{ $lote->ubicacion ?? 'Sin ubicación' }}
                </p>
            </div>
            <div class="col-md-4 text-md-right">
                @php
                    $estadoColors = [
                        'disponible' => 'bg-secondary',
                        'en preparación' => 'bg-info',
                        'sembrado' => 'bg-primary',
                        'en producción' => 'bg-success',
                        'cosechado' => 'bg-warning text-dark',
                        'en descanso' => 'bg-dark'
                    ];
                    $estadoNombre = strtolower($lote->estadoTipo->nombre ?? 'disponible');
                    $estadoClass = $estadoColors[$estadoNombre] ?? 'bg-secondary';
                @endphp
                <span
                    class="estado-badge {{ $estadoClass }}">{{ ucfirst($lote->estadoTipo->nombre ?? 'Sin estado') }}</span>
                @if($lote->cultivo)
                    <br><span class="badge badge-light mt-2" style="font-size: 0.9rem;"><i class="fas fa-seedling mr-1"></i>
                        {{ $lote->cultivo->nombre }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats estilo dashboard -->
    <div class="row">
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $lote->superficie }}</h3>
                    <p>Hectáreas</p>
                </div>
                <div class="icon"><i class="fas fa-ruler-combined"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $estadisticas['dias_desde_siembra'] ?? '-' }}</h3>
                    <p>Días</p>
                </div>
                <div class="icon"><i class="fas fa-calendar-day"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3>{{ $estadisticas['total_insumos'] }}</h3>
                    <p>Insumos</p>
                </div>
                <div class="icon"><i class="fas fa-flask"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $estadisticas['total_actividades'] }}</h3>
                    <p>Actividades</p>
                </div>
                <div class="icon"><i class="fas fa-tasks"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $estadisticas['total_cosechas'] }}</h3>
                    <p>Cosechas</p>
                </div>
                <div class="icon"><i class="fas fa-tractor"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
        <div class="col-lg-2 col-md-4 col-6">
            <div class="small-box small-box-red">
                <div class="inner">
                    <h3>{{ number_format($estadisticas['produccion_total'], 0) }}<span style="font-size: 14px;">kg</span>
                    </h3>
                    <p>Producción</p>
                </div>
                <div class="icon"><i class="fas fa-weight-hanging"></i></div>
                <span class="small-box-footer">&nbsp;</span>
            </div>
        </div>
    </div>

    <!-- Vista unificada: ubicación + ficha + historial -->
    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card map-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><i class="fas fa-map-marked-alt text-success mr-2"></i>Parcela en el mapa</strong>
                    @if($lote->latitud && $lote->longitud)
                        <a href="https://www.google.com/maps?q={{ $lote->latitud }},{{ $lote->longitud }}" target="_blank" rel="noopener"
                           class="btn btn-sm btn-outline-success">
                            <i class="fas fa-external-link-alt mr-1"></i>Abrir en Maps
                        </a>
                    @endif
                </div>
                @if($lote->latitud && $lote->longitud)
                    <div id="map"></div>
                    <div class="card-body py-2 small text-muted">
                        <i class="fas fa-map-pin mr-1"></i>{{ $lote->ubicacion ?? 'Ubicación GPS' }}
                        <span class="float-right font-monospace">{{ number_format((float) $lote->latitud, 5) }}, {{ number_format((float) $lote->longitud, 5) }}</span>
                    </div>
                @else
                    <div class="card-body text-center py-5">
                        <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-3">Sin coordenadas GPS registradas.</p>
                        @can('lotes.update')
                            <a href="{{ route('lotes.edit', $lote) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-map-pin mr-1"></i>Marcar en mapa
                            </a>
                        @endcan
                    </div>
                @endif
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <strong><i class="fas fa-id-card text-success mr-2"></i>Ficha y operación del lote</strong>
                </div>
                <div class="card-body">
                    <div class="resumen-mini">
                        <div class="item">
                            <h4>{{ $estadisticas['actividades_completadas'] }}</h4>
                            <small class="text-muted">Actividades hechas</small>
                        </div>
                        <div class="item">
                            <h4>{{ max(0, $estadisticas['total_actividades'] - $estadisticas['actividades_completadas']) }}</h4>
                            <small class="text-muted">Pendientes</small>
                        </div>
                        <div class="item">
                            <h4>{{ $estadisticas['total_insumos'] }}</h4>
                            <small class="text-muted">Aplicaciones</small>
                        </div>
                        <div class="item">
                            <h4>{{ number_format($estadisticas['produccion_total'], 0) }} kg</h4>
                            <small class="text-muted">Producidos</small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="ficha-chip">
                                <i class="fas fa-hashtag"></i>
                                <div>
                                    <div class="chip-label">Identificación</div>
                                    <div class="chip-value">#{{ $lote->loteid }}
                                        @if($lote->codigo_trazabilidad)
                                            <span class="badge badge-light border ml-1">{{ $lote->codigo_trazabilidad }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="ficha-chip">
                                <i class="fas fa-user"></i>
                                <div>
                                    <div class="chip-label">Responsable</div>
                                    <div class="chip-value">{{ trim(($lote->usuario->nombre ?? '').' '.($lote->usuario->apellido ?? '')) ?: '—' }}</div>
                                </div>
                            </div>
                            <div class="ficha-chip">
                                <i class="fas fa-seedling"></i>
                                <div>
                                    <div class="chip-label">Cultivo · Estado</div>
                                    <div class="chip-value">
                                        @if($lote->cultivo)<span class="badge badge-success">{{ $lote->cultivo->nombre }}</span>@else<span class="text-muted">Sin cultivo</span>@endif
                                        <span class="badge {{ $estadoClass }} ml-1">{{ ucfirst($lote->estadoTipo->nombre ?? '—') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ficha-chip">
                                <i class="fas fa-ruler-combined"></i>
                                <div>
                                    <div class="chip-label">Superficie</div>
                                    <div class="chip-value">{{ $lote->superficie }} ha</div>
                                </div>
                            </div>
                            <div class="ficha-chip">
                                <i class="fas fa-calendar-day"></i>
                                <div>
                                    <div class="chip-label">Siembra</div>
                                    <div class="chip-value">
                                        @if($lote->fechasiembra)
                                            {{ \Carbon\Carbon::parse($lote->fechasiembra)->format('d/m/Y') }}
                                            <small class="text-muted">({{ $estadisticas['dias_desde_siembra'] }} días)</small>
                                        @else
                                            <span class="text-muted">No registrada</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if($lote->imagenurl)
                                <div class="text-center mt-2">
                                    <img src="{{ $lote->imagenurl }}" alt="Lote" class="img-fluid rounded" style="max-height: 120px;">
                                </div>
                            @endif
                        </div>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 font-weight-bold"><i class="fas fa-history text-success mr-2"></i>Historial de trazabilidad</h6>
                        <span class="badge badge-secondary">{{ $trazabilidad->count() }} eventos</span>
                    </div>

                    <div class="timeline-panel">
                        @if($trazabilidad->count() > 0)
                            <div class="timeline mb-0">
                                @foreach($trazabilidad as $evento)
                                    <div class="timeline-item">
                                        <div class="timeline-icon {{ $evento['color'] }}">
                                            <i class="fas fa-{{ $evento['icono'] }}"></i>
                                        </div>
                                        <div class="timeline-content {{ $evento['color'] }}">
                                            <div class="timeline-date">
                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                {{ $evento['fecha'] instanceof \Carbon\Carbon ? $evento['fecha']->format('d/m/Y H:i') : \Carbon\Carbon::parse($evento['fecha'])->format('d/m/Y H:i') }}
                                                @if(isset($evento['completada']))
                                                    <span class="badge badge-{{ $evento['completada'] ? 'success' : 'warning' }} ml-2">
                                                        {{ $evento['completada'] ? 'Hecha' : 'Pendiente' }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="timeline-title">{{ $evento['titulo'] }}</div>
                                            <p class="timeline-desc">{{ $evento['descripcion'] }}</p>
                                            @if(!empty($evento['usuario']))
                                                <div class="timeline-user"><i class="fas fa-user mr-1"></i>{{ $evento['usuario'] }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-timeline py-4">
                                <i class="fas fa-history"></i>
                                <h6>Sin eventos aún</h6>
                                <p class="text-muted small mb-0">Aparecerán siembras, insumos, actividades y cosechas vinculadas a este lote.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between flex-wrap">
                <a href="{{ route('lotes.index') }}" class="btn btn-secondary btn-action mb-2"><i
                        class="fas fa-arrow-left mr-1"></i> Volver</a>
                <div>
                    @can('lotes.update')
                    <a href="{{ route('lotes.edit', $lote) }}" class="btn btn-warning btn-action mb-2"><i
                            class="fas fa-edit mr-1"></i> Editar</a>
                    @endcan
                    @can('lotes.delete')
                    <form action="{{ route('lotes.destroy', $lote) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('¿Eliminar este lote?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-action mb-2"><i class="fas fa-trash mr-1"></i> Eliminar</button>
                    </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @if($lote->latitud && $lote->longitud)
    <script>
        (function () {
            var lat = {{ $lote->latitud }};
            var lng = {{ $lote->longitud }};
            var sup = {{ $lote->superficie ?? 0 }};
            var map = L.map('map').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
            L.marker([lat, lng]).addTo(map)
                .bindPopup({!! json_encode($lote->nombre.' · '.$lote->superficie.' ha'.($lote->cultivo ? ' · '.$lote->cultivo->nombre : '')) !!})
                .openPopup();
            if (sup > 0) {
                L.circle([lat, lng], {
                    color: '#2c5530', fillColor: '#28a745', fillOpacity: 0.28,
                    radius: Math.sqrt(sup * 10000 / Math.PI)
                }).addTo(map);
            }
            setTimeout(function () { map.invalidateSize(); }, 200);
        })();
    </script>
    @endif
@endpush