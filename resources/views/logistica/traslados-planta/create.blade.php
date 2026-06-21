@extends('layouts.app')

@section('title', 'Traslado planta → mayorista')
@section('page_title', 'Nuevo traslado a almacén mayorista')

@push('styles')
@include('logistica.partials.ayuda-logistica-styles')
@include('logistica.partials.mapa-ruta-styles')
<style>
.tpm-card{border:0;border-radius:14px;box-shadow:0 4px 18px rgba(18,38,63,.08)}
#mapaTrasladoPlanta{height:360px;border-radius:12px;border:1px solid #e2e8f0}
.tpm-paso{border:2px solid #e2e8f0;border-radius:12px;padding:1rem;margin-bottom:1rem;background:#fafbfc}
.tpm-paso.is-done{border-color:#7c3aed;background:#f5f3ff}
.tpm-paso__num{width:28px;height:28px;border-radius:50%;background:#7c3aed;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:700;font-size:.85rem;margin-right:.5rem}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2">
        <p class="text-muted mb-0">Traslado desde almacén de planta hacia un centro mayorista. Asigne transportista y marque en ruta al salir.</p>
        <a href="{{ route('pedidos.create') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver a nuevo envío
        </a>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('logistica.traslados-planta.store') }}" id="form-traslado-planta">
            @csrf
            <div class="row">
                <div class="col-lg-7">
                    <div class="card tpm-card mb-3">
                        <div class="card-header bg-white">
                            <h3 class="card-title font-weight-bold mb-0">
                                <i class="fas fa-industry text-purple mr-2" style="color:#7c3aed"></i>Planificar traslado
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="form-row mb-3">
                                <div class="col-md-6">
                                    <label class="small font-weight-bold">Código</label>
                                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $codigoPreview }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="small font-weight-bold">Referencia (opcional)</label>
                                    <input type="text" name="nombre" class="form-control form-control-sm" value="{{ old('nombre') }}" placeholder="Ej. Despacho semanal planta">
                                </div>
                            </div>

                            <div class="tpm-paso" id="paso-planta">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="tpm-paso__num">1</span>
                                    <strong>Almacén de planta (origen)</strong>
                                </div>
                                @include('partials.selector-catalogo', [
                                    'id' => 'traslado_planta_origen',
                                    'name' => 'almacen_planta_origenid',
                                    'label' => 'Seleccione planta de carga',
                                    'icon' => 'fa-industry',
                                    'required' => true,
                                    'value' => old('almacen_planta_origenid', ''),
                                    'endpoint' => route('catalogo-selector.almacenes'),
                                    'title' => 'Almacén de planta',
                                    'searchPlaceholder' => 'Buscar almacén de planta…',
                                    'params' => ['ambito' => 'planta'],
                                ])
                            </div>

                            <div class="tpm-paso" id="paso-mayorista">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="tpm-paso__num">2</span>
                                    <strong>Almacén mayorista (destino)</strong>
                                </div>
                                @include('partials.selector-catalogo', [
                                    'id' => 'traslado_mayorista_destino',
                                    'name' => 'almacen_mayorista_destinoid',
                                    'label' => 'Seleccione centro mayorista',
                                    'icon' => 'fa-warehouse',
                                    'required' => true,
                                    'value' => old('almacen_mayorista_destinoid', ''),
                                    'endpoint' => route('catalogo-selector.almacenes'),
                                    'title' => 'Almacén mayorista',
                                    'searchPlaceholder' => 'Buscar almacén mayorista…',
                                    'params' => ['ambito' => 'mayorista'],
                                ])
                            </div>

                            <div class="tpm-paso">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="tpm-paso__num">3</span>
                                    <strong>Transportista y vehículo</strong>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        @include('partials.selector-catalogo', [
                                            'id' => 'traslado_transportista',
                                            'name' => 'transportista_usuarioid',
                                            'label' => 'Chofer',
                                            'icon' => 'fa-user-tie',
                                            'required' => true,
                                            'value' => old('transportista_usuarioid', ''),
                                            'endpoint' => route('catalogo-selector.usuarios'),
                                            'title' => 'Elegir transportista',
                                            'params' => ['roles' => 'transportista'],
                                        ])
                                    </div>
                                    <div class="col-md-6">
                                        @include('partials.selector-catalogo', [
                                            'id' => 'traslado_vehiculo',
                                            'name' => 'vehiculoid',
                                            'label' => 'Vehículo',
                                            'icon' => 'fa-truck',
                                            'required' => false,
                                            'value' => old('vehiculoid', ''),
                                            'endpoint' => route('catalogo-selector.vehiculos'),
                                            'title' => 'Elegir vehículo',
                                        ])
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label class="small font-weight-bold">Costo servicio Bs (opcional)</label>
                                <input type="number" name="costo_bs" class="form-control form-control-sm" min="0" step="0.01" value="{{ old('costo_bs') }}">
                            </div>
                        </div>
                        <div class="card-footer bg-white text-right">
                            <button type="submit" class="btn btn-primary font-weight-bold">
                                <i class="fas fa-save mr-1"></i> Crear traslado planificado
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card tpm-card">
                        <div class="card-body p-2">
                            <div id="mapaTrasladoPlanta"></div>
                            <p class="small text-muted mt-2 mb-0 px-1">
                                <i class="fas fa-info-circle mr-1"></i>
                                Violeta: planta → mayorista. Tras crear el traslado, use <strong>Marcar en ruta</strong> para verlo en tiempo real.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
@include('logistica.partials.mapa-ruta-libs')
<script>
(function () {
    const almacenes = @json($almacenesMapa);
    const hub = { lat: {{ $hubLat }}, lng: {{ $hubLng }} };
    let mapa = null;
    let capa = null;

    function initMapa() {
        if (!window.L) return;
        mapa = L.map('mapaTrasladoPlanta').setView([hub.lat, hub.lng], 11);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(mapa);
        capa = L.layerGroup().addTo(mapa);
        almacenes.forEach((a) => {
            const color = a.ambito === 'planta' ? '#7c3aed' : '#ea580c';
            L.circleMarker([a.lat, a.lng], { radius: 8, color, fillColor: color, fillOpacity: .85, weight: 2 })
                .bindPopup(`<strong>${a.nombre}</strong><br><small>${a.ambito}</small>`)
                .addTo(capa);
        });
        if (almacenes.length) {
            const bounds = L.latLngBounds(almacenes.map((a) => [a.lat, a.lng]));
            mapa.fitBounds(bounds.pad(0.15));
        }
        setTimeout(() => mapa.invalidateSize(), 300);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMapa);
    } else {
        initMapa();
    }
})();
</script>
@endpush
