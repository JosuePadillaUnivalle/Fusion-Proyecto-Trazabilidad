/**
 * Mapa global — todas las rutas en simulación con capas por tipo y colores distintos.
 */
(function () {
    const SVG_CAMION = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18"><path fill="#ffffff" d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>';

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

    function haversineMetros(lat1, lng1, lat2, lng2) {
        const r = 6371000;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) ** 2
            + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
        return r * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
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
        if (total <= 0) return { lat: coords[0][1], lng: coords[0][0] };
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
        if (progreso <= 0) return [[coords[0][1], coords[0][0]]];
        const distancias = [0];
        let total = 0;
        for (let i = 1; i < coords.length; i++) {
            total += haversineMetros(coords[i - 1][1], coords[i - 1][0], coords[i][1], coords[i][0]);
            distancias.push(total);
        }
        if (total <= 0) return [[coords[0][1], coords[0][0]], [pos.lat, pos.lng]];
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
        if (resultado.length < 2) resultado.push([pos.lat, pos.lng]);
        return resultado;
    }

    function inicioMsDesde(data) {
        if (data.inicio_at_unix != null && Number(data.inicio_at_unix) > 0) {
            return Number(data.inicio_at_unix) * 1000;
        }
        if (data.inicio_at) {
            const texto = String(data.inicio_at).trim();
            const parsed = Date.parse(texto.includes('T') ? texto : texto.replace(' ', 'T'));
            if (Number.isFinite(parsed)) return parsed;
        }
        return NaN;
    }

    function progresoCliente(data) {
        if (data.completada) return 100;
        const inicioMs = inicioMsDesde(data);
        const duracion = Number(data.duracion_seg) || 0;
        if (!Number.isFinite(inicioMs) || duracion <= 0) return Number(data.progreso) || 0;
        const ratio = Math.min(1, Math.max(0, (Date.now() - inicioMs) / (duracion * 1000)));
        return Math.round(ratio * 1000) / 10;
    }

    function enriquecerRuta(ruta) {
        const geojson = ruta.geojson || geojsonDesdeParadas(ruta.paradas);
        const progreso = progresoCliente(ruta);
        const ratio = Math.min(1, progreso / 100);
        const posicion = ruta.posicion
            || posicionDesdeGeojson(geojson, ratio)
            || (ruta.paradas?.[0] ? { lat: ruta.paradas[0].lat, lng: ruta.paradas[0].lng } : null);
        return Object.assign({}, ruta, { geojson, progreso, progreso_ratio: ratio, posicion });
    }

    function claveRuta(r) {
        return `${r.tipo}-${r.id}`;
    }

    function crearIconoCamion(color) {
        return L.divIcon({
            className: 'rt-camion-global',
            html: `<div class="rt-camion-pin-global" style="background:${color}">${SVG_CAMION}</div>`,
            iconSize: [32, 32],
            iconAnchor: [16, 16],
        });
    }

    function initMapaGlobal() {
        const contenedor = document.querySelector('[data-rt-mapa-global]');
        if (!contenedor || !window.L) return;

        const estadoUrl = contenedor.dataset.estadoUrl;
        const capasVisibles = new Set();
        document.querySelectorAll('[data-rt-capa-toggle]').forEach((cb) => {
            if (cb.checked) capasVisibles.add(cb.value);
            cb.addEventListener('change', () => {
                if (cb.checked) capasVisibles.add(cb.value);
                else capasVisibles.delete(cb.value);
                const label = cb.closest('.rt-capa-item');
                if (label) label.classList.toggle('off', !cb.checked);
                aplicarVisibilidad();
            });
        });

        const mapa = L.map(contenedor, { scrollWheelZoom: true }).setView([-17.7833, -63.1821], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap',
        }).addTo(mapa);

        const grupoRutas = L.layerGroup().addTo(mapa);
        const estados = new Map();
        const capas = new Map();
        let boundsInicial = false;

        function aplicarVisibilidad() {
            capas.forEach((capa, key) => {
                const r = estados.get(key);
                const visible = r && capasVisibles.has(r.variante);
                if (visible) {
                    if (!mapa.hasLayer(capa.grupo)) capa.grupo.addTo(grupoRutas);
                } else if (mapa.hasLayer(capa.grupo)) {
                    grupoRutas.removeLayer(capa.grupo);
                }
            });
        }

        function actualizarContadores(rutas) {
            const conteos = {};
            rutas.forEach((r) => {
                conteos[r.variante] = (conteos[r.variante] || 0) + 1;
            });
            document.querySelectorAll('[data-rt-capa-count]').forEach((el) => {
                const v = el.dataset.rtCapaCount;
                el.textContent = String(conteos[v] || 0);
            });
            const total = document.querySelector('[data-rt-contador]');
            if (total) total.textContent = String(rutas.length);
        }

        function dibujarRuta(rutaRaw) {
            const ruta = enriquecerRuta(rutaRaw);
            const key = claveRuta(ruta);
            estados.set(key, ruta);

            let capa = capas.get(key);
            if (!capa) {
                capa = { grupo: L.layerGroup(), pendiente: null, recorrida: null, camion: null };
                capas.set(key, capa);
            }

            const color = ruta.color || '#2563eb';
            const geo = ruta.geojson || geojsonDesdeParadas(ruta.paradas);
            if (!geo) return;

            capa.grupo.clearLayers();

            const puntosRuta = extraerCoordenadasLinea(geo).map((c) => [c[1], c[0]]);
            if (puntosRuta.length >= 2) {
                capa.pendiente = L.polyline(puntosRuta, {
                    color,
                    weight: 5,
                    opacity: 0.35,
                    lineCap: 'round',
                    lineJoin: 'round',
                }).addTo(capa.grupo);
            }

            const recorrido = coordsRecorridas(geo, ruta.progreso_ratio ?? 0);
            if (recorrido.length >= 2) {
                capa.recorrida = L.polyline(recorrido, {
                    color,
                    weight: 6,
                    opacity: 0.92,
                    lineCap: 'round',
                    lineJoin: 'round',
                }).addTo(capa.grupo);
            }

            if (ruta.posicion?.lat != null) {
                const latLng = [ruta.posicion.lat, ruta.posicion.lng];
                if (!capa.camion) {
                    capa.camion = L.marker(latLng, {
                        icon: crearIconoCamion(color),
                        zIndexOffset: 1000 + (ruta.mapa_offset || 0),
                    }).bindPopup(`<strong>${ruta.codigo}</strong><br>${ruta.tipo_etiqueta || ''}<br>${ruta.chofer || ''}`);
                } else {
                    capa.camion.setLatLng(latLng);
                }
                capa.camion.addTo(capa.grupo);
            }

            if (capasVisibles.has(ruta.variante) && !grupoRutas.hasLayer(capa.grupo)) {
                capa.grupo.addTo(grupoRutas);
            }
        }

        function sincronizarRutas(rutas) {
            const activas = new Set(rutas.map(claveRuta));
            capas.forEach((capa, key) => {
                if (!activas.has(key)) {
                    grupoRutas.removeLayer(capa.grupo);
                    capas.delete(key);
                    estados.delete(key);
                }
            });
            rutas.forEach(dibujarRuta);
            actualizarContadores(rutas);

            if (!boundsInicial && rutas.length > 0) {
                const bounds = L.latLngBounds([]);
                rutas.forEach((r) => {
                    const geo = r.geojson || geojsonDesdeParadas(r.paradas);
                    extraerCoordenadasLinea(geo).forEach((c) => bounds.extend([c[1], c[0]]));
                });
                if (bounds.isValid()) {
                    mapa.fitBounds(bounds.pad(0.12));
                    boundsInicial = true;
                }
            }
        }

        function poll() {
            fetch(`${estadoUrl}?_=${Date.now()}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store',
            })
                .then((r) => r.json())
                .then((data) => sincronizarRutas(data.rutas || []))
                .catch((e) => console.warn('Mapa global:', e));
        }

        function tickAnimacion() {
            estados.forEach((rutaRaw, key) => {
                const capa = capas.get(key);
                if (!capa || !capasVisibles.has(rutaRaw.variante)) return;
                const ruta = enriquecerRuta(rutaRaw);
                estados.set(key, ruta);
                const geo = ruta.geojson;
                if (capa.recorrida && geo) {
                    const pts = coordsRecorridas(geo, ruta.progreso_ratio);
                    if (pts.length >= 2) capa.recorrida.setLatLngs(pts);
                }
                if (capa.camion && ruta.posicion) {
                    capa.camion.setLatLng([ruta.posicion.lat, ruta.posicion.lng]);
                }
            });
        }

        poll();
        setInterval(poll, 2000);
        setInterval(tickAnimacion, 250);

        setTimeout(() => mapa.invalidateSize(), 300);
        window.addEventListener('resize', () => mapa.invalidateSize());
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMapaGlobal);
    } else {
        initMapaGlobal();
    }
})();
