@extends('layouts.app')

@section('title', 'Ubicación del lote | AgroFusion')
@section('page_title', 'Ubicación — '.$lote->nombre)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.index') }}">Lotes</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.show', $lote) }}">{{ $lote->nombre }}</a></li>
    <li class="breadcrumb-item active">Ubicación</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @include('lotes.partials.detalle-styles')
@endpush

@section('content')
    @include('lotes.partials.detalle-header')
    @include('lotes.partials.detalle-stats')
    @include('lotes.partials.detalle-nav')

    <div class="card lote-section-card">
        <div class="card-body">
            <h5 class="mb-3"><i class="fas fa-map mr-2 text-success"></i>Ubicación del lote</h5>

            @if($lote->latitud && $lote->longitud)
                <div class="p-3 bg-light rounded mb-3">
                    <div class="d-flex align-items-start">
                        <i class="fas fa-road text-success fa-lg mr-3 mt-1"></i>
                        <div>
                            <strong class="d-block text-muted small text-uppercase mb-1">Dirección / referencia</strong>
                            <span class="font-weight-bold" style="font-size: 1.05rem;">{{ $lote->ubicacion_visible }}</span>
                        </div>
                    </div>
                </div>
                <div id="map"></div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <strong><i class="fas fa-ruler-combined mr-1"></i> Superficie</strong><br>{{ $lote->superficie_etiqueta }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded">
                            <strong><i class="fas fa-seedling mr-1"></i> Cultivo</strong><br>{{ $lote->cultivo->nombre ?? '—' }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-map-marked-alt fa-4x text-muted mb-3"></i>
                    <h5>Sin coordenadas</h5>
                    <p class="text-muted">Este lote no tiene ubicación geográfica registrada.</p>
                    @can('lotes.update')
                        <a href="{{ route('lotes.edit', $lote) }}" class="btn btn-success">
                            <i class="fas fa-edit mr-1"></i> Agregar en edición del lote
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>

    @include('lotes.partials.detalle-actions')
@endsection

@push('scripts')
    @if($lote->latitud && $lote->longitud)
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var lat = {{ $lote->latitud }};
                var lng = {{ $lote->longitud }};
                var sup = {{ $lote->superficie ?? 0 }};
                var map = L.map('map').setView([lat, lng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap'
                }).addTo(map);
                L.marker([lat, lng]).addTo(map)
                    .bindPopup({!! json_encode('<strong>'.$lote->nombre.'</strong><br>'.$lote->ubicacion_visible.'<br>'.$lote->superficie_etiqueta) !!})
                    .openPopup();
                if (sup > 0) {
                    L.circle([lat, lng], {
                        color: '#2c5530',
                        fillColor: '#28a745',
                        fillOpacity: 0.3,
                        radius: Math.sqrt(sup * 10000 / Math.PI)
                    }).addTo(map);
                }
                setTimeout(function () { map.invalidateSize(); }, 200);
            });
        </script>
    @endif
@endpush
