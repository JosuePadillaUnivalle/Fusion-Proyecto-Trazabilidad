@extends('layouts.app')

@section('title', 'Mapa en tiempo real | AgroFusion')
@section('page_title', 'Mapa en tiempo real')

@push('styles')
@include('logistica.partials.mapa-ruta-styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<style>
#mapaGlobalTiempoReal{height:calc(100vh - 220px);min-height:480px;width:100%;border-radius:14px;border:1px solid #e2e8f0;z-index:1}
.rt-mapa-panel{border:0;border-radius:14px;box-shadow:0 4px 18px rgba(18,38,63,.08)}
.rt-capa-item{
    display:flex;align-items:center;gap:.6rem;padding:.55rem .75rem;
    border-radius:10px;margin-bottom:.45rem;cursor:pointer;
    border:2px solid transparent;transition:all .15s ease;
    background:#f8fafc;
}
.rt-capa-item:hover{background:#f1f5f9}
.rt-capa-item.off{opacity:.45}
.rt-capa-item input{accent-color:var(--capa-color,#2563eb);width:1rem;height:1rem;cursor:pointer}
.rt-capa-swatch{width:14px;height:14px;border-radius:4px;flex-shrink:0;box-shadow:0 0 0 2px #fff,0 1px 3px rgba(0,0,0,.2)}
.rt-capa-label{font-size:.82rem;font-weight:600;color:#334155;line-height:1.25}
.rt-capa-count{font-size:.7rem;color:#94a3b8;margin-left:auto}
.rt-mapa-leyenda{font-size:.75rem;color:#64748b}
.leaflet-div-icon.rt-camion-global{
    width:auto!important;height:auto!important;margin:0!important;
    background:transparent!important;border:none!important;
}
.rt-camion-pin-global{
    width:32px;height:32px;border-radius:50%;
    border:2px solid #fff;color:#fff;
    display:flex!important;align-items:center;justify-content:center;
    box-shadow:0 3px 10px rgba(0,0,0,.4);
}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <p class="text-muted mb-0">Todas las rutas activas en un solo mapa. Desactive capas para enfocar un tramo.</p>
            <p class="rt-mapa-leyenda mb-0 mt-1">
                <i class="fas fa-info-circle mr-1"></i>
                Rutas coincidentes se desplazan ligeramente para verse por separado.
            </p>
        </div>
        <a href="{{ $volverUrl }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-9 mb-3 mb-lg-0">
                <div class="card rt-mapa-panel">
                    <div class="card-body p-2 position-relative">
                        <div id="mapaGlobalTiempoReal"
                             data-rt-mapa-global
                             data-estado-url="{{ $estadoUrl }}"></div>
                        <div class="position-absolute" style="top:12px;right:12px;z-index:1000">
                            <span class="badge badge-light border shadow-sm px-2 py-1 rt-mapa-contador">
                                <i class="fas fa-truck text-primary mr-1"></i>
                                <span data-rt-contador>0</span> en ruta
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card rt-mapa-panel">
                    <div class="card-header bg-white">
                        <h3 class="card-title font-weight-bold mb-0">
                            <i class="fas fa-layer-group text-primary mr-2"></i>Capas visibles
                        </h3>
                    </div>
                    <div class="card-body pt-2" data-rt-capas>
                        @foreach($variantes as $tipo => $meta)
                        <label class="rt-capa-item mb-0 w-100" style="--capa-color:{{ $meta['color'] }}" data-variante="{{ $meta['variante'] }}">
                            <input type="checkbox" checked data-rt-capa-toggle value="{{ $meta['variante'] }}">
                            <span class="rt-capa-swatch" style="background:{{ $meta['color'] }}"></span>
                            <span class="rt-capa-label">
                                <i class="fas {{ $meta['icono'] }} mr-1" style="color:{{ $meta['color'] }}"></i>
                                {{ $meta['etiqueta'] }}
                            </span>
                            <span class="rt-capa-count" data-rt-capa-count="{{ $meta['variante'] }}">0</span>
                        </label>
                        @endforeach
                    </div>
                    <div class="card-footer bg-white small text-muted">
                        Actualización automática cada 2 s.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="{{ asset('js/simulacion-mapa-global.js') }}?v=1"></script>
@endpush
