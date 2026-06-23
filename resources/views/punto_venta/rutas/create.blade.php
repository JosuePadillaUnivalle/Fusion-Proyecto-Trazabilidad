@extends('layouts.app')

@section('title', 'Nueva ruta de distribución | AgroFusion')
@section('page_title', 'Nueva ruta de distribución')

@push('styles')
@include('logistica.partials.mapa-ruta-styles')
<style>
#mapaRutaDistribucion-wrap { position: relative; }
#mapaRutaDistribucion { height: 380px; min-height: 380px; border-radius: 10px; border: 1px solid #dee2e6; }
#mapa-dist-flash {
    position: absolute; z-index: 1000; top: 10px; left: 50%; transform: translateX(-50%);
    background: #1e293b; color: #fff; padding: .4rem .85rem; border-radius: 8px;
    font-size: .8rem; display: none; pointer-events: none;
}
.pedido-ruta-check:checked + label { font-weight: 700; }
.ruta-pedidos-table tbody tr { cursor: pointer; }
.ruta-pedidos-table tbody tr.selected { background: #ecfdf5; }
.dist-mapa-marker { background: transparent !important; border: none !important; }
.dist-mapa-pin {
    width: 32px; height: 32px; border-radius: 50%; color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.35); cursor: pointer;
    transition: transform .12s ease;
}
.dist-mapa-pin:hover { transform: scale(1.12); }
.dist-mapa-pin.is-selected { box-shadow: 0 0 0 3px #fbbf24, 0 2px 8px rgba(0,0,0,.35); }
.leaflet-tooltip.dist-mapa-tooltip {
    background: #1e293b; color: #fff; border: 0; border-radius: 8px;
    font-size: .8rem; font-weight: 600; padding: .35rem .65rem;
    box-shadow: 0 4px 12px rgba(0,0,0,.2);
}
.leaflet-tooltip.dist-mapa-tooltip::before { border-top-color: #1e293b; }
.ruta-mapa-hint { font-size: .875rem; color: #64748b; }
.ruta-flota-panel {
    border-radius: 12px;
    padding: 1rem 1.1rem;
    border: 1px solid #e5e7eb;
    background: #fafafa;
    height: 100%;
}
.ruta-flota-panel--origen { border-color: #fecaca; background: linear-gradient(160deg, #fffafa 0%, #fef2f2 100%); }
.ruta-flota-panel--chofer { border-color: #bfdbfe; background: linear-gradient(160deg, #f8fbff 0%, #eff6ff 100%); }
.ruta-flota-panel--vehiculo { border-color: #a7f3d0; background: linear-gradient(160deg, #f6fffb 0%, #ecfdf5 100%); }
.ruta-flota-panel .panel-icon {
    width: 34px; height: 34px; border-radius: 10px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.9rem; margin-right: 0.45rem;
}
.ruta-flota-panel--origen .panel-icon { background: #fee2e2; color: #b91c1c; }
.ruta-flota-panel--chofer .panel-icon { background: #dbeafe; color: #1d4ed8; }
.ruta-flota-panel--vehiculo .panel-icon { background: #d1fae5; color: #047857; }
.ruta-mapa-leyenda span { display: inline-flex; align-items: center; margin-right: 1rem; font-size: 0.82rem; }
.ruta-mapa-leyenda i { width: 12px; height: 12px; border-radius: 50%; display: inline-block; margin-right: 0.35rem; }
.ruta-mapa-leyenda .dot-planta { background: #dc2626; }
.ruta-mapa-leyenda .dot-pdv { background: #2563eb; }
</style>
@endpush

@section('content')
<div class="card card-outline card-success">
    <div class="card-header">
        <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-route mr-2"></i>Armar ruta planta → minoristas</h3>
    </div>
    <div class="card-body">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($pedidosListos->isEmpty())
            <div class="alert alert-warning mb-0">
                No hay pedidos en estado «Listo para envío». Acepte solicitudes en
                <a href="{{ route('punto-venta.pedidos.index') }}">Pedidos de distribución</a> primero.
            </div>
        @else
        <form method="POST" action="{{ route('punto-venta.rutas.store') }}" id="formRutaDistribucion">
            @csrf

            <div class="alert alert-info py-2 small">
                <i class="fas fa-info-circle mr-1"></i>
                Elija el <strong>almacén de planta</strong> donde cargará (solo aparecen plantas con pedidos listos),
                marque los pedidos de minoristas a entregar y asigne un <strong>transportista de flota planta</strong>.
                Registre choferes en <a href="{{ route('envios.transportistas.create') }}">Transportistas</a> (flota «Planta»).
            </div>

            <div class="form-row mb-3">
                <div class="form-group col-lg-4 mb-lg-0">
                    <div class="ruta-flota-panel ruta-flota-panel--origen">
                        <label class="small font-weight-bold mb-2 d-block">
                            <span class="panel-icon"><i class="fas fa-industry"></i></span>
                            Almacén planta (carga) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group mb-2">
                            <input type="text" id="txtAlmacenOrigen" class="form-control bg-white" readonly
                                   placeholder="Buscar almacén de planta…" value="{{ $oldAlmacenLabel }}" required>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-danger" id="btnBuscarAlmacenOrigen" title="Buscar planta">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="almacen_planta_origenid" id="almacen_origen" value="{{ old('almacen_planta_origenid') }}" required>
                        <p class="small text-muted mb-0">Solo se listan plantas con pedidos de minoristas pendientes de reparto.</p>
                    </div>
                </div>
                <div class="form-group col-lg-4 mb-lg-0">
                    <div class="ruta-flota-panel ruta-flota-panel--chofer">
                        <label class="small font-weight-bold mb-2 d-block">
                            <span class="panel-icon"><i class="fas fa-id-card"></i></span>
                            Chofer planta <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="text" id="txtTransportistaPlanta" class="form-control bg-white" readonly placeholder="Buscar transportista de planta…" required>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary" id="btnBuscarTransportistaPlanta" title="Buscar chofer"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        <input type="hidden" name="transportista_usuarioid" id="transportista_planta_id" value="{{ old('transportista_usuarioid') }}" required>
                    </div>
                </div>
                <div class="form-group col-lg-4">
                    <div class="ruta-flota-panel ruta-flota-panel--vehiculo">
                        <label class="small font-weight-bold mb-2 d-block">
                            <span class="panel-icon"><i class="fas fa-truck"></i></span>
                            Vehículo <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="text" id="txtVehiculoPlanta" class="form-control bg-white" readonly placeholder="Seleccione el vehículo del chofer…" required>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-success" id="btnBuscarVehiculoPlanta" disabled title="Buscar vehículo"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                        <input type="hidden" name="vehiculoid" id="vehiculo_planta_id" value="{{ old('vehiculoid') }}" required>
                        <p class="small text-muted mb-0 mt-2"><i class="fas fa-info-circle mr-1"></i>Primero elija el chofer; luego asigne su vehículo.</p>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="form-group col-lg-4 mb-lg-0">
                    <label class="small font-weight-bold">Costo del servicio (Bs) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <div class="input-group-prepend"><span class="input-group-text">Bs</span></div>
                        <input type="number" name="costo_bs" class="form-control" min="0.01" step="0.01"
                               value="{{ old('costo_bs') }}" placeholder="Monto por toda la ruta" required>
                    </div>
                    @error('costo_bs')<small class="text-danger d-block">{{ $message }}</small>@enderror
                    <p class="small text-muted mb-0 mt-1">Un solo monto por la ruta completa planta → puntos de venta.</p>
                </div>
            </div>

            <h5 class="font-weight-bold mb-2"><i class="fas fa-store text-danger mr-1"></i>Pedidos a incluir</h5>
            <p class="small text-muted">Al elegir planta solo verá los pedidos que salen de ese almacén. Marque el orden de visita (de arriba hacia abajo).</p>
            <div class="table-responsive mb-3">
                <table class="table table-sm ruta-pedidos-table" id="tablaPedidosRuta">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:40px"></th>
                            <th>Solicitud</th>
                            <th>Planta origen</th>
                            <th>Punto de venta</th>
                            <th>Producto</th>
                            <th>Kg</th>
                        </tr>
                    </thead>
                    <tbody id="pedidos-sortable">
                        @foreach($pedidosListos as $pedido)
                        @php
                            $det = $pedido->detalles->first();
                            $pdv = $pedido->puntoVenta;
                            $checked = in_array($pedido->pedidodistribucionid, old('pedidos', []), true);
                            $pesoFilaRuta = app(\App\Services\TransporteCapacidadService::class)->pesoDetallePedidoDistribucion($det);
                            $volFilaRuta = app(\App\Services\TransporteCapacidadService::class)->volumenDetallePedidoDistribucion($det);
                        @endphp
                        <tr data-pedido-id="{{ $pedido->pedidodistribucionid }}"
                            data-pdv-id="{{ $pdv?->puntoventaid }}"
                            data-almacen-origen-id="{{ $pedido->almacen_planta_origenid }}"
                            data-lat="{{ $pdv?->latitud }}"
                            data-lng="{{ $pdv?->longitud }}"
                            data-label="{{ $pdv?->nombre ?? 'PDV' }}"
                            data-peso-kg="{{ number_format((float) $pesoFilaRuta, 2, '.', '') }}"
                            data-volumen-m3="{{ $volFilaRuta !== null ? number_format((float) $volFilaRuta, 4, '.', '') : '' }}"
                            class="{{ $checked ? 'selected' : '' }}">
                            <td>
                                <input type="checkbox" class="pedido-ruta-check" name="pedidos[]"
                                       value="{{ $pedido->pedidodistribucionid }}" @checked($checked)>
                            </td>
                            <td>{{ $pedido->numero_solicitud }}</td>
                            <td>{{ $pedido->almacenPlantaOrigen?->nombre ?? '—' }}</td>
                            <td>{{ $pdv?->nombre ?? '—' }}</td>
                            <td>{{ $det?->producto_nombre ?? '—' }}</td>
                            <td>{{ $det ? number_format((float) $det->cantidad, 2) : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <h5 class="font-weight-bold mb-2"><i class="fas fa-map text-info mr-1"></i>Vista previa del recorrido</h5>
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-2 gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="btnVerUbicacionesMapa">
                    <i class="fas fa-map-marked-alt mr-1"></i>Ver en mapa
                </button>
                <div class="ruta-mapa-leyenda d-none" id="leyendaMapaDist">
                    <span><i class="dot-planta"></i>Planta (clic para elegir origen)</span>
                    <span><i class="dot-pdv"></i>PDV con pedido (clic para incluir)</span>
                </div>
            </div>
            <div id="mapaRutaDistribucion-wrap" class="mb-2">
                <div id="mapa-dist-flash"></div>
                <div id="mapaRutaDistribucion"></div>
            </div>
            <small id="mapaDistResumen" class="ruta-mapa-hint d-block mb-3"></small>

            <div class="mt-3">
                <button type="submit" class="btn btn-success px-4" id="btnGuardarRuta">
                    <i class="fas fa-save mr-1"></i>Crear ruta e iniciar distribución
                </button>
                <a href="{{ route('punto-venta.rutas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
        @endif
    </div>
</div>

@include('partials.selector-catalogo-assets')
@endsection

@if($pedidosListos->isNotEmpty())
@push('scripts')
@include('logistica.partials.mapa-ruta-libs')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function aviso(mensaje, titulo, tono) {
        if (window.ModalAviso) {
            window.ModalAviso.mostrar(mensaje, titulo || 'Aviso', tono || 'warning');
        } else {
            window.alert(mensaje);
        }
    }

    const almacenes = @json($almacenesMapa);
    const puntosDisponibles = @json($puntosMapaDisponibles);
    const hub = { lat: {{ $hubLat }}, lng: {{ $hubLng }} };
    const mapEl = document.getElementById('mapaRutaDistribucion');
    if (!mapEl || !window.L) return;

    const inputAlmacen = document.getElementById('almacen_origen');
    const almacenesIdsPedidos = @json($almacenesIdsConPedidos ?? '');

    function filtrarPedidosPorAlmacen() {
        const almId = inputAlmacen.value;
        document.querySelectorAll('#pedidos-sortable tr').forEach(function (tr) {
            const rowAlm = tr.dataset.almacenOrigenId || '';
            const visible = !almId || String(rowAlm) === String(almId);
            tr.style.display = visible ? '' : 'none';
            if (!visible) {
                const chk = tr.querySelector('.pedido-ruta-check');
                if (chk) {
                    chk.checked = false;
                    tr.classList.remove('selected');
                }
            }
        });
    }

    const state = {
        ubicacionesVisibles: false,
        cargandoUbicaciones: false,
    };

    let mapa = L.map(mapEl).setView([hub.lat, hub.lng], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19, attribution: '© OpenStreetMap',
    }).addTo(mapa);
    const capasRuta = L.layerGroup().addTo(mapa);
    const capasDisponibles = L.layerGroup();

    function flashMapaMsg(texto) {
        const el = document.getElementById('mapa-dist-flash');
        if (!el) return;
        el.textContent = texto;
        el.style.display = 'block';
        clearTimeout(flashMapaMsg._t);
        flashMapaMsg._t = setTimeout(function () { el.style.display = 'none'; }, 2200);
    }

    function iconoPlanta(seleccionado) {
        const sel = seleccionado ? ' is-selected' : '';
        return L.divIcon({
            html: '<div class="dist-mapa-pin' + sel + '" style="background:#dc2626"><i class="fas fa-industry"></i></div>',
            className: 'dist-mapa-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
        });
    }

    function iconoPdv(seleccionado) {
        const sel = seleccionado ? ' is-selected' : '';
        return L.divIcon({
            html: '<div class="dist-mapa-pin' + sel + '" style="background:#2563eb"><i class="fas fa-store"></i></div>',
            className: 'dist-mapa-marker',
            iconSize: [32, 32],
            iconAnchor: [16, 16],
        });
    }

    function filasVisiblesPdv(pdvId) {
        return Array.from(document.querySelectorAll('#pedidos-sortable tr')).filter(function (tr) {
            return tr.style.display !== 'none' && String(tr.dataset.pdvId) === String(pdvId);
        });
    }

    function pdvTienePedidoMarcado(pdvId) {
        return filasVisiblesPdv(pdvId).some(function (tr) {
            return tr.querySelector('.pedido-ruta-check')?.checked;
        });
    }

    function seleccionarPlantaDesdeMapa(item) {
        inputAlmacen.value = item.id;
        document.getElementById('txtAlmacenOrigen').value = item.label;
        onAlmacenCambiado();
        flashMapaMsg('Planta seleccionada: ' + item.label);
    }

    function togglePedidoDesdeMapa(pdvId, label) {
        const filas = filasVisiblesPdv(pdvId);
        if (!filas.length) {
            flashMapaMsg('No hay pedidos visibles para este PDV.');
            return;
        }
        const marcar = filas.some(function (tr) { return !tr.querySelector('.pedido-ruta-check').checked; });
        filas.forEach(function (tr) {
            const chk = tr.querySelector('.pedido-ruta-check');
            chk.checked = marcar;
            tr.classList.toggle('selected', marcar);
        });
        redibujarMapa();
        flashMapaMsg((marcar ? 'Incluido: ' : 'Quitado: ') + (label || 'PDV'));
    }

    function pintarUbicacionesDisponibles() {
        capasDisponibles.clearLayers();
        const bounds = [];

        almacenes.forEach(function (item) {
            const lat = parseFloat(item.extra?.lat ?? item.lat);
            const lng = parseFloat(item.extra?.lng ?? item.lng);
            if (isNaN(lat) || isNaN(lng)) return;

            const pedidos = item.extra?.pedidos ?? 0;
            const seleccionado = String(inputAlmacen.value) === String(item.id);
            const marker = L.marker([lat, lng], {
                icon: iconoPlanta(seleccionado),
                zIndexOffset: seleccionado ? 200 : 0,
            })
                .bindTooltip(item.label + (pedidos ? ' · ' + pedidos + ' ped.' : ''), {
                    className: 'dist-mapa-tooltip',
                    direction: 'top',
                    offset: [0, -14],
                })
                .addTo(capasDisponibles);
            marker.on('mouseover', function () { this.openTooltip(); });
            marker.on('click', function () { seleccionarPlantaDesdeMapa(item); });
            marker._distItem = { tipo: 'planta', id: item.id };
            bounds.push([lat, lng]);
        });

        puntosDisponibles.filter(function (p) { return p.tipo === 'pdv'; }).forEach(function (p) {
            const lat = parseFloat(p.lat);
            const lng = parseFloat(p.lng);
            if (isNaN(lat) || isNaN(lng)) return;

            const pedidos = p.pedidos ?? 0;
            const seleccionado = pdvTienePedidoMarcado(p.id);
            const marker = L.marker([lat, lng], {
                icon: iconoPdv(seleccionado),
                zIndexOffset: seleccionado ? 200 : 0,
            })
                .bindTooltip(p.label + (pedidos ? ' · ' + pedidos + ' ped.' : ''), {
                    className: 'dist-mapa-tooltip',
                    direction: 'top',
                    offset: [0, -14],
                })
                .addTo(capasDisponibles);
            marker.on('mouseover', function () { this.openTooltip(); });
            marker.on('click', function () { togglePedidoDesdeMapa(p.id, p.label); });
            marker._distItem = { tipo: 'pdv', id: p.id };
            bounds.push([lat, lng]);
        });

        return bounds;
    }

    function resaltarUbicacionesEnMapa() {
        if (!state.ubicacionesVisibles) return;
        capasDisponibles.eachLayer(function (layer) {
            if (!layer._distItem) return;
            const item = layer._distItem;
            if (item.tipo === 'planta') {
                const sel = String(inputAlmacen.value) === String(item.id);
                layer.setIcon(iconoPlanta(sel));
                layer.setZIndexOffset(sel ? 200 : 0);
            } else {
                const sel = pdvTienePedidoMarcado(item.id);
                layer.setIcon(iconoPdv(sel));
                layer.setZIndexOffset(sel ? 200 : 0);
            }
        });
    }

    function puntosRutaActuales() {
        const puntos = [];
        const alm = almacenSeleccionado();
        if (alm && alm.extra) {
            puntos.push({
                lat: parseFloat(alm.extra.lat),
                lng: parseFloat(alm.extra.lng),
            });
        }
        pedidosSeleccionados().forEach(function (tr) {
            const lat = parseFloat(tr.dataset.lat);
            const lng = parseFloat(tr.dataset.lng);
            if (!isNaN(lat) && !isNaN(lng)) {
                puntos.push({ lat: lat, lng: lng });
            }
        });
        return puntos;
    }

    function ajustarVistaMapa(boundsUbicaciones) {
        const puntos = (boundsUbicaciones || []).slice();
        puntosRutaActuales().forEach(function (p) { puntos.push([p.lat, p.lng]); });
        if (!puntos.length) return;
        try {
            mapa.fitBounds(L.latLngBounds(puntos).pad(0.12));
        } catch (e) {
            mapa.setView([puntos[0][0], puntos[0][1]], 12);
        }
    }

    function mostrarUbicacionesEnMapa() {
        if (!puntosDisponibles.length && !almacenes.length) {
            aviso('No hay ubicaciones con coordenadas para mostrar en el mapa.', 'Sin ubicaciones', 'info');
            return false;
        }
        const bounds = pintarUbicacionesDisponibles();
        if (!mapa.hasLayer(capasDisponibles)) {
            mapa.addLayer(capasDisponibles);
        }
        state.ubicacionesVisibles = true;
        document.getElementById('leyendaMapaDist').classList.remove('d-none');
        ajustarVistaMapa(bounds);
        return true;
    }

    function toggleUbicacionesEnMapa() {
        const btn = document.getElementById('btnVerUbicacionesMapa');
        if (state.cargandoUbicaciones) return;

        if (state.ubicacionesVisibles) {
            if (mapa.hasLayer(capasDisponibles)) {
                mapa.removeLayer(capasDisponibles);
            }
            state.ubicacionesVisibles = false;
            document.getElementById('leyendaMapaDist').classList.add('d-none');
            btn.innerHTML = '<i class="fas fa-map-marked-alt mr-1"></i> Ver en mapa';
            btn.classList.remove('active');
            return;
        }

        state.cargandoUbicaciones = true;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Cargando ubicaciones…';

        window.requestAnimationFrame(function () {
            try {
                if (mostrarUbicacionesEnMapa()) {
                    btn.innerHTML = '<i class="fas fa-eye-slash mr-1"></i> Ocultar ubicaciones';
                    btn.classList.add('active');
                }
            } catch (e) {
                console.error(e);
                if (mapa.hasLayer(capasDisponibles)) {
                    mapa.removeLayer(capasDisponibles);
                }
                state.ubicacionesVisibles = false;
                document.getElementById('leyendaMapaDist').classList.add('d-none');
                aviso('No se pudieron mostrar las ubicaciones. Intente de nuevo.', 'Error en mapa', 'danger');
                btn.innerHTML = '<i class="fas fa-map-marked-alt mr-1"></i> Ver en mapa';
                btn.classList.remove('active');
            } finally {
                state.cargandoUbicaciones = false;
                btn.disabled = false;
            }
        });
    }

    document.getElementById('btnVerUbicacionesMapa').addEventListener('click', toggleUbicacionesEnMapa);

    function almacenSeleccionado() {
        const id = inputAlmacen.value;
        return almacenes.find(function (a) { return String(a.id) === String(id); }) || null;
    }

    function pedidosSeleccionados() {
        const filas = [];
        document.querySelectorAll('#pedidos-sortable tr').forEach(function (tr) {
            const chk = tr.querySelector('.pedido-ruta-check');
            if (chk && chk.checked) filas.push(tr);
        });
        return filas;
    }

    window.calcularCargaRutaDistribucion = function () {
        let peso = 0;
        let volumen = 0;
        let tieneVol = false;
        pedidosSeleccionados().forEach(function (tr) {
            peso += parseFloat(tr.dataset.pesoKg) || 0;
            const v = parseFloat(tr.dataset.volumenM3);
            if (!isNaN(v) && v > 0) {
                volumen += v;
                tieneVol = true;
            }
        });
        return { peso_kg: peso, volumen_m3: tieneVol ? volumen : null };
    };

    async function redibujarMapa() {
        capasRuta.clearLayers();
        const puntos = [];
        const alm = almacenSeleccionado();
        if (alm && alm.extra) {
            puntos.push({
                lat: parseFloat(alm.extra.lat),
                lng: parseFloat(alm.extra.lng),
                orden: 1,
                label: alm.label,
            });
        }
        pedidosSeleccionados().forEach(function (tr, i) {
            const lat = parseFloat(tr.dataset.lat);
            const lng = parseFloat(tr.dataset.lng);
            if (!isNaN(lat) && !isNaN(lng)) {
                puntos.push({
                    lat: lat, lng: lng,
                    orden: puntos.length + 1,
                    label: tr.dataset.label || ('PDV ' + (i + 1)),
                });
            }
        });

        const resumenEl = document.getElementById('mapaDistResumen');
        if (puntos.length === 0) {
            resumenEl.textContent = state.ubicacionesVisibles
                ? 'Haga clic en una planta (rojo) o PDV (azul) para armar la ruta.'
                : 'Use «Ver en mapa» para ver plantas y PDV con pedidos, o seleccione arriba.';
            resaltarUbicacionesEnMapa();
            return;
        }

        let routeResult = null;
        if (puntos.length >= 2 && window.RutaPorCalles) {
            routeResult = await RutaPorCalles.fetchRoute(puntos);
        }
        if (window.RutaPorCalles) {
            RutaPorCalles.drawOnMap(mapa, capasRuta, puntos, routeResult);
            const km = routeResult?.distance_m ? (routeResult.distance_m / 1000).toFixed(1) : null;
            const min = routeResult?.duration_s ? Math.round(routeResult.duration_s / 60) : null;
            let txt = puntos.length + ' parada(s)';
            if (routeResult?.straight) {
                txt += ' · ruta estimada (línea recta)';
            } else if (km && min) {
                txt += ' · ~' + km + ' km · ~' + min + ' min';
            }
            resumenEl.textContent = txt;
        } else {
            puntos.forEach(function (p) {
                L.marker([p.lat, p.lng]).addTo(capasRuta).bindPopup(p.label);
            });
            resumenEl.textContent = puntos.length + ' parada(s) en el recorrido.';
        }

        if (!state.ubicacionesVisibles && puntos.length >= 1) {
            try {
                mapa.fitBounds(L.latLngBounds(puntos.map(function (p) { return [p.lat, p.lng]; })).pad(0.15));
            } catch (e) {}
        }
        resaltarUbicacionesEnMapa();
        setTimeout(function () { mapa.invalidateSize(); }, 150);
    }

    function onAlmacenCambiado() {
        filtrarPedidosPorAlmacen();
        redibujarMapa();
        resaltarUbicacionesEnMapa();
    }
    document.querySelectorAll('.pedido-ruta-check').forEach(function (chk) {
        chk.addEventListener('change', function () {
            chk.closest('tr').classList.toggle('selected', chk.checked);
            redibujarMapa();
        });
    });
    document.querySelectorAll('#pedidos-sortable tr').forEach(function (tr) {
        tr.addEventListener('click', function (e) {
            if (e.target.type === 'checkbox') return;
            const chk = tr.querySelector('.pedido-ruta-check');
            chk.checked = !chk.checked;
            tr.classList.toggle('selected', chk.checked);
            redibujarMapa();
        });
    });

    const inputTransportista = document.getElementById('transportista_planta_id');
    const inputVehiculo = document.getElementById('vehiculo_planta_id');
    const btnVehiculo = document.getElementById('btnBuscarVehiculoPlanta');

    if (window.CatalogoSelector) {
        if (!CatalogoSelector.modalEl) {
            CatalogoSelector.init();
        }
        CatalogoSelector.register('ruta_dist_almacen', {
            endpoint: @json(route('catalogo-selector.almacenes')),
            title: 'Almacén de planta (origen)',
            searchPlaceholder: 'Nombre o ubicación…',
            searchLabel: 'Buscar planta con pedidos',
            modalIcon: 'fa-industry',
            rowIcon: 'fa-warehouse',
            theme: 'origen',
            colNombre: 'Planta',
            colDetalle: 'Pedidos listos',
            params: {
                ambito: 'planta',
                almacenids_pedidos: almacenesIdsPedidos,
            },
            onSelect: function (item) {
                inputAlmacen.value = item.id;
                document.getElementById('txtAlmacenOrigen').value = item.label;
                onAlmacenCambiado();
                resaltarUbicacionesEnMapa();
            },
        });
        document.getElementById('btnBuscarAlmacenOrigen').addEventListener('click', function () {
            CatalogoSelector.open('ruta_dist_almacen');
        });
        CatalogoSelector.register('ruta_dist_transportista', {
            endpoint: @json(route('catalogo-selector.usuarios')),
            title: 'Transportista de planta',
            searchPlaceholder: 'Nombre o correo…',
            searchLabel: 'Buscar chofer',
            modalIcon: 'fa-id-card',
            rowIcon: 'fa-user-tie',
            theme: 'planta',
            colNombre: 'Chofer',
            colDetalle: 'Contacto',
            params: { roles: 'transportista', ambito_flota: 'planta' },
            onSelect: function (item) {
                inputTransportista.value = item.id;
                document.getElementById('txtTransportistaPlanta').value = item.label;
                inputVehiculo.value = '';
                document.getElementById('txtVehiculoPlanta').value = '';
                btnVehiculo.disabled = false;
                CatalogoSelector.instances.ruta_dist_vehiculo.params = {
                    transportista_usuarioid: item.id,
                    solo_transportista: '1',
                };
            },
        });
        CatalogoSelector.register('ruta_dist_vehiculo', {
            endpoint: @json(route('catalogo-selector.vehiculos')),
            title: 'Vehículo de reparto',
            searchPlaceholder: 'Placa, marca o modelo…',
            searchLabel: 'Buscar vehículo',
            modalIcon: 'fa-truck',
            rowIcon: 'fa-truck',
            theme: 'vehiculo',
            colNombre: 'Placa',
            colDetalle: 'Vehículo',
            params: { transportista_usuarioid: inputTransportista.value || '', solo_transportista: '1' },
            onSelect: function (item) {
                inputVehiculo.value = item.id;
                document.getElementById('txtVehiculoPlanta').value = item.label;
            },
        });
        document.getElementById('btnBuscarTransportistaPlanta').addEventListener('click', function () {
            CatalogoSelector.open('ruta_dist_transportista');
        });
        btnVehiculo.addEventListener('click', function () {
            if (inputTransportista.value) CatalogoSelector.open('ruta_dist_vehiculo');
        });
    }

    document.getElementById('formRutaDistribucion').addEventListener('submit', function (e) {
        if (!inputAlmacen.value) {
            e.preventDefault();
            aviso('Seleccione el almacén de planta donde se cargará la mercadería.', 'Falta origen', 'warning');
            return;
        }
        if (!inputTransportista.value) {
            e.preventDefault();
            aviso('Asigne un transportista de flota planta.', 'Falta chofer', 'warning');
            return;
        }
        if (!inputVehiculo.value) {
            e.preventDefault();
            aviso('Asigne el vehículo del chofer. La distribución siempre va en vehículo.', 'Falta vehículo', 'warning');
            return;
        }
        if (pedidosSeleccionados().length === 0) {
            e.preventDefault();
            aviso('Seleccione al menos un pedido para incluir en la ruta.', 'Sin pedidos', 'warning');
            return;
        }
        const costoInput = document.querySelector('input[name="costo_bs"]');
        const costo = costoInput ? parseFloat(String(costoInput.value).replace(',', '.')) : NaN;
        if (!Number.isFinite(costo) || costo <= 0) {
            e.preventDefault();
            aviso('Ingrese el costo del servicio en bolivianos (mayor a 0).', 'Falta costo', 'warning');
        }
    });

    filtrarPedidosPorAlmacen();
    redibujarMapa();
    document.getElementById('mapaDistResumen').textContent =
        'Use «Ver en mapa» para ver plantas y PDV con pedidos disponibles.';
});
</script>
@endpush
@endif
