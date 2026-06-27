/**
 * Simulación de ruta en tiempo real — progreso fluido en cliente + sincronización con servidor.
 */
(function () {
    const estadoUrlBase = document.querySelector('meta[name="simulacion-estado-base"]')?.content || '';
    let paginaActiva = true;
    const timersGlobales = [];
    const cleanupsGlobales = [];

    function registrarTimer(id) {
        if (id) timersGlobales.push(id);
        return id;
    }

    function registrarCleanup(fn) {
        if (typeof fn === 'function') cleanupsGlobales.push(fn);
    }

    function enLogoutActivo() {
        return window.__agrofusionLoggingOut === true;
    }

    function manejarRespuesta419() {
        if (!paginaActiva || enLogoutActivo()) {
            return false;
        }
        destruirSimulacion();
        window.location.reload();

        return true;
    }

    function destruirSimulacion() {
        if (!paginaActiva) return;
        paginaActiva = false;
        timersGlobales.forEach((id) => clearInterval(id));
        timersGlobales.length = 0;
        cleanupsGlobales.forEach((fn) => {
            try { fn(); } catch (e) { /* noop */ }
        });
        cleanupsGlobales.length = 0;
    }

    window.addEventListener('pagehide', destruirSimulacion);

    function urlEstado(tipo, id) {
        if (estadoUrlBase) {
            return estadoUrlBase.replace('__TIPO__', tipo).replace('__ID__', String(id));
        }
        return `/logistica/simulacion/${tipo}/${id}/estado`;
    }

    function formatearEta(segundos, progreso, completada) {
        if (completada) return 'Envío recibido en planta.';
        if (progreso >= 100 || !segundos || segundos <= 0) return 'Finalizando recepción en planta…';
        if (segundos < 60) return `Llegada estimada en ${Math.ceil(segundos)} s`;
        const m = Math.ceil(segundos / 60);
        return `Llegada estimada en ${m} min`;
    }

    function inicioMsDesde(data) {
        if (data._inicioMsAnchor != null && Number.isFinite(Number(data._inicioMsAnchor))) {
            return Number(data._inicioMsAnchor);
        }
        if (data.inicio_at_unix != null && !Number.isNaN(Number(data.inicio_at_unix))) {
            const unix = Number(data.inicio_at_unix);
            if (unix > 0) return unix * 1000;
        }
        if (data.inicio_at) {
            const texto = String(data.inicio_at).trim();
            const parsed = Date.parse(texto.includes('T') ? texto : texto.replace(' ', 'T'));
            if (Number.isFinite(parsed)) return parsed;
        }
        return NaN;
    }

    function anclarTiempoCliente(data, container) {
        const base = Object.assign({}, data);

        if (container) {
            const unixDs = Number(container.dataset.simInicioUnix || 0);
            const duracionDs = Number(container.dataset.simDuracion || 0);
            if ((!base.inicio_at_unix || Number(base.inicio_at_unix) <= 0) && unixDs > 0) {
                base.inicio_at_unix = unixDs;
            }
            if ((!base.duracion_seg || Number(base.duracion_seg) <= 0) && duracionDs > 0) {
                base.duracion_seg = duracionDs;
            }
        }

        const inicioMs = inicioMsDesde(base);
        const duracion = Number(base.duracion_seg) || 0;

        if (Number.isFinite(inicioMs)) {
            base._inicioMsAnchor = inicioMs;
            return base;
        }

        if (duracion > 0) {
            const ratio = Math.min(1, Math.max(0, Number(base.progreso_ratio ?? (Number(base.progreso) || 0) / 100)));
            base._inicioMsAnchor = Date.now() - ratio * duracion * 1000;
        }

        return base;
    }

    function progresoCliente(data) {
        if (data.completada) return 100;
        const inicioMs = inicioMsDesde(data);
        const duracion = Number(data.duracion_seg) || 0;
        if (!Number.isFinite(inicioMs) || duracion <= 0) {
            return Number(data.progreso) || 0;
        }
        const ratio = Math.min(1, Math.max(0, (Date.now() - inicioMs) / (duracion * 1000)));
        return Math.round(ratio * 1000) / 10;
    }

    function segundosRestantesCliente(data) {
        if (data.completada) return 0;
        const inicioMs = inicioMsDesde(data);
        const duracion = Number(data.duracion_seg) || 0;
        if (!Number.isFinite(inicioMs) || duracion <= 0) {
            return Number(data.segundos_restantes) || 0;
        }
        return Math.max(0, Math.ceil(duracion - (Date.now() - inicioMs) / 1000));
    }

    function extraerCoordenadasLinea(geojson) {
        const geom = geojson?.features?.[0]?.geometry;
        if (!geom || !Array.isArray(geom.coordinates)) return [];
        if (geom.type === 'LineString') return geom.coordinates;
        if (geom.type === 'MultiLineString') return geom.coordinates.flat();
        return [];
    }

    function geojsonDesdeParadas(paradas) {
        if (!paradas || paradas.length < 2) return null;
        return {
            type: 'FeatureCollection',
            features: [{
                type: 'Feature',
                properties: { provider: 'straight' },
                geometry: {
                    type: 'LineString',
                    coordinates: paradas.map((p) => [p.lng, p.lat]),
                },
            }],
        };
    }

    function normalizarGeojson(data) {
        if (extraerCoordenadasLinea(data.geojson).length >= 2) return data.geojson;
        return geojsonDesdeParadas(data.paradas) || data.geojson;
    }

    function haversineMetros(lat1, lng1, lat2, lng2) {
        const r = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) ** 2
            + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
        return r * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function posicionDesdeParadas(paradas, ratio) {
        if (!paradas?.length) return null;
        if (paradas.length < 2) {
            return { lat: paradas[0].lat, lng: paradas[0].lng };
        }
        const progreso = Math.min(1, Math.max(0, Number(ratio) || 0));
        const origen = paradas[0];
        const destino = paradas[paradas.length - 1];
        return {
            lat: origen.lat + (destino.lat - origen.lat) * progreso,
            lng: origen.lng + (destino.lng - origen.lng) * progreso,
        };
    }

    function posicionDesdeGeojson(geojson, ratio) {
        const coords = extraerCoordenadasLinea(geojson);
        if (!coords.length) return null;

        const progreso = Math.min(1, Math.max(0, Number(ratio) || 0));
        if (progreso >= 1) {
            const ultimo = coords[coords.length - 1];
            return { lat: ultimo[1], lng: ultimo[0] };
        }

        const distancias = [0];
        let total = 0;
        for (let i = 1; i < coords.length; i++) {
            total += haversineMetros(coords[i - 1][1], coords[i - 1][0], coords[i][1], coords[i][0]);
            distancias.push(total);
        }

        if (total <= 0) {
            return { lat: coords[0][1], lng: coords[0][0] };
        }

        const objetivo = total * progreso;
        for (let i = 1; i < coords.length; i++) {
            if (distancias[i] >= objetivo) {
                const segmento = distancias[i] - distancias[i - 1];
                const t = segmento > 0 ? (objetivo - distancias[i - 1]) / segmento : 0;
                return {
                    lat: coords[i - 1][1] + (coords[i][1] - coords[i - 1][1]) * t,
                    lng: coords[i - 1][0] + (coords[i][0] - coords[i - 1][0]) * t,
                };
            }
        }

        const ultimo = coords[coords.length - 1];
        return { lat: ultimo[1], lng: ultimo[0] };
    }

    function coordsRecorridas(geojson, ratio) {
        const coords = extraerCoordenadasLinea(geojson);
        if (coords.length < 2) return [];

        const progreso = Math.min(1, Math.max(0, Number(ratio) || 0));
        const pos = posicionDesdeGeojson(geojson, progreso);
        if (!pos) return [];

        if (progreso <= 0) {
            return [[coords[0][1], coords[0][0]]];
        }

        const distancias = [0];
        let total = 0;
        for (let i = 1; i < coords.length; i++) {
            total += haversineMetros(coords[i - 1][1], coords[i - 1][0], coords[i][1], coords[i][0]);
            distancias.push(total);
        }

        if (total <= 0) {
            return [[coords[0][1], coords[0][0]], [pos.lat, pos.lng]];
        }

        const objetivo = total * progreso;
        const resultado = [[coords[0][1], coords[0][0]]];

        for (let i = 1; i < coords.length; i++) {
            if (distancias[i] <= objetivo + 0.5) {
                resultado.push([coords[i][1], coords[i][0]]);
            } else {
                resultado.push([pos.lat, pos.lng]);
                break;
            }
        }

        if (resultado.length < 2) {
            resultado.push([pos.lat, pos.lng]);
        }

        return resultado;
    }

    function resolverPosicion(data) {
        const geojson = data.geojson || geojsonDesdeParadas(data.paradas);
        const ratio = data.progreso_ratio ?? 0;
        return posicionDesdeGeojson(geojson, ratio)
            || posicionDesdeParadas(data.paradas, ratio)
            || (data.posicion?.lat != null ? data.posicion : null);
    }

    function enriquecerEstadoVivo(data) {
        const geojson = normalizarGeojson(data);
        const progreso = progresoCliente(data);
        const ratio = Math.min(1, progreso / 100);
        const posicion = resolverPosicion(Object.assign({}, data, { geojson, progreso_ratio: ratio }));
        const esperandoConfirmacion = !!(data.esperando_confirmacion || (!data.completada && progreso >= 100));
        return Object.assign({}, data, {
            geojson,
            progreso,
            progreso_ratio: ratio,
            segundos_restantes: segundosRestantesCliente(data),
            posicion,
            esperando_confirmacion: esperandoConfirmacion,
        });
    }

    function actualizarPanelVivo(container, data) {
        const pct = container.querySelector('.sim-live-pct');
        const bar = container.querySelector('.sim-live-bar');
        const eta = container.querySelector('.sim-live-eta');
        const badge = container.querySelector('.sim-live-badge');
        const progreso = data.progreso ?? 0;
        const completada = !!data.completada;
        const espera = !!(data.esperando_confirmacion || (!completada && progreso >= 100));
        const estadoClase = completada ? 'completado' : (espera ? 'espera' : 'activo');

        if (pct) {
            pct.textContent = `${progreso}%`;
            pct.className = `h4 mb-0 sim-live-pct sim-live--${estadoClase}`;
        }
        if (bar) {
            bar.style.width = `${progreso}%`;
            bar.setAttribute('aria-valuenow', String(progreso));
            bar.className = `progress-bar sim-live-bar sim-live--${estadoClase}`;
        }
        if (eta) {
            if (completada) {
                eta.textContent = 'Recorrido finalizado. Pendiente cierre con firmas (fase documentos).';
            } else if (espera) {
                eta.textContent = 'Esperando recepción en destino. Confirme llegada, incidentes y firmas para cerrar el envío.';
            } else {
                eta.textContent = formatearEta(data.segundos_restantes, progreso, completada);
            }
        }
        if (badge) {
            badge.textContent = completada ? 'Finalizada' : (espera ? 'Esperando recepción' : 'En curso');
            badge.className = `badge sim-live-badge sim-live--${estadoClase}`;
        }

        const confirmBlock = container.querySelector('[data-sim-confirmar-block]');
        if (confirmBlock && container.dataset.simPuedeConfirmar === '1') {
            confirmBlock.classList.toggle('d-none', completada || !espera);
        }
    }

    function manejarCompletada(container, data) {
        if (!data.completada || !paginaActiva) return;
        actualizarPanelVivo(container, data);
        setTimeout(() => {
            if (!paginaActiva) return;
            window.location.href = container.dataset.simVolverUrl || window.location.href;
        }, 1500);
    }

    function actualizarProgresoTransportista(el, data) {
        const vivo = enriquecerEstadoVivo(data);
        const pct = vivo.progreso ?? 0;
        const bar = el.querySelector('.sim-progreso-bar');
        const lbl = el.querySelector('.sim-progreso-pct');
        const eta = el.querySelector('.sim-progreso-eta');
        if (bar) bar.style.width = `${pct}%`;
        if (lbl) lbl.textContent = `${pct}%`;
        if (eta) {
            eta.textContent = vivo.completada
                ? 'Envío completado.'
                : formatearEta(vivo.segundos_restantes, pct, false);
        }
        if (vivo.completada) {
            if (paginaActiva) {
                setTimeout(() => {
                    if (paginaActiva) window.location.reload();
                }, 1500);
            }
        }
        return vivo;
    }

    const SVG_CAMION = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path fill="#ffffff" d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>';

    function crearIconoCamion() {
        return L.divIcon({
            className: 'sim-camion-marker',
            html: `<div class="sim-camion-pin">${SVG_CAMION}</div>`,
            iconSize: [36, 36],
            iconAnchor: [18, 18],
        });
    }

    function initMapaVivo(container) {
        if (!container) return null;

        const mapEl = container.querySelector('[data-sim-mapa]');
        const tipo = container.dataset.simTipo;
        const id = container.dataset.simId;
        let mapa = null;
        let capasEstaticas = null;
        let capasRuta = null;
        let marcadorCamion = null;
        let lineaPendiente = null;
        let lineaRecorrida = null;
        let rutaDibujada = false;
        let vistaAjustada = false;
        let estadoBase = null;
        let tickPanelTimer = null;
        let tickMapaTimer = null;
        let pollTimer = null;
        let completadaLocal = false;
        let mapaListo = false;
        let zoomEnCurso = false;
        let lineasRenderer = null;

        function crearRendererLineas() {
            if (!window.L || lineasRenderer) return lineasRenderer;
            lineasRenderer = L.canvas({ padding: 0.5 });
            return lineasRenderer;
        }

        function redibujarLineasRuta() {
            if (!mapa) return;
            if (lineaRecorrida?.redraw) lineaRecorrida.redraw();
            if (lineaPendiente?.redraw) lineaPendiente.redraw();
        }

        function asegurarMapa(data) {
            if (!window.L || mapa || !mapEl || !data.paradas?.length) return;
            const p0 = data.paradas[0];
            mapa = L.map(mapEl, { scrollWheelZoom: true, preferCanvas: true }).setView([p0.lat, p0.lng], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap',
            }).addTo(mapa);
            capasEstaticas = L.layerGroup().addTo(mapa);
            capasRuta = L.layerGroup().addTo(mapa);
            crearRendererLineas();
            mapaListo = true;
            mapa.on('zoomstart', () => { zoomEnCurso = true; });
            mapa.on('zoomend', () => {
                zoomEnCurso = false;
                redibujarLineasRuta();
            });
            mapa.on('moveend', redibujarLineasRuta);
            setTimeout(() => mapa && mapa.invalidateSize(), 200);
            setTimeout(() => mapa && mapa.invalidateSize(), 800);
        }

        function dibujarRutaEstatica(data) {
            if (!window.L || !data.paradas?.length || rutaDibujada) return;
            const geo = data.geojson || geojsonDesdeParadas(data.paradas);
            if (!geo) return;

            asegurarMapa(data);
            if (!capasEstaticas || !mapa) return;

            data.paradas.forEach((p, i) => {
                const isFirst = i === 0;
                const isLast = i === data.paradas.length - 1;
                const color = isFirst ? '#16a34a' : (isLast ? '#dc2626' : '#0891b2');
                L.marker([p.lat, p.lng], {
                    icon: L.divIcon({
                        className: 'ruta-parada-marker',
                        html: `<div style="background:${color};color:#fff;border-radius:50%;width:28px;height:28px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:bold;border:3px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.35)">${p.orden ?? (i + 1)}</div>`,
                        iconSize: [28, 28],
                        iconAnchor: [14, 14],
                    }),
                }).bindPopup(p.label || `Parada ${i + 1}`).addTo(capasEstaticas);
            });

            const puntosRuta = extraerCoordenadasLinea(geo).map((c) => [c[1], c[0]]);
            if (puntosRuta.length >= 2) {
                lineaPendiente = L.polyline(puntosRuta, {
                    color: '#1e40af',
                    weight: 6,
                    opacity: 0.92,
                    lineCap: 'round',
                    lineJoin: 'round',
                    smoothFactor: 0,
                    renderer: crearRendererLineas(),
                    className: 'sim-ruta-pendiente',
                }).addTo(capasRuta);
            }

            rutaDibujada = true;

            if (!vistaAjustada) {
                const bounds = capasEstaticas.getBounds();
                const posicion = resolverPosicion(data);
                if (posicion) bounds.extend([posicion.lat, posicion.lng]);
                if (bounds.isValid()) {
                    mapa.fitBounds(bounds.pad(0.14));
                    vistaAjustada = true;
                }
            }
        }

        function actualizarRecorrido(data) {
            if (!window.L || !mapa || !capasRuta || zoomEnCurso) return;
            const geo = data.geojson || geojsonDesdeParadas(data.paradas);
            if (!geo) return;

            const puntos = coordsRecorridas(geo, data.progreso_ratio ?? 0);
            if (puntos.length < 2) return;

            if (lineaRecorrida) {
                lineaRecorrida.setLatLngs(puntos);
            } else {
                lineaRecorrida = L.polyline(puntos, {
                    color: '#ea580c',
                    weight: 5,
                    opacity: 0.95,
                    lineCap: 'round',
                    lineJoin: 'round',
                    smoothFactor: 0,
                    renderer: crearRendererLineas(),
                    className: 'sim-ruta-recorrida',
                }).addTo(capasRuta);
            }
            if (lineaRecorrida.bringToFront) lineaRecorrida.bringToFront();
        }

        function actualizarCamion(data) {
            if (!window.L) return;
            const posicion = resolverPosicion(data);
            if (!posicion || posicion.lat == null || posicion.lng == null) return;

            asegurarMapa(data);
            if (!mapa) return;

            const latLng = [posicion.lat, posicion.lng];

            if (!marcadorCamion) {
                marcadorCamion = L.marker(latLng, {
                    icon: crearIconoCamion(),
                    pane: 'markerPane',
                    zIndexOffset: 20000,
                    riseOnHover: true,
                    riseOffset: 25000,
                }).addTo(mapa).bindPopup('Camión en ruta');
            } else {
                marcadorCamion.setLatLng(latLng);
            }

            if (marcadorCamion._icon) {
                marcadorCamion._icon.style.zIndex = '20000';
            }
            if (marcadorCamion.bringToFront) marcadorCamion.bringToFront();

            actualizarRecorrido(data);
        }

        function dibujarMapa(data) {
            if (!data || !window.L) return;
            try {
                dibujarRutaEstatica(data);
                actualizarCamion(data);
            } catch (e) {
                console.warn('Error dibujando mapa de simulación', e);
            }
        }

        function leerEstadoInicial() {
            const scriptId = container.dataset.simEstadoId;
            if (!scriptId) return null;
            const el = document.getElementById(scriptId);
            if (!el?.textContent) return null;
            try {
                return JSON.parse(el.textContent);
            } catch (e) {
                console.warn('Estado inicial de simulación inválido', e);
                return null;
            }
        }

        function tickPanel() {
            if (!estadoBase || completadaLocal) return;
            const vivo = enriquecerEstadoVivo(estadoBase);
            actualizarPanelVivo(container, vivo);

            const cierreManual = container.querySelector('.sim-cierre-manual');
            if (cierreManual) cierreManual.style.display = vivo.completada ? 'none' : '';

            if (vivo.completada && !completadaLocal) {
                completadaLocal = true;
                detenerTimers();
                manejarCompletada(container, vivo);
            } else if (!vivo.completada && vivo.progreso >= 100) {
                poll();
            }
        }

        function tickMapa() {
            if (!estadoBase || completadaLocal || !window.L || zoomEnCurso) return;
            dibujarMapa(enriquecerEstadoVivo(estadoBase));
        }

        function aplicarEstadoServidor(data) {
            if (!data) return;
            estadoBase = anclarTiempoCliente(data, container);
            tickPanel();
            tickMapa();
        }

        function detenerTimers() {
            if (tickPanelTimer) {
                clearInterval(tickPanelTimer);
                tickPanelTimer = null;
            }
            if (tickMapaTimer) {
                clearInterval(tickMapaTimer);
                tickMapaTimer = null;
            }
            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }
        }

        function poll() {
            if (!paginaActiva || enLogoutActivo()) return;
            fetch(`${urlEstado(tipo, id)}?_=${Date.now()}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store',
            })
                .then((r) => {
                    if (r.status === 419) {
                        manejarRespuesta419();
                        return null;
                    }
                    if (!r.ok) throw new Error(`estado ${r.status}`);
                    return r.json();
                })
                .then((data) => {
                    if (data && paginaActiva) aplicarEstadoServidor(data);
                })
                .catch((e) => {
                    if (paginaActiva) console.warn('Error actualizando simulación', e);
                });
        }

        const estadoInicial = leerEstadoInicial();
        if (estadoInicial) aplicarEstadoServidor(estadoInicial);

        poll();
        pollTimer = registrarTimer(setInterval(poll, 2000));
        tickPanelTimer = registrarTimer(setInterval(tickPanel, 200));
        tickMapaTimer = registrarTimer(setInterval(tickMapa, 350));

        registrarCleanup(() => {
            detenerTimers();
            if (mapa) {
                mapa.remove();
                mapa = null;
            }
        });

        window.addEventListener('pageshow', () => {
            if (!paginaActiva || !mapa) return;
            mapa.invalidateSize();
            poll();
        });

        window.addEventListener('resize', () => {
            if (mapa) mapa.invalidateSize();
        });

        return mapa;
    }

    function actualizarContadorLista() {
        const filas = document.querySelectorAll('.sim-lista-fila').length;
        const badge = document.querySelector('.sim-lista-total');
        if (badge) badge.textContent = String(filas);

        const vacio = document.querySelector('.sim-lista-vacio');
        const tbody = document.querySelector('.sim-lista-tbody');
        if (filas === 0 && tbody && vacio) {
            vacio.classList.remove('d-none');
        }
    }

    function initListaTiempoReal() {
        const filas = document.querySelectorAll('.sim-lista-fila');
        if (!filas.length) return;

        filas.forEach((fila) => {
            const tipo = fila.dataset.simTipo;
            const id = fila.dataset.simId;
            let estadoBase = null;
            let completadaLocal = false;

            function actualizarFila(data) {
                if (completadaLocal) return;
                const vivo = enriquecerEstadoVivo(data);
                const pct = Math.min(100, vivo.progreso ?? 0);
                const bar = fila.querySelector('.sim-lista-bar');
                const pctEl = fila.querySelector('.sim-lista-pct');
                const camion = fila.querySelector('.sim-lista-camion');
                const tiempo = fila.querySelector('.sim-lista-tiempo');

                if (bar) {
                    bar.style.width = `${pct}%`;
                    const color = fila.dataset.simColor;
                    if (color) bar.style.background = color;
                }
                if (pctEl) {
                    pctEl.textContent = `${pct}%`;
                    const color = fila.dataset.simColor;
                    if (color) pctEl.style.color = color;
                }
                if (camion) {
                    camion.style.left = `calc(${pct}% - 10px)`;
                    const color = fila.dataset.simColor;
                    if (color) {
                        camion.style.color = color;
                        camion.style.borderColor = color;
                    }
                }
                if (tiempo) {
                    tiempo.textContent = vivo.completada
                        ? 'Entrega completada'
                        : formatearEta(vivo.segundos_restantes, pct, false);
                }

                if (vivo.completada) {
                    completadaLocal = true;
                    fila.style.transition = 'opacity .45s ease';
                    fila.style.opacity = '0';
                    setTimeout(() => {
                        fila.remove();
                        actualizarContadorLista();
                    }, 500);
                }
            }

            const pollFila = () => {
                if (!paginaActiva || enLogoutActivo()) return;
                fetch(`${urlEstado(tipo, id)}?_=${Date.now()}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    cache: 'no-store',
                })
                    .then((r) => {
                        if (r.status === 419) {
                            manejarRespuesta419();
                            return null;
                        }
                        return r.json();
                    })
                    .then((data) => {
                        if (!data || !paginaActiva) return;
                        estadoBase = anclarTiempoCliente(data);
                        actualizarFila(estadoBase);
                    })
                    .catch(() => {});
            };

            pollFila();
            const pollId = registrarTimer(setInterval(pollFila, 2000));
            const tickId = registrarTimer(setInterval(() => {
                if (estadoBase && !completadaLocal && paginaActiva) actualizarFila(estadoBase);
            }, 200));
            registrarCleanup(() => {
                clearInterval(pollId);
                clearInterval(tickId);
            });
        });
    }

    function boot() {
        document.querySelectorAll('.sim-progreso-transportista').forEach((el) => {
            const tipo = el.dataset.simTipo;
            const id = el.dataset.simId;
            let estadoBase = null;

            const pollTrans = () => {
                if (!paginaActiva || enLogoutActivo()) return;
                fetch(`${urlEstado(tipo, id)}?_=${Date.now()}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                    cache: 'no-store',
                })
                    .then((r) => r.json())
                    .then((data) => {
                        if (!paginaActiva) return;
                        estadoBase = anclarTiempoCliente(data);
                        actualizarProgresoTransportista(el, estadoBase);
                    })
                    .catch(() => {});
            };

            pollTrans();
            registrarTimer(setInterval(pollTrans, 2000));
            registrarTimer(setInterval(() => {
                if (estadoBase && paginaActiva) actualizarProgresoTransportista(el, estadoBase);
            }, 200));
        });

        document.querySelectorAll('[data-sim-mapa-vivo]').forEach(initMapaVivo);
        initListaTiempoReal();
    }

    window.SimulacionRuta = {
        destruir: destruirSimulacion,
        marcarLogout: function () {
            window.__agrofusionLoggingOut = true;
            destruirSimulacion();
            if (window.SimulacionMapaGlobal && typeof window.SimulacionMapaGlobal.destruir === 'function') {
                window.SimulacionMapaGlobal.destruir();
            }
        },
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
