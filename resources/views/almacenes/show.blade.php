@php

    $ambitoActual = $ambito ?? \App\Support\AlmacenAmbito::AGRICOLA;

    $tituloDetalle = $ambitoActual === \App\Support\AlmacenAmbito::PLANTA

        ? 'Detalle de Almacén Planta'

        : 'Detalle de Almacén Agrícola';

    $coords = \App\Support\UbicacionGpsParser::coordsOrDefault($almacen->ubicacion ?? null);

    $tieneGps = \App\Support\UbicacionGpsParser::fromTexto($almacen->ubicacion ?? null) !== null;

@endphp

@extends('layouts.app')



@section('title', $tituloDetalle.' | AgroFusion')

@section('page_title', $tituloDetalle)



@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>

    <li class="breadcrumb-item"><a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.index') }}" style="color: #2c5530;">Almacenes</a></li>

    <li class="breadcrumb-item active">Detalle</li>

@endsection



@push('styles')

@include('partials.modulo-inventario-styles')

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>

<style>

.page-almacen-show .border-left-info { border-left: .25rem solid #36b9cc !important; }

.page-almacen-show .border-left-success { border-left: .25rem solid #1cc88a !important; }

.page-almacen-show .contenido-card {

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    box-shadow: 0 2px 12px rgba(0,0,0,.06);

}

.page-almacen-show .contenido-card .card-header {

    background: linear-gradient(135deg, #f0f7f1, #fff);

    border-bottom: 1px solid #e8f0e9;

}

#mapaDetalleAlmacen {

    height: 220px;

    min-height: 220px;

    width: 100%;

    border-radius: 10px;

    border: 1px solid #e2e8f0;

    background: #e8eef3;

    z-index: 1;

}

.leaflet-container { font-family: inherit; }

.ubicacion-texto { font-size: .9rem; color: #64748b; }

</style>

@endpush



@section('content')

<div class="modulo-inv page-almacen-show">

    <div class="row">

        <div class="col-lg-8">

            <div class="card shadow-sm mb-4 border-left-info">

                <div class="card-header bg-white py-3 d-flex flex-row align-items-center justify-content-between">

                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-warehouse mr-2"></i>Información del Almacén</h6>

                    @if(isset($resumenCapacidad))

                        <span class="badge badge-{{ ($resumenCapacidad['porcentaje'] ?? 0) > 85 ? 'danger' : 'success' }} px-3 py-2">

                            {{ $resumenCapacidad['porcentaje'] ?? 0 }}% ocupado

                        </span>

                    @endif

                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-6 mb-4">

                            <small class="text-uppercase text-muted font-weight-bold">Nombre</small>

                            <h4 class="font-weight-bold text-dark">{{ $almacen->nombre }}</h4>

                        </div>

                        @if($almacen->descripcion)

                        <div class="col-md-6 mb-4">

                            <small class="text-uppercase text-muted font-weight-bold">Descripción</small>

                            <p class="mb-0">{{ $almacen->descripcion }}</p>

                        </div>

                        @endif

                    </div>



                    <div class="row">

                        <div class="col-md-6 mb-4">

                            <small class="text-uppercase text-muted font-weight-bold d-block mb-2">Ubicación en mapa</small>

                            <div id="mapaDetalleAlmacen" data-lat="{{ $coords['lat'] }}" data-lng="{{ $coords['lng'] }}" data-nombre="{{ $almacen->nombre }}"></div>

                            @if($almacen->ubicacion)

                                <p class="ubicacion-texto mt-2 mb-0">

                                    <i class="fas fa-map-marker-alt text-danger mr-1"></i>{{ $almacen->ubicacion }}

                                    @unless($tieneGps)

                                        <span class="text-warning d-block small mt-1">Sin coordenadas GPS — se muestra ubicación por defecto (Santa Cruz).</span>

                                    @endunless

                                </p>

                            @else

                                <p class="ubicacion-texto mt-2 mb-0">Ubicación no especificada — mapa en Santa Cruz por defecto.</p>

                            @endif

                        </div>

                        <div class="col-md-6 mb-4">

                            <small class="text-uppercase text-muted font-weight-bold">Capacidad total</small>

                            <div>

                                <div class="d-flex align-items-center">

                                    <i class="fas fa-ruler-combined text-primary mr-2"></i>

                                    <span class="h4 font-weight-bold mb-0 text-primary">

                                        {{ number_format($almacen->capacidad, 0) }} <span class="h6 text-muted">kg</span>

                                    </span>

                                </div>

                                @if(isset($resumenCapacidad))

                                <p class="small text-muted mb-0 mt-2 pl-4">

                                    Ocupado: {{ number_format($resumenCapacidad['ocupado_kg'], 0) }} kg ·

                                    Disponible: {{ number_format($resumenCapacidad['disponible_kg'], 0) }} kg

                                </p>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>



        <div class="col-lg-4">

            <div class="card shadow-sm mb-4">

                <div class="card-header bg-light">

                    <h6 class="m-0 font-weight-bold text-dark">Acciones</h6>

                </div>

                <div class="card-body">

                    <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.edit', $almacen) }}" class="btn btn-warning btn-block mb-3 shadow-sm">

                        <i class="fas fa-edit mr-2"></i>Editar Datos

                    </a>

                    <hr>

                    <form action="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.destroy', $almacen) }}" method="POST"

                        onsubmit="return confirm('¿Está seguro de eliminar este almacén? Esta acción no se puede deshacer.')">

                        @csrf

                        @method('DELETE')

                        <button type="submit" class="btn btn-outline-danger btn-block">

                            <i class="fas fa-trash-alt mr-2"></i>Eliminar Almacén

                        </button>

                    </form>

                    <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.index') }}" class="btn btn-link btn-block mt-3 text-secondary">

                        <i class="fas fa-arrow-left mr-1"></i> Volver a la lista

                    </a>

                </div>

            </div>



            <div class="card shadow-sm border-left-success">

                <div class="card-body">

                    <div class="row no-gutters align-items-center">

                        <div class="col mr-2">

                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Stock en depósito</div>

                            <div class="h5 mb-0 font-weight-bold text-gray-800">

                                @if(isset($resumenCapacidad))

                                    {{ number_format($resumenCapacidad['ocupado_kg'], 0) }} kg

                                    <small class="text-muted d-block font-weight-normal" style="font-size:.75rem">Insumos, cosecha y producto de planta</small>

                                @else — @endif

                            </div>

                        </div>

                        <div class="col-auto"><i class="fas fa-boxes fa-2x text-gray-300"></i></div>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <div class="card contenido-card mt-2">

        <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center">

            <h5 class="mb-0 font-weight-bold text-success"><i class="fas fa-box-open mr-1"></i> Contenido del almacén</h5>

            <span class="badge badge-light border">{{ $contenidos->count() }} ítems</span>

        </div>

        <div class="card-body pb-2">

            <div class="row mb-3">

                <div class="col-md-6 mb-2 mb-md-0">

                    <label class="small text-muted mb-1">Buscar</label>

                    <div class="input-group input-group-sm">

                        <div class="input-group-prepend"><span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span></div>

                        <input type="text" id="contenidoSearch" class="form-control" placeholder="Nombre, cultivo o tipo...">

                    </div>

                </div>

                <div class="col-md-3 mb-2 mb-md-0">

                    <label class="small text-muted mb-1">Categoría</label>

                    <select id="contenidoFiltroCategoria" class="form-control form-control-sm">

                        <option value="">Todas</option>

                        <option value="insumo">Insumos</option>

                        <option value="cosecha">Cosecha</option>

                        <option value="producto_planta">Producto terminado</option>

                    </select>

                </div>

                <div class="col-md-3">

                    <label class="small text-muted mb-1">Tipo</label>

                    <select id="contenidoFiltroTipo" class="form-control form-control-sm">

                        <option value="">Todos los tipos</option>

                        @foreach($tiposContenidoFiltro as $tipo)

                            <option value="{{ strtolower($tipo) }}">{{ $tipo }}</option>

                        @endforeach

                    </select>

                </div>

            </div>

        </div>

        <div class="table-responsive">

            <table class="table table-modulo table-hover table-sm mb-0">

                <thead>

                    <tr>

                        <th>Producto</th><th>Categoría</th><th>Tipo</th>

                        <th class="text-right">Cantidad</th><th class="text-right">Equivalente (kg)</th><th>Detalle</th>

                    </tr>

                </thead>

                <tbody id="contenidoTableBody">

                    @forelse($contenidos as $item)

                        <tr class="contenido-row" data-search="{{ $item->search }}" data-categoria="{{ $item->categoria }}" data-tipo="{{ strtolower($item->tipo_label) }}">

                            <td><strong class="text-success">{{ $item->nombre }}</strong></td>

                            <td><span class="badge badge-{{ match($item->categoria) { 'cosecha' => 'info', 'producto_planta' => 'warning', default => 'secondary' } }}">{{ match($item->categoria) { 'cosecha' => 'Cosecha', 'producto_planta' => 'Producto terminado', default => 'Insumo' } }}</span></td>

                            <td>{{ $item->tipo_label }}</td>

                            <td class="text-right">{{ number_format($item->cantidad, 2) }} <small class="text-muted">{{ $item->unidad }}</small></td>

                            <td class="text-right">{{ number_format($item->kg, 2) }} kg</td>

                            <td class="text-muted small">{{ $item->detalle }}</td>

                        </tr>

                    @empty

                        <tr id="contenidoEmptyRow">

                            <td colspan="6" class="text-center text-muted py-4">

                                <i class="fas fa-inbox fa-2x mb-2 d-block text-light"></i>

                                Este almacén no tiene stock registrado todavía.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="card-footer bg-white py-2 d-none" id="contenidoSinResultados">

            <span class="text-muted small"><i class="fas fa-filter mr-1"></i> Ningún ítem coincide con la búsqueda.</span>

        </div>

    </div>

</div>

@endsection



@push('scripts')

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>

(function () {

    function initMapaDetalleAlmacen() {

        const mapEl = document.getElementById('mapaDetalleAlmacen');

        if (!mapEl || typeof window.L === 'undefined') {

            return;

        }

        if (mapEl._leafletMap) {

            mapEl._leafletMap.invalidateSize();

            return;

        }

        const lat = parseFloat(mapEl.dataset.lat) || -17.7833;

        const lng = parseFloat(mapEl.dataset.lng) || -63.1821;

        const nombre = mapEl.dataset.nombre || 'Almacén';

        const map = L.map(mapEl, { scrollWheelZoom: false }).setView([lat, lng], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {

            maxZoom: 19,

            attribution: '&copy; OpenStreetMap'

        }).addTo(map);

        L.marker([lat, lng]).addTo(map).bindPopup(nombre).openPopup();

        mapEl._leafletMap = map;

        setTimeout(function () { map.invalidateSize(); }, 100);

        setTimeout(function () { map.invalidateSize(); }, 500);

    }



    document.addEventListener('DOMContentLoaded', initMapaDetalleAlmacen);

    window.addEventListener('load', initMapaDetalleAlmacen);



    const q = document.getElementById('contenidoSearch');

    const fCat = document.getElementById('contenidoFiltroCategoria');

    const fTipo = document.getElementById('contenidoFiltroTipo');

    const rows = Array.from(document.querySelectorAll('.contenido-row'));

    const sinResultados = document.getElementById('contenidoSinResultados');



    function aplicarFiltroContenido() {

        const texto = (q?.value || '').toLowerCase();

        const cat = (fCat?.value || '').toLowerCase();

        const tipo = (fTipo?.value || '').toLowerCase();

        let visibles = 0;

        rows.forEach(function (row) {

            const show = (!texto || (row.dataset.search || '').includes(texto))

                && (!cat || (row.dataset.categoria || '') === cat)

                && (!tipo || (row.dataset.tipo || '') === tipo);

            row.style.display = show ? '' : 'none';

            if (show) visibles++;

        });

        if (sinResultados) sinResultados.classList.toggle('d-none', visibles > 0 || rows.length === 0);

    }

    if (q) q.addEventListener('input', aplicarFiltroContenido);

    if (fCat) fCat.addEventListener('change', aplicarFiltroContenido);

    if (fTipo) fTipo.addEventListener('change', aplicarFiltroContenido);

})();

</script>

@endpush

