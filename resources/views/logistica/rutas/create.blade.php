@extends('layouts.app')
@push('styles')
@include('logistica.partials.ayuda-logistica-styles')
@include('logistica.partials.mapa-ruta-styles')
<style>
.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
.section-title{font-weight:700;color:#2c5530}
#mapaRutaEntrega { height: 480px; min-height: 480px; }
.envio-pick-row:hover{background:#f8fafc}
.envio-pick-row.is-hidden{display:none!important}
.envio-pick-row.is-selected{background:#e8f5e9}
.envio-pick-row .custom-control{padding-left:1.75rem;min-height:auto}
.envio-pick-row .custom-control-label{cursor:pointer;width:100%;line-height:1.4}
.envio-pick-row .custom-control-label::before,
.envio-pick-row .custom-control-label::after{top:.15rem;left:-1.75rem}
.envio-mapa-marker.is-dimmed{opacity:.35}
.envio-mapa-marker.is-dimmed .envio-mapa-marker-label{background:#94a3b8}
.filtros-envios-ruta .form-control{height:calc(1.5em + .5rem + 2px)}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Crear ruta de entrega</h1>
        <p class="text-muted mb-0">Seleccione envíos en el mapa o en la lista, filtre por nombre y arme el recorrido.</p>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong><i class="fas fa-exclamation-triangle mr-1"></i> Revise lo siguiente:</strong>
                <ul class="mb-0 mt-2 pl-3">
                    @foreach($errors->all() as $error)
                        <li>{{ str_contains($error, 'pedidoid') ? 'El número de pedido no existe en el sistema. Déjelo vacío si no está seguro.' : $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($enviosParaRuta->isNotEmpty())
        <div class="card x-card mb-3 border-success">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title mb-0"><i class="fas fa-map-marked-alt mr-1"></i> Envíos disponibles para ruta</h3>
                <span class="badge badge-light text-success" id="badge-envios-visibles">{{ $enviosMapa->count() }} envíos</span>
            </div>
            <div class="card-body">
                <div class="row filtros-envios-ruta mb-3">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Buscar por código, destino o chofer</label>
                        <input type="search" id="buscar-envios-ruta" class="form-control form-control-sm"
                            placeholder="Ej: Quillacollo, ENV-MOD, Carlos…" autocomplete="off">
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Situación</label>
                        <select id="filtro-estado-envios" class="form-control form-control-sm">
                            <option value="">Todas</option>
                            @foreach($enviosMapa->pluck('estado')->unique()->sort() as $est)
                                <option value="{{ $est }}">{{ $est }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="filtro-solo-ubicacion" checked>
                            <label class="custom-control-label" for="filtro-solo-ubicacion">Solo con ubicación en mapa</label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8 mb-3 mb-lg-0">
                        <div id="mapaRutaEntrega"></div>
                        <p class="ruta-mapa-leyenda mt-2 mb-0">
                            <i class="fas fa-mouse-pointer mr-1"></i> Clic en un punto del mapa para marcar o desmarcar el envío.
                            Los seleccionados se muestran en <span class="text-success font-weight-bold">verde</span>.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="border rounded bg-light d-flex flex-column" style="max-height:480px;">
                            <div class="p-2 border-bottom bg-white d-flex justify-content-between align-items-center">
                                <strong class="small mb-0">Lista de envíos</strong>
                                <input type="checkbox" id="marcar-todos-envios" title="Marcar todos los visibles">
                            </div>
                            <div class="overflow-auto flex-grow-1" id="lista-envios-ruta" style="max-height:360px;">
                                @foreach($enviosMapa as $e)
                                    <div class="envio-pick-row p-2 border-bottom"
                                        data-id="{{ $e['id'] }}"
                                        data-codigo="{{ $e['codigo'] }}"
                                        data-destino="{{ $e['destino'] ?? '' }}"
                                        data-pedido="{{ $e['pedidoid'] ?? '' }}"
                                        data-chofer="{{ $e['chofer_id'] ?? '' }}"
                                        data-chofer-nombre="{{ $e['chofer'] }}"
                                        data-estado="{{ $e['estado'] }}"
                                        data-lat="{{ $e['lat'] }}"
                                        data-lng="{{ $e['lng'] }}"
                                        data-tiene-ubicacion="{{ $e['lat'] && $e['lng'] ? '1' : '0' }}">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input envio-ruta-check"
                                                id="env-ruta-{{ $e['id'] }}"
                                                value="{{ $e['id'] }}"
                                                data-codigo="{{ $e['codigo'] }}"
                                                data-destino="{{ $e['destino'] ?? ('Entrega '.$e['codigo']) }}"
                                                data-pedido="{{ $e['pedidoid'] ?? '' }}"
                                                data-chofer="{{ $e['chofer_id'] ?? '' }}"
                                                data-lat="{{ $e['lat'] }}"
                                                data-lng="{{ $e['lng'] }}"
                                                @disabled(!$e['lat'] || !$e['lng'])>
                                            <label class="custom-control-label" for="env-ruta-{{ $e['id'] }}">
                                                <strong>{{ $e['codigo'] }}</strong>
                                                <small class="d-block text-muted">{{ $e['destino'] ?? 'Sin destino' }}</small>
                                                <small class="d-block">{{ $e['chofer'] }} · {{ $e['estado'] }}</small>
                                                @if(!$e['lat'] || !$e['lng'])
                                                    <small class="text-warning">Sin coordenadas — no aparece en mapa</small>
                                                @elseif($e['ubicacion_aproximada'])
                                                    <small class="text-info">Ubicación aproximada por ciudad</small>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="p-2 border-top bg-white">
                                <button type="button" class="btn btn-success btn-sm btn-block" id="btn-cargar-envios">
                                    <i class="fas fa-download mr-1"></i> Usar seleccionados como paradas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i> No hay envíos pendientes sin ruta. Puede crear la ruta manualmente abajo.
        </div>
        @endif

        <div class="card x-card">
            <div class="card-body">
                <form method="POST" action="{{ route('logistica.rutas.store') }}" id="form-ruta">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nombre de la ruta <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input name="nombre" id="nombre-ruta" class="form-control @error('nombre') is-invalid @enderror" required
                                    placeholder="Ej: Entregas zona sur - tarde" value="{{ old('nombre') }}">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="btn-nombre-auto" title="Sugerir nombre según fecha, paradas y chofer">
                                        <i class="fas fa-magic"></i> Sugerir
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">Use «Sugerir» después de elegir envíos y chofer.</small>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Chofer asignado</label>
                            @php $drivers = \App\Models\Usuario::where('role','transportista')->where('activo', true)->orderBy('nombre')->get(); @endphp
                            <select name="transportista_usuarioid" id="chofer-ruta" class="form-control">
                                <option value="">Sin asignar aún</option>
                                @foreach($drivers as $d)
                                    <option value="{{ $d->usuarioid }}" @selected(old('transportista_usuarioid') == $d->usuarioid)>
                                        {{ $d->nombre }} {{ $d->apellido }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Fecha y hora de salida</label>
                            <input type="datetime-local" name="fecha_salida" id="fecha-salida" class="form-control" value="{{ old('fecha_salida') }}">
                            <small class="text-muted">Opcional</small>
                        </div>
                    </div>

                    <hr>
                    <h5 class="section-title"><i class="fas fa-map-pin mr-1"></i> Paradas del recorrido</h5>
                    <p class="text-muted small mb-2">
                        Solo complete <strong>código de envío</strong> y/o <strong>lugar</strong>.
                        El <strong>nº de pedido</strong> es opcional: si no conoce el ID exacto en el sistema, déjelo vacío.
                    </p>

                    <div id="paradas-wrapper">
                        @php $paradasOld = old('paradas', [['destino' => '', 'externo_envio_id' => '', 'pedidoid' => '']]); @endphp
                        @foreach($paradasOld as $i => $parada)
                        <div class="row parada-item border rounded p-2 mb-2 bg-light">
                            <div class="col-md-5 form-group mb-md-0">
                                <label class="small">Lugar o cliente</label>
                                <input name="paradas[{{ $i }}][destino]" class="form-control" placeholder="Ej: Mercado central"
                                    value="{{ $parada['destino'] ?? '' }}">
                            </div>
                            <div class="col-md-4 form-group mb-md-0">
                                <label class="small">Código de envío</label>
                                <input name="paradas[{{ $i }}][externo_envio_id]" class="form-control" placeholder="Ej: ENV-2026-0001"
                                    value="{{ $parada['externo_envio_id'] ?? '' }}">
                            </div>
                            <div class="col-md-2 form-group mb-md-0">
                                <label class="small">Nº pedido <span class="text-muted">(opc.)</span></label>
                                <input type="number" name="paradas[{{ $i }}][pedidoid]" class="form-control parada-pedido" min="1" step="1"
                                    placeholder="Vacío" value="{{ $parada['pedidoid'] ?? '' }}">
                            </div>
                            <div class="col-md-1 form-group mb-md-0 d-flex align-items-end">
                                @if($i > 0)
                                <button type="button" class="btn btn-outline-danger btn-sm remove-parada" title="Quitar">×</button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-outline-secondary mb-3" id="add-parada">
                        <i class="fas fa-plus mr-1"></i> Agregar otra parada vacía
                    </button>
                    <br>
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-save mr-1"></i> Guardar ruta
                    </button>
                    <a href="{{ route('logistica.rutas.index') }}" class="btn btn-outline-secondary btn-lg ml-1">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</section>

@push('scripts')
@include('logistica.partials.mapa-ruta-libs')
<script>
document.addEventListener('DOMContentLoaded', () => {
(() => {
    const enviosCatalogo = @json($enviosMapa->values());
    const hub = { lat: -17.7833, lng: -63.1821 };
    let mapaRuta = null;
    let capasMarcadores = null;
    let capasTrazado = null;
    const markersPorId = {};

    function esperarMapaLibs(cb, intentos = 0) {
        if (window.L && window.RutaPorCalles) {
            cb();
            return;
        }
        if (intentos > 80) {
            console.error('No se pudo cargar Leaflet / RutaPorCalles');
            return;
        }
        setTimeout(() => esperarMapaLibs(cb, intentos + 1), 50);
    }

    function initMapaRuta() {
        const el = document.getElementById('mapaRutaEntrega');
        if (!el || mapaRuta) return mapaRuta;
        mapaRuta = L.map(el, { scrollWheelZoom: true }).setView([hub.lat, hub.lng], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }).addTo(mapaRuta);
        capasMarcadores = L.layerGroup().addTo(mapaRuta);
        capasTrazado = L.layerGroup().addTo(mapaRuta);
        setTimeout(() => mapaRuta.invalidateSize(), 200);
        return mapaRuta;
    }

    function filtroActivo() {
        const q = (document.getElementById('buscar-envios-ruta')?.value || '').trim().toLowerCase();
        const estado = document.getElementById('filtro-estado-envios')?.value || '';
        const soloUbic = document.getElementById('filtro-solo-ubicacion')?.checked ?? false;
        return { q, estado, soloUbic };
    }

    function filaCoincideFiltro(row) {
        const { q, estado, soloUbic } = filtroActivo();
        const texto = [
            row.dataset.codigo,
            row.dataset.destino,
            row.dataset.choferNombre,
            row.dataset.estado,
        ].join(' ').toLowerCase();

        if (q && !texto.includes(q)) return false;
        if (estado && row.dataset.estado !== estado) return false;
        if (soloUbic && row.dataset.tieneUbicacion !== '1') return false;
        return true;
    }

    function aplicarFiltrosLista() {
        let visibles = 0;
        document.querySelectorAll('.envio-pick-row').forEach(row => {
            const ok = filaCoincideFiltro(row);
            row.classList.toggle('is-hidden', !ok);
            if (ok) visibles++;
            const id = row.dataset.id;
            const markerWrap = markersPorId[id];
            if (markerWrap) {
                markerWrap.classList.toggle('is-dimmed', !ok);
            }
        });
        const badge = document.getElementById('badge-envios-visibles');
        if (badge) badge.textContent = visibles + ' visibles';
    }

    function syncFilaSeleccion(id) {
        const chk = document.getElementById(`env-ruta-${id}`);
        const row = document.querySelector(`.envio-pick-row[data-id="${id}"]`);
        if (row) row.classList.toggle('is-selected', !!chk?.checked);
        const marker = markersPorId[id];
        if (marker) {
            const label = marker.querySelector('.envio-mapa-marker-label');
            if (label) label.classList.toggle('is-selected', !!chk?.checked);
        }
    }

    function puntosSeleccionados() {
        const puntos = [];
        const vistos = new Set();
        document.querySelectorAll('.envio-ruta-check:checked').forEach(c => {
            const lat = parseFloat(c.dataset.lat);
            const lng = parseFloat(c.dataset.lng);
            const codigo = c.dataset.codigo || '';
            if (!isNaN(lat) && !isNaN(lng) && !vistos.has(codigo)) {
                vistos.add(codigo);
                puntos.push({
                    lat,
                    lng,
                    orden: puntos.length + 1,
                    label: (c.dataset.destino || codigo),
                });
            }
        });
        return puntos;
    }

    function dibujarMarcadoresDisponibles() {
        initMapaRuta();
        if (!capasMarcadores || !mapaRuta) return;

        Object.keys(markersPorId).forEach(k => delete markersPorId[k]);
        capasMarcadores.clearLayers();
        capasTrazado?.clearLayers();

        const conCoords = enviosCatalogo.filter(e => e.lat && e.lng);
        const bounds = [];

        conCoords.forEach(e => {
            const labelHtml = `<span class="envio-mapa-marker-label" data-env-id="${e.id}">${e.codigo}</span>`;
            const buildIcon = (anchor) => L.divIcon({
                className: 'envio-mapa-marker',
                html: labelHtml,
                iconAnchor: anchor || [0, 0],
            });
            const m = L.marker([e.lat, e.lng], { icon: buildIcon() }).addTo(capasMarcadores)
                .bindPopup(`<strong>${e.codigo}</strong><br>${e.destino || ''}<br><small>${e.chofer}</small>`);
            const el = m.getElement();
            if (el) {
                markersPorId[e.id] = el;
                const w = el.offsetWidth || 120;
                const h = el.offsetHeight || 24;
                m.setIcon(buildIcon([Math.round(w / 2), h]));
                syncFilaSeleccion(e.id);
            }
            bounds.push([e.lat, e.lng]);
            m.on('click', () => {
                const chk = document.getElementById(`env-ruta-${e.id}`);
                if (chk && !chk.disabled) {
                    chk.checked = !chk.checked;
                    syncFilaSeleccion(e.id);
                    actualizarRutaTrazada();
                }
            });
        });

        aplicarFiltrosLista();

        if (bounds.length) {
            mapaRuta.fitBounds(L.latLngBounds(bounds), { padding: [36, 36] });
        } else {
            mapaRuta.setView([hub.lat, hub.lng], 6);
        }
    }

    async function actualizarRutaTrazada() {
        initMapaRuta();
        if (!capasMarcadores || !capasTrazado || !mapaRuta) return;

        const puntos = puntosSeleccionados();
        capasTrazado.clearLayers();

        if (puntos.length === 0) {
            dibujarMarcadoresDisponibles();
            return;
        }

        if (Object.keys(markersPorId).length === 0) {
            dibujarMarcadoresDisponibles();
        }

        if (puntos.length === 1) {
            if (window.RutaPorCalles) {
                RutaPorCalles.drawOnMap(mapaRuta, capasTrazado, puntos, null);
            }
            mapaRuta.setView([puntos[0].lat, puntos[0].lng], 13);
            enviosCatalogo.filter(e => e.lat && e.lng).forEach(e => syncFilaSeleccion(e.id));
            return;
        }

        const routeResult = window.RutaPorCalles
            ? await RutaPorCalles.fetchRoute(puntos)
            : null;
        if (window.RutaPorCalles) {
            RutaPorCalles.drawOnMap(mapaRuta, capasTrazado, puntos, routeResult);
        }
        enviosCatalogo.filter(e => e.lat && e.lng).forEach(e => syncFilaSeleccion(e.id));
    }

    const wrapper = document.getElementById('paradas-wrapper');
    const addBtn = document.getElementById('add-parada');
    let idx = {{ count(old('paradas', [['x']])) }};

    function nuevaFilaParada(destino, codigo, pedido, chofer) {
        const row = document.createElement('div');
        row.className = 'row parada-item border rounded p-2 mb-2 bg-light';
        const pedidoVal = pedido ? String(pedido) : '';
        row.innerHTML = `
            <div class="col-md-5 form-group mb-md-0">
                <label class="small">Lugar o cliente</label>
                <input name="paradas[${idx}][destino]" class="form-control" value="${(destino || '').replace(/"/g, '&quot;')}">
            </div>
            <div class="col-md-4 form-group mb-md-0">
                <label class="small">Código de envío</label>
                <input name="paradas[${idx}][externo_envio_id]" class="form-control" value="${(codigo || '').replace(/"/g, '&quot;')}">
            </div>
            <div class="col-md-2 form-group mb-md-0">
                <label class="small">Nº pedido (opc.)</label>
                <input type="number" name="paradas[${idx}][pedidoid]" class="form-control parada-pedido" min="1" step="1" value="${pedidoVal}">
            </div>
            <div class="col-md-1 form-group mb-md-0 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger btn-sm remove-parada" title="Quitar">×</button>
            </div>
        `;
        wrapper.appendChild(row);
        idx++;
        if (chofer) {
            const sel = document.getElementById('chofer-ruta');
            if (sel && !sel.value) sel.value = String(chofer);
        }
    }

    if (addBtn) {
        addBtn.addEventListener('click', () => nuevaFilaParada('', '', '', null));
    }

    if (wrapper) {
        wrapper.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-parada')) {
                e.target.closest('.parada-item').remove();
            }
        });
    }

    const marcarTodos = document.getElementById('marcar-todos-envios');
    if (marcarTodos) {
        marcarTodos.addEventListener('change', function () {
            document.querySelectorAll('.envio-pick-row:not(.is-hidden) .envio-ruta-check:not(:disabled)').forEach(c => {
                c.checked = marcarTodos.checked;
                const id = c.id.replace('env-ruta-', '');
                syncFilaSeleccion(id);
            });
            actualizarRutaTrazada();
        });
    }

    document.querySelectorAll('.envio-ruta-check').forEach(c => {
        c.addEventListener('change', () => {
            syncFilaSeleccion(c.id.replace('env-ruta-', ''));
            actualizarRutaTrazada();
        });
    });

    document.querySelectorAll('.envio-pick-row').forEach(row => {
        row.addEventListener('click', (ev) => {
            if (ev.target.closest('input[type="checkbox"]') || ev.target.closest('label')) return;
            const id = row.dataset.id;
            const chk = document.getElementById(`env-ruta-${id}`);
            if (chk && !chk.disabled) {
                chk.checked = !chk.checked;
                syncFilaSeleccion(id);
                actualizarRutaTrazada();
            }
        });
    });

    ['buscar-envios-ruta', 'filtro-estado-envios', 'filtro-solo-ubicacion'].forEach(id => {
        const el = document.getElementById(id);
        if (!el) return;
        const evt = el.type === 'checkbox' || el.tagName === 'SELECT' ? 'change' : 'input';
        el.addEventListener(evt, aplicarFiltrosLista);
    });

    const btnCargar = document.getElementById('btn-cargar-envios');
    if (btnCargar) {
        btnCargar.addEventListener('click', () => {
            const checks = Array.from(document.querySelectorAll('.envio-ruta-check:checked'));
            if (!checks.length) {
                alert('Marque al menos un envío en el mapa o en la lista.');
                return;
            }
            wrapper.innerHTML = '';
            idx = 0;
            checks.forEach(c => {
                nuevaFilaParada(
                    c.getAttribute('data-destino'),
                    c.getAttribute('data-codigo'),
                    c.getAttribute('data-pedido'),
                    c.getAttribute('data-chofer')
                );
            });
            sugerirNombreRuta();
            document.getElementById('form-ruta').scrollIntoView({ behavior: 'smooth' });
        });
    }

    function sugerirNombreRuta() {
        const nombre = document.getElementById('nombre-ruta');
        if (!nombre) return;
        const chofer = document.getElementById('chofer-ruta');
        const fecha = new Date().toLocaleDateString('es-BO');
        const checks = document.querySelectorAll('.envio-ruta-check:checked');
        const n = checks.length;
        const partes = ['Entregas', fecha];
        if (n > 0) {
            partes.push(n + ' parada' + (n === 1 ? '' : 's'));
            const destinos = Array.from(checks).map(c => (c.dataset.destino || '').trim()).filter(Boolean);
            if (destinos.length === 1) {
                partes.push(destinos[0]);
            } else if (destinos.length > 1) {
                partes.push(destinos[0] + ' y otros');
            }
        }
        if (chofer?.value) {
            const txt = chofer.options[chofer.selectedIndex]?.text?.trim();
            if (txt && txt !== 'Sin asignar aún') partes.push(txt);
        }
        nombre.value = partes.join(' — ');
    }

    const btnNombre = document.getElementById('btn-nombre-auto');
    if (btnNombre) {
        btnNombre.addEventListener('click', sugerirNombreRuta);
    }

    const choferSel = document.getElementById('chofer-ruta');
    if (choferSel) {
        choferSel.addEventListener('change', () => {
            const nombre = document.getElementById('nombre-ruta');
            if (nombre && !nombre.value.trim()) sugerirNombreRuta();
        });
    }

    const desdeMapa = sessionStorage.getItem('ruta_desde_mapa');
    if (desdeMapa) {
        try {
            const items = JSON.parse(desdeMapa);
            sessionStorage.removeItem('ruta_desde_mapa');
            if (items.length && wrapper) {
                wrapper.innerHTML = '';
                idx = 0;
                items.forEach(it => nuevaFilaParada(it.destino, it.codigo, it.pedidoid, null));
                items.forEach(it => {
                    document.querySelectorAll('.envio-ruta-check').forEach(c => {
                        if (c.dataset.codigo === it.codigo) {
                            c.checked = true;
                            syncFilaSeleccion(c.id.replace('env-ruta-', ''));
                        }
                    });
                });
                sugerirNombreRuta();
                setTimeout(actualizarRutaTrazada, 400);
            }
        } catch (e) {}
    }

    const formRuta = document.getElementById('form-ruta');
    if (formRuta) {
        formRuta.addEventListener('submit', function () {
            document.querySelectorAll('.parada-pedido').forEach(function (input) {
                if (!input.value || input.value === '0') {
                    input.removeAttribute('name');
                }
            });
        });
    }

    esperarMapaLibs(() => {
        initMapaRuta();
        dibujarMarcadoresDisponibles();
    });
})();
});
</script>
@endpush
@endsection
