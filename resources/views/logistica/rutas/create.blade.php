@extends('layouts.app')
@push('styles')
@include('logistica.partials.ayuda-logistica-styles')
@include('logistica.partials.mapa-ruta-styles')
<style>
.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
.section-title{font-weight:700;color:#2c5530}
.envio-pick-row:hover{background:#f8fafc}
</style>
@endpush

@php
    $enviosMapaJson = $enviosParaRuta->map(function ($env) {
        return [
            'codigo' => $env->externo_envio_id,
            'destino' => $env->pedido?->nombre_planta ?: $env->pedido?->direccion_texto,
            'lat' => $env->pedido?->latitud ? (float) $env->pedido->latitud : null,
            'lng' => $env->pedido?->longitud ? (float) $env->pedido->longitud : null,
        ];
    })->values();
@endphp

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Crear ruta de entrega</h1>
        <p class="text-muted mb-0">Puede cargar los envíos pendientes con un clic o armar la ruta manualmente.</p>
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
            <div class="card-header bg-success text-white">
                <h3 class="card-title mb-0"><i class="fas fa-magic mr-1"></i> Cargar envíos automáticamente</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-2">Marque los envíos que van en esta ruta y pulse el botón. El sistema llenará las paradas por usted.</p>
                <div class="table-responsive" style="max-height:280px;overflow-y:auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light sticky-top">
                            <tr>
                                <th style="width:40px"><input type="checkbox" id="marcar-todos-envios" title="Marcar todos"></th>
                                <th>Código envío</th>
                                <th>Destino</th>
                                <th>Chofer actual</th>
                                <th>Situación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($enviosParaRuta as $env)
                                <tr class="envio-pick-row">
                                    <td>
                                        <input type="checkbox" class="envio-ruta-check" value="{{ $env->envioasignacionmultipleid }}"
                                            data-codigo="{{ $env->externo_envio_id }}"
                                            data-destino="{{ $env->pedido?->nombre_planta ?: $env->pedido?->direccion_texto ?: 'Entrega '.$env->externo_envio_id }}"
                                            data-pedido="{{ $env->pedidoid ?: '' }}"
                                            data-chofer="{{ $env->transportista_usuarioid ?: '' }}"
                                            data-lat="{{ $env->pedido?->latitud }}"
                                            data-lng="{{ $env->pedido?->longitud }}">
                                    </td>
                                    <td><strong>{{ $env->externo_envio_id }}</strong></td>
                                    <td>{{ $env->pedido?->nombre_planta ?: $env->pedido?->direccion_texto ?: '—' }}</td>
                                    <td>{{ $env->transportista?->nombreusuario ?? 'Sin chofer' }}</td>
                                    <td>{{ $env->estado }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-success mt-3" id="btn-cargar-envios">
                    <i class="fas fa-download mr-1"></i> Usar envíos seleccionados como paradas
                </button>
            </div>
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
                                    <button type="button" class="btn btn-outline-secondary" id="btn-nombre-auto" title="Sugerir nombre con fecha de hoy">
                                        <i class="fas fa-wand-magic-sparkles"></i> Sugerir
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Chofer asignado</label>
                            @php $drivers = \App\Models\Usuario::where('role','transportista')->where('activo', true)->orderBy('nombre')->get(); @endphp
                            <select name="transportista_usuarioid" id="chofer-ruta" class="form-control">
                                <option value="">— Sin asignar aún —</option>
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
                    <h5 class="section-title"><i class="fas fa-map mr-1"></i> Vista previa del recorrido</h5>
                    <div id="mapaRutaEntrega" class="mb-3"></div>
                    <p class="ruta-mapa-leyenda mb-3">El mapa muestra los envíos con ubicación (puntos grises). Marque envíos arriba para trazar la ruta por calles.</p>

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
    const enviosCatalogo = @json($enviosMapaJson);
    const hub = { lat: -16.5, lng: -68.15 };
    let mapaRuta = null;
    let capasRuta = null;

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
        mapaRuta = L.map(el, { scrollWheelZoom: true }).setView([hub.lat, hub.lng], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }).addTo(mapaRuta);
        capasRuta = L.layerGroup().addTo(mapaRuta);
        setTimeout(() => mapaRuta.invalidateSize(), 150);
        return mapaRuta;
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
                    label: c.dataset.destino || codigo,
                });
            }
        });
        return puntos;
    }

    async function actualizarMapaDesdeParadas() {
        initMapaRuta();
        if (!capasRuta || !mapaRuta) return;

        const puntos = puntosSeleccionados();

        if (puntos.length === 0) {
            capasRuta.clearLayers();
            enviosCatalogo.filter(e => e.lat && e.lng).forEach(e => {
                L.circleMarker([e.lat, e.lng], {
                    radius: 6,
                    color: '#94a3b8',
                    fillColor: '#cbd5e1',
                    fillOpacity: 0.85,
                    weight: 2,
                }).bindPopup(`<strong>${e.codigo}</strong><br>${e.destino || ''}<br><em>Seleccione para incluir en la ruta</em>`)
                    .addTo(capasRuta);
            });
            const conUbicacion = enviosCatalogo.filter(e => e.lat && e.lng);
            if (conUbicacion.length) {
                const bounds = L.latLngBounds(conUbicacion.map(e => [e.lat, e.lng]));
                mapaRuta.fitBounds(bounds.pad(0.15));
            } else {
                mapaRuta.setView([hub.lat, hub.lng], 11);
            }
            return;
        }

        if (puntos.length === 1) {
            capasRuta.clearLayers();
            RutaPorCalles.drawOnMap(mapaRuta, capasRuta, puntos, null);
            mapaRuta.setView([puntos[0].lat, puntos[0].lng], 14);
            return;
        }

        const routeResult = await RutaPorCalles.fetchRoute(puntos);
        RutaPorCalles.drawOnMap(mapaRuta, capasRuta, puntos, routeResult);
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

    addBtn.addEventListener('click', () => nuevaFilaParada('', '', '', null));

    wrapper.addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-parada')) {
            e.target.closest('.parada-item').remove();
        }
    });

    const marcarTodos = document.getElementById('marcar-todos-envios');
    if (marcarTodos) {
        marcarTodos.addEventListener('change', function () {
            document.querySelectorAll('.envio-ruta-check').forEach(c => { c.checked = marcarTodos.checked; });
        });
    }

    const btnCargar = document.getElementById('btn-cargar-envios');
    if (btnCargar) {
        btnCargar.addEventListener('click', () => {
            const checks = Array.from(document.querySelectorAll('.envio-ruta-check:checked'));
            if (!checks.length) {
                alert('Marque al menos un envío de la lista superior.');
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
            const nombre = document.getElementById('nombre-ruta');
            if (nombre && !nombre.value.trim()) {
                nombre.value = 'Ruta ' + new Date().toLocaleDateString('es-BO') + ' (' + checks.length + ' entregas)';
            }
            document.getElementById('form-ruta').scrollIntoView({ behavior: 'smooth' });
            actualizarMapaDesdeParadas();
        });
    }

    document.querySelectorAll('.envio-ruta-check').forEach(c => {
        c.addEventListener('change', actualizarMapaDesdeParadas);
    });

    const desdeMapa = sessionStorage.getItem('ruta_desde_mapa');
    if (desdeMapa) {
        try {
            const items = JSON.parse(desdeMapa);
            sessionStorage.removeItem('ruta_desde_mapa');
            if (items.length && wrapper) {
                wrapper.innerHTML = '';
                idx = 0;
                items.forEach(it => nuevaFilaParada(it.destino, it.codigo, it.pedidoid, null));
                const nombre = document.getElementById('nombre-ruta');
                if (nombre && !nombre.value.trim()) {
                    nombre.value = 'Ruta desde mapa ' + new Date().toLocaleDateString('es-BO');
                }
                items.forEach(it => {
                    document.querySelectorAll('.envio-ruta-check').forEach(c => {
                        if (c.dataset.codigo === it.codigo) c.checked = true;
                    });
                });
                setTimeout(actualizarMapaDesdeParadas, 300);
            }
        } catch (e) {}
    }

    document.getElementById('btn-nombre-auto').addEventListener('click', () => {
        const chofer = document.getElementById('chofer-ruta');
        const textoChofer = chofer.options[chofer.selectedIndex]?.text?.trim() || 'sin chofer';
        const fecha = new Date().toLocaleDateString('es-BO');
        document.getElementById('nombre-ruta').value = 'Entregas ' + fecha + ' — ' + textoChofer;
    });

    document.getElementById('form-ruta').addEventListener('submit', function () {
        document.querySelectorAll('.parada-pedido').forEach(function (input) {
            if (!input.value || input.value === '0') {
                input.removeAttribute('name');
            }
        });
    });

    esperarMapaLibs(() => {
        initMapaRuta();
        actualizarMapaDesdeParadas();
    });
})();
});
</script>
@endpush
@endsection
