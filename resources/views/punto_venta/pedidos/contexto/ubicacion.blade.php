@extends('layouts.app')

@section('title', $contexto['titulo'].' — '.$pedido->numero_solicitud)
@section('page_title', $contexto['titulo'])

@push('styles')
@include('punto_venta.partials.modulo-styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
.pdv-ctx-volver {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .45rem .95rem;
    font-size: .84rem;
    font-weight: 600;
    color: #334155;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    text-decoration: none !important;
    box-shadow: 0 1px 4px rgba(15, 23, 42, .06);
    transition: all .15s ease;
}
.pdv-ctx-volver:hover {
    color: #065f46;
    border-color: #86efac;
    background: #f0fdf4;
    text-decoration: none !important;
}
.pdv-ctx-ubicacion-card {
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 10px 32px rgba(15, 23, 42, .08);
    overflow: hidden;
    background: #fff;
}
.pdv-ctx-ubicacion-header {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 55%, #f0fdf4 100%);
    border-bottom: 1px solid #a7f3d0;
    padding: 1.35rem 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}
.pdv-ctx-ubicacion-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: #fff;
    border: 1px solid #86efac;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #059669;
    font-size: 1.2rem;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(16, 185, 129, .15);
}
.pdv-ctx-ubicacion-header h2 {
    font-size: 1.2rem;
    font-weight: 700;
    color: #065f46;
    margin: 0 0 .3rem;
    line-height: 1.3;
}
.pdv-ctx-ubicacion-header p {
    margin: 0;
    color: #64748b;
    font-size: .86rem;
}
.pdv-ctx-ubicacion-dir {
    padding: 1.15rem 1.5rem 1.25rem;
    background: #fff;
}
.pdv-ctx-ubicacion-dir-label {
    display: flex;
    align-items: center;
    gap: .45rem;
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #94a3b8;
    margin-bottom: .55rem;
}
.pdv-ctx-ubicacion-dir-label i { color: #ef4444; }
.pdv-ctx-ubicacion-dir-text {
    display: block;
    padding: .85rem 1rem;
    border-radius: 12px;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
    border: 1px solid #e2e8f0;
    color: #1e293b;
    font-size: .92rem;
    font-weight: 600;
    line-height: 1.45;
}
.pdv-ctx-ubicacion-hint {
    margin: .55rem 0 0;
    font-size: .78rem;
    color: #94a3b8;
}
.pdv-ctx-ubicacion-map-wrap {
    padding: 0 1.5rem 1.5rem;
}
.pdv-ctx-ubicacion-map {
    height: 300px;
    width: 100%;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,.6);
}
.pdv-ctx-ubicacion-sin-mapa {
    margin: 0 1.5rem 1.5rem;
    padding: 2rem 1rem;
    text-align: center;
    color: #94a3b8;
    font-size: .88rem;
    background: #f8fafc;
    border-radius: 12px;
    border: 1px dashed #e2e8f0;
}
</style>
@endpush

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ $volverUrl }}" class="pdv-ctx-volver">
                <i class="fas fa-arrow-left"></i>
                <span>Volver al pedido</span>
            </a>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card pdv-card pdv-ctx-ubicacion-card mb-0">
                <div class="pdv-ctx-ubicacion-header">
                    <div class="pdv-ctx-ubicacion-icon">
                        <i class="fas {{ $contexto['icono'] }}"></i>
                    </div>
                    <div>
                        <h2>{{ $contexto['nombre'] }}</h2>
                        <p>{{ $contexto['titulo'] }} · Pedido {{ $pedido->numero_solicitud }}</p>
                    </div>
                </div>

                <div class="pdv-ctx-ubicacion-dir">
                    <div class="pdv-ctx-ubicacion-dir-label">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Ubicación</span>
                    </div>
                    <span class="pdv-ctx-ubicacion-dir-text">{{ $contexto['direccion'] }}</span>
                    @if(!empty($contexto['estimada']))
                    <p class="pdv-ctx-ubicacion-hint mb-0">
                        <i class="fas fa-info-circle mr-1"></i>
                        Dirección referencial según la posición en el mapa.
                    </p>
                    @endif
                </div>

                @if(!empty($contexto['lat']) && !empty($contexto['lng']))
                <div class="pdv-ctx-ubicacion-map-wrap">
                    <div id="pdvCtxMapa" class="pdv-ctx-ubicacion-map"
                         data-lat="{{ $contexto['lat'] }}"
                         data-lng="{{ $contexto['lng'] }}"
                         data-nombre="{{ $contexto['nombre'] }}"></div>
                </div>
                @else
                <div class="pdv-ctx-ubicacion-sin-mapa">
                    <i class="fas fa-map d-block mb-2 fa-lg opacity-50"></i>
                    No hay coordenadas para mostrar en el mapa.
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@if(!empty($contexto['lat']) && !empty($contexto['lng']))
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.L) return;
    var el = document.getElementById('pdvCtxMapa');
    if (!el) return;
    var lat = parseFloat(el.dataset.lat);
    var lng = parseFloat(el.dataset.lng);
    if (isNaN(lat) || isNaN(lng)) return;
    var map = L.map(el, { scrollWheelZoom: true }).setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap',
    }).addTo(map);
    L.marker([lat, lng]).addTo(map).bindPopup(el.dataset.nombre || '');
    setTimeout(function () { map.invalidateSize(); }, 220);
});
</script>
@endpush
@endif
