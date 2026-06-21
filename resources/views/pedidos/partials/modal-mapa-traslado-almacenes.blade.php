@php
    $puntosMapa = $almacenesMapaTraslado ?? [];
    $modalId = 'modalMapaTrasladoAlmacenes';
    $mapaId = 'mapaTrasladoAlmacenesModal';
@endphp

@if(!empty($puntosMapa))
@push('styles')
<style>
#{{ $mapaId }} {
    height: 480px;
    min-height: 420px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}
.tpm-mapa-pin {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,.35);
}
.tpm-mapa-pin--planta { background: #7c3aed; }
.tpm-mapa-pin--mayorista { background: #ea580c; }
.tpm-mapa-marker { background: transparent !important; border: none !important; }
.tpm-mapa-leyenda {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    font-size: .8rem;
    color: #64748b;
}
.tpm-mapa-leyenda span { display: inline-flex; align-items: center; gap: .35rem; }
.tpm-mapa-leyenda i { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
.tpm-mapa-chip {
    border: 1px solid #e2e8f0;
    border-radius: 999px;
    padding: .25rem .65rem;
    font-size: .78rem;
    background: #f8fafc;
    cursor: pointer;
}
.tpm-mapa-chip--planta.is-active,
.tpm-mapa-chip--planta:hover { border-color: #7c3aed; background: #f5f3ff; }
.tpm-mapa-chip--mayorista.is-active,
.tpm-mapa-chip--mayorista:hover { border-color: #ea580c; background: #fff7ed; }
</style>
@endpush

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content" style="border-radius:14px;overflow:hidden">
            <div class="modal-header text-white" style="background:linear-gradient(135deg,#7c3aed,#5b21b6)">
                <h5 class="modal-title font-weight-bold">
                    <i class="fas fa-map-marked-alt mr-2"></i>Plantas y almacenes mayoristas
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="tpm-mapa-leyenda mb-3">
                    <span><i style="background:#7c3aed"></i> Almacén de planta</span>
                    <span><i style="background:#ea580c"></i> Almacén mayorista</span>
                </div>
                <div class="row mb-3">
                    <div class="col-md-8">
                        <input type="search" id="{{ $mapaId }}-buscar" class="form-control form-control-sm"
                               placeholder="Buscar por nombre…" autocomplete="off">
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-block" id="{{ $mapaId }}-reset">
                            <i class="fas fa-compress-arrows-alt mr-1"></i> Ver todos
                        </button>
                    </div>
                </div>
                <div id="{{ $mapaId }}"></div>
                <div class="d-flex flex-wrap gap-2 mt-2" id="{{ $mapaId }}-lista"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const modalId = @json($modalId);
    const mapaId = @json($mapaId);
    const puntos = @json($puntosMapa);
    const hub = { lat: {{ $hubLat ?? -17.7833 }}, lng: {{ $hubLng ?? -63.1821 }} };

    const state = { map: null, capa: null, marcadores: {}, listo: false };

    function icono(ambito, activo) {
        const tipo = ambito === 'planta' ? 'planta' : 'mayorista';
        const icon = ambito === 'planta' ? 'fa-industry' : 'fa-warehouse';
        const extra = activo ? ' style="box-shadow:0 0 0 3px #fbbf24,0 2px 8px rgba(0,0,0,.4)"' : '';
        return L.divIcon({
            html: '<div class="tpm-mapa-pin tpm-mapa-pin--' + tipo + '"' + extra + '><i class="fas ' + icon + '"></i></div>',
            className: 'tpm-mapa-marker',
            iconSize: [34, 34],
            iconAnchor: [17, 17],
        });
    }

    function initMapa() {
        if (!window.L || state.listo) {
            if (state.map) setTimeout(() => state.map.invalidateSize(), 200);
            return;
        }
        const el = document.getElementById(mapaId);
        if (!el) return;

        state.map = L.map(el, { scrollWheelZoom: true }).setView([hub.lat, hub.lng], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OpenStreetMap' }).addTo(state.map);
        state.capa = L.layerGroup().addTo(state.map);

        const bounds = [];
        puntos.forEach((p) => {
            const m = L.marker([p.lat, p.lng], { icon: icono(p.ambito, false) })
                .bindTooltip(p.nombre, { direction: 'top', offset: [0, -14] })
                .bindPopup('<strong>' + p.nombre + '</strong><br><small>' + (p.direccion || p.ambito) + '</small>')
                .addTo(state.capa);
            m.on('click', () => centrar(p.id));
            state.marcadores[p.id] = m;
            bounds.push([p.lat, p.lng]);
        });

        const lista = document.getElementById(mapaId + '-lista');
        if (lista) {
            puntos.forEach((p) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'tpm-mapa-chip tpm-mapa-chip--' + (p.ambito === 'planta' ? 'planta' : 'mayorista');
                btn.textContent = p.nombre;
                btn.addEventListener('click', () => centrar(p.id));
                lista.appendChild(btn);
            });
        }

        if (bounds.length) {
            state.map.fitBounds(L.latLngBounds(bounds).pad(0.12));
        }
        state.listo = true;
        setTimeout(() => state.map.invalidateSize(), 250);
    }

    function centrar(id) {
        const p = puntos.find((x) => String(x.id) === String(id));
        if (!p || !state.map) return;
        Object.keys(state.marcadores).forEach((k) => {
            const item = puntos.find((x) => String(x.id) === String(k));
            state.marcadores[k].setIcon(icono(item?.ambito, String(k) === String(id)));
        });
        state.map.setView([p.lat, p.lng], 15, { animate: true });
        state.marcadores[id]?.openPopup();
    }

    function filtrar(q) {
        const term = (q || '').toLowerCase().trim();
        puntos.forEach((p) => {
            const visible = !term || (p.nombre || '').toLowerCase().includes(term);
            const m = state.marcadores[p.id];
            if (!m) return;
            if (visible) {
                if (!state.capa.hasLayer(m)) m.addTo(state.capa);
            } else {
                state.capa.removeLayer(m);
            }
        });
    }

    document.getElementById('btnVerMapaTrasladoAlmacenes')?.addEventListener('click', function () {
        $('#' + modalId).modal('show');
    });

    $('#' + modalId).on('shown.bs.modal', initMapa);

    document.getElementById(mapaId + '-buscar')?.addEventListener('input', function () {
        filtrar(this.value);
    });
    document.getElementById(mapaId + '-reset')?.addEventListener('click', function () {
        const inp = document.getElementById(mapaId + '-buscar');
        if (inp) inp.value = '';
        filtrar('');
        if (state.map && puntos.length) {
            const bounds = L.latLngBounds(puntos.map((p) => [p.lat, p.lng]));
            state.map.fitBounds(bounds.pad(0.12));
        }
    });
})();
</script>
@endpush
@endif
