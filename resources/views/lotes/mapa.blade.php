@extends('layouts.app')

@section('title', 'Mapa de lotes | Fusion-Proyectos')
@section('page_title', 'Mapa de Lotes')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.index') }}" style="color: #2c5530;">Lotes</a></li>
    <li class="breadcrumb-item active">Mapa</li>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    :root {
        --primary-color: #2c5530;
        --secondary-color: #4a7c59;
        --accent-color: #e8f5e8;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --info-color: #17a2b8;
        --text-dark: #1a252f;
        --text-light: #6c757d;
        --border-color: #dee2e6;
    }

    .map-container {
        height: 550px;
        border-radius: 10px;
        overflow: hidden;
        position: relative;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .map-filters {
        position: absolute;
        top: 15px;
        left: 15px;
        z-index: 1000;
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        min-width: 280px;
        max-height: 90%;
        overflow-y: auto;
    }

    .filter-group { margin-bottom: 12px; }
    .filter-group:last-child { margin-bottom: 0; }
    .filter-group label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 5px;
        display: block;
        font-size: 13px;
    }
    .filter-group select, .filter-group input {
        width: 100%;
        padding: 6px 10px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 13px;
    }
    .filter-group select:focus, .filter-group input:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 2px rgba(44, 85, 48, 0.2);
    }

    .legend-container {
        position: absolute;
        bottom: 15px;
        left: 15px;
        z-index: 1000;
        background: white;
        padding: 12px 15px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    }
    .legend-title {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 10px;
        font-size: 14px;
    }
    .legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 6px;
    }
    .legend-item:last-child { margin-bottom: 0; }
    .legend-color {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        margin-right: 8px;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .legend-color.disponible { background: #6c757d; }
    .legend-color.preparacion { background: var(--info-color); }
    .legend-color.sembrado { background: #007bff; }
    .legend-color.produccion { background: var(--success-color); }
    .legend-color.cosechado { background: var(--warning-color); }
    .legend-color.descanso { background: #343a40; }
    .legend-text { font-size: 13px; color: var(--text-dark); }

    .lot-info-panel {
        position: absolute;
        bottom: 15px;
        right: 15px;
        z-index: 1000;
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        min-width: 300px;
        max-width: 350px;
        display: none;
    }
    .lot-info-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f1f3f4;
    }
    .lot-info-title { font-size: 16px; font-weight: 600; color: var(--text-dark); }
    .close-panel {
        background: none; border: none;
        font-size: 18px; color: var(--text-light);
        cursor: pointer; padding: 0;
        width: 28px; height: 28px;
        display: flex; align-items: center; justify-content: center;
        border-radius: 50%; transition: all 0.3s ease;
    }
    .close-panel:hover { background: #f1f3f4; color: var(--text-dark); }

    .lot-detail {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        border-bottom: 1px solid #f8f9fc;
    }
    .lot-detail:last-child { border-bottom: none; }
    .lot-detail-label { font-weight: 600; color: var(--text-dark); font-size: 13px; }
    .lot-detail-value { color: var(--text-light); font-size: 13px; }

    .lot-status-badge {
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .status-disponible { background: rgba(108, 117, 125, 0.2); color: #6c757d; }
    .status-preparacion { background: rgba(23, 162, 184, 0.2); color: var(--info-color); }
    .status-sembrado { background: rgba(0, 123, 255, 0.2); color: #007bff; }
    .status-produccion { background: rgba(40, 167, 69, 0.2); color: var(--success-color); }
    .status-cosechado { background: rgba(255, 193, 7, 0.2); color: #d39e00; }
    .status-descanso { background: rgba(52, 58, 64, 0.2); color: #343a40; }

    .lot-actions { margin-top: 12px; padding-top: 12px; border-top: 2px solid #f1f3f4; }
    .lot-action-btn { width: 100%; margin-bottom: 6px; border-radius: 6px; font-weight: 500; font-size: 13px; }
    .lot-action-btn:last-child { margin-bottom: 0; }

    .statistics-panel {
        background: white;
        border-radius: 10px;
        padding: 15px 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .stat-item { text-align: center; padding: 10px; }
    .stat-number { font-size: 24px; font-weight: 700; color: var(--primary-color); margin-bottom: 3px; }
    .stat-label { font-size: 12px; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-divider { border-left: 2px solid #f1f3f4; height: 50px; margin: auto; }

    .btn-primary { background: var(--primary-color); border-color: var(--primary-color); }
    .btn-primary:hover { background: var(--secondary-color); border-color: var(--secondary-color); }

    .card { border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
    .card-header { background: white; border-bottom: 2px solid #f1f3f4; font-weight: 600; }

    .lot-ranking-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f1f3f4;
    }
    .lot-ranking-item:last-child { border-bottom: none; }

    .alert-geo-item {
        display: flex;
        align-items: center;
        padding: 10px;
        margin-bottom: 8px;
        border-radius: 8px;
    }
    .alert-geo-item:last-child { margin-bottom: 0; }
</style>
@endpush

@section('content')
<!-- Panel de Estadísticas -->
<div class="statistics-panel">
    <div class="row align-items-center">
        <div class="col-md-2 stat-item">
            <div class="stat-number">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Lotes</div>
        </div>
        <div class="col-md-1 d-none d-md-flex align-items-center">
            <div class="stat-divider"></div>
        </div>
        <div class="col-md-2 stat-item">
            <div class="stat-number text-success">{{ $stats['en_produccion'] }}</div>
            <div class="stat-label">En Produccion</div>
        </div>
        <div class="col-md-1 d-none d-md-flex align-items-center">
            <div class="stat-divider"></div>
        </div>
        <div class="col-md-2 stat-item">
            <div class="stat-number text-warning">{{ $stats['cosechados'] }}</div>
            <div class="stat-label">Cosechados</div>
        </div>
        <div class="col-md-1 d-none d-md-flex align-items-center">
            <div class="stat-divider"></div>
        </div>
        <div class="col-md-2 stat-item">
            <div class="stat-number">{{ number_format($stats['hectareas'], 1) }}</div>
            <div class="stat-label">Hectareas Total</div>
        </div>
    </div>
</div>

<!-- Mapa Principal -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fas fa-map mr-2"></i>
                    Visualizacion Geografica de Lotes
                </h3>
                <div>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleFilters()">
                        <i class="fas fa-filter"></i> Filtros
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="centerMap()">
                        <i class="fas fa-crosshairs"></i> Centrar
                    </button>
                    @can('lotes.create')
                    <a href="{{ route('lotes.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Lote
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body p-0">
                <div class="map-container" id="mapContainer">
                    <!-- Mapa Leaflet -->
                    <div id="map" style="height: 100%; width: 100%;"></div>

                    <!-- Filtros del Mapa -->
                    <div class="map-filters" id="mapFilters">
                        <h6 style="margin-bottom: 12px; color: var(--text-dark); font-weight: 600;">
                            <i class="fas fa-filter mr-2"></i>Filtros
                        </h6>
                        <div class="filter-group">
                            <label>Propietario</label>
                            <select class="form-control form-control-sm" id="filtroUsuario">
                                <option value="">Todos</option>
                                @foreach($usuarios as $u)
                                    <option value="{{ $u->usuarioid }}">{{ $u->nombre }} {{ $u->apellido }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Cultivo</label>
                            <select class="form-control form-control-sm" id="filtroCultivo">
                                <option value="">Todos</option>
                                @foreach($cultivos as $c)
                                    <option value="{{ $c->cultivoid }}">{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Estado</label>
                            <select class="form-control form-control-sm" id="filtroEstado">
                                <option value="">Todos</option>
                                @foreach($estados as $e)
                                    <option value="{{ $e->estadolotetipoid }}">{{ ucfirst($e->nombre) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row mt-2">
                            <div class="col-6">
                                <button class="btn btn-primary btn-sm btn-block" onclick="aplicarFiltros()">
                                    <i class="fas fa-search"></i> Aplicar
                                </button>
                            </div>
                            <div class="col-6">
                                <button class="btn btn-outline-secondary btn-sm btn-block" onclick="limpiarFiltros()">
                                    <i class="fas fa-times"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Leyenda -->
                    <div class="legend-container">
                        <div class="legend-title"><i class="fas fa-info-circle mr-1"></i> Estados</div>
                        <div class="legend-item">
                            <div class="legend-color disponible"></div>
                            <span class="legend-text">Disponible</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color preparacion"></div>
                            <span class="legend-text">En Preparacion</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color sembrado"></div>
                            <span class="legend-text">Sembrado</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color produccion"></div>
                            <span class="legend-text">En Produccion</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color cosechado"></div>
                            <span class="legend-text">Cosechado</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color descanso"></div>
                            <span class="legend-text">En Descanso</span>
                        </div>
                    </div>

                    <!-- Panel de Info del Lote -->
                    <div class="lot-info-panel" id="lotInfoPanel">
                        <div class="lot-info-header">
                            <span class="lot-info-title" id="panelLoteName">Nombre del Lote</span>
                            <button class="close-panel" onclick="cerrarPanel()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="lot-detail">
                            <span class="lot-detail-label">Estado</span>
                            <span id="panelEstado" class="lot-status-badge status-produccion">En Produccion</span>
                        </div>
                        <div class="lot-detail">
                            <span class="lot-detail-label">Propietario</span>
                            <span class="lot-detail-value" id="panelPropietario">-</span>
                        </div>
                        <div class="lot-detail">
                            <span class="lot-detail-label">Cultivo</span>
                            <span class="lot-detail-value" id="panelCultivo">-</span>
                        </div>
                        <div class="lot-detail">
                            <span class="lot-detail-label">Superficie</span>
                            <span class="lot-detail-value" id="panelSuperficie">- Ha</span>
                        </div>
                        <div class="lot-detail">
                            <span class="lot-detail-label">Ubicacion</span>
                            <span class="lot-detail-value" id="panelUbicacion">-</span>
                        </div>
                        <div class="lot-actions">
                            <a href="#" id="btnVerDetalle" class="btn btn-primary btn-sm lot-action-btn">
                                <i class="fas fa-eye mr-1"></i> Ver Detalle
                            </a>
                            <a href="#" id="btnEditar" class="btn btn-outline-secondary btn-sm lot-action-btn">
                                <i class="fas fa-edit mr-1"></i> Editar Lote
                            </a>
                            <a href="{{ route('actividades.create') }}" class="btn btn-outline-success btn-sm lot-action-btn">
                                <i class="fas fa-plus mr-1"></i> Nueva Actividad
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fila de Ranking y Alertas -->
<div class="row mt-3">
    <!-- Top Lotes por Producción -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-trophy mr-2 text-warning"></i>
                    Top Lotes por Produccion
                </h3>
            </div>
            <div class="card-body">
                @forelse($topLotes as $lote)
                    <div class="lot-ranking-item">
                        <div>
                            <strong style="color: var(--text-dark);">{{ $lote->nombre }}</strong>
                            <br>
                            <small style="color: var(--text-light);">
                                {{ $lote->usuario->nombre ?? '' }} {{ $lote->usuario->apellido ?? '' }} 
                                • {{ $lote->cultivo->nombre ?? 'Sin cultivo' }}
                            </small>
                        </div>
                        <div class="text-right">
                            <span style="color: var(--success-color); font-weight: 600;">
                                {{ number_format($lote->total_produccion, 0) }} kg
                            </span>
                            <br>
                            <small style="color: var(--text-light);">{{ $lote->superficie }} Ha</small>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-chart-bar fa-2x mb-2"></i>
                        <p class="mb-0">No hay datos de produccion</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Alertas Geográficas -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle mr-2 text-danger"></i>
                    Alertas Geograficas
                </h3>
            </div>
            <div class="card-body">
                @forelse($lotesSinCoordenadas as $lote)
                    <div class="alert-geo-item" style="background: rgba(255, 193, 7, 0.1); border-left: 4px solid var(--warning-color);">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--warning-color); color: white; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-size: 14px;">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div style="flex: 1;">
                            <h6 style="margin: 0; font-size: 13px; font-weight: 600; color: var(--text-dark);">Sin ubicacion: {{ $lote->nombre }}</h6>
                            <small style="color: var(--text-light); font-size: 11px;">
                                <i class="fas fa-user mr-1"></i>{{ $lote->usuario->nombre ?? 'Sin asignar' }} - {{ $lote->superficie }} Ha
                            </small>
                        </div>
                        @can('lotes.update')
                        <a href="{{ route('lotes.edit', $lote) }}" class="btn btn-sm btn-outline-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan
                    </div>
                @empty
                    <div class="text-center text-success py-3">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <p class="mb-0">Todos los lotes tienen ubicacion</p>
                    </div>
                @endforelse

                @if($alertasClimaticas->count() > 0)
                    @foreach($alertasClimaticas as $alerta)
                        <div class="alert-geo-item" style="background: rgba(220, 53, 69, 0.1); border-left: 4px solid var(--danger-color);">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--danger-color); color: white; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-size: 14px;">
                                <i class="fas fa-cloud-rain"></i>
                            </div>
                            <div style="flex: 1;">
                                <h6 style="margin: 0; font-size: 13px; font-weight: 600; color: var(--text-dark);">{{ $alerta->tipo ?? 'Alerta climatica' }}</h6>
                                <small style="color: var(--text-light); font-size: 11px;">
                                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $alerta->lote->nombre ?? '' }}
                                </small>
                            </div>
                            <button class="btn btn-sm btn-outline-danger" onclick="enfocarLote({{ $alerta->lote->latitud ?? 0 }}, {{ $alerta->lote->longitud ?? 0 }})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    @endforeach
                @endif

                @foreach($lotesStockBajo as $lote)
                    <div class="alert-geo-item" style="background: rgba(23, 162, 184, 0.1); border-left: 4px solid var(--info-color);">
                        <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--info-color); color: white; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-size: 14px;">
                            <i class="fas fa-box"></i>
                        </div>
                        <div style="flex: 1;">
                            <h6 style="margin: 0; font-size: 13px; font-weight: 600; color: var(--text-dark);">Requiere insumos: {{ $lote->nombre }}</h6>
                            <small style="color: var(--text-light); font-size: 11px;">
                                @if($lote->latitud && $lote->longitud)
                                    <i class="fas fa-map-marker-alt mr-1"></i>{{ number_format($lote->latitud, 4) }}, {{ number_format($lote->longitud, 4) }}
                                @else
                                    <i class="fas fa-info-circle mr-1"></i>Sin coordenadas
                                @endif
                            </small>
                        </div>
                        @if($lote->latitud && $lote->longitud)
                            <button class="btn btn-sm btn-outline-info" onclick="enfocarLote({{ $lote->latitud }}, {{ $lote->longitud }})">
                                <i class="fas fa-eye"></i>
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Herramientas del Mapa -->
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tools mr-2"></i>
                    Herramientas del Mapa
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <button class="btn btn-outline-primary btn-block" onclick="exportarMapa()">
                            <i class="fas fa-download mr-2"></i>
                            Exportar Mapa
                        </button>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <button class="btn btn-outline-success btn-block" onclick="window.print()">
                            <i class="fas fa-print mr-2"></i>
                            Imprimir Mapa
                        </button>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <button class="btn btn-outline-info btn-block" onclick="compartirMapa()">
                            <i class="fas fa-share mr-2"></i>
                            Compartir
                        </button>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <a href="{{ route('producciones.index') }}" class="btn btn-outline-warning btn-block">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Ver Reportes
                        </a>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <button class="btn btn-outline-secondary btn-block" onclick="toggleCapaSatelite()">
                            <i class="fas fa-satellite mr-2"></i>
                            Vista Satelite
                        </button>
                    </div>
                    <div class="col-md-2 col-sm-4 col-6 mb-2">
                        <button class="btn btn-outline-dark btn-block" onclick="toggleFullscreen()">
                            <i class="fas fa-expand mr-2"></i>
                            Pantalla Completa
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Datos de lotes desde PHP
var lotesData = @json($lotesConCoordenadas);

// Colores por estado
var coloresEstado = {
    'disponible': '#6c757d',
    'en preparación': '#17a2b8',
    'sembrado': '#007bff',
    'en producción': '#28a745',
    'cosechado': '#ffc107',
    'en descanso': '#343a40'
};

// Capas del mapa
var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
});

var sateliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: '© Esri'
});

// Inicializar mapa
var map = L.map('map', {
    layers: [osmLayer]
}).setView([-17.7833, -63.1821], 10);

var currentLayer = 'osm';
var markers = [];
var circles = [];

// Función para calcular radio según hectáreas
function calcularRadio(ha) {
    return Math.sqrt(ha * 10000 / Math.PI);
}

// Función para agregar lotes al mapa
function cargarLotes(lotes) {
    // Limpiar markers anteriores
    markers.forEach(m => map.removeLayer(m));
    circles.forEach(c => map.removeLayer(c));
    markers = [];
    circles = [];

    lotes.forEach(function(lote) {
        var estado = lote.estado ? lote.estado.toLowerCase() : 'disponible';
        var color = coloresEstado[estado] || '#6c757d';

        // Crear círculo para representar el área
        var circle = L.circle([lote.latitud, lote.longitud], {
            color: color,
            fillColor: color,
            fillOpacity: 0.4,
            radius: calcularRadio(lote.superficie || 1)
        }).addTo(map);
        circles.push(circle);

        // Crear marcador
        var marker = L.marker([lote.latitud, lote.longitud]).addTo(map);
        markers.push(marker);

        // Popup básico
        marker.bindPopup('<b>' + lote.nombre + '</b><br>' + (lote.cultivo || 'Sin cultivo') + '<br>' + lote.superficie + ' Ha');

        // Click para mostrar panel
        marker.on('click', function() {
            mostrarPanelLote(lote);
        });

        circle.on('click', function() {
            mostrarPanelLote(lote);
        });
    });

    // Ajustar vista si hay lotes
    if (lotes.length > 0) {
        var group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
}

// Mostrar panel de información del lote
function mostrarPanelLote(lote) {
    document.getElementById('panelLoteName').textContent = lote.nombre;
    document.getElementById('panelPropietario').textContent = lote.propietario || '-';
    document.getElementById('panelCultivo').textContent = lote.cultivo || 'Sin cultivo';
    document.getElementById('panelSuperficie').textContent = lote.superficie + ' Ha';
    document.getElementById('panelUbicacion').textContent = lote.ubicacion || 'Sin ubicación';
    
    var estado = lote.estado ? lote.estado.toLowerCase() : 'disponible';
    var estadoClass = 'status-' + estado.replace('en ', '').replace('ó', 'o');
    var panelEstado = document.getElementById('panelEstado');
    panelEstado.className = 'lot-status-badge ' + estadoClass;
    panelEstado.textContent = lote.estado || 'Disponible';

    document.getElementById('btnVerDetalle').href = '/lotes/' + lote.id;
    document.getElementById('btnEditar').href = '/lotes/' + lote.id + '/edit';

    document.getElementById('lotInfoPanel').style.display = 'block';
}

function cerrarPanel() {
    document.getElementById('lotInfoPanel').style.display = 'none';
}

function toggleFilters() {
    var filters = document.getElementById('mapFilters');
    filters.style.display = filters.style.display === 'none' ? 'block' : 'none';
}

function centerMap() {
    if (markers.length > 0) {
        var group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    } else {
        map.setView([-17.7833, -63.1821], 10);
    }
}

function aplicarFiltros() {
    var usuario = document.getElementById('filtroUsuario').value;
    var cultivo = document.getElementById('filtroCultivo').value;
    var estado = document.getElementById('filtroEstado').value;

    var lotesFiltrados = lotesData.filter(function(lote) {
        if (usuario && lote.usuarioid != usuario) return false;
        if (cultivo && lote.cultivoid != cultivo) return false;
        if (estado && lote.estadoid != estado) return false;
        return true;
    });

    cargarLotes(lotesFiltrados);
}

function limpiarFiltros() {
    document.getElementById('filtroUsuario').value = '';
    document.getElementById('filtroCultivo').value = '';
    document.getElementById('filtroEstado').value = '';
    cargarLotes(lotesData);
}

// Enfocar en un lote específico
function enfocarLote(lat, lng) {
    map.setView([lat, lng], 15);
}

// Cambiar a vista satélite
function toggleCapaSatelite() {
    if (currentLayer === 'osm') {
        map.removeLayer(osmLayer);
        map.addLayer(sateliteLayer);
        currentLayer = 'satelite';
    } else {
        map.removeLayer(sateliteLayer);
        map.addLayer(osmLayer);
        currentLayer = 'osm';
    }
}

// Pantalla completa
function toggleFullscreen() {
    var container = document.getElementById('mapContainer');
    if (!document.fullscreenElement) {
        container.requestFullscreen().catch(err => {
            alert('Error al activar pantalla completa');
        });
    } else {
        document.exitFullscreen();
    }
}

// Exportar mapa (captura simple)
function exportarMapa() {
    alert('Funcionalidad de exportar mapa.\n\nPuedes usar Ctrl+P para imprimir o guardar como PDF.');
}

// Compartir mapa
function compartirMapa() {
    var url = window.location.href;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(function() {
            alert('Enlace copiado al portapapeles:\n' + url);
        });
    } else {
        prompt('Copia este enlace:', url);
    }
}

// Cargar lotes al iniciar
$(document).ready(function() {
    cargarLotes(lotesData);
    
    // Animación de cards
    $('.card').css('opacity', '0').animate({'opacity': '1'}, 800);
});
</script>
@endpush