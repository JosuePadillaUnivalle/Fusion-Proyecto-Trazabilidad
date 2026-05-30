@extends('layouts.app')

@section('title', 'Detalle de envío | AgroNexus')

@section('page_title', 'Detalle de envío')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.seguimiento') }}">Seguimiento</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
<style>
        .page-env-detalle .timeline-item {
            border-left: 3px solid #dee2e6;
            padding-left: 15px;
            margin-left: 5px;
        }

        .timeline-item.recogida {
            border-left-color: #28a745;
        }

        .page-env-detalle .timeline-item.entrega {
            border-left-color: #dc3545;
        }
</style>
@endpush

@section('content')
<div class="modulo-env page-env-detalle">

    <!-- Banner offline (solo cuando no hay conexión) -->
    <div id="offline-banner" class="offline-banner" style="display: none;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                <strong>Modo Offline</strong>
                <p class="mb-0 small text-muted">Mostrando datos guardados localmente. La información puede no estar
                    actualizada.</p>
            </div>
            <button class="btn btn-warning btn-sm" onclick="reintentarConexion()">
                <i class="fas fa-sync-alt"></i> Reintentar
            </button>
        </div>
    </div>

    <div class="card card-modulo-main">
        <div class="card-header">
            <h3 class="card-title mb-0"><i class="fas fa-info-circle text-success mr-2"></i>Envío #{{ $id }}</h3>
            <div class="card-tools">
                <a href="{{ route('envios.seguimiento') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al listado
                </a>
            </div>
        </div>
        <div class="card-body">
            <div id="detalleEnvio"></div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    <script>
        (function () {
            const LOCAL_API_URL = '{{ url('/envios/api') }}';
            const envioId = {{ $id ?? 0 }};
            const CACHE_KEY = `agronexus_envio_${envioId}`;
            const TIENE_DETALLE_SERVIDOR = @json($tieneDetalle ?? false);
            const envioServidor = @json($envioInicial ?? null);
            const FETCH_OPTS = { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } };
            const DETALLE_TIMEOUT_MS = 6000;

            const cont = document.getElementById('detalleEnvio');
            let conectado = true;
            let state = { envio: null };
            let leafletReady = null;

            const loaderHtml = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando información del envío...</div>';

            function cargarLeaflet() {
                if (window.L) return Promise.resolve();
                if (leafletReady) return leafletReady;
                leafletReady = new Promise((resolve, reject) => {
                    if (!document.querySelector('link[data-leaflet-css]')) {
                        const link = document.createElement('link');
                        link.rel = 'stylesheet';
                        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                        link.setAttribute('data-leaflet-css', '1');
                        document.head.appendChild(link);
                    }
                    const script = document.createElement('script');
                    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    script.onload = () => resolve();
                    script.onerror = () => reject(new Error('Leaflet no cargó'));
                    document.body.appendChild(script);
                });
                return leafletReady;
            }

            function normalizarDetalleEnvio(payload) {
                if (!payload || typeof payload !== 'object') return null;
                if (payload.data && typeof payload.data === 'object' && !Array.isArray(payload.data)) {
                    return payload.data;
                }
                return payload;
            }

            // ========================================
            // TOLERANCIA A FALLOS
            // ========================================

            async function verificarConexion() {
                try {
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), 3000);

                    const res = await fetch(`${LOCAL_API_URL}/ping`, {
                        ...FETCH_OPTS,
                        signal: controller.signal,
                        cache: 'no-store',
                    });

                    clearTimeout(timeoutId);
                    if (res.ok) {
                        const body = await res.json().catch(() => ({}));
                        conectado = body?.ok === true || res.ok;
                    } else {
                        conectado = false;
                    }
                } catch (e) {
                    conectado = false;
                }

                actualizarIndicador();
                return conectado;
            }

            function actualizarIndicador() {
                const banner = document.getElementById('offline-banner');
                banner.style.display = conectado ? 'none' : 'block';
            }

            function guardarEnCache(data) {
                localStorage.setItem(CACHE_KEY, JSON.stringify({
                    envio: data,
                    timestamp: Date.now()
                }));
            }

            function obtenerDeCache() {
                try {
                    const cached = localStorage.getItem(CACHE_KEY);
                    if (!cached) return null;
                    return JSON.parse(cached).envio;
                } catch (e) {
                    return null;
                }
            }

            // ========================================
            // RENDER
            // ========================================

            function badgeFor(estado) {
                const map = {
                    'En curso': 'badge-info',
                    'en_ruta': 'badge-info',
                    'Pendiente': 'badge-warning',
                    'pendiente': 'badge-warning',
                    'Asignado': 'badge-primary',
                    'asignado': 'badge-primary',
                    'Entregado': 'badge-success',
                    'entregado': 'badge-success',
                    'Completado': 'badge-success',
                    'Finalizado': 'badge-secondary',
                    'Completada': 'badge-success'
                };
                const cls = map[estado] || 'badge-light';
                return `<span class="badge ${cls} ml-2">${estado}</span>`;
            }

            function renderEnvio(envio, fromCache = false) {
                state.envio = envio;
                const particiones = Array.isArray(envio.particiones) ? envio.particiones : [];

                if (particiones.length === 0) {
                    const msg = envio._meta?.error || 'Este envío no tiene particiones o no se encontró en el sistema.';
                    cont.innerHTML = (fromCache ? '<div class="alert alert-warning mb-3"><strong>Datos offline</strong></div>' : '')
                        + `<div class="alert alert-info"><i class="fas fa-info-circle mr-2"></i>${msg}</div>`;
                    return;
                }

                const wrapper = document.createElement('div');

                if (fromCache) {
                    const notice = document.createElement('div');
                    notice.className = 'alert alert-warning mb-3';
                    notice.innerHTML = '<i class="fas fa-info-circle mr-2"></i><strong>Datos offline:</strong> Esta información puede no estar actualizada.';
                    wrapper.appendChild(notice);
                }

                const header = document.createElement('div');
                header.className = 'mb-4';
                header.innerHTML = `
                    <h4 class="mb-2">
                        ${envio.externo_envio_id || ('Envío #' + (envio.id || envioId))}
                        ${badgeFor(envio.estado || particiones[0]?.estado)}
                    </h4>
                    ${envio.numero_solicitud ? `<p class="text-muted mb-1"><strong>Solicitud:</strong> ${envio.numero_solicitud}</p>` : ''}
                    <p class="text-muted mb-0 small">${envio._meta?.mensaje || ''}</p>
                `;
                wrapper.appendChild(header);

                particiones.forEach((p, idx) => {
                    const card = document.createElement('div');
                    card.className = 'card mb-4';

                    const body = document.createElement('div');
                    body.className = 'card-body';

                    const row = document.createElement('div');
                    row.className = 'row';

                    const colLeft = document.createElement('div');
                    colLeft.className = 'col-lg-6';
                    colLeft.innerHTML = `
                    <h5 class="mb-3 d-flex align-items-center justify-content-between">
                        Partición ${idx + 1}
                        ${badgeFor(p.estado)}
                    </h5>

                    <div class="mb-3">
                        <h6 class="font-weight-bold">Transportista</h6>
                        <p class="mb-1">Nombre: ${(p.transportista?.nombre || '—')} ${(p.transportista?.apellido || '')}</p>
                        <p class="mb-1">Teléfono: ${p.transportista?.telefono || '—'}</p>
                        <p class="mb-0">CI: ${p.transportista?.ci || '—'}</p>
                    </div>

                    <div class="mb-3">
                        <h6 class="font-weight-bold">Vehículo</h6>
                        <p class="mb-0">Placa: ${p.vehiculo?.placa || '—'}</p>
                    </div>

                    <div class="mb-3">
                        <h6 class="font-weight-bold">Transporte</h6>
                        <p class="mb-1">Tipo: ${p.tipoTransporte?.nombre || '—'}</p>
                        <p class="mb-0">Descripción: ${p.tipoTransporte?.descripcion || '—'}</p>
                    </div>

                    <div class="timeline-item recogida mb-3">
                        <div class="mb-2">
                            <i class="fas fa-circle text-success mr-1" style="font-size: 0.5rem;"></i>
                            <strong>Recogida:</strong> ${p.recogidaEntrega?.fecha_recogida || '—'} – ${p.recogidaEntrega?.hora_recogida || '—'}
                        </div>
                        <div class="p-2 bg-light rounded">
                            <strong>Origen:</strong> ${envio.nombre_origen || '—'}<br>
                            ${Array.isArray(p.cargas) && p.cargas.length ? p.cargas.map(c => `
                                <div class="mt-1">• ${c.tipo} - ${c.variedad} (${Number(c.cantidad || 0)} uds, ${Number(c.peso || 0).toFixed(1)} kg)</div>
                            `).join('') : '<div class="mt-1">Sin productos</div>'}
                            <div class="mt-2 text-muted" style="font-size: 0.9rem;">
                                ${p.recogidaEntrega?.instrucciones_recogida || 'Sin instrucciones'}
                            </div>
                        </div>
                    </div>

                    <div class="timeline-item entrega">
                        <div class="mb-2">
                            <i class="fas fa-circle text-danger mr-1" style="font-size: 0.5rem;"></i>
                            <strong>Entrega:</strong> ${p.recogidaEntrega?.fecha_recogida || '—'} – ${p.recogidaEntrega?.hora_entrega || '—'}
                        </div>
                        <div class="p-2 bg-light rounded">
                            <strong>Destino:</strong> ${envio.nombre_destino || '—'}<br>
                            ${Array.isArray(p.cargas) && p.cargas.length ? p.cargas.map(c => `
                                <div class="mt-1">• ${c.tipo} - ${c.variedad} (${Number(c.cantidad || 0)} uds, ${Number(c.peso || 0).toFixed(1)} kg)</div>
                            `).join('') : '<div class="mt-1">Sin productos</div>'}
                            <div class="mt-2 text-muted" style="font-size: 0.9rem;">
                                ${p.recogidaEntrega?.instrucciones_entrega || 'Sin instrucciones'}
                            </div>
                        </div>
                    </div>
                `;

                    const colRight = document.createElement('div');
                    colRight.className = 'col-lg-6';
                    const mapId = `map-${idx}`;
                    colRight.innerHTML = `
                    <div id="${mapId}" style="height: 420px;" class="rounded border mb-3"></div>
                    <div class="p-3 bg-light rounded border text-center">
                        <h6 class="mb-2 font-weight-bold">Código de Acceso</h6>
                        <p class="mb-0 h4 text-primary" style="font-family: monospace; letter-spacing: 3px;">${p.codigo_acceso || 'No asignado'}</p>
                    </div>
                `;

                    row.appendChild(colLeft);
                    row.appendChild(colRight);
                    body.appendChild(row);

                    if (p.id_transportista && p.id_vehiculo) {
                        const alertSection = document.createElement('div');
                        alertSection.className = 'mt-3';
                        alertSection.innerHTML = `
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle mr-2"></i>
                            <strong>Recursos confirmados</strong><br>
                            Esta partición ya tiene transportista y vehículo asignados.
                        </div>
                    `;
                        body.appendChild(alertSection);
                    }

                    card.appendChild(body);
                    wrapper.appendChild(card);

                    // Mapa: carga diferida (no bloquea el detalle)
                    const mapEl = colRight.querySelector(`#${mapId}`);
                    if (mapEl) {
                        mapEl.innerHTML = '<div class="text-center text-muted py-5"><i class="fas fa-map mr-1"></i> Cargando mapa...</div>';
                        const initMapa = () => {
                        try {
                            mapEl.innerHTML = '';
                            const map = L.map(mapId).setView([
                                envio.coordenadas_origen?.lat || -17.3935,
                                envio.coordenadas_origen?.lng || -66.1570
                            ], 12);

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '© OpenStreetMap'
                            }).addTo(map);

                            const o = [envio.coordenadas_origen?.lat, envio.coordenadas_origen?.lng];
                            const d = [envio.coordenadas_destino?.lat, envio.coordenadas_destino?.lng];

                            const iconoOrigen = L.icon({
                                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-green.png',
                                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                                iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
                            });

                            const iconoDestino = L.icon({
                                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
                                iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41]
                            });

                            if (o[0] && o[1]) {
                                L.marker(o, { icon: iconoOrigen }).addTo(map).bindPopup(`<strong>Origen:</strong><br>${envio.nombre_origen || 'N/A'}`);
                            }
                            if (d[0] && d[1]) {
                                L.marker(d, { icon: iconoDestino }).addTo(map).bindPopup(`<strong>Destino:</strong><br>${envio.nombre_destino || 'N/A'}`);
                            }

                            async function dibujarRutaEnvio() {
                                const estilo = (straight) => ({
                                    color: straight ? '#e67e22' : '#2563eb',
                                    weight: 5,
                                    opacity: 0.85,
                                    dashArray: straight ? '8,8' : null,
                                });
                                if (envio.rutaGeoJSON) {
                                    try {
                                        const gj = JSON.parse(envio.rutaGeoJSON);
                                        const layer = L.geoJSON(gj, { style: estilo(false) }).addTo(map);
                                        map.fitBounds(layer.getBounds(), { padding: [20, 20] });
                                        return;
                                    } catch (e) {}
                                }
                                if (!o[0] || !d[0]) return;
                                const cargarRuta = () => {
                                    if (!window.RutaPorCalles) return Promise.reject();
                                    return RutaPorCalles.fetchRoute([
                                        { lat: o[0], lng: o[1] },
                                        { lat: d[0], lng: d[1] },
                                    ]);
                                };
                                const conScript = window.RutaPorCalles
                                    ? cargarRuta()
                                    : new Promise((resolve, reject) => {
                                        const s = document.createElement('script');
                                        s.src = @json(asset('js/ruta-por-calles.js'));
                                        s.onload = () => cargarRuta().then(resolve).catch(reject);
                                        s.onerror = reject;
                                        document.body.appendChild(s);
                                    });
                                try {
                                    const routeResult = await conScript;
                                    if (routeResult?.geojson) {
                                        const layer = L.geoJSON(routeResult.geojson, { style: estilo(routeResult.straight) }).addTo(map);
                                        map.fitBounds(layer.getBounds(), { padding: [20, 20] });
                                        return;
                                    }
                                } catch (e) {}
                                const line = L.polyline([o, d], { color: '#e67e22', weight: 4, dashArray: '8,8', opacity: 0.7 }).addTo(map);
                                map.fitBounds(line.getBounds(), { padding: [20, 20] });
                            }
                            dibujarRutaEnvio();
                        } catch (e) {
                            console.error('Error inicializando mapa:', e);
                            mapEl.innerHTML = '<div class="alert alert-light mb-0 text-center small">Mapa no disponible</div>';
                        }
                        };
                        cargarLeaflet().then(initMapa).catch(() => {
                            mapEl.innerHTML = '<div class="alert alert-light mb-0 text-center small">Mapa no disponible sin conexión externa</div>';
                        });
                    }
                });

                cont.innerHTML = '';
                cont.appendChild(wrapper);
            }

            // ========================================
            // CARGA DE DATOS
            // ========================================

            async function fetchDetalleRed() {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), DETALLE_TIMEOUT_MS);
                try {
                    const res = await fetch(`${LOCAL_API_URL}/envios/${envioId}`, {
                        ...FETCH_OPTS,
                        signal: controller.signal,
                        cache: 'no-store',
                    });
                    clearTimeout(timeoutId);
                    if (!res.ok) throw new Error('No se pudo cargar el envío');
                    const raw = await res.json();
                    const envio = normalizarDetalleEnvio(raw);
                    if (!envio) throw new Error('Respuesta vacía');
                    guardarEnCache(envio);
                    conectado = true;
                    actualizarIndicador();
                    return envio;
                } catch (error) {
                    clearTimeout(timeoutId);
                    console.warn('Error cargando envío:', error);
                    return null;
                }
            }

            async function cargarEnvio(opciones = {}) {
                const { silencioso = false, forzarRed = false } = opciones;

                if (!silencioso && !state.envio) {
                    cont.innerHTML = loaderHtml;
                }

                if (!forzarRed && TIENE_DETALLE_SERVIDOR && envioServidor) {
                    guardarEnCache(envioServidor);
                    renderEnvio(envioServidor, false);
                    fetchDetalleRed().then(envio => {
                        if (envio && JSON.stringify(envio) !== JSON.stringify(envioServidor)) {
                            renderEnvio(envio, false);
                        }
                    });
                    return;
                }

                const envio = await fetchDetalleRed();
                if (envio) {
                    renderEnvio(envio, false);
                    return;
                }

                const cached = obtenerDeCache();
                if (cached) {
                    conectado = false;
                    actualizarIndicador();
                    renderEnvio(cached, true);
                    return;
                }

                cont.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i>No se pudo cargar el envío. <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="reintentarConexion()">Reintentar</button></div>`;
            }

            window.reintentarConexion = function () {
                cargarEnvio({ forzarRed: true });
            };

            if (TIENE_DETALLE_SERVIDOR && envioServidor) {
                conectado = true;
                actualizarIndicador();
                guardarEnCache(envioServidor);
                renderEnvio(envioServidor, false);
            } else {
                cargarEnvio();
            }
        })();
    </script>
@endpush