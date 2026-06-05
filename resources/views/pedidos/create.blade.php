@extends('layouts.app')

@section('title', 'Nuevo Pedido')
@section('page_title', 'Crear Pedido')

@push('styles')
@include('logistica.partials.mapa-ruta-styles')
<style>
#mapaPedidoEntrega { height: 380px; min-height: 380px; border-radius: 10px; border: 1px solid #dee2e6; }
.pedido-mapa-hint { font-size: .875rem; color: #64748b; }
.custom-marker { background: transparent; border: none; }
.pedido-picker-field {
    display: flex; align-items: stretch; gap: 0;
    border: 2px solid #dee2e6; border-radius: 10px; overflow: hidden; background: #fff;
}
.pedido-picker-field:focus-within { border-color: #2c5530; box-shadow: 0 0 0 .15rem rgba(44,85,48,.12); }
.pedido-picker-field .picker-display {
    flex: 1; border: 0; background: transparent; padding: .55rem .85rem;
    font-size: .9rem; min-height: 42px;
}
.pedido-picker-field .picker-actions { display: flex; border-left: 1px solid #e5e7eb; }
.pedido-picker-field .picker-actions .btn { border-radius: 0; border: 0; padding: 0 .85rem; font-weight: 600; font-size: .85rem; }
</style>
@endpush

@section('content')
    <div class="card card-outline card-primary border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-header bg-white py-3">
            <h3 class="card-title mb-0 font-weight-bold"><i class="fas fa-shopping-cart text-success mr-2"></i> Registro de pedido</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info py-2 mb-3">
                <small>
                    <i class="fas fa-info-circle mr-1"></i>
                    Seleccione el <strong>almacén agrícola</strong> de recogida, el <strong>almacén de planta</strong> de entrega y el producto desde los buscadores.
                </small>
            </div>

            <form action="{{ route('pedidos.store') }}" method="POST" id="form-pedido">
                @csrf
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="small font-weight-bold">Número de solicitud</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $numeroSolicitud }}" readonly>
                        <small class="text-muted">Se confirma al guardar.</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="small font-weight-bold">Fecha de entrega deseada</label>
                        <input type="date" name="fechaEntregaDeseada" class="form-control form-control-sm" value="{{ old('fechaEntregaDeseada') }}">
                    </div>
                </div>

                <div class="card border mb-3" style="border-radius:12px;">
                    <div class="card-header py-2 bg-light">
                        <h5 class="card-title mb-0 small font-weight-bold text-uppercase text-muted">
                            <i class="fas fa-map-marked-alt mr-1"></i> Ruta de entrega
                        </h5>
                    </div>
                    <div class="card-body pb-3">
                        <div class="form-row mb-3">
                            <div class="form-group col-md-6 mb-md-0">
                                <label class="small font-weight-bold text-success mb-1">
                                    <i class="fas fa-map-marker-alt"></i> Origen (recogida) — Almacén agrícola <span class="text-danger">*</span>
                                </label>
                                <div class="pedido-picker-field">
                                    <input type="text" id="txtNombreOrigen" class="picker-display text-muted" readonly
                                           placeholder="Buscar almacén agrícola…" value="{{ old('origen_direccion') }}">
                                    <div class="picker-actions">
                                        <button type="button" class="btn btn-outline-success btn-sm" id="btnBuscarOrigen">
                                            <i class="fas fa-search mr-1"></i> Buscar
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarOrigen" title="Quitar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <small id="txtOrigenCoords" class="form-text text-muted"></small>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="small font-weight-bold text-danger mb-1">
                                    <i class="fas fa-map-marker-alt"></i> Destino (entrega) — Almacén de planta <span class="text-danger">*</span>
                                </label>
                                <div class="pedido-picker-field">
                                    <input type="text" id="txtNombreDestino" class="picker-display text-muted" readonly
                                           placeholder="Buscar almacén de planta…" value="{{ old('direccion_texto') }}">
                                    <div class="picker-actions">
                                        <button type="button" class="btn btn-outline-danger btn-sm" id="btnBuscarDestino">
                                            <i class="fas fa-search mr-1"></i> Buscar
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarDestino" title="Quitar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <small id="txtDestinoCoords" class="form-text text-muted"></small>
                            </div>
                        </div>

                        <div id="mapaPedidoEntrega" class="mb-2"></div>
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <small id="rutaResumen" class="text-muted pedido-mapa-hint"></small>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnResetMapaPedido">
                                <i class="fas fa-undo mr-1"></i> Limpiar ruta
                            </button>
                        </div>

                        <input type="hidden" name="origen_latitud" id="origen_latitud" value="{{ old('origen_latitud') }}">
                        <input type="hidden" name="origen_longitud" id="origen_longitud" value="{{ old('origen_longitud') }}">
                        <input type="hidden" name="origen_direccion" id="origen_direccion" value="{{ old('origen_direccion') }}">
                        <input type="hidden" name="latitud" id="latitud" value="{{ old('latitud') }}">
                        <input type="hidden" name="longitud" id="longitud" value="{{ old('longitud') }}">
                        <input type="hidden" name="direccion_texto" id="direccion_texto" value="{{ old('direccion_texto') }}">
                        @error('latitud')<small class="text-danger d-block">{{ $message }}</small>@enderror
                        @error('origen_latitud')<small class="text-danger d-block">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="form-group">
                    <label class="small font-weight-bold">Producto solicitado <span class="text-danger">*</span></label>
                    <div class="form-row align-items-end">
                        <div class="col-md-5">
                            @include('partials.selector-catalogo', [
                                'id' => 'pedido_producto',
                                'name' => 'detalles[0][producto_ref]',
                                'label' => '',
                                'inputGroup' => true,
                                'required' => true,
                                'value' => old('detalles.0.producto_ref'),
                                'labelSelected' => old('detalles.0.producto_label'),
                                'endpoint' => route('catalogo-selector.productos-pedido'),
                                'title' => 'Buscar producto agrícola',
                                'searchPlaceholder' => 'Cultivo, lote, insumo…',
                                'filter' => [
                                    'param' => 'almacenid',
                                    'options' => array_merge(
                                        [['value' => '', 'label' => 'Todos los almacenes agrícolas']],
                                        $filtroAlmacenesAgricola
                                    ),
                                ],
                            ])
                            @error('detalles.0.producto_ref')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted mb-1">Cantidad (kg)</label>
                            <input type="number" step="0.01" min="0.01" name="detalles[0][cantidad]" class="form-control form-control-sm"
                                   placeholder="Ej: 350" value="{{ old('detalles.0.cantidad') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted mb-1">Observaciones del ítem</label>
                            <input type="text" name="detalles[0][observaciones]" class="form-control form-control-sm"
                                   placeholder="Opcional" value="{{ old('detalles.0.observaciones') }}">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="small font-weight-bold">Observaciones generales</label>
                    <textarea name="observaciones" class="form-control form-control-sm" rows="2">{{ old('observaciones') }}</textarea>
                </div>

                @can('pedidos.create')
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-save mr-1"></i> Guardar pedido
                    </button>
                @endcan
                <a href="{{ route('pedidos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
@include('logistica.partials.mapa-ruta-libs')
<script>
(function () {
    const hub = { lat: {{ $hubLat }}, lng: {{ $hubLng }} };
    const state = {
        map: null,
        capas: null,
        routeLayer: null,
        markers: { origin: null, destination: null },
    };

    function iconMarker(color) {
        return L.divIcon({
            html: '<i class="fas fa-map-marker-alt" style="color:' + color + ';font-size:32px;"></i>',
            className: 'custom-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
        });
    }

    function setOrigen(lat, lng, label) {
        document.getElementById('origen_latitud').value = lat.toFixed(7);
        document.getElementById('origen_longitud').value = lng.toFixed(7);
        document.getElementById('origen_direccion').value = label;
        const display = document.getElementById('txtNombreOrigen');
        display.value = label;
        display.classList.remove('text-muted');
        document.getElementById('txtOrigenCoords').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
        if (state.markers.origin) state.capas.removeLayer(state.markers.origin);
        state.markers.origin = L.marker([lat, lng], { icon: iconMarker('#28a745') }).addTo(state.capas);
    }

    function setDestino(lat, lng, label) {
        document.getElementById('latitud').value = lat.toFixed(7);
        document.getElementById('longitud').value = lng.toFixed(7);
        document.getElementById('direccion_texto').value = label;
        const display = document.getElementById('txtNombreDestino');
        display.value = label;
        display.classList.remove('text-muted');
        document.getElementById('txtDestinoCoords').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
        if (state.markers.destination) state.capas.removeLayer(state.markers.destination);
        state.markers.destination = L.marker([lat, lng], { icon: iconMarker('#dc3545') }).addTo(state.capas);
    }

    function limpiarOrigen() {
        ['origen_latitud', 'origen_longitud', 'origen_direccion'].forEach(function (id) {
            document.getElementById(id).value = '';
        });
        document.getElementById('txtNombreOrigen').value = '';
        document.getElementById('txtNombreOrigen').classList.add('text-muted');
        document.getElementById('txtOrigenCoords').textContent = '';
        if (state.markers.origin) { state.capas.removeLayer(state.markers.origin); state.markers.origin = null; }
    }

    function limpiarDestino() {
        ['latitud', 'longitud', 'direccion_texto'].forEach(function (id) {
            document.getElementById(id).value = '';
        });
        document.getElementById('txtNombreDestino').value = '';
        document.getElementById('txtNombreDestino').classList.add('text-muted');
        document.getElementById('txtDestinoCoords').textContent = '';
        if (state.markers.destination) { state.capas.removeLayer(state.markers.destination); state.markers.destination = null; }
    }

    async function drawRoute() {
        if (!state.markers.origin || !state.markers.destination || !window.RutaPorCalles) return;

        const start = state.markers.origin.getLatLng();
        const end = state.markers.destination.getLatLng();
        const waypoints = [
            { lat: start.lat, lng: start.lng, orden: 1, label: 'Origen' },
            { lat: end.lat, lng: end.lng, orden: 2, label: 'Destino' },
        ];

        if (state.routeLayer) {
            state.capas.removeLayer(state.routeLayer);
            state.routeLayer = null;
        }

        const routeResult = await RutaPorCalles.fetchRoute(waypoints);
        if (routeResult?.geojson) {
            state.routeLayer = L.geoJSON(routeResult.geojson, {
                style: {
                    color: routeResult.straight ? '#e67e22' : '#2563eb',
                    weight: 5,
                    opacity: 0.85,
                    dashArray: routeResult.straight ? '8,8' : null,
                },
            }).addTo(state.capas);
            try { state.map.fitBounds(state.routeLayer.getBounds(), { padding: [40, 40] }); } catch (e) {}
            const km = routeResult.distance_m ? (routeResult.distance_m / 1000).toFixed(1) : null;
            const min = routeResult.duration_s ? Math.round(routeResult.duration_s / 60) : null;
            let resumen = routeResult.straight ? 'Ruta estimada (línea recta)' : 'Ruta por calles trazada';
            if (km && min) resumen += ' · ~' + km + ' km · ~' + min + ' min';
            document.getElementById('rutaResumen').textContent = resumen;
        }
    }

    function aplicarAlmacen(almacen, tipo) {
        const lat = parseFloat(almacen.extra?.lat);
        const lng = parseFloat(almacen.extra?.lng);
        const label = almacen.extra?.direccion || almacen.label;

        if (isNaN(lat) || isNaN(lng)) {
            return;
        }

        if (tipo === 'origen') {
            setOrigen(lat, lng, label);
        } else {
            setDestino(lat, lng, label);
        }

        if (state.markers.origin && state.markers.destination) {
            drawRoute();
        }
    }

    function resetMapa() {
        if (state.capas) state.capas.clearLayers();
        state.markers = { origin: null, destination: null };
        state.routeLayer = null;
        limpiarOrigen();
        limpiarDestino();
        document.getElementById('rutaResumen').textContent = '';
    }

    function initMapa() {
        const el = document.getElementById('mapaPedidoEntrega');
        if (!el || !window.L) return;

        state.map = L.map(el, { scrollWheelZoom: true }).setView([hub.lat, hub.lng], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap',
        }).addTo(state.map);
        state.capas = L.layerGroup().addTo(state.map);

        document.getElementById('btnResetMapaPedido').addEventListener('click', resetMapa);
        document.getElementById('btnLimpiarOrigen').addEventListener('click', function () {
            limpiarOrigen();
            if (state.routeLayer) { state.capas.removeLayer(state.routeLayer); state.routeLayer = null; }
            document.getElementById('rutaResumen').textContent = '';
        });
        document.getElementById('btnLimpiarDestino').addEventListener('click', function () {
            limpiarDestino();
            if (state.routeLayer) { state.capas.removeLayer(state.routeLayer); state.routeLayer = null; }
            document.getElementById('rutaResumen').textContent = '';
        });

        document.getElementById('form-pedido').addEventListener('submit', function (e) {
            if (!document.getElementById('latitud').value || !document.getElementById('origen_latitud').value) {
                e.preventDefault();
                alert('Seleccione el almacén agrícola de origen y el almacén de planta de destino.');
            }
        });

        setTimeout(function () {
            state.map.invalidateSize();
            const oLat = parseFloat(document.getElementById('origen_latitud').value);
            const oLng = parseFloat(document.getElementById('origen_longitud').value);
            const dLat = parseFloat(document.getElementById('latitud').value);
            const dLng = parseFloat(document.getElementById('longitud').value);
            if (!isNaN(oLat) && !isNaN(oLng)) {
                state.markers.origin = L.marker([oLat, oLng], { icon: iconMarker('#28a745') }).addTo(state.capas);
                document.getElementById('txtOrigenCoords').textContent = oLat.toFixed(6) + ', ' + oLng.toFixed(6);
                document.getElementById('txtNombreOrigen').classList.remove('text-muted');
            }
            if (!isNaN(dLat) && !isNaN(dLng)) {
                state.markers.destination = L.marker([dLat, dLng], { icon: iconMarker('#dc3545') }).addTo(state.capas);
                document.getElementById('txtDestinoCoords').textContent = dLat.toFixed(6) + ', ' + dLng.toFixed(6);
                document.getElementById('txtNombreDestino').classList.remove('text-muted');
                drawRoute();
            }
        }, 200);
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!window.CatalogoSelector) return;

        CatalogoSelector.register('pedido_almacen_origen', {
            endpoint: @json(route('catalogo-selector.almacenes')),
            title: 'Almacén agrícola — origen',
            searchPlaceholder: 'Nombre o ubicación…',
            params: { ambito: 'agricola' },
            onSelect: function (item) { aplicarAlmacen(item, 'origen'); },
        });

        CatalogoSelector.register('pedido_almacen_destino', {
            endpoint: @json(route('catalogo-selector.almacenes')),
            title: 'Almacén de planta — destino',
            searchPlaceholder: 'Nombre o ubicación…',
            params: { ambito: 'planta' },
            onSelect: function (item) { aplicarAlmacen(item, 'destino'); },
        });

        document.getElementById('btnBuscarOrigen').addEventListener('click', function () {
            CatalogoSelector.open('pedido_almacen_origen');
        });
        document.getElementById('btnBuscarDestino').addEventListener('click', function () {
            CatalogoSelector.open('pedido_almacen_destino');
        });

        if (window.L && window.RutaPorCalles) {
            initMapa();
        }
    });
})();
</script>
@endpush
