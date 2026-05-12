@extends('layouts.app')
@push('styles')
<style>
.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
.section-title{font-weight:700;color:#2c5530}
    #map{height:400px;border-radius:8px;}
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-sA+e2XQ6k9sRk1p3pGkJkN5FQ5p1wQv1Y+5s5m0h3k0=" crossorigin=""/>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Crear ruta multi-entrega</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card x-card">
            <div class="card-body">
                <form method="POST" action="{{ route('logistica.rutas.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Nombre de ruta</label>
                            <input name="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <h5 class="section-title">Mapa de la ruta</h5>
                            <div id="map"></div>
                            <p class="text-muted small">Haz click en el mapa para añadir una parada (se añadirá abajo en "Paradas iniciales").</p>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Transportista</label>
                            @php $drivers = \App\Models\Usuario::where('role','transportista')->where('activo', true)->orderBy('nombre')->get(); @endphp
                            <select name="transportista_usuarioid" class="form-control">
                                <option value="">-- Ninguno --</option>
                                @foreach($drivers as $d)
                                    <option value="{{ $d->usuarioid }}">{{ $d->nombre }} {{ $d->apellido }} ({{ $d->nombreusuario }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Fecha salida</label>
                            <input type="datetime-local" name="fecha_salida" class="form-control">
                        </div>
                    </div>

                    <hr>
                    <h5 class="section-title">Paradas iniciales (opcional)</h5>
                    <div id="paradas-wrapper">
                        <div class="row parada-item">
                            <div class="col-md-5 form-group">
                                <label>Destino</label>
                                <input name="paradas[0][destino]" class="form-control">
                                <input type="hidden" name="paradas[0][lat]" class="parada-lat" />
                                <input type="hidden" name="paradas[0][lng]" class="parada-lng" />
                            </div>
                            <div class="col-md-4 form-group">
                                <label>Envío</label>
                                <input name="paradas[0][externo_envio_id]" class="form-control">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Pedido ID</label>
                                <input type="number" name="paradas[0][pedidoid]" class="form-control">
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-secondary mb-3" id="add-parada">Agregar parada</button>
                    <br>
                    <button class="btn btn-primary">Guardar ruta</button>
                    <a href="{{ route('logistica.rutas.index') }}" class="btn btn-default">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
(() => {
    const wrapper = document.getElementById('paradas-wrapper');
    const addBtn = document.getElementById('add-parada');
    let idx = 1;

    // Leaflet map setup
    const LScript = document.createElement('script');
    LScript.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    LScript.onload = () => {
        const map = L.map('map').setView([0, 0], 2);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const markers = {};
        let poly = L.polyline([], { color: 'blue' }).addTo(map);

        function refreshPolyline() {
            const latlngs = Object.keys(markers).map(k => markers[k].getLatLng());
            poly.setLatLngs(latlngs);
            if (latlngs.length) map.fitBounds(poly.getBounds().pad(0.2));
        }

        function addParadaRow(dest = '', externo = '', pedido = '', lat = '', lng = '') {
            const row = document.createElement('div');
            row.className = 'row parada-item';
            row.dataset.idx = idx;
            row.innerHTML = `
                <div class="col-md-5 form-group">
                    <label>Destino</label>
                    <input name="paradas[${idx}][destino]" class="form-control" value="${dest}">
                    <input type="hidden" name="paradas[${idx}][lat]" class="parada-lat" value="${lat}" />
                    <input type="hidden" name="paradas[${idx}][lng]" class="parada-lng" value="${lng}" />
                </div>
                <div class="col-md-4 form-group">
                    <label>Envío</label>
                    <input name="paradas[${idx}][externo_envio_id]" class="form-control" value="${externo}">
                </div>
                <div class="col-md-2 form-group">
                    <label>Pedido ID</label>
                    <input type="number" name="paradas[${idx}][pedidoid]" class="form-control" value="${pedido}">
                </div>
                <div class="col-md-1 form-group d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-parada">X</button>
                </div>
            `;
            wrapper.appendChild(row);

            // If coordinates provided, add marker
            if (lat !== '' && lng !== '') {
                const m = L.marker([parseFloat(lat), parseFloat(lng)]).addTo(map).bindPopup(dest || `${lat}, ${lng}`);
                markers[idx] = m;
                refreshPolyline();
            }

            idx++;
        }

        // Map click adds a parada using coordinates
        map.on('click', (e) => {
            const lat = e.latlng.lat.toFixed(6);
            const lng = e.latlng.lng.toFixed(6);
            addParadaRow(`Mapa: ${lat}, ${lng}`, '', '', lat, lng);
        });

        addBtn.addEventListener('click', () => {
            addParadaRow();
        });

        wrapper.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-parada')) {
                const row = e.target.closest('.parada-item');
                const id = row?.dataset?.idx;
                if (id && markers[id]) {
                    map.removeLayer(markers[id]);
                    delete markers[id];
                    refreshPolyline();
                }
                row.remove();
            }
        });
    };
    document.head.appendChild(LScript);
})();
</script>
            wrapper.addEventListener('click', (e) => {

