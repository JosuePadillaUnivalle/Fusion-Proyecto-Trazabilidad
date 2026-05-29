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
            height: 300px;
            width: 100%;
            border-radius: 10px;
            border: 2px solid #e9ecef;
        }

        .info-table td {
            padding: 10px 0;
            border-bottom: 1px solid #f1f3f4;
        }

        .info-table td:first-child {
            font-weight: 600;
            color: #495057;
            width: 40%;
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

        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 12px 20px;
        }

        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
            background: transparent;
        }

        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: var(--primary-color);
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

    <!-- Tabs -->
    <div class="card">
        <div class="card-header p-0">
            <ul class="nav nav-tabs" id="loteTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab">
                        <i class="fas fa-info-circle mr-1"></i> Información
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="trazabilidad-tab" data-toggle="tab" href="#trazabilidad" role="tab">
                        <i class="fas fa-history mr-1"></i> Trazabilidad
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="mapa-tab" data-toggle="tab" href="#mapa" role="tab">
                        <i class="fas fa-map mr-1"></i> Ubicación
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="loteTabsContent">
                <!-- Info -->
                <div class="tab-pane fade show active" id="info" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3"><i class="fas fa-clipboard-list mr-2 text-success"></i>Datos del Lote</h5>
                            <table class="table info-table">
                                <tr>
                                    <td>ID</td>
                                    <td><strong>#{{ $lote->loteid }}</strong></td>
                                </tr>
                                <tr>
                                    <td>Propietario</td>
                                    <td>{{ $lote->usuario->nombre ?? '-' }} {{ $lote->usuario->apellido ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td>Cultivo</td>
                                    <td>@if($lote->cultivo)<span
                                    class="badge badge-success">{{ $lote->cultivo->nombre }}</span>@else<span
                                            class="text-muted">Sin cultivo</span>@endif</td>
                                </tr>
                                <tr>
                                    <td>Estado</td>
                                    <td><span
                                            class="badge {{ $estadoClass }}">{{ ucfirst($lote->estadoTipo->nombre ?? 'Sin estado') }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Superficie</td>
                                    <td><strong>{{ $lote->superficie }}</strong> ha</td>
                                </tr>
                                <tr>
                                    <td>Fecha Siembra</td>
                                    <td>@if($lote->fechasiembra){{ \Carbon\Carbon::parse($lote->fechasiembra)->format('d/m/Y') }}
                                        <small class="text-muted">({{ $estadisticas['dias_desde_siembra'] }}
                                    días)</small>@else<span class="text-muted">No registrada</span>@endif
                                    </td>
                                </tr>
                                <tr>
                                    <td>Ubicación</td>
                                    <td>{{ $lote->ubicacion ?? 'No especificada' }}</td>
                                </tr>
                                <tr>
                                    <td>Coordenadas</td>
                                    <td>@if($lote->latitud && $lote->longitud)<code>{{ $lote->latitud }}, {{ $lote->longitud }}</code>@else<span
                                    class="text-muted">No registradas</span>@endif</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            @if($lote->imagenurl)
                                <h5 class="mb-3"><i class="fas fa-image mr-2 text-success"></i>Imagen</h5>
                                <div class="text-center mb-4">
                                    <img src="{{ $lote->imagenurl }}" alt="Lote" class="img-fluid rounded shadow-sm"
                                        style="max-height: 300px;">
                                </div>
                            @endif
                            <h5 class="mb-3"><i class="fas fa-chart-pie mr-2 text-success"></i>Resumen</h5>
                            <div class="p-3 bg-light rounded text-center">
                                <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                <h4 class="mb-0">{{ $estadisticas['actividades_completadas'] }}</h4>
                                <small class="text-muted">Completadas</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="p-3 bg-light rounded text-center">
                                <i class="fas fa-clock text-warning fa-2x mb-2"></i>
                                <h4 class="mb-0">
                                    {{ $estadisticas['total_actividades'] - $estadisticas['actividades_completadas'] }}
                                </h4>
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
                                <small class="text-muted">Kg Producidos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trazabilidad -->
        <div class="tab-pane fade" id="trazabilidad" role="tabpanel">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0"><i class="fas fa-history mr-2 text-success"></i>Historial Completo</h5>
                <span class="badge badge-secondary">{{ $trazabilidad->count() }} eventos</span>
            </div>

            @if($trazabilidad->count() > 0)
                <div class="timeline">
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
                                        <span
                                            class="badge badge-{{ $evento['completada'] ? 'success' : 'warning' }} ml-2">{{ $evento['completada'] ? 'Completada' : 'Pendiente' }}</span>
                                    @endif
                                </div>
                                <div class="timeline-title">{{ $evento['titulo'] }}</div>
                                <p class="timeline-desc">{{ $evento['descripcion'] }}</p>
                                @if(isset($evento['usuario']) && $evento['usuario'])
                                    <div class="timeline-user"><i class="fas fa-user mr-1"></i> {{ $evento['usuario'] }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-timeline">
                    <i class="fas fa-history"></i>
                    <h5>Sin historial registrado</h5>
                    <p class="text-muted">Los eventos aparecerán cuando se registren siembras, insumos, actividades o
                        cosechas.</p>
                </div>
            @endif
        </div>

        <!-- Mapa -->
        <div class="tab-pane fade" id="mapa" role="tabpanel">
            @if($lote->latitud && $lote->longitud)
                <div id="map"></div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded"><strong><i class="fas fa-map-pin mr-1"></i> Latitud:</strong>
                            {{ $lote->latitud }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded"><strong><i class="fas fa-map-pin mr-1"></i> Longitud:</strong>
                            {{ $lote->longitud }}</div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-map-marked-alt fa-4x text-muted mb-3"></i>
                    <h5>Sin coordenadas</h5>
                    <p class="text-muted">Este lote no tiene ubicación geográfica.</p>
                    @can('lotes.update')
                    <a href="{{ route('lotes.edit', $lote) }}" class="btn btn-success"><i class="fas fa-edit mr-1"></i>
                        Agregar</a>
                    @endcan
                </div>
            @endif
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
    <script>
        $(function () {
            $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                if ($(e.target).attr('href') === '#mapa') initMap();
            });

            function initMap() {
                @if($lote->latitud && $lote->longitud)
                    if (typeof window.loteMap !== 'undefined') return;
                    var lat = {{ $lote->latitud }}, lng = {{ $lote->longitud }}, sup = {{ $lote->superficie ?? 0 }};
                    window.loteMap = L.map('map').setView([lat, lng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(loteMap);
                    L.marker([lat, lng]).addTo(loteMap).bindPopup('<strong>{{ $lote->nombre }}</strong><br>{{ $lote->superficie }} ha').openPopup();
                    if (sup > 0) L.circle([lat, lng], { color: '#2c5530', fillColor: '#28a745', fillOpacity: 0.3, radius: Math.sqrt(sup * 10000 / Math.PI) }).addTo(loteMap);
                    setTimeout(function () { loteMap.invalidateSize(); }, 100);
                @endif
            }
        });
    </script>
@endpush