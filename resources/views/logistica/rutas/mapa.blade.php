@extends('layouts.app')
@push('styles')
@include('logistica.partials.ayuda-logistica-styles')
@include('logistica.partials.mapa-ruta-styles')
<style>
.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
#mapaRutaEntrega { height: 520px; min-height: 520px; }
.envio-mapa-item { cursor: pointer; }
.envio-mapa-item.active { background: #e8f5e9; }
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0">Mapa de envíos pendientes</h1>
            <p class="text-muted mb-0">Seleccione entregas en el mapa o en la lista y cree una ruta desde aquí.</p>
        </div>
        <a href="{{ route('logistica.rutas.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 mb-3">
                <div class="card x-card">
                    <div class="card-body p-2">
                        <div id="mapaRutaEntrega"></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card x-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Sin ruta ({{ $enviosMapa->where('lat')->count() }} con ubicación)</h3>
                        <input type="checkbox" id="marcar-todos-mapa" title="Marcar todos">
                    </div>
                    <div class="list-group list-group-flush" style="max-height:420px;overflow-y:auto" id="lista-envios-mapa">
                        @forelse($enviosMapa as $e)
                            <label class="list-group-item envio-mapa-item mb-0 {{ $e['lat'] ? '' : 'text-muted' }}"
                                data-id="{{ $e['id'] }}"
                                data-codigo="{{ $e['codigo'] }}"
                                data-destino="{{ $e['destino'] }}"
                                data-pedido="{{ $e['pedidoid'] ?? '' }}"
                                data-lat="{{ $e['lat'] }}"
                                data-lng="{{ $e['lng'] }}">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input chk-envio-mapa" id="env-{{ $e['id'] }}"
                                        @disabled(!$e['lat'])>
                                    <span class="custom-control-label w-100">
                                        <strong>{{ $e['codigo'] }}</strong><br>
                                        <small>{{ $e['destino'] ?? 'Sin destino' }}</small>
                                        @if(!$e['lat'])<br><small class="text-warning">Sin coordenadas en pedido</small>@endif
                                    </span>
                                </div>
                            </label>
                        @empty
                            <div class="list-group-item text-muted">No hay envíos pendientes sin ruta.</div>
                        @endforelse
                    </div>
                    <div class="card-footer">
                        <button type="button" class="btn btn-success btn-block" id="btn-crear-desde-mapa">
                            <i class="fas fa-route mr-1"></i> Crear ruta con seleccionados
                        </button>
                        <a href="{{ route('logistica.rutas.create') }}" class="btn btn-outline-secondary btn-block mt-2">Ir a formulario manual</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
@include('logistica.partials.mapa-ruta-libs')
<script>
document.addEventListener('DOMContentLoaded', () => {
function iniciarMapaEnvios() {
    const envios = @json($enviosMapa->values());
    const conCoords = envios.filter(e => e.lat && e.lng);
    const el = document.getElementById('mapaRutaEntrega');
    if (!el || !window.L) return;
    const map = L.map(el).setView([-16.5, -68.15], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OSM' }).addTo(map);
    const markers = {};
    const bounds = [];

    conCoords.forEach(e => {
        const m = L.marker([e.lat, e.lng], {
            icon: L.divIcon({
                className: '',
                html: `<div style="background:#17a2b8;color:#fff;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:bold;border:2px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,.3)">${e.codigo}</div>`,
                iconAnchor: [20, 10],
            }),
        }).addTo(map).bindPopup(`<strong>${e.codigo}</strong><br>${e.destino || ''}`);
        markers[e.id] = m;
        bounds.push([e.lat, e.lng]);
        m.on('click', () => toggleEnvio(e.id, true));
    });
    if (bounds.length) map.fitBounds(bounds, { padding: [40, 40] });

    function toggleEnvio(id, fromMap) {
        const chk = document.querySelector(`#env-${id}`);
        if (!chk || chk.disabled) return;
        if (!fromMap) chk.checked = !chk.checked;
        else chk.checked = !chk.checked;
        const item = document.querySelector(`.envio-mapa-item[data-id="${id}"]`);
        item?.classList.toggle('active', chk.checked);
        const m = markers[id];
        if (m) {
            const el = m.getElement();
            if (el) el.style.filter = chk.checked ? 'hue-rotate(90deg) brightness(1.1)' : '';
        }
    }

    document.querySelectorAll('.chk-envio-mapa').forEach(chk => {
        chk.addEventListener('change', () => toggleEnvio(chk.id.replace('env-', ''), false));
    });
    document.querySelectorAll('.envio-mapa-item').forEach(row => {
        row.addEventListener('click', (ev) => {
            if (ev.target.type === 'checkbox') return;
            const id = row.dataset.id;
            toggleEnvio(id, false);
        });
    });
    const marcarTodos = document.getElementById('marcar-todos-mapa');
    if (marcarTodos) {
        marcarTodos.addEventListener('change', () => {
            document.querySelectorAll('.chk-envio-mapa:not(:disabled)').forEach(c => {
                c.checked = marcarTodos.checked;
                toggleEnvio(c.id.replace('env-', ''), false);
            });
        });
    }

    document.getElementById('btn-crear-desde-mapa').addEventListener('click', () => {
        const seleccion = [];
        document.querySelectorAll('.envio-mapa-item').forEach(row => {
            const id = row.dataset.id;
            const chk = document.getElementById(`env-${id}`);
            if (chk?.checked) {
                seleccion.push({
                    codigo: row.dataset.codigo,
                    destino: row.dataset.destino,
                    pedidoid: row.dataset.pedido || '',
                });
            }
        });
        if (!seleccion.length) {
            alert('Seleccione al menos un envío con ubicación en el mapa.');
            return;
        }
        sessionStorage.setItem('ruta_desde_mapa', JSON.stringify(seleccion));
        window.location.href = @json(route('logistica.rutas.create'));
    });
    setTimeout(() => map.invalidateSize(), 150);
}
if (window.L) iniciarMapaEnvios();
else {
    let n = 0;
    const t = setInterval(() => {
        if (window.L) { clearInterval(t); iniciarMapaEnvios(); }
        else if (++n > 80) clearInterval(t);
    }, 50);
}
});
</script>
@endpush
@endsection
