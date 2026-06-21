@php
    $almacenesMapa = $almacenesMapa ?? [];
    $modalId = 'modalMapaAlmacenesIndex';
    $mapaId = 'mapaAlmacenesIndex';
@endphp

@if(!empty($almacenesMapa))
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
#{{ $mapaId }} {
    height: 480px;
    min-height: 480px;
    border-radius: 10px;
    border: 1px solid #dee2e6;
}
.almacen-index-mapa-pin {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.35);
    cursor: pointer;
    background: #2c5530;
    transition: transform .12s ease, box-shadow .12s ease;
}
.almacen-index-mapa-pin:hover { transform: scale(1.1); }
.almacen-index-mapa-pin.is-highlight {
    box-shadow: 0 0 0 3px #fbbf24, 0 2px 10px rgba(0,0,0,.4);
    transform: scale(1.15);
}
.almacen-index-mapa-marker { background: transparent !important; border: none !important; }
.almacen-index-mapa-flash {
    position: absolute;
    z-index: 1000;
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    background: #1e293b;
    color: #fff;
    padding: .45rem .9rem;
    border-radius: 8px;
    font-size: .82rem;
    display: none;
    pointer-events: none;
    max-width: 90%;
    text-align: center;
}
.leaflet-tooltip.almacen-index-mapa-tooltip {
    background: #1e293b;
    color: #fff;
    border: 0;
    border-radius: 8px;
    font-size: .8rem;
    font-weight: 600;
    padding: .35rem .65rem;
    box-shadow: 0 4px 12px rgba(0,0,0,.2);
}
.leaflet-tooltip.almacen-index-mapa-tooltip::before { border-top-color: #1e293b; }
.almacen-index-mapa-lista {
    max-height: 120px;
    overflow-y: auto;
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
}
.almacen-index-mapa-chip {
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    padding: .25rem .65rem;
    font-size: .78rem;
    background: #f8fafc;
    cursor: pointer;
    transition: all .15s ease;
}
.almacen-index-mapa-chip:hover,
.almacen-index-mapa-chip.is-active {
    border-color: #2c5530;
    background: #d4edda;
    color: #1a3d1f;
}
.almacen-index-mapa-buscar .form-control:focus {
    border-color: #2c5530;
    box-shadow: 0 0 0 0.15rem rgba(44, 85, 48, .2);
}
.leaflet-popup.almacen-index-mapa-popup-wrap .leaflet-popup-content-wrapper {
    border-radius: 12px;
    box-shadow: 0 10px 28px rgba(18, 38, 63, .16);
    padding: 0;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}
.leaflet-popup.almacen-index-mapa-popup-wrap .leaflet-popup-content {
    margin: 0;
    min-width: 240px;
    line-height: 1.4;
}
.leaflet-popup.almacen-index-mapa-popup-wrap .leaflet-popup-close-button {
    color: #94a3b8;
    font-size: 20px;
    padding: 6px 8px 0 0;
}
.leaflet-popup.almacen-index-mapa-popup-wrap .leaflet-popup-close-button:hover {
    color: #2c5530;
}
.almacen-index-mapa-popup {
    padding: .9rem 1rem .85rem;
}
.almacen-index-mapa-popup-titulo {
    font-weight: 700;
    color: #1a3d1f;
    font-size: .95rem;
    margin-bottom: .4rem;
    display: flex;
    align-items: center;
    gap: .45rem;
}
.almacen-index-mapa-popup-titulo i {
    color: #2c5530;
    font-size: .9rem;
}
.almacen-index-mapa-popup-dir {
    font-size: .78rem;
    color: #64748b;
    line-height: 1.45;
    margin-bottom: .7rem;
    padding-left: 1.35rem;
}
.almacen-index-mapa-popup-dir i {
    color: #dc3545;
    margin-right: .2rem;
}
.almacen-index-mapa-popup-btn {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    background: linear-gradient(135deg, #4a7c59, #2c5530);
    color: #fff !important;
    font-size: .78rem;
    font-weight: 600;
    padding: .42rem .85rem;
    border-radius: 8px;
    text-decoration: none !important;
    transition: transform .12s ease, box-shadow .12s ease;
    box-shadow: 0 2px 8px rgba(44, 85, 48, .28);
}
.almacen-index-mapa-popup-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(44, 85, 48, .38);
    color: #fff !important;
}
</style>
@endpush

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    <i class="fas fa-map-marked-alt mr-2"></i>Almacenes en mapa — {{ $tituloModulo ?? 'Almacenes' }}
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row almacen-index-mapa-buscar mb-3">
                    <div class="col-md-8 mb-2 mb-md-0">
                        <label class="small text-muted mb-1" for="{{ $mapaId }}-buscar">Buscar almacén</label>
                        <input type="search"
                               id="{{ $mapaId }}-buscar"
                               class="form-control form-control-sm"
                               placeholder="Escriba el nombre, ej. Pirai…"
                               autocomplete="off">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-success btn-sm btn-block" id="{{ $mapaId }}-btn-buscar">
                            <i class="fas fa-search mr-1"></i> Buscar
                        </button>
                    </div>
                </div>
                <div class="position-relative mb-2">
                    <div id="{{ $mapaId }}"></div>
                    <div class="almacen-index-mapa-flash" id="{{ $mapaId }}-flash"></div>
                </div>
                <div class="almacen-index-mapa-lista" id="{{ $mapaId }}-lista"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
(function ($) {
    const modalId = @json($modalId);
    const mapaId = @json($mapaId);
    const almacenesMapa = @json($almacenesMapa);

    const state = {
        map: null,
        capa: null,
        marcadores: {},
        inicializado: false,
        highlightId: null,
    };

    function flashMapa(texto) {
        const el = document.getElementById(mapaId + '-flash');
        if (!el) return;
        el.textContent = texto;
        el.style.display = 'block';
        clearTimeout(flashMapa._t);
        flashMapa._t = setTimeout(function () { el.style.display = 'none'; }, 2800);
    }

    function escapeHtml(texto) {
        return String(texto || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function popupAlmacenHtml(item) {
        const nombre = escapeHtml(item.nombre || '');
        const dir = item.direccion ? escapeHtml(item.direccion) : '';

        return '<div class="almacen-index-mapa-popup">' +
            '<div class="almacen-index-mapa-popup-titulo"><i class="fas fa-warehouse"></i>' + nombre + '</div>' +
            (dir ? '<div class="almacen-index-mapa-popup-dir"><i class="fas fa-map-marker-alt"></i>' + dir + '</div>' : '') +
            '<a href="' + item.url + '" class="almacen-index-mapa-popup-btn"><i class="fas fa-eye"></i> Ver detalle</a>' +
            '</div>';
    }

    function iconoPin(highlight) {
        const cls = highlight ? ' is-highlight' : '';
        return L.divIcon({
            html: '<div class="almacen-index-mapa-pin' + cls + '"><i class="fas fa-warehouse"></i></div>',
            className: 'almacen-index-mapa-marker',
            iconSize: [34, 34],
            iconAnchor: [17, 17],
        });
    }

    function resaltarMarcador(id) {
        Object.keys(state.marcadores).forEach(function (key) {
            const marker = state.marcadores[key];
            const hl = String(key) === String(id);
            marker.setIcon(iconoPin(hl));
            marker.setZIndexOffset(hl ? 300 : 0);
        });
        state.highlightId = id;

        $('#' + mapaId + '-lista .almacen-index-mapa-chip').removeClass('is-active');
        $('#' + mapaId + '-lista .almacen-index-mapa-chip').filter(function () {
            return String($(this).data('id')) === String(id);
        }).addClass('is-active');
    }

    function renderChips() {
        const cont = $('#' + mapaId + '-lista');
        cont.empty();
        almacenesMapa.forEach(function (item) {
            const chip = $('<button type="button" class="almacen-index-mapa-chip"></button>');
            chip.attr('data-id', item.id);
            chip.text(item.nombre);
            chip.on('click', function () {
                centrarEnAlmacen(item.id, 15);
            });
            cont.append(chip);
        });
    }

    function initMapa() {
        if (state.inicializado || typeof L === 'undefined') {
            if (state.map) state.map.invalidateSize();
            return;
        }

        state.map = L.map(mapaId, { scrollWheelZoom: true });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }).addTo(state.map);

        state.capa = L.layerGroup().addTo(state.map);
        const bounds = [];

        almacenesMapa.forEach(function (item) {
            const lat = parseFloat(item.lat);
            const lng = parseFloat(item.lng);
            if (isNaN(lat) || isNaN(lng)) return;

            const popupHtml = popupAlmacenHtml(item);

            const marker = L.marker([lat, lng], { icon: iconoPin(false) })
                .bindTooltip(item.nombre, {
                    className: 'almacen-index-mapa-tooltip',
                    direction: 'top',
                    offset: [0, -16],
                })
                .bindPopup(popupHtml, {
                    autoPan: false,
                    offset: [0, -18],
                    className: 'almacen-index-mapa-popup-wrap',
                })
                .addTo(state.capa);

            marker._almacenId = item.id;
            marker.on('click', function () {
                centrarEnAlmacen(item.id, 15);
            });

            state.marcadores[item.id] = marker;
            bounds.push([lat, lng]);
        });

        if (bounds.length) {
            try {
                state.map.fitBounds(L.latLngBounds(bounds).pad(0.12));
            } catch (e) {
                state.map.setView(bounds[0], 12);
            }
        } else {
            state.map.setView([-17.7833, -63.1821], 11);
        }

        renderChips();
        state.inicializado = true;
    }

    function centrarEnAlmacen(id, zoom) {
        const item = almacenesMapa.find(function (a) {
            return String(a.id) === String(id);
        });
        if (!item || !state.map) return false;

        const lat = parseFloat(item.lat);
        const lng = parseFloat(item.lng);
        if (isNaN(lat) || isNaN(lng)) return false;

        const z = zoom || 15;
        state.map.closePopup();

        state.map.flyTo([lat, lng], z, { animate: true, duration: 0.55 });

        state.map.once('moveend', function onMoveEnd() {
            state.map.off('moveend', onMoveEnd);
            resaltarMarcador(item.id);

            const marker = state.marcadores[item.id];
            if (marker) {
                marker.openPopup();
            }

            $('#' + mapaId + '-buscar').val(item.nombre);
        });

        return true;
    }

    function buscarYCentrar(query) {
        const q = (query || '').trim().toLowerCase();
        if (!q) {
            if (state.map && Object.keys(state.marcadores).length) {
                const bounds = almacenesMapa.map(function (a) {
                    return [parseFloat(a.lat), parseFloat(a.lng)];
                }).filter(function (p) {
                    return !isNaN(p[0]) && !isNaN(p[1]);
                });
                if (bounds.length) {
                    try {
                        state.map.flyToBounds(L.latLngBounds(bounds).pad(0.12), { animate: true, duration: 0.55 });
                    } catch (e) {
                        state.map.setView(bounds[0], 12);
                    }
                    state.highlightId = null;
                    Object.keys(state.marcadores).forEach(function (key) {
                        state.marcadores[key].setIcon(iconoPin(false));
                        state.marcadores[key].setZIndexOffset(0);
                    });
                    $('#' + mapaId + '-lista .almacen-index-mapa-chip').removeClass('is-active');
                }
            }
            return true;
        }

        const coincidencias = almacenesMapa.filter(function (a) {
            return (a.search || '').indexOf(q) > -1 || (a.nombre || '').toLowerCase().indexOf(q) > -1;
        });

        if (!coincidencias.length) {
            flashMapa('No se encontró ningún almacén para "' + query + '".');
            return false;
        }

        const exacta = coincidencias.find(function (a) {
            return (a.nombre || '').toLowerCase() === q;
        });
        const objetivo = exacta || coincidencias[0];

        return centrarEnAlmacen(objetivo.id, 15);
    }

    function abrirMapa(buscar) {
        $('#' + modalId).modal('show');
        window.setTimeout(function () {
            initMapa();
            if (state.map) state.map.invalidateSize();
            if (buscar !== undefined && buscar !== null && String(buscar).trim() !== '') {
                $('#' + mapaId + '-buscar').val(String(buscar).trim());
                buscarYCentrar(buscar);
            }
        }, 200);
    }

    window.AlmacenesIndexMapa = {
        abrir: abrirMapa,
        buscar: buscarYCentrar,
    };

    $(function () {
        $('#btnVerMapaAlmacenes').on('click', function () {
            abrirMapa();
        });

        $('#' + mapaId + '-btn-buscar').on('click', function () {
            buscarYCentrar($('#' + mapaId + '-buscar').val());
        });

        $('#' + mapaId + '-buscar').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                buscarYCentrar($(this).val());
            }
        });

        $('#' + modalId).on('shown.bs.modal', function () {
            window.setTimeout(function () {
                initMapa();
                if (state.map) state.map.invalidateSize();
            }, 120);
        });

        $('#' + modalId).on('hidden.bs.modal', function () {
            if (state.map) {
                state.map.closePopup();
            }
        });
    });
})(jQuery);
</script>
@endpush
@endif
