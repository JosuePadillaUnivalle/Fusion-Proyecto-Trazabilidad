@extends('layouts.app')
@push('styles')
@include('logistica.partials.ayuda-logistica-styles')
@include('logistica.partials.mapa-ruta-styles')
<style>.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="m-0">Confirmar ruta automática</h1>
            <p class="text-muted mb-0">Revise el recorrido por calles y las {{ $envios->count() }} paradas antes de crear la ruta.</p>
        </div>
        <a href="{{ route('logistica.rutas.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Cancelar
        </a>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-7 mb-3">
                <div class="card x-card">
                    <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-map mr-1"></i> Recorrido estimado</h3></div>
                    <div class="card-body p-2">
                        <div id="mapaRutaEntrega"></div>
                        <p class="ruta-mapa-leyenda mt-2 mb-0">
                            <span class="text-primary">■</span> Ruta por calles (OSRM) &nbsp;
                            <span class="text-warning">▬▬</span> Línea recta si faltan coordenadas
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card x-card mb-3">
                    <div class="card-body">
                        <p><strong>Chofer:</strong> {{ $choferNombre }}</p>
                        <p class="mb-0"><strong>Envíos incluidos:</strong> {{ $envios->count() }}</p>
                    </div>
                </div>
                <div class="card x-card mb-3">
                    <div class="card-header"><h3 class="card-title mb-0">Paradas (orden actual)</h3></div>
                    <ol class="list-group list-group-flush">
                        @foreach($puntos as $p)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <span><strong>{{ $p['orden'] }}.</strong> {{ $p['destino'] }}</span>
                                <small class="text-muted">{{ $p['codigo'] }}</small>
                            </li>
                        @endforeach
                    </ol>
                </div>
                <form method="POST" action="{{ route('logistica.rutas.generar-automatica') }}">
                    @csrf
                    @if($transportistaId)
                        <input type="hidden" name="transportista_usuarioid" value="{{ $transportistaId }}">
                    @endif
                    <div class="custom-control custom-checkbox mb-3">
                        <input type="checkbox" class="custom-control-input" id="confirmar" name="confirmar" value="1" required>
                        <label class="custom-control-label" for="confirmar">
                            Confirmo crear esta ruta con {{ $envios->count() }} paradas y vincular los envíos listados.
                        </label>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg btn-block">
                        <i class="fas fa-check mr-1"></i> Crear ruta confirmada
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

@push('scripts')
@include('logistica.partials.mapa-ruta-libs')
<script>
document.addEventListener('DOMContentLoaded', () => {
function iniciarPreview() {
(async () => {
    const paradas = @json($puntos->filter(fn($p) => $p['lat'] && $p['lng'])->values());
    const geoGuardado = @json($geo);
    const el = document.getElementById('mapaRutaEntrega');
    if (!el || !window.L) return;
    const map = L.map(el).setView([-16.5, -68.15], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OSM' }).addTo(map);
    const capas = L.layerGroup().addTo(map);
    let routeResult = geoGuardado ? { geojson: geoGuardado, straight: geoGuardado?.features?.[0]?.properties?.provider === 'straight' } : null;
    if (!routeResult && paradas.length >= 2) {
        routeResult = await RutaPorCalles.fetchRoute(paradas);
    }
    RutaPorCalles.drawOnMap(map, capas, paradas, routeResult);
    setTimeout(() => map.invalidateSize(), 150);
})();
}
if (window.L && window.RutaPorCalles) iniciarPreview();
else {
    let n = 0;
    const t = setInterval(() => {
        if (window.L && window.RutaPorCalles) { clearInterval(t); iniciarPreview(); }
        else if (++n > 80) clearInterval(t);
    }, 50);
}
});
</script>
@endpush
@endsection
