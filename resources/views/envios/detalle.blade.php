@extends('layouts.app')

@section('title', 'Seguimiento del Envío')

@section('page_title', 'Seguimiento del Envío')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.seguimiento') }}">Seguimiento</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

@section('content')
    <style>
        /* Indicador de conexión */
        #conexion-status {
            position: fixed;
            top: 70px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .status-online {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .status-offline {
            background: linear-gradient(135deg, #dc3545, #e74a3b);
            color: white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }

        .offline-banner {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .timeline-item {
            border-left: 3px solid #dee2e6;
            padding-left: 15px;
            margin-left: 5px;
        }

        .timeline-item.recogida {
            border-left-color: #28a745;
        }

        .timeline-item.entrega {
            border-left-color: #dc3545;
        }
    </style>

    <!-- Indicador de conexión -->
    <div id="conexion-status" class="status-online">
        <i class="fas fa-wifi" id="conexion-icon"></i>
        <span id="conexion-text">Conectado</span>
    </div>

    <!-- Banner offline -->
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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div id="detalleEnvio"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        (function () {
            const LOCAL_API_URL = '/envios/api';
            const envioId = {{ $id ?? 0 }};
            const CACHE_KEY = `agronexus_envio_${envioId}`;
            const FETCH_OPTS = { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } };

            const cont = document.getElementById('detalleEnvio');
            let conectado = true;
            let state = { envio: null };

            const loaderHtml = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Cargando información del envío...</div>';

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
                const status = document.getElementById('conexion-status');
                const icon = document.getElementById('conexion-icon');
                const text = document.getElementById('conexion-text');
                const banner = document.getElementById('offline-banner');

                if (conectado) {
                    status.className = 'status-online';
                    icon.className = 'fas fa-wifi';
                    text.textContent = 'Conectado';
                    banner.style.display = 'none';
                } else {
                    status.className = 'status-offline';
                    icon.className = 'fas fa-wifi-slash';
                    text.textContent = 'Sin Conexión';
                    banner.style.display = 'block';
                }
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

                    // Inicializar mapa
                    setTimeout(() => {
                        try {
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

                            // Dibujar ruta
                            if (envio.rutaGeoJSON) {
                                try {
                                    const gj = JSON.parse(envio.rutaGeoJSON);
                                    const layer = L.geoJSON(gj, { style: { color: '#007bff', weight: 4, opacity: 0.7 } }).addTo(map);
                                    map.fitBounds(layer.getBounds(), { padding: [20, 20] });
                                } catch (e) {
                                    if (o[0] && d[0]) {
                                        const line = L.polyline([o, d], { color: '#007bff', weight: 4, opacity: 0.7 }).addTo(map);
                                        map.fitBounds(line.getBounds(), { padding: [20, 20] });
                                    }
                                }
                            } else if (o[0] && d[0]) {
                                const line = L.polyline([o, d], { color: '#007bff', weight: 4, opacity: 0.7 }).addTo(map);
                                map.fitBounds(line.getBounds(), { padding: [20, 20] });
                            }
                        } catch (e) {
                            console.error('Error inicializando mapa:', e);
                        }
                    }, 100);
                });

                cont.innerHTML = '';
                cont.appendChild(wrapper);
            }

            // ========================================
            // CARGA DE DATOS
            // ========================================

            async function cargarEnvio() {
                cont.innerHTML = loaderHtml;

                await verificarConexion();

                if (conectado) {
                    try {
                        const res = await fetch(`${LOCAL_API_URL}/envios/${envioId}`, {
                            ...FETCH_OPTS,
                            cache: 'no-store',
                        });

                        if (!res.ok) throw new Error('No se pudo cargar el envío');

                        const raw = await res.json();
                        const envio = normalizarDetalleEnvio(raw);
                        if (!envio) throw new Error('Respuesta vacía');

                        guardarEnCache(envio);
                        renderEnvio(envio, false);

                    } catch (error) {
                        console.error('Error cargando envío:', error);
                        const cached = obtenerDeCache();
                        if (cached) {
                            renderEnvio(cached, true);
                        } else {
                            cont.innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i>No se pudo cargar el envío. <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="reintentarConexion()">Reintentar</button></div>`;
                        }
                    }
                } else {
                    const cached = obtenerDeCache();
                    if (cached) {
                        renderEnvio(cached, true);
                    } else {
                        cont.innerHTML = `<div class="alert alert-warning"><i class="fas fa-wifi-slash mr-2"></i>Sin conexión. No hay datos guardados para este envío. <button type="button" class="btn btn-sm btn-warning mt-2" onclick="reintentarConexion()">Reintentar</button></div>`;
                    }
                }
            }

            window.reintentarConexion = async function () {
                await verificarConexion();
                if (conectado) {
                    cargarEnvio();
                }
            };

            setInterval(async () => {
                const wasOffline = !conectado;
                await verificarConexion();
                if (wasOffline && conectado) {
                    cargarEnvio();
                }
            }, 15000);

            window.addEventListener('online', () => {
                verificarConexion().then((ok) => { if (ok) cargarEnvio(); });
            });

            verificarConexion().then(() => cargarEnvio());
        })();
    </script>
@endpush