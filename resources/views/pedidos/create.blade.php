@extends('layouts.app')

@section('title', 'Nuevo envío')
@section('page_title', 'Nuevo envío')

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
.origen-extra-row {
    border: 1px dashed #cbd5e1; border-radius: 10px; padding: .75rem; margin-bottom: .5rem; background: #f8fafc;
}
.ruta-bloqueada { opacity: .55; pointer-events: none; }
.almacen-mapa-marker { background: transparent !important; border: none !important; }
.almacen-mapa-pin {
    width: 32px; height: 32px; border-radius: 50%; color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.35); cursor: pointer;
    transition: transform .12s ease;
}
.almacen-mapa-pin:hover { transform: scale(1.12); }
.almacen-mapa-pin.is-selected { box-shadow: 0 0 0 3px #fbbf24, 0 2px 8px rgba(0,0,0,.35); }
.leaflet-tooltip.almacen-mapa-tooltip {
    background: #1e293b; color: #fff; border: 0; border-radius: 8px;
    font-size: .8rem; font-weight: 600; padding: .35rem .65rem;
    box-shadow: 0 4px 12px rgba(0,0,0,.2);
}
.leaflet-tooltip.almacen-mapa-tooltip::before { border-top-color: #1e293b; }
#mapa-asignacion-flash {
    position: absolute; z-index: 1000; top: 10px; left: 50%; transform: translateX(-50%);
    background: #1e293b; color: #fff; padding: .4rem .85rem; border-radius: 8px;
    font-size: .8rem; display: none; pointer-events: none;
}
#mapaPedidoEntrega-wrap { position: relative; }
</style>
@endpush

@section('content')
    <div class="card card-outline card-primary border-0 shadow-sm" style="border-radius:14px;">
        <div class="card-header bg-white py-3">
            <h3 class="card-title mb-0 font-weight-bold"><i class="fas fa-truck text-success mr-2"></i> Registro de envío</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-info py-2 mb-3">
                <small>
                    <i class="fas fa-info-circle mr-1"></i>
                    Defina el trayecto (origen → planta), asigne <strong>chofer y vehículo</strong> y seleccione el producto.
                    Puede agregar más almacenes de recogida con el botón <strong>+</strong> si el camión pasa por varios puntos.
                </small>
            </div>

            <form action="{{ route('pedidos.store') }}" method="POST" id="form-pedido">
                @csrf
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="small font-weight-bold">Código de envío</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $numeroSolicitud }}" readonly>
                        <small class="text-muted">Se confirma al guardar.</small>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="small font-weight-bold">Fecha de entrega deseada</label>
                        <input type="date" name="fechaEntregaDeseada" class="form-control form-control-sm" value="{{ old('fechaEntregaDeseada') }}">
                        <small class="text-muted">Si no indica fecha, se usará la de hoy.</small>
                    </div>
                </div>

                <div class="card border mb-3" style="border-radius:12px;">
                    <div class="card-header py-2 bg-light">
                        <h5 class="card-title mb-0 small font-weight-bold text-uppercase text-muted">
                            <i class="fas fa-map-marked-alt mr-1"></i> Ruta de entrega
                        </h5>
                    </div>
                    <div class="card-body pb-3">
                        <div class="alert alert-light border py-2 mb-3 small" id="ruta-paso-ayuda">
                            <strong>Paso a paso:</strong> 1) Elija todos los almacenes de recogida en orden.
                            2) Al final, elija el almacén de planta (destino). Si necesita empezar de nuevo, use <strong>Reiniciar ruta</strong>.
                        </div>
                        <div class="mb-3" id="bloque-recogidas">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="small font-weight-bold text-success mb-0">
                                    <i class="fas fa-map-marker-alt"></i> Puntos de recogida — Almacenes agrícolas <span class="text-danger">*</span>
                                </label>
                                <button type="button" class="btn btn-sm btn-outline-danger" id="btnReiniciarRuta">
                                    <i class="fas fa-redo mr-1"></i> Reiniciar ruta
                                </button>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small text-muted mb-1">Recogida 1</label>
                                <div class="pedido-picker-field">
                                    <input type="text" id="txtNombreOrigen" class="picker-display text-muted" readonly
                                           placeholder="Buscar almacén agrícola…" value="{{ old('origen_direccion') }}">
                                    <div class="picker-actions">
                                        <button type="button" class="btn btn-outline-success btn-sm" id="btnBuscarOrigen">
                                            <i class="fas fa-search mr-1"></i> Buscar
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-sm d-none" id="btnVerProductosOrigen" title="Ver productos en este almacén">
                                            <i class="fas fa-box-open mr-1"></i> Ver
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" id="btnLimpiarOrigen" title="Quitar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                                <small id="txtOrigenCoords" class="form-text text-muted"></small>
                            </div>
                            <div id="recogidas-extra-container"></div>
                            <button type="button" class="btn btn-sm btn-outline-success" id="btnAgregarRecogida">
                                <i class="fas fa-plus mr-1"></i> Agregar otro almacén de recogida
                            </button>
                            <small class="text-muted d-block mt-1">Opcional: útil cuando el camión recoge en 2 o más almacenes antes de ir a planta.</small>
                        </div>

                        <div class="form-group mb-3" id="bloque-destino">
                            <label class="small font-weight-bold text-danger mb-1">
                                <i class="fas fa-map-marker-alt"></i> Destino (entrega) — Almacén de planta <span class="text-danger">*</span>
                            </label>
                            <small class="text-muted d-block mb-2" id="destino-bloqueado-msg">Complete las recogidas antes de elegir destino.</small>
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

                        <div class="mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnVerAlmacenesMapa">
                                <i class="fas fa-map-marked-alt mr-1"></i> Ver en mapa
                            </button>
                        </div>
                        <div id="mapaPedidoEntrega-wrap" class="mb-2">
                            <div id="mapa-asignacion-flash"></div>
                            <div id="mapaPedidoEntrega"></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <small id="rutaResumen" class="text-muted pedido-mapa-hint"></small>
                        </div>

                        <input type="hidden" name="origen_latitud" id="origen_latitud" value="{{ old('origen_latitud') }}">
                        <input type="hidden" name="origen_longitud" id="origen_longitud" value="{{ old('origen_longitud') }}">
                        <input type="hidden" name="origen_direccion" id="origen_direccion" value="{{ old('origen_direccion') }}">
                        <input type="hidden" name="origen_almacenid" id="origen_almacenid" value="{{ old('origen_almacenid') }}">
                        <input type="hidden" name="latitud" id="latitud" value="{{ old('latitud') }}">
                        <input type="hidden" name="longitud" id="longitud" value="{{ old('longitud') }}">
                        <input type="hidden" name="direccion_texto" id="direccion_texto" value="{{ old('direccion_texto') }}">
                        @error('latitud')<small class="text-danger d-block">{{ $message }}</small>@enderror
                        @error('origen_latitud')<small class="text-danger d-block">{{ $message }}</small>@enderror
                    </div>
                </div>

                <div class="card border mb-3 d-none" id="bloque-productos" style="border-radius:12px;">
                    <div class="card-header py-2 bg-light">
                        <h5 class="card-title mb-0 small font-weight-bold text-uppercase text-muted">
                            <i class="fas fa-box-open mr-1"></i> Productos a enviar <span class="text-danger">*</span>
                        </h5>
                    </div>
                    <div class="card-body pb-3">
                        <p class="text-muted small mb-3">
                            Indique qué recoger en <strong>cada almacén</strong> de la ruta. En el buscador de almacenes use <strong>Ver</strong> para elegir del inventario.
                        </p>
                        <div id="productos-recogida-container"></div>
                        @error('detalles')
                            <small class="text-danger d-block">{{ $message }}</small>
                        @enderror
                        @error('detalles.*.producto_ref')
                            <small class="text-danger d-block">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="card border mb-3" style="border-radius:12px;">
                    <div class="card-header py-2 bg-light">
                        <h5 class="card-title mb-0 small font-weight-bold text-uppercase text-muted">
                            <i class="fas fa-user-tag mr-1"></i> Transporte
                        </h5>
                    </div>
                    <div class="card-body pb-3">
                        <p class="text-muted small mb-3">
                            Todo envío debe tener chofer y vehículo. Si necesita cambiarlos después, edite el envío desde el listado.
                            La salida se activa cuando producción agrícola acepte el pedido.
                        </p>
                        @php
                            $transportistaId = old('transportista_usuarioid', '');
                            $transportistaLabel = old('transportista_label', '');
                            $vehiculoId = old('vehiculoid', '');
                            $vehiculoLabel = old('vehiculo_label', '');
                        @endphp
                        <div class="form-row">
                            <div class="form-group col-md-6 mb-md-0">
                                <label class="small font-weight-bold">Chofer <span class="text-danger">*</span></label>
                                <div class="pedido-picker-field">
                                    <input type="text" id="txtTransportistaCreate" class="picker-display {{ $transportistaLabel ? '' : 'text-muted' }}"
                                           readonly placeholder="Buscar transportista…" value="{{ $transportistaLabel }}" required>
                                    <div class="picker-actions">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnBuscarTransportistaCreate">
                                            <i class="fas fa-search mr-1"></i> Buscar
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="transportista_usuarioid" id="transportista_usuarioid_create" value="{{ $transportistaId }}" required>
                                @error('transportista_usuarioid')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                            <div class="form-group col-md-6">
                                <label class="small font-weight-bold">Vehículo <span class="text-danger">*</span></label>
                                <div class="pedido-picker-field">
                                    <input type="text" id="txtVehiculoCreate" class="picker-display {{ $vehiculoLabel ? '' : 'text-muted' }}"
                                           readonly placeholder="Primero elija transportista…" value="{{ $vehiculoLabel }}" required>
                                    <div class="picker-actions">
                                        <button type="button" class="btn btn-outline-success btn-sm" id="btnBuscarVehiculoCreate" @disabled(! $transportistaId)>
                                            <i class="fas fa-search mr-1"></i> Buscar
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="vehiculoid" id="vehiculoid_create" value="{{ $vehiculoId }}" required>
                                @error('vehiculoid')<small class="text-danger">{{ $message }}</small>@enderror
                            </div>
                        </div>
                        <div class="form-row mt-2">
                            <div class="form-group col-md-6 mb-0">
                                <label class="small font-weight-bold">Costo del servicio (Bs) <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text">Bs</span></div>
                                    <input type="number" name="costo_bs" id="costo_bs" class="form-control" min="0" step="1"
                                           value="{{ old('costo_bs', '0') }}" placeholder="0 — se calcula al trazar la ruta" required>
                                </div>
                                @error('costo_bs')<small class="text-danger">{{ $message }}</small>@enderror
                                <small class="text-muted d-block" id="costoEnvioDetalle">Se estima por distancia, paradas y clima (recargo si lluvia). Puede editarlo manualmente.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="small font-weight-bold">Observaciones generales</label>
                    <textarea name="observaciones" class="form-control form-control-sm" rows="2">{{ old('observaciones') }}</textarea>
                </div>

                @can('pedidos.create')
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-save mr-1"></i> Guardar envío
                    </button>
                @endcan
                <a href="{{ route('logistica.asignaciones.listado') }}" class="btn btn-outline-secondary">Cancelar</a>
            </form>
        </div>
    </div>
    @include('partials.modal-confirmar-accion')
@endsection

@push('scripts')
@include('partials.selector-catalogo-assets')
@include('logistica.partials.mapa-ruta-libs')
<script>
(function () {
    const hub = { lat: {{ $hubLat }}, lng: {{ $hubLng }} };
    const MAX_RECOGIDAS_EXTRA = 5;
    const PRODUCTO_ENDPOINT = @json(route('catalogo-selector.productos-pedido'));
    const COSTO_ENVIO_URL = @json(route('pedidos.calcular-costo-envio'));
    const CSRF_TOKEN = @json(csrf_token());
    let pickerRecogidaActivo = null;
    let redrawToken = 0;
    let costoEditadoManual = false;
    let costoEnvioToken = 0;

    function aviso(mensaje, titulo, tono) {
        if (window.ModalConfirmar && typeof ModalConfirmar.aviso === 'function') {
            ModalConfirmar.aviso({ mensaje: mensaje, titulo: titulo || 'Aviso', tono: tono || 'warning' });
        } else {
            window.alert(mensaje);
        }
    }

    function etiquetaAlmacenPedido(almacen) {
        const nombre = (almacen.label || '').trim();
        const dir = (almacen.extra?.direccion || '').trim();
        if (!dir || /^GPS\s/i.test(dir)) {
            return nombre || dir;
        }
        if (nombre && !dir.toLowerCase().includes(nombre.toLowerCase())) {
            return nombre + ' · ' + dir;
        }
        return dir || nombre;
    }

    const ALMACENES_MAPA = @json($almacenesMapa ?? []);

    const state = {
        map: null,
        capasRuta: null,
        capasAlmacenes: null,
        routeLayer: null,
        markers: [],
        almacenesVisibles: false,
        cargandoAlmacenes: false,
    };

    function iconMarker(color, numero) {
        const html = numero
            ? '<div style="background:' + color + ';color:#fff;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:bold;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3)">' + numero + '</div>'
            : '<i class="fas fa-map-marker-alt" style="color:' + color + ';font-size:32px;"></i>';
        return L.divIcon({
            html: html,
            className: 'custom-marker',
            iconSize: numero ? [28, 28] : [32, 32],
            iconAnchor: numero ? [14, 14] : [16, 32],
        });
    }

    function tieneDestino() {
        return !!document.getElementById('latitud').value && !!document.getElementById('longitud').value;
    }

    function recogida1Lista() {
        return !!document.getElementById('origen_latitud').value;
    }

    function hayRecogidasExtraVacias() {
        let vacia = false;
        document.querySelectorAll('.origen-extra-row').forEach(function (row) {
            if (!row.querySelector('[data-field="latitud"]').value) vacia = true;
        });
        return vacia;
    }

    function puedeElegirDestino() {
        return recogida1Lista() && !hayRecogidasExtraVacias();
    }

    function bloquearRecogidaSiDestino() {
        if (tieneDestino()) {
            aviso('Ya definió el destino. Use «Reiniciar ruta» para volver a editar las recogidas.');
            return true;
        }
        return false;
    }

    function syncEstadoRuta() {
        const destinoFijado = tieneDestino();
        const puedeDestino = puedeElegirDestino();

        document.getElementById('bloque-recogidas').classList.toggle('ruta-bloqueada', destinoFijado);
        document.getElementById('btnAgregarRecogida').disabled = destinoFijado || !recogida1Lista();

        const bloqueDestino = document.getElementById('bloque-destino');
        const msgDestino = document.getElementById('destino-bloqueado-msg');
        bloqueDestino.classList.toggle('ruta-bloqueada', !puedeDestino);
        if (destinoFijado) {
            msgDestino.textContent = 'Destino definido. Reinicie la ruta si necesita cambiar el orden de recogidas.';
        } else if (!recogida1Lista()) {
            msgDestino.textContent = 'Primero elija la recogida 1.';
        } else if (hayRecogidasExtraVacias()) {
            msgDestino.textContent = 'Complete o quite las recogidas adicionales antes de elegir destino.';
        } else {
            msgDestino.textContent = 'Ahora puede elegir el almacén de planta (destino final).';
        }
    }

    function renumerarRecogidas() {
        document.querySelectorAll('.origen-extra-row').forEach(function (row, i) {
            const num = i + 2;
            row.querySelector('.lbl-recogida-num').textContent = 'Recogida ' + num;
            row.querySelector('[data-field="latitud"]').setAttribute('name', 'recogidas[' + i + '][latitud]');
            row.querySelector('[data-field="longitud"]').setAttribute('name', 'recogidas[' + i + '][longitud]');
            row.querySelector('[data-field="direccion"]').setAttribute('name', 'recogidas[' + i + '][direccion]');
            const almacenField = row.querySelector('[data-field="almacenid"]');
            if (almacenField) almacenField.setAttribute('name', 'recogidas[' + i + '][almacenid]');
        });
    }

    function waypointsActuales() {
        const puntos = [];
        const oLat = parseFloat(document.getElementById('origen_latitud').value);
        const oLng = parseFloat(document.getElementById('origen_longitud').value);
        if (!isNaN(oLat) && !isNaN(oLng)) {
            puntos.push({ lat: oLat, lng: oLng, orden: 1, label: 'Recogida 1', tipo: 'recogida' });
        }
        document.querySelectorAll('.origen-extra-row').forEach(function (row, i) {
            const lat = parseFloat(row.querySelector('[data-field="latitud"]').value);
            const lng = parseFloat(row.querySelector('[data-field="longitud"]').value);
            const dir = row.querySelector('[data-field="direccion"]').value;
            if (!isNaN(lat) && !isNaN(lng)) {
                puntos.push({ lat: lat, lng: lng, orden: puntos.length + 1, label: dir || ('Recogida ' + (i + 2)), tipo: 'recogida' });
            }
        });
        const dLat = parseFloat(document.getElementById('latitud').value);
        const dLng = parseFloat(document.getElementById('longitud').value);
        if (!isNaN(dLat) && !isNaN(dLng)) {
            puntos.push({ lat: dLat, lng: dLng, orden: puntos.length + 1, label: document.getElementById('direccion_texto').value || 'Planta', tipo: 'destino' });
        }
        return puntos;
    }

    function waypointsParaTrazado(puntos) {
        const out = [];
        puntos.forEach(function (p) {
            const last = out[out.length - 1];
            if (last && Math.abs(last.lat - p.lat) < 0.000001 && Math.abs(last.lng - p.lng) < 0.000001) {
                return;
            }
            out.push(p);
        });
        return out.length >= 2 ? out : puntos;
    }

    function coordsSeleccionadas() {
        const coords = [];
        const oLat = parseFloat(document.getElementById('origen_latitud').value);
        const oLng = parseFloat(document.getElementById('origen_longitud').value);
        if (!isNaN(oLat) && !isNaN(oLng)) coords.push({ lat: oLat, lng: oLng });
        document.querySelectorAll('.origen-extra-row').forEach(function (row) {
            const lat = parseFloat(row.querySelector('[data-field="latitud"]').value);
            const lng = parseFloat(row.querySelector('[data-field="longitud"]').value);
            if (!isNaN(lat) && !isNaN(lng)) coords.push({ lat: lat, lng: lng });
        });
        const dLat = parseFloat(document.getElementById('latitud').value);
        const dLng = parseFloat(document.getElementById('longitud').value);
        if (!isNaN(dLat) && !isNaN(dLng)) coords.push({ lat: dLat, lng: dLng });
        return coords;
    }

    function coordYaSeleccionada(lat, lng) {
        return coordsSeleccionadas().some(function (c) {
            return Math.abs(c.lat - lat) < 0.00001 && Math.abs(c.lng - lng) < 0.00001;
        });
    }

    function flashMapaMsg(texto) {
        const el = document.getElementById('mapa-asignacion-flash');
        if (!el) return;
        el.textContent = texto;
        el.style.display = 'block';
        clearTimeout(flashMapaMsg._t);
        flashMapaMsg._t = setTimeout(function () { el.style.display = 'none'; }, 2200);
    }

    function iconAlmacen(ambito, seleccionado) {
        const esPlanta = ambito === 'planta';
        const color = esPlanta ? '#dc3545' : '#28a745';
        const icon = esPlanta ? 'fa-industry' : 'fa-warehouse';
        const sel = seleccionado ? ' is-selected' : '';
        return L.divIcon({
            html: '<div class="almacen-mapa-pin' + sel + '" style="background:' + color + '"><i class="fas ' + icon + '"></i></div>',
            className: 'almacen-mapa-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
        });
    }

    function pintarAlmacenesEnMapa(items) {
        state.capasAlmacenes.clearLayers();
        const bounds = [];
        items.forEach(function (item) {
            const lat = parseFloat(item.extra.lat);
            const lng = parseFloat(item.extra.lng);
            const ambito = item.extra.ambito || 'agricola';
            const seleccionado = coordYaSeleccionada(lat, lng);
            const m = L.marker([lat, lng], {
                icon: iconAlmacen(ambito, seleccionado),
                zIndexOffset: seleccionado ? 200 : 0,
            })
                .bindTooltip(item.label, {
                    className: 'almacen-mapa-tooltip',
                    direction: 'top',
                    offset: [0, -14],
                })
                .addTo(state.capasAlmacenes);
            m.on('mouseover', function () { this.openTooltip(); });
            m.on('click', function () { asignarAlmacenDesdeMapa(item); });
            m._almacenItem = item;
            bounds.push([lat, lng]);
        });
        return bounds;
    }

    function resaltarAlmacenesEnMapa() {
        if (!state.almacenesVisibles || !state.capasAlmacenes) return;
        state.capasAlmacenes.eachLayer(function (layer) {
            if (!layer._almacenItem) return;
            const item = layer._almacenItem;
            const lat = parseFloat(item.extra.lat);
            const lng = parseFloat(item.extra.lng);
            const ambito = item.extra.ambito || 'agricola';
            const sel = coordYaSeleccionada(lat, lng);
            layer.setIcon(iconAlmacen(ambito, sel));
            layer.setZIndexOffset(sel ? 200 : 0);
        });
    }

    function asignarAlmacenDesdeMapa(item) {
        const ambito = item.extra?.ambito || 'agricola';
        if (ambito === 'planta') {
            if (!puedeElegirDestino()) {
                aviso('Complete todas las recogidas antes de asignar el almacén de planta.');
                return;
            }
            aplicarDestino(item);
            flashMapaMsg('Destino asignado: ' + item.label);
            resaltarAlmacenesEnMapa();
            return;
        }
        if (bloquearRecogidaSiDestino()) return;

        if (!recogida1Lista()) {
            aplicarRecogidaPrincipal(item);
            flashMapaMsg('Recogida 1: ' + item.label);
            resaltarAlmacenesEnMapa();
            return;
        }

        let row = Array.prototype.find.call(
            document.querySelectorAll('.origen-extra-row'),
            function (r) { return !r.querySelector('[data-field="latitud"]').value; }
        );
        if (!row && document.querySelectorAll('.origen-extra-row').length < MAX_RECOGIDAS_EXTRA) {
            row = crearFilaRecogidaExtra();
            document.getElementById('recogidas-extra-container').appendChild(row);
            renumerarRecogidas();
            syncEstadoRuta();
        }
        if (row) {
            aplicarRecogidaExtra(row, item);
            const num = row.querySelector('.lbl-recogida-num')?.textContent || 'Recogida';
            flashMapaMsg(num + ': ' + item.label);
            resaltarAlmacenesEnMapa();
        } else {
            aviso('Máximo de recogidas alcanzado. Quite una o reinicie la ruta.');
        }
    }

    function ajustarVistaMapa(bounds) {
        if (!bounds.length || !state.map) return;
        const rutaPts = waypointsActuales();
        const puntos = bounds.slice();
        rutaPts.forEach(function (p) { puntos.push([p.lat, p.lng]); });
        try {
            state.map.fitBounds(L.latLngBounds(puntos).pad(0.1));
        } catch (e) {
            state.map.setView([puntos[0][0], puntos[0][1]], 12);
        }
    }

    function mostrarAlmacenesEnMapa() {
        const items = ALMACENES_MAPA || [];
        if (!items.length) {
            aviso('No hay almacenes con ubicación para mostrar en el mapa.');
            return false;
        }

        const bounds = pintarAlmacenesEnMapa(items);
        if (!state.map.hasLayer(state.capasAlmacenes)) {
            state.map.addLayer(state.capasAlmacenes);
        }
        state.almacenesVisibles = true;
        ajustarVistaMapa(bounds);
        return true;
    }

    function toggleAlmacenesEnMapa() {
        const btn = document.getElementById('btnVerAlmacenesMapa');
        if (state.cargandoAlmacenes) return;

        if (state.almacenesVisibles) {
            if (state.map.hasLayer(state.capasAlmacenes)) {
                state.map.removeLayer(state.capasAlmacenes);
            }
            state.almacenesVisibles = false;
            btn.innerHTML = '<i class="fas fa-map-marked-alt mr-1"></i> Ver en mapa';
            btn.classList.remove('active');
            return;
        }

        state.cargandoAlmacenes = true;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Cargando almacenes…';

        window.requestAnimationFrame(function () {
            try {
                if (mostrarAlmacenesEnMapa()) {
                    btn.innerHTML = '<i class="fas fa-eye-slash mr-1"></i> Ocultar almacenes';
                    btn.classList.add('active');
                }
            } catch (e) {
                console.error(e);
                if (state.map.hasLayer(state.capasAlmacenes)) {
                    state.map.removeLayer(state.capasAlmacenes);
                }
                state.almacenesVisibles = false;
                aviso('No se pudieron mostrar los almacenes en el mapa. Intente de nuevo.');
                btn.innerHTML = '<i class="fas fa-map-marked-alt mr-1"></i> Ver en mapa';
                btn.classList.remove('active');
            } finally {
                state.cargandoAlmacenes = false;
                btn.disabled = false;
            }
        });
    }

    function limpiarCapaMapa() {
        if (!state.capasRuta) return;
        if (state.routeLayer) {
            state.capasRuta.removeLayer(state.routeLayer);
            state.routeLayer = null;
        }
        state.markers.forEach(function (m) { state.capasRuta.removeLayer(m); });
        state.markers = [];
    }

    function rebuildMarcadores(puntos) {
        limpiarCapaMapa();
        if (!state.capasRuta) return;
        puntos.forEach(function (p, i) {
            const esDestino = p.tipo === 'destino';
            const color = esDestino ? '#dc3545' : (i === 0 ? '#28a745' : '#16a34a');
            const marker = L.marker([p.lat, p.lng], { icon: iconMarker(color, i + 1), zIndexOffset: 1000 }).addTo(state.capasRuta);
            marker.bindTooltip(p.label, { className: 'almacen-mapa-tooltip', direction: 'top', offset: [0, -14] });
            state.markers.push(marker);
        });
        resaltarAlmacenesEnMapa();
    }

    async function redrawRoute() {
        if (!window.RutaPorCalles || !state.capasRuta) return;
        const token = ++redrawToken;
        const puntos = waypointsActuales();
        rebuildMarcadores(puntos);

        if (puntos.length < 2) {
            document.getElementById('rutaResumen').textContent = puntos.length === 1
                ? 'Agregue más recogidas o el destino para trazar la ruta.'
                : '';
            syncEstadoRuta();
            actualizarCostoEnvio(null);
            return;
        }

        const paraRuta = waypointsParaTrazado(puntos);
        let routeResult = null;
        routeResult = await RutaPorCalles.fetchRoute(paraRuta);
        if (token !== redrawToken) return;

        if (routeResult?.geojson) {
            state.routeLayer = L.geoJSON(routeResult.geojson, {
                style: {
                    color: routeResult.straight ? '#e67e22' : '#2563eb',
                    weight: 5,
                    opacity: 0.85,
                    dashArray: routeResult.straight ? '8,8' : null,
                },
            }).addTo(state.capasRuta);

            if (!state.almacenesVisibles) {
                const bounds = L.latLngBounds(puntos.map(function (p) { return [p.lat, p.lng]; }));
                try { state.map.fitBounds(bounds.pad(0.15)); } catch (e) {}
            }

            const km = routeResult.distance_m ? Math.round(routeResult.distance_m / 1000) : null;
            const min = routeResult.duration_s ? Math.round(routeResult.duration_s / 60) : null;
            let resumen = (puntos.length > 2 ? puntos.length + ' paradas · ' : '') +
                (routeResult.straight ? 'Ruta estimada (línea recta)' : 'Ruta por calles');
            if (km && min) resumen += ' · ~' + km + ' km · ~' + min + ' min';
            document.getElementById('rutaResumen').textContent = resumen;
        }
        syncEstadoRuta();
        actualizarCostoEnvio(routeResult?.distance_m ?? null);
    }

    function resetCostoEnvio() {
        costoEditadoManual = false;
        const input = document.getElementById('costo_bs');
        if (input) {
            input.value = '0';
        }
        const detalle = document.getElementById('costoEnvioDetalle');
        if (detalle) {
            detalle.textContent = 'Se estima por distancia, paradas y clima (recargo si lluvia). Puede editarlo manualmente.';
        }
    }

    async function actualizarCostoEnvio(distanciaMetros) {
        if (costoEditadoManual) {
            return;
        }

        const puntos = waypointsActuales();
        const token = ++costoEnvioToken;

        if (puntos.length < 2) {
            resetCostoEnvio();
            return;
        }

        const detalle = document.getElementById('costoEnvioDetalle');
        if (detalle) {
            detalle.textContent = 'Calculando costo de envío…';
        }

        try {
            const resp = await fetch(COSTO_ENVIO_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify({
                    paradas: puntos.map(function (p) { return { lat: p.lat, lng: p.lng }; }),
                    distancia_m: distanciaMetros,
                }),
            });
            if (token !== costoEnvioToken) return;
            if (!resp.ok) {
                throw new Error('No se pudo calcular el costo');
            }
            const data = await resp.json();
            const input = document.getElementById('costo_bs');
            if (input && !costoEditadoManual) {
                input.value = String(Math.round(Number(data.costo_bs || 0)));
            }
            if (detalle) {
                let texto = data.detalle || 'Costo estimado según la ruta.';
                if (data.lluvia && data.descripcion_clima) {
                    texto += ' Clima: ' + data.descripcion_clima + '.';
                }
                detalle.textContent = texto;
            }
        } catch (e) {
            if (detalle) {
                detalle.textContent = 'No se pudo calcular automáticamente. Ingrese el costo manualmente.';
            }
        }
    }

    function selectorIdForRecogida(key) {
        return 'pedido_producto_' + key;
    }

    function recogidaUidExtra(row) {
        if (!row.getAttribute('data-recogida-uid')) {
            row.setAttribute('data-recogida-uid', 'rec-' + Date.now() + '-' + Math.random().toString(36).slice(2, 8));
        }
        return row.getAttribute('data-recogida-uid');
    }

    function recogidaKeyFromContext(context) {
        return context === 'principal' ? 'principal' : recogidaUidExtra(context);
    }

    function recogidasConAlmacen() {
        const list = [];
        if (recogida1Lista()) {
            list.push({
                key: 'principal',
                num: 1,
                almacenId: document.getElementById('origen_almacenid').value,
                almacenLabel: document.getElementById('txtNombreOrigen').value || document.getElementById('origen_direccion').value || 'Recogida 1',
            });
        }
        document.querySelectorAll('.origen-extra-row').forEach(function (row, i) {
            if (!row.querySelector('[data-field="latitud"]').value) return;
            list.push({
                key: recogidaUidExtra(row),
                num: i + 2,
                almacenId: row.querySelector('[data-field="almacenid"]').value,
                almacenLabel: row.querySelector('.txt-recogida-extra').value || ('Recogida ' + (i + 2)),
            });
        });
        return list;
    }

    function renumerarDetallesInputs() {
        document.querySelectorAll('.producto-recogida-row').forEach(function (fila, idx) {
            const hidden = fila.querySelector('.selector-catalogo-value');
            const cant = fila.querySelector('[data-field="cantidad"]');
            const obs = fila.querySelector('[data-field="observaciones"]');
            if (hidden) hidden.setAttribute('name', 'detalles[' + idx + '][producto_ref]');
            if (cant) cant.setAttribute('name', 'detalles[' + idx + '][cantidad]');
            if (obs) obs.setAttribute('name', 'detalles[' + idx + '][observaciones]');
        });
    }

    function actualizarEtiquetaFilaProducto(fila, rec) {
        const lbl = fila.querySelector('.lbl-producto-recogida');
        if (lbl) {
            lbl.innerHTML = '<i class="fas fa-seedling mr-1 text-success"></i> Recogida ' + rec.num + ' — ' + (rec.almacenLabel || 'Almacén');
        }
        fila.setAttribute('data-selector-id', selectorIdForRecogida(rec.key));
    }

    function formatearStock(valor) {
        const n = Number(valor);
        if (!Number.isFinite(n)) return '—';
        return n.toLocaleString('es-BO', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function actualizarStockFilaProducto(fila, extra) {
        extra = extra || {};
        const lblStock = fila.querySelector('.lbl-stock-disponible');
        const lblCantidad = fila.querySelector('.lbl-cantidad-titulo');
        const cantInput = fila.querySelector('[data-field="cantidad"]');
        if (!lblStock || !cantInput) return;

        const unidad = extra.unidad || 'kg';
        if (lblCantidad) lblCantidad.textContent = 'Cantidad (' + unidad + ')';

        if (extra.stock != null && extra.stock !== '' && Number.isFinite(Number(extra.stock))) {
            const stockNum = Number(extra.stock);
            lblStock.textContent = 'Stock: ' + formatearStock(stockNum) + ' ' + unidad;
            lblStock.classList.remove('text-muted');
            lblStock.classList.add('text-success');
            cantInput.max = String(stockNum);
            cantInput.setAttribute('title', 'Máximo disponible: ' + formatearStock(stockNum) + ' ' + unidad);
            validarCantidadContraStock(fila, cantInput, stockNum, unidad);
        } else {
            lblStock.textContent = 'Stock: no registrado';
            lblStock.classList.remove('text-success');
            lblStock.classList.add('text-muted');
            cantInput.removeAttribute('max');
            cantInput.removeAttribute('title');
            cantInput.classList.remove('is-invalid');
            fila.querySelector('.stock-excedido-aviso')?.remove();
        }
    }

    function validarCantidadContraStock(fila, cantInput, stockNum, unidad) {
        if (!cantInput || !Number.isFinite(stockNum)) return;
        let avisoEl = fila.querySelector('.stock-excedido-aviso');
        const val = cantInput.value ? parseFloat(cantInput.value) : NaN;
        const excede = !isNaN(val) && val > stockNum;
        if (excede) {
            if (!avisoEl) {
                avisoEl = document.createElement('small');
                avisoEl.className = 'stock-excedido-aviso text-danger d-block mt-1';
                cantInput.closest('.col-md-3')?.appendChild(avisoEl);
            }
            avisoEl.textContent = 'Supera el stock disponible (' + formatearStock(stockNum) + ' ' + unidad + ').';
            cantInput.classList.add('is-invalid');
        } else {
            if (avisoEl) avisoEl.remove();
            cantInput.classList.remove('is-invalid');
        }
    }

    function vincularStockProducto(fila) {
        const wrap = fila.querySelector('.selector-catalogo-wrapper');
        if (!wrap || wrap.dataset.stockVinculado === '1') return;
        wrap.dataset.stockVinculado = '1';
        wrap.addEventListener('selector-catalogo:change', function (e) {
            actualizarStockFilaProducto(fila, e.detail?.extra || {});
        });
        const cantInput = fila.querySelector('[data-field="cantidad"]');
        if (cantInput && cantInput.dataset.stockListener !== '1') {
            cantInput.dataset.stockListener = '1';
            cantInput.addEventListener('input', function () {
                const max = cantInput.max ? parseFloat(cantInput.max) : NaN;
                if (!isNaN(max)) {
                    const unidad = fila.querySelector('.lbl-cantidad-titulo')?.textContent?.replace(/^Cantidad\s*\(/, '').replace(/\)$/, '') || 'kg';
                    validarCantidadContraStock(fila, cantInput, max, unidad);
                }
            });
        }
    }

    function registrarSelectorProducto(selId, rec) {
        if (!window.CatalogoSelector) return;
        const params = rec.almacenId ? { almacenid: String(rec.almacenId) } : {};
        CatalogoSelector.register(selId, {
            endpoint: PRODUCTO_ENDPOINT,
            title: 'Productos en ' + (rec.almacenLabel || 'almacén'),
            searchPlaceholder: 'Cultivo, lote, insumo…',
            params: params,
            rowIcon: 'fa-box',
            theme: 'planta',
            onSelect: function (item) {
                const fila = document.querySelector('[data-selector-id="' + selId + '"]');
                if (fila) actualizarStockFilaProducto(fila, item.extra || {});
            },
        });
    }

    function crearFilaProducto(rec, detalleIdx) {
        const selId = selectorIdForRecogida(rec.key);
        const fila = document.createElement('div');
        fila.className = 'producto-recogida-row border rounded p-3 mb-2 bg-white';
        fila.setAttribute('data-recogida-key', rec.key);
        fila.innerHTML =
            '<label class="small font-weight-bold lbl-producto-recogida d-block mb-2"></label>' +
            '<div class="form-row align-items-end">' +
                '<div class="col-md-5">' +
                    '<div class="selector-catalogo-wrapper flex-grow-1 w-100 mb-0 selector-catalogo--filtros" id="selector_wrap_' + selId + '">' +
                        '<div class="selector-filtros-field">' +
                            '<input type="text" class="selector-filtros-field__input selector-catalogo-label is-empty" readonly placeholder="Elegir producto…">' +
                            '<input type="hidden" name="detalles[' + detalleIdx + '][producto_ref]" class="selector-catalogo-value" required>' +
                            '<div class="selector-filtros-field__actions">' +
                                '<button type="button" class="selector-filtros-field__open" data-selector-open="' + selId + '" title="Abrir catálogo">' +
                                    '<i class="fas fa-chevron-down"></i>' +
                                '</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="col-md-3">' +
                    '<div class="d-flex justify-content-between align-items-baseline mb-1">' +
                        '<label class="small text-muted mb-0 lbl-cantidad-titulo">Cantidad (kg)</label>' +
                        '<small class="lbl-stock-disponible text-muted font-weight-bold">Stock: —</small>' +
                    '</div>' +
                    '<input type="number" step="0.01" min="0.01" name="detalles[' + detalleIdx + '][cantidad]" data-field="cantidad" class="form-control form-control-sm" placeholder="Ej: 350" required>' +
                '</div>' +
                '<div class="col-md-4">' +
                    '<label class="small text-muted mb-1">Observaciones del ítem</label>' +
                    '<input type="text" name="detalles[' + detalleIdx + '][observaciones]" data-field="observaciones" class="form-control form-control-sm" placeholder="Opcional">' +
                '</div>' +
            '</div>';
        actualizarEtiquetaFilaProducto(fila, rec);
        registrarSelectorProducto(selId, rec);
        vincularStockProducto(fila);
        return fila;
    }

    function syncFilasProducto() {
        const container = document.getElementById('productos-recogida-container');
        const bloque = document.getElementById('bloque-productos');
        if (!container || !bloque) return;

        const recogidas = recogidasConAlmacen();
        bloque.classList.toggle('d-none', recogidas.length === 0);

        const keysActivas = new Set();
        recogidas.forEach(function (rec, idx) {
            keysActivas.add(rec.key);
            let fila = container.querySelector('[data-recogida-key="' + rec.key + '"]');
            if (!fila) {
                fila = crearFilaProducto(rec, idx);
                container.appendChild(fila);
            } else {
                actualizarEtiquetaFilaProducto(fila, rec);
                const selId = selectorIdForRecogida(rec.key);
                const cfg = CatalogoSelector?.instances?.[selId];
                if (cfg) {
                    cfg.title = 'Productos en ' + (rec.almacenLabel || 'almacén');
                    cfg.params = rec.almacenId ? { almacenid: String(rec.almacenId) } : {};
                }
            }
        });

        container.querySelectorAll('.producto-recogida-row').forEach(function (fila) {
            const key = fila.getAttribute('data-recogida-key');
            if (!keysActivas.has(key)) {
                const selId = selectorIdForRecogida(key);
                if (CatalogoSelector?.instances?.[selId]) {
                    delete CatalogoSelector.instances[selId];
                }
                fila.remove();
            }
        });

        renumerarDetallesInputs();
    }

    function actualizarBtnVerOrigen() {
        const btn = document.getElementById('btnVerProductosOrigen');
        const almacenId = document.getElementById('origen_almacenid')?.value;
        if (btn) btn.classList.toggle('d-none', !almacenId);
    }

    function abrirProductosDelAlmacen(almacen, context) {
        if (!almacen?.id || !window.CatalogoSelector) return;

        const ejecutar = function () {
            if (context === 'principal') {
                if (bloquearRecogidaSiDestino()) return;
                aplicarRecogidaPrincipal(almacen, true);
            } else if (context && typeof context.querySelector === 'function') {
                aplicarRecogidaExtra(context, almacen, true);
            }
            syncFilasProducto();

            const key = recogidaKeyFromContext(context);
            const selId = selectorIdForRecogida(key);
            const cfg = CatalogoSelector.instances[selId];
            if (cfg) {
                cfg.params = { almacenid: String(almacen.id) };
                cfg.title = 'Productos en ' + (almacen.label || almacen.extra?.direccion || 'almacén');
            }
            window.setTimeout(function () {
                CatalogoSelector.open(selId);
            }, 150);
        };

        const $m = window.jQuery('#modalSelectorCatalogo');
        if ($m.hasClass('show')) {
            $m.one('hidden.bs.modal', ejecutar);
            $m.modal('hide');
        } else {
            ejecutar();
        }
    }

    function aplicarRecogidaPrincipal(almacen, desdeVer) {
        document.getElementById('origen_almacenid').value = almacen.id || '';
        actualizarBtnVerOrigen();

        const lat = parseFloat(almacen.extra?.lat);
        const lng = parseFloat(almacen.extra?.lng);
        const label = etiquetaAlmacenPedido(almacen);
        if (isNaN(lat) || isNaN(lng)) {
            if (!desdeVer) syncFilasProducto();
            return;
        }

        document.getElementById('origen_latitud').value = lat.toFixed(7);
        document.getElementById('origen_longitud').value = lng.toFixed(7);
        document.getElementById('origen_direccion').value = label;
        const display = document.getElementById('txtNombreOrigen');
        display.value = label;
        display.classList.remove('text-muted');
        document.getElementById('txtOrigenCoords').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
        if (!desdeVer) syncFilasProducto();
        redrawRoute();
    }

    function aplicarRecogidaExtra(row, almacen, desdeVer) {
        const lat = parseFloat(almacen.extra?.lat);
        const lng = parseFloat(almacen.extra?.lng);
        const label = etiquetaAlmacenPedido(almacen);
        if (isNaN(lat) || isNaN(lng)) {
            if (!desdeVer) syncFilasProducto();
            return;
        }

        row.querySelector('[data-field="latitud"]').value = lat.toFixed(7);
        row.querySelector('[data-field="longitud"]').value = lng.toFixed(7);
        row.querySelector('[data-field="direccion"]').value = label;
        const almacenField = row.querySelector('[data-field="almacenid"]');
        if (almacenField) almacenField.value = almacen.id || '';
        const txt = row.querySelector('.txt-recogida-extra');
        txt.value = label;
        txt.classList.remove('text-muted');
        if (!desdeVer) syncFilasProducto();
        redrawRoute();
    }

    function aplicarDestino(almacen) {
        if (!puedeElegirDestino()) {
            aviso('Complete todas las recogidas en orden antes de elegir el destino.');
            return;
        }
        const lat = parseFloat(almacen.extra?.lat);
        const lng = parseFloat(almacen.extra?.lng);
        const label = etiquetaAlmacenPedido(almacen);
        if (isNaN(lat) || isNaN(lng)) return;

        document.getElementById('latitud').value = lat.toFixed(7);
        document.getElementById('longitud').value = lng.toFixed(7);
        document.getElementById('direccion_texto').value = label;
        const display = document.getElementById('txtNombreDestino');
        display.value = label;
        display.classList.remove('text-muted');
        document.getElementById('txtDestinoCoords').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
        redrawRoute();
    }

    function limpiarOrigen() {
        ['origen_latitud', 'origen_longitud', 'origen_direccion', 'origen_almacenid'].forEach(function (id) {
            document.getElementById(id).value = '';
        });
        actualizarBtnVerOrigen();
        document.getElementById('txtNombreOrigen').value = '';
        document.getElementById('txtNombreOrigen').classList.add('text-muted');
        document.getElementById('txtOrigenCoords').textContent = '';
        syncFilasProducto();
        redrawRoute();
    }

    function limpiarDestino() {
        ['latitud', 'longitud', 'direccion_texto'].forEach(function (id) {
            document.getElementById(id).value = '';
        });
        document.getElementById('txtNombreDestino').value = '';
        document.getElementById('txtNombreDestino').classList.add('text-muted');
        document.getElementById('txtDestinoCoords').textContent = '';
        redrawRoute();
    }

    function crearFilaRecogidaExtra(datos) {
        datos = datos || {};
        const row = document.createElement('div');
        row.className = 'origen-extra-row';
        row.setAttribute('data-recogida-uid', 'rec-' + Date.now() + '-' + Math.random().toString(36).slice(2, 8));
        row.innerHTML =
            '<div class="d-flex justify-content-between align-items-center mb-1">' +
                '<label class="small text-muted mb-0 lbl-recogida-num">Recogida</label>' +
                '<button type="button" class="btn btn-link btn-sm text-danger p-0 btn-quitar-recogida">Quitar</button>' +
            '</div>' +
            '<div class="pedido-picker-field">' +
                '<input type="text" class="picker-display txt-recogida-extra text-muted" readonly placeholder="Buscar almacén agrícola…">' +
                '<div class="picker-actions">' +
                    '<button type="button" class="btn btn-outline-success btn-sm btn-buscar-recogida-extra">' +
                        '<i class="fas fa-search mr-1"></i> Buscar' +
                    '</button>' +
                '</div>' +
            '</div>' +
            '<input type="hidden" data-field="latitud" value="">' +
            '<input type="hidden" data-field="longitud" value="">' +
            '<input type="hidden" data-field="direccion" value="">' +
            '<input type="hidden" data-field="almacenid" value="">';

        if (datos.latitud) row.querySelector('[data-field="latitud"]').value = datos.latitud;
        if (datos.longitud) row.querySelector('[data-field="longitud"]').value = datos.longitud;
        if (datos.direccion) {
            row.querySelector('[data-field="direccion"]').value = datos.direccion;
            row.querySelector('.txt-recogida-extra').value = datos.direccion;
            row.querySelector('.txt-recogida-extra').classList.remove('text-muted');
        }
        if (datos.almacenid) row.querySelector('[data-field="almacenid"]').value = datos.almacenid;

        row.querySelector('.btn-buscar-recogida-extra').addEventListener('click', function () {
            if (bloquearRecogidaSiDestino()) return;
            if (!recogida1Lista()) {
                aviso('Primero elija la recogida 1.');
                return;
            }
            pickerRecogidaActivo = row;
            CatalogoSelector.open('pedido_almacen_recogida_extra');
        });
        row.querySelector('.btn-quitar-recogida').addEventListener('click', function () {
            if (bloquearRecogidaSiDestino()) return;
            row.remove();
            renumerarRecogidas();
            syncFilasProducto();
            redrawRoute();
        });

        return row;
    }

    function ejecutarReinicioRuta() {
        document.getElementById('recogidas-extra-container').innerHTML = '';
        limpiarOrigen();
        limpiarDestino();
        document.getElementById('rutaResumen').textContent = '';
        document.getElementById('productos-recogida-container').innerHTML = '';
        syncFilasProducto();
        resetCostoEnvio();
        redrawRoute();
    }

    function reiniciarRuta() {
        if (window.ModalConfirmar && typeof ModalConfirmar.confirmar === 'function') {
            ModalConfirmar.confirmar({
                titulo: 'Reiniciar ruta',
                mensaje: '¿Reiniciar toda la ruta? Se borrarán recogidas, destino, productos y el trazo del mapa.',
                tono: 'warning',
                btnText: 'Reiniciar',
            }).then(function (ok) {
                if (ok) ejecutarReinicioRuta();
            });
            return;
        }
        if (confirm('¿Reiniciar toda la ruta? Se borrarán recogidas, destino, productos y el trazo del mapa.')) {
            ejecutarReinicioRuta();
        }
    }

    function initMapa() {
        const el = document.getElementById('mapaPedidoEntrega');
        if (!el || !window.L) return;

        state.map = L.map(el, { scrollWheelZoom: true }).setView([hub.lat, hub.lng], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap',
        }).addTo(state.map);
        state.capasAlmacenes = L.layerGroup();
        state.capasRuta = L.layerGroup().addTo(state.map);

        document.getElementById('btnVerAlmacenesMapa').addEventListener('click', toggleAlmacenesEnMapa);
        document.getElementById('btnReiniciarRuta').addEventListener('click', reiniciarRuta);
        const costoInput = document.getElementById('costo_bs');
        if (costoInput) {
            costoInput.addEventListener('input', function () {
                costoEditadoManual = true;
            });
        }
        document.getElementById('btnLimpiarOrigen').addEventListener('click', function () {
            if (bloquearRecogidaSiDestino()) return;
            limpiarOrigen();
        });
        document.getElementById('btnLimpiarDestino').addEventListener('click', limpiarDestino);

        document.getElementById('form-pedido').addEventListener('submit', function (e) {
            if (!document.getElementById('origen_latitud').value) {
                e.preventDefault();
                aviso('Seleccione al menos la recogida 1.');
                return;
            }
            if (hayRecogidasExtraVacias()) {
                e.preventDefault();
                aviso('Complete o elimine las recogidas adicionales vacías.');
                return;
            }
            if (!document.getElementById('latitud').value) {
                e.preventDefault();
                aviso('Seleccione el almacén de planta (destino).');
                return;
            }
            const filasProducto = document.querySelectorAll('.producto-recogida-row');
            if (!filasProducto.length) {
                e.preventDefault();
                aviso('Indique el producto a recoger en cada almacén de la ruta.');
                return;
            }
            let faltaProducto = false;
            filasProducto.forEach(function (fila) {
                if (!fila.querySelector('.selector-catalogo-value')?.value) faltaProducto = true;
            });
            if (faltaProducto) {
                e.preventDefault();
                aviso('Complete el producto solicitado en cada recogida.');
                return;
            }
            let excedeStock = false;
            filasProducto.forEach(function (fila) {
                const cantInput = fila.querySelector('[data-field="cantidad"]');
                const max = cantInput?.max ? parseFloat(cantInput.max) : NaN;
                const val = cantInput?.value ? parseFloat(cantInput.value) : NaN;
                if (!isNaN(max) && !isNaN(val) && val > max) excedeStock = true;
            });
            if (excedeStock) {
                e.preventDefault();
                aviso('La cantidad solicitada supera el stock disponible en algún producto.');
                return;
            }
            if (!document.getElementById('transportista_usuarioid_create').value || !document.getElementById('vehiculoid_create').value) {
                e.preventDefault();
                aviso('Seleccione chofer y vehículo para el envío.');
                return;
            }
            const costoInput = document.querySelector('input[name="costo_bs"]');
            const costo = costoInput ? parseFloat(String(costoInput.value).replace(',', '.')) : NaN;
            if (!Number.isFinite(costo) || costo <= 0) {
                e.preventDefault();
                aviso('Ingrese el costo del servicio en bolivianos (mayor a 0).');
            }
        });

        setTimeout(function () {
            state.map.invalidateSize();
            @if(is_array(old('recogidas')))
            @foreach(old('recogidas') as $rec)
            document.getElementById('recogidas-extra-container').appendChild(crearFilaRecogidaExtra(@json($rec)));
            @endforeach
            renumerarRecogidas();
            @endif
            syncFilasProducto();
            @if(is_array(old('detalles')))
            (function () {
                const oldDetalles = @json(old('detalles'));
                document.querySelectorAll('.producto-recogida-row').forEach(function (fila, i) {
                    const d = oldDetalles[i];
                    if (!d) return;
                    const selId = fila.getAttribute('data-selector-id');
                    if (selId && d.producto_ref) {
                        CatalogoSelector.setValue(selId, d.producto_ref, d.producto_label || d.producto_ref);
                    }
                    const cant = fila.querySelector('[data-field="cantidad"]');
                    const obs = fila.querySelector('[data-field="observaciones"]');
                    if (cant && d.cantidad) cant.value = d.cantidad;
                    if (obs && d.observaciones) obs.value = d.observaciones;
                });
            })();
            @endif
            redrawRoute();
        }, 200);
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (!window.CatalogoSelector) return;

        CatalogoSelector.register('pedido_almacen_origen', {
            endpoint: @json(route('catalogo-selector.almacenes')),
            title: 'Almacén agrícola — recogida 1',
            searchPlaceholder: 'Nombre o ubicación…',
            params: { ambito: 'agricola' },
            rowAction: { label: 'Ver' },
            onRowAction: function (item) {
                if (bloquearRecogidaSiDestino()) return;
                abrirProductosDelAlmacen(item, 'principal');
            },
            onSelect: function (item) {
                if (bloquearRecogidaSiDestino()) return;
                aplicarRecogidaPrincipal(item);
            },
        });

        CatalogoSelector.register('pedido_almacen_recogida_extra', {
            endpoint: @json(route('catalogo-selector.almacenes')),
            title: 'Almacén agrícola — recogida adicional',
            searchPlaceholder: 'Nombre o ubicación…',
            params: { ambito: 'agricola' },
            rowAction: { label: 'Ver' },
            onRowAction: function (item) {
                if (!pickerRecogidaActivo) return;
                abrirProductosDelAlmacen(item, pickerRecogidaActivo);
            },
            onSelect: function (item) {
                if (!pickerRecogidaActivo) return;
                aplicarRecogidaExtra(pickerRecogidaActivo, item);
            },
        });

        CatalogoSelector.register('pedido_almacen_destino', {
            endpoint: @json(route('catalogo-selector.almacenes')),
            title: 'Almacén de planta — destino',
            searchPlaceholder: 'Nombre o ubicación…',
            params: { ambito: 'planta' },
            onSelect: function (item) { aplicarDestino(item); },
        });

        document.getElementById('btnBuscarOrigen').addEventListener('click', function () {
            if (bloquearRecogidaSiDestino()) return;
            CatalogoSelector.open('pedido_almacen_origen');
        });
        document.getElementById('btnVerProductosOrigen').addEventListener('click', function () {
            const almacenId = document.getElementById('origen_almacenid')?.value;
            if (!almacenId) return;
            abrirProductosDelAlmacen({
                id: almacenId,
                label: document.getElementById('txtNombreOrigen')?.value || document.getElementById('origen_direccion')?.value || '',
                extra: {
                    lat: document.getElementById('origen_latitud')?.value,
                    lng: document.getElementById('origen_longitud')?.value,
                    direccion: document.getElementById('origen_direccion')?.value,
                },
            }, 'principal');
        });

        actualizarBtnVerOrigen();
        document.getElementById('btnBuscarDestino').addEventListener('click', function () {
            if (!puedeElegirDestino()) {
                aviso('Complete todas las recogidas antes de elegir destino.');
                return;
            }
            CatalogoSelector.open('pedido_almacen_destino');
        });

        const btnAgregarRecogida = document.getElementById('btnAgregarRecogida');
        if (btnAgregarRecogida) {
            btnAgregarRecogida.addEventListener('click', function () {
                if (bloquearRecogidaSiDestino()) return;
                if (!recogida1Lista()) {
                    aviso('Primero elija la recogida 1.');
                    return;
                }
                if (hayRecogidasExtraVacias()) {
                    aviso('Complete la recogida adicional pendiente antes de agregar otra.');
                    return;
                }
                const actuales = document.querySelectorAll('.origen-extra-row').length;
                if (actuales >= MAX_RECOGIDAS_EXTRA) {
                    aviso('Máximo ' + MAX_RECOGIDAS_EXTRA + ' almacenes de recogida adicionales.');
                    return;
                }
                const container = document.getElementById('recogidas-extra-container');
                container.appendChild(crearFilaRecogidaExtra());
                renumerarRecogidas();
                syncEstadoRuta();
            });
        }

        syncEstadoRuta();

        const txtTransportista = document.getElementById('txtTransportistaCreate');
        if (txtTransportista) {
            const txtVehiculo = document.getElementById('txtVehiculoCreate');
            const inputTransportista = document.getElementById('transportista_usuarioid_create');
            const inputVehiculo = document.getElementById('vehiculoid_create');
            const btnBuscarVehiculo = document.getElementById('btnBuscarVehiculoCreate');

            function syncVehiculoBtn() {
                if (btnBuscarVehiculo) btnBuscarVehiculo.disabled = !inputTransportista.value;
            }

            function limpiarVehiculo() {
                inputVehiculo.value = '';
                txtVehiculo.value = '';
                txtVehiculo.classList.add('text-muted');
                txtVehiculo.placeholder = inputTransportista.value ? 'Buscar vehículo…' : 'Primero elija transportista…';
            }

            CatalogoSelector.register('create_envio_transportista', {
                endpoint: @json(route('catalogo-selector.usuarios')),
                title: 'Buscar transportista',
                searchPlaceholder: 'Nombre, usuario o correo…',
                params: { roles: 'transportista', ambito_flota: 'agricola' },
                onSelect(item) {
                    inputTransportista.value = item.id;
                    txtTransportista.value = item.label;
                    txtTransportista.classList.remove('text-muted');
                    limpiarVehiculo();
                    syncVehiculoBtn();
                    CatalogoSelector.instances.create_envio_vehiculo.params = {
                        transportista_usuarioid: item.id,
                        solo_transportista: '1',
                    };
                },
            });

            CatalogoSelector.register('create_envio_vehiculo', {
                endpoint: @json(route('catalogo-selector.vehiculos')),
                title: 'Buscar vehículo',
                searchPlaceholder: 'Placa, marca, modelo…',
                params: {
                    transportista_usuarioid: inputTransportista.value || '',
                    solo_transportista: '1',
                },
                onSelect(item) {
                    inputVehiculo.value = item.id;
                    txtVehiculo.value = item.label;
                    txtVehiculo.classList.remove('text-muted');
                },
            });

            document.getElementById('btnBuscarTransportistaCreate').addEventListener('click', function () {
                CatalogoSelector.open('create_envio_transportista');
            });
            document.getElementById('btnBuscarVehiculoCreate').addEventListener('click', function () {
                if (!inputTransportista.value) return;
                CatalogoSelector.open('create_envio_vehiculo');
            });
            syncVehiculoBtn();
        }

        if (window.L && window.RutaPorCalles) {
            initMapa();
        }
    });
})();
</script>
@endpush
