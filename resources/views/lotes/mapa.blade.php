@extends('layouts.app')

@section('title', 'Mapa de lotes | AgroNexus')
@section('page_title', 'Mapa de lotes')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.index') }}">Lotes</a></li>
    <li class="breadcrumb-item active">Mapa</li>
@endsection

@php
    $maxProd = $topLotes->max('total_produccion') ?: 1;
    $lotesEnMapa = $lotesConCoordenadas->count();
    $pctEnMapa = $stats['total'] > 0
        ? round(($stats['en_mapa'] / $stats['total']) * 100)
        : 0;
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@include('partials.modulo-lotes-actividades-styles')
<style>
.page-lotes-mapa .card {
    border-radius: 10px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}
.page-lotes-mapa .map-container {
    height: 560px;
    border-radius: 0 0 10px 10px;
    overflow: hidden;
    position: relative;
}
.page-lotes-mapa .map-filters {
    position: absolute;
    top: 12px;
    left: 12px;
    z-index: 1000;
    background: #fff;
    padding: 12px 14px;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    min-width: 260px;
    max-width: 300px;
    max-height: 85%;
    overflow-y: auto;
}
.page-lotes-mapa .legend-container {
    position: absolute;
    bottom: 12px;
    left: 12px;
    z-index: 1000;
    background: #fff;
    padding: 10px 14px;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}
.page-lotes-mapa .legend-color {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    margin-right: 8px;
    border: 2px solid #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
    display: inline-block;
}
.page-lotes-mapa .legend-color.disponible { background: #6c757d; }
.page-lotes-mapa .legend-color.preparacion { background: #17a2b8; }
.page-lotes-mapa .legend-color.sembrado { background: #007bff; }
.page-lotes-mapa .legend-color.produccion { background: #28a745; }
.page-lotes-mapa .legend-color.cosechado { background: #ffc107; }
.page-lotes-mapa .legend-color.descanso { background: #343a40; }
.page-lotes-mapa .lot-info-panel {
    position: absolute;
    bottom: 12px;
    right: 12px;
    z-index: 1000;
    background: #fff;
    padding: 14px;
    border-radius: 8px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    min-width: 280px;
    max-width: 320px;
    display: none;
}
.page-lotes-mapa .lot-status-badge {
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
}
.page-lotes-mapa .status-disponible { background: rgba(108,117,125,.2); color: #6c757d; }
.page-lotes-mapa .status-preparacion { background: rgba(23,162,184,.2); color: #17a2b8; }
.page-lotes-mapa .status-sembrado { background: rgba(0,123,255,.2); color: #007bff; }
.page-lotes-mapa .status-produccion { background: rgba(40,167,69,.2); color: #28a745; }
.page-lotes-mapa .status-cosechado { background: rgba(255,193,7,.2); color: #d39e00; }
.page-lotes-mapa .status-descanso { background: rgba(52,58,64,.2); color: #343a40; }
.page-lotes-mapa .map-sidebar { max-height: 600px; overflow-y: auto; }
.page-lotes-mapa .rank-bar {
    height: 6px;
    border-radius: 3px;
    background: #e9ecef;
    margin-top: 6px;
    overflow: hidden;
}
.page-lotes-mapa .rank-bar > span {
    display: block;
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997);
    border-radius: 3px;
}
.page-lotes-mapa .products-list .product-img {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endpush

@section('content')
<div class="modulo-la page-lotes-mapa">

    <div class="row mb-2">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Lotes totales</p>
                </div>
                <div class="icon"><i class="fas fa-map"></i></div>
                <a href="{{ route('lotes.index') }}" class="small-box-footer">
                    Ver listado <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $stats['en_mapa'] }}</h3>
                    <p>Con ubicación GPS</p>
                </div>
                <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                <span class="small-box-footer">{{ $pctEnMapa }}% georreferenciados</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3>{{ $stats['en_produccion'] }}</h3>
                    <p>En producción</p>
                </div>
                <div class="icon"><i class="fas fa-leaf"></i></div>
                <span class="small-box-footer">{{ $stats['cosechados'] }} cosechados</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-purple">
                <div class="inner">
                    <h3>{{ number_format($stats['hectareas'], 1) }}</h3>
                    <p>Hectáreas totales</p>
                </div>
                <div class="icon"><i class="fas fa-ruler-combined"></i></div>
                <span class="small-box-footer">Superficie registrada</span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-9 mb-3">
            <div class="card card-outline card-success card-modulo-main elevation-1">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-map-marked-alt text-success mr-1"></i>
                        Mapa de parcelas
                        <span class="badge badge-light border text-muted badge-registros ml-2" id="badgeLotesVisibles">{{ $lotesEnMapa }} en mapa</span>
                    </h3>
                    <div class="card-tools d-flex align-items-center flex-wrap" style="gap: 6px;">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-default" onclick="toggleFilters()" title="Filtros del mapa">
                                <i class="fas fa-filter"></i>
                            </button>
                            <button type="button" class="btn btn-default" onclick="centerMap()" title="Centrar">
                                <i class="fas fa-crosshairs"></i>
                            </button>
                            <button type="button" class="btn btn-default" onclick="toggleCapaSatelite()" title="Satélite">
                                <i class="fas fa-satellite"></i>
                            </button>
                            <button type="button" class="btn btn-default" onclick="toggleFullscreen()" title="Pantalla completa">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>
                        @can('lotes.create')
                        <a href="{{ route('lotes.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus mr-1"></i> Nuevo
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="map-container" id="mapContainer">
                        <div id="map" style="height: 100%; width: 100%;"></div>

                        <div class="map-filters" id="mapFilters">
                            <h6 class="mb-2 font-weight-bold">
                                <i class="fas fa-filter text-primary mr-1"></i> Filtros del mapa
                            </h6>
                            <div class="form-group mb-2">
                                <label class="small text-muted mb-1">Propietario</label>
                                <select class="form-control form-control-sm" id="filtroUsuario">
                                    <option value="">Todos</option>
                                    @foreach($usuarios as $u)
                                        <option value="{{ $u->usuarioid }}">{{ $u->nombre }} {{ $u->apellido }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small text-muted mb-1">Cultivo</label>
                                <select class="form-control form-control-sm" id="filtroCultivo">
                                    <option value="">Todos</option>
                                    @foreach($cultivos as $c)
                                        <option value="{{ $c->cultivoid }}">{{ $c->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small text-muted mb-1">Estado</label>
                                <select class="form-control form-control-sm" id="filtroEstado">
                                    <option value="">Todos</option>
                                    @foreach($estados as $e)
                                        <option value="{{ $e->estadolotetipoid }}">{{ ucfirst($e->nombre) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <button type="button" class="btn btn-primary btn-sm btn-block" onclick="aplicarFiltros()">
                                        <i class="fas fa-search"></i> Aplicar
                                    </button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block" onclick="limpiarFiltros()">
                                        <i class="fas fa-eraser"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="legend-container">
                            <div class="font-weight-bold small mb-2">
                                <i class="fas fa-info-circle text-info mr-1"></i> Estados
                            </div>
                            @foreach([
                                ['disponible', 'Disponible'],
                                ['preparacion', 'En preparación'],
                                ['sembrado', 'Sembrado'],
                                ['produccion', 'En producción'],
                                ['cosechado', 'Cosechado'],
                                ['descanso', 'En descanso'],
                            ] as [$cls, $label])
                            <div class="small mb-1">
                                <span class="legend-color {{ $cls }}"></span>{{ $label }}
                            </div>
                            @endforeach
                        </div>

                        <div class="lot-info-panel" id="lotInfoPanel">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <strong id="panelLoteName">Lote</strong>
                                <button type="button" class="close" onclick="cerrarPanel()" aria-label="Cerrar">
                                    <span>&times;</span>
                                </button>
                            </div>
                            <dl class="row mb-2 small">
                                <dt class="col-5 text-muted">Estado</dt>
                                <dd class="col-7 mb-1"><span id="panelEstado" class="lot-status-badge status-produccion">—</span></dd>
                                <dt class="col-5 text-muted">Propietario</dt>
                                <dd class="col-7 mb-1" id="panelPropietario">—</dd>
                                <dt class="col-5 text-muted">Cultivo</dt>
                                <dd class="col-7 mb-1" id="panelCultivo">—</dd>
                                <dt class="col-5 text-muted">Superficie</dt>
                                <dd class="col-7 mb-1" id="panelSuperficie">—</dd>
                                <dt class="col-5 text-muted">Ubicación</dt>
                                <dd class="col-7 mb-0" id="panelUbicacion">—</dd>
                            </dl>
                            <a href="#" id="btnVerDetalle" class="btn btn-primary btn-sm btn-block mb-1">
                                <i class="fas fa-eye mr-1"></i> Ver detalle
                            </a>
                            <a href="#" id="btnEditar" class="btn btn-warning btn-sm btn-block mb-1">
                                <i class="fas fa-edit mr-1"></i> Editar lote
                            </a>
                            <a href="{{ route('actividades.create') }}" class="btn btn-success btn-sm btn-block">
                                <i class="fas fa-plus mr-1"></i> Nueva actividad
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-footer py-2">
                    <small class="text-muted">
                        <i class="fas fa-mouse-pointer text-success mr-1"></i>
                        Clic en círculo o marcador para abrir el panel. Colores según estado (leyenda inferior izquierda).
                    </small>
                    <span class="float-right">
                        <button type="button" class="btn btn-link btn-sm p-0" onclick="compartirMapa()">Compartir enlace</button>
                        ·
                        <button type="button" class="btn btn-link btn-sm p-0" onclick="window.print()">Imprimir</button>
                    </span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 mb-3">
            <div class="card card-warning card-outline elevation-2 mb-3">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-trophy text-warning mr-1"></i> Top producción</h3>
                </div>
                <div class="card-body map-sidebar p-0">
                    <ul class="products-list product-list-in-card px-2 mb-0">
                        @forelse($topLotes as $i => $lote)
                            @php
                                $pct = min(100, ($lote->total_produccion / $maxProd) * 100);
                                $medal = match($i) { 0 => 'warning', 1 => 'secondary', 2 => 'dark', default => 'light' };
                            @endphp
                            <li class="item border-bottom py-2">
                                <div class="product-img bg-light rounded">
                                    <span class="badge badge-{{ $medal }}">{{ $i + 1 }}</span>
                                </div>
                                <div class="product-info">
                                    <span class="product-title">{{ $lote->nombre }}</span>
                                    <span class="product-description">
                                        {{ $lote->cultivo->nombre ?? '—' }} · {{ $lote->superficie }} ha
                                        <span class="float-right text-success font-weight-bold">
                                            {{ number_format($lote->total_produccion, 0) }} kg
                                        </span>
                                    </span>
                                    <div class="rank-bar"><span style="width: {{ $pct }}%"></span></div>
                                </div>
                            </li>
                        @empty
                            <li class="item text-center text-muted py-4">Sin datos de producción.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="card card-danger card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-circle text-danger mr-1"></i> Alertas en campo</h3>
                </div>
                <div class="card-body map-sidebar py-2">
                    @forelse($lotesSinCoordenadas as $lote)
                        <div class="callout callout-warning py-2 mb-2">
                            <h6 class="mb-1"><i class="fas fa-map-marker-alt mr-1"></i> Sin ubicación</h6>
                            <p class="mb-1 small"><strong>{{ $lote->nombre }}</strong></p>
                            <small class="text-muted">
                                {{ $lote->usuario->nombre ?? 'Sin asignar' }} · {{ $lote->superficie }} ha
                            </small>
                            @can('lotes.update')
                            <div class="mt-2">
                                <a href="{{ route('lotes.edit', $lote) }}" class="btn btn-warning btn-xs">
                                    <i class="fas fa-edit"></i> Asignar GPS
                                </a>
                            </div>
                            @endcan
                        </div>
                    @empty
                        <div class="text-center text-success py-3">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <p class="mb-0 small">Todos los lotes tienen coordenadas</p>
                        </div>
                    @endforelse

                    @foreach($alertasClimaticas as $alerta)
                        <div class="callout callout-danger py-2 mb-2">
                            <h6 class="mb-1"><i class="fas fa-cloud-rain mr-1"></i> {{ $alerta->tipo ?? 'Alerta climática' }}</h6>
                            <small>{{ $alerta->lote->nombre ?? '' }}</small>
                            <button type="button" class="btn btn-danger btn-xs float-right"
                                onclick="enfocarLote({{ $alerta->lote->latitud ?? 0 }}, {{ $alerta->lote->longitud ?? 0 }})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    @endforeach

                    @foreach($lotesStockBajo as $lote)
                        <div class="callout callout-info py-2 mb-2">
                            <h6 class="mb-1"><i class="fas fa-box mr-1"></i> Requiere insumos</h6>
                            <p class="mb-0 small"><strong>{{ $lote->nombre }}</strong></p>
                            @if($lote->latitud && $lote->longitud)
                                <button type="button" class="btn btn-info btn-xs mt-1"
                                    onclick="enfocarLote({{ $lote->latitud }}, {{ $lote->longitud }})">
                                    <i class="fas fa-eye"></i> Ver en mapa
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var lotesData = @json($lotesConCoordenadas);

var coloresEstado = {
    'disponible': '#6c757d',
    'en preparación': '#17a2b8',
    'sembrado': '#007bff',
    'en producción': '#28a745',
    'cosechado': '#ffc107',
    'en descanso': '#343a40'
};

var osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
});

var sateliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: '© Esri'
});

var map = L.map('map', { layers: [osmLayer] }).setView([-17.7833, -63.1821], 10);

var currentLayer = 'osm';
var markers = [];
var circles = [];

function calcularRadio(ha) {
    return Math.sqrt(ha * 10000 / Math.PI);
}

function cargarLotes(lotes) {
    markers.forEach(m => map.removeLayer(m));
    circles.forEach(c => map.removeLayer(c));
    markers = [];
    circles = [];

    lotes.forEach(function (lote) {
        var estado = lote.estado ? lote.estado.toLowerCase() : 'disponible';
        var color = coloresEstado[estado] || '#6c757d';

        var circle = L.circle([lote.latitud, lote.longitud], {
            color: color,
            fillColor: color,
            fillOpacity: 0.4,
            radius: calcularRadio(lote.superficie || 1)
        }).addTo(map);
        circles.push(circle);

        var marker = L.marker([lote.latitud, lote.longitud]).addTo(map);
        markers.push(marker);

        marker.bindPopup('<b>' + lote.nombre + '</b><br>' + (lote.cultivo || 'Sin cultivo') + '<br>' + lote.superficie + ' Ha');

        marker.on('click', function () { mostrarPanelLote(lote); });
        circle.on('click', function () { mostrarPanelLote(lote); });
    });

    actualizarContador(lotes.length);

    if (lotes.length > 0) {
        var group = new L.featureGroup(markers);
        map.fitBounds(group.getBounds().pad(0.1));
    }
}

function mostrarPanelLote(lote) {
    document.getElementById('panelLoteName').textContent = lote.nombre;
    document.getElementById('panelPropietario').textContent = lote.propietario || '—';
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
        map.fitBounds(new L.featureGroup(markers).getBounds().pad(0.1));
    } else {
        map.setView([-17.7833, -63.1821], 10);
    }
}

function aplicarFiltros() {
    var usuario = document.getElementById('filtroUsuario').value;
    var cultivo = document.getElementById('filtroCultivo').value;
    var estado = document.getElementById('filtroEstado').value;

    var lotesFiltrados = lotesData.filter(function (lote) {
        if (usuario && lote.usuarioid != usuario) return false;
        if (cultivo && lote.cultivoid != cultivo) return false;
        if (estado && lote.estadoid != estado) return false;
        return true;
    });

    cargarLotes(lotesFiltrados);
}

function actualizarContador(n) {
    var el = document.getElementById('badgeLotesVisibles');
    if (el) el.textContent = n + ' en mapa';
}

function limpiarFiltros() {
    document.getElementById('filtroUsuario').value = '';
    document.getElementById('filtroCultivo').value = '';
    document.getElementById('filtroEstado').value = '';
    cargarLotes(lotesData);
}

function enfocarLote(lat, lng) {
    if (lat && lng) map.setView([lat, lng], 15);
}

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

function toggleFullscreen() {
    var container = document.getElementById('mapContainer');
    if (!document.fullscreenElement) {
        container.requestFullscreen().catch(function () {
            alert('No se pudo activar pantalla completa');
        });
    } else {
        document.exitFullscreen();
    }
}

function compartirMapa() {
    var url = window.location.href;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(function () {
            alert('Enlace copiado:\n' + url);
        });
    } else {
        prompt('Copia este enlace:', url);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    cargarLotes(lotesData);
});
</script>
@endpush
