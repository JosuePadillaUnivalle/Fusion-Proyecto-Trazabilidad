/**
 * Trazado por calles vía OSRM (público). Fallback a línea recta.
 */
window.RutaPorCalles = {
    async fetchRoute(waypoints) {
        const pts = (waypoints || []).filter(p => p && p.lat != null && p.lng != null);
        if (pts.length < 2) {
            return { geojson: null, straight: true };
        }

        const path = pts.map(p => `${p.lng},${p.lat}`).join(';');
        const url = `https://router.project-osrm.org/route/v1/driving/${path}?overview=full&geometries=geojson&steps=false`;

        try {
            const res = await fetch(url);
            const data = await res.json();
            if (data.code === 'Ok' && data.routes?.[0]?.geometry) {
                return {
                    geojson: {
                        type: 'FeatureCollection',
                        features: [{
                            type: 'Feature',
                            properties: {
                                provider: 'osrm',
                                distance_m: data.routes[0].distance,
                                duration_s: data.routes[0].duration,
                            },
                            geometry: data.routes[0].geometry,
                        }],
                    },
                    straight: false,
                    distance_m: data.routes[0].distance,
                    duration_s: data.routes[0].duration,
                };
            }
        } catch (e) {
            console.warn('OSRM no disponible', e);
        }

        return {
            geojson: {
                type: 'FeatureCollection',
                features: [{
                    type: 'Feature',
                    properties: { provider: 'straight' },
                    geometry: {
                        type: 'LineString',
                        coordinates: pts.map(p => [p.lng, p.lat]),
                    },
                }],
            },
            straight: true,
        };
    },

    drawOnMap(map, layerGroup, waypoints, routeResult) {
        if (!map || !layerGroup) return;
        layerGroup.clearLayers();

        const pts = waypoints || [];
        pts.forEach((p, i) => {
            const isFirst = i === 0;
            const isLast = i === pts.length - 1;
            const color = isFirst ? '#28a745' : (isLast ? '#dc3545' : '#17a2b8');
            L.marker([p.lat, p.lng], {
                icon: L.divIcon({
                    className: 'ruta-parada-marker',
                    html: `<div style="background:${color};color:#fff;border-radius:50%;width:26px;height:26px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:bold;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.3)">${p.orden ?? (i + 1)}</div>`,
                    iconSize: [26, 26],
                    iconAnchor: [13, 13],
                }),
            }).bindPopup(p.label || `Parada ${i + 1}`).addTo(layerGroup);
        });

        if (routeResult?.geojson) {
            const layer = L.geoJSON(routeResult.geojson, {
                style: {
                    color: routeResult.straight ? '#e67e22' : '#2563eb',
                    weight: 5,
                    opacity: 0.85,
                    dashArray: routeResult.straight ? '8,8' : null,
                },
            }).addTo(layerGroup);
            try {
                map.fitBounds(layer.getBounds().pad(0.12));
            } catch (e) {
                if (pts.length) map.setView([pts[0].lat, pts[0].lng], 12);
            }
        } else if (pts.length) {
            map.setView([pts[0].lat, pts[0].lng], 12);
        }
    },
};
