@php
    $resumen = $resumen_siembra_completar ?? [];
    $insumo = $resumen['insumo'] ?? null;
    $proyeccion = $resumen['proyeccion'] ?? null;
    $mapa = $resumen['mapa'] ?? [];
    $mapaId = $mapaId ?? 'siembraResumenMapa';
    $mapaInitEvent = $mapaInitEvent ?? null;
@endphp

@once
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .siembra-resumen-mapa {
        height: 220px;
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid #d1e7dd;
        background: #f8fafc;
    }
    .siembra-resumen-mapa--vacio {
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: .85rem;
        text-align: center;
        padding: 1rem;
    }
    .siembra-proyeccion-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
    }
    @media (max-width: 576px) {
        .siembra-proyeccion-grid { grid-template-columns: 1fr; }
    }
    .siembra-proyeccion-item {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 10px;
        padding: 10px 8px;
        text-align: center;
    }
    .siembra-proyeccion-item strong {
        display: block;
        font-size: 1.05rem;
        color: #065f46;
        line-height: 1.2;
    }
    .siembra-proyeccion-item span {
        font-size: .68rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .02em;
    }
    .siembra-insumo-total {
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 10px;
        padding: .75rem 1rem;
    }
</style>
@endpush
@endonce

@if(!empty($mapa['tiene_coordenadas']))
    <div class="mb-3">
        <p class="small text-uppercase text-muted font-weight-bold mb-2">
            <i class="fas fa-map-marked-alt mr-1"></i> Área a sembrar ({{ number_format($mapa['superficie_ha'] ?? 0, 2, ',', '.') }} ha)
        </p>
        <div id="{{ $mapaId }}" class="siembra-resumen-mapa" role="img"
             aria-label="Mapa del lote con área según hectáreas"></div>
        @if(!empty($mapa['ubicacion']))
            <p class="small text-muted mb-0 mt-2">
                <i class="fas fa-location-dot mr-1"></i>{{ $mapa['ubicacion'] }}
            </p>
        @endif
    </div>
@else
    <div class="mb-3">
        <p class="small text-uppercase text-muted font-weight-bold mb-2">
            <i class="fas fa-map-marked-alt mr-1"></i> Área a sembrar
        </p>
        <div class="siembra-resumen-mapa siembra-resumen-mapa--vacio">
            <span>
                <i class="fas fa-map-pin d-block mb-2" style="font-size:1.25rem;opacity:.5;"></i>
                Sin coordenadas GPS en el lote.<br>
                Superficie planificada: <strong>{{ number_format($mapa['superficie_ha'] ?? 0, 2, ',', '.') }} ha</strong>
            </span>
        </div>
    </div>
@endif

@if($insumo && ($insumo['cantidad_total'] ?? null) !== null && (float) $insumo['cantidad_total'] > 0)
    <div class="siembra-insumo-total mb-3">
        <p class="small text-uppercase text-muted font-weight-bold mb-1">
            <i class="fas fa-flask mr-1"></i> Insumo de siembra
        </p>
        <p class="mb-1 font-weight-bold text-dark">
            {{ $insumo['insumo_nombre'] ?? 'Semilla / insumo' }}
        </p>
        <p class="mb-0">
            Cantidad total a usar:
            <strong class="text-success" style="font-size:1.1rem;">
                {{ number_format((float) $insumo['cantidad_total'], 2, ',', '.') }}
                {{ $insumo['cantidad_unidad'] ?? 'kg' }}
            </strong>
        </p>
        @if(!empty($insumo['por_ha']) && (float) $insumo['por_ha'] > 0)
            <p class="small text-muted mb-0 mt-1">
                Referencia: {{ number_format((float) $insumo['por_ha'], 3, ',', '.') }}
                {{ $insumo['unidad'] ?? 'kg' }}/ha ×
                {{ number_format((float) ($insumo['superficie_ha'] ?? $mapa['superficie_ha'] ?? 0), 2, ',', '.') }} ha
                @if(!empty($insumo['planificado_en_lote']))
                    <span class="badge badge-warning ml-1">Planificado en lote</span>
                @endif
            </p>
        @endif
    </div>
@endif

@if($proyeccion)
    <div class="mb-3 rounded p-3" style="background:#f0fdf4;border:1px solid #bbf7d0;">
        <p class="small text-uppercase text-muted font-weight-bold mb-2">
            <i class="fas fa-chart-line mr-1"></i> Proyección de cosecha estimada
        </p>
        <div class="siembra-proyeccion-grid mb-2">
            <div class="siembra-proyeccion-item">
                <strong>{{ number_format($proyeccion['empaques_estimados'] ?? 0, 0, ',', '.') }}</strong>
                <span>{{ $proyeccion['empaque_label'] ?? 'Cajas' }}</span>
            </div>
            <div class="siembra-proyeccion-item">
                <strong>~{{ number_format($proyeccion['unidades_estimadas'] ?? 0, 0, ',', '.') }}</strong>
                <span>Unidades aprox.</span>
            </div>
            <div class="siembra-proyeccion-item">
                <strong>{{ number_format($proyeccion['kg_cosecha_estimados'] ?? 0, 1, ',', '.') }}</strong>
                <span>Kilogramos</span>
            </div>
        </div>
        @if(!empty($proyeccion['calibre_nombre']))
            <p class="small text-muted mb-0">
                Calibre: {{ $proyeccion['calibre_nombre'] }}
                @if(!empty($proyeccion['unidades_por_caja']))
                    · {{ $proyeccion['unidades_por_caja'] }} uds/{{ strtolower($proyeccion['empaque_label'] ?? 'caja') }}
                @endif
                · {{ number_format($proyeccion['hectareas'] ?? 0, 2, ',', '.') }} ha
            </p>
        @endif
    </div>
@endif

@if(!empty($mapa['tiene_coordenadas']))
@once
@push('scripts')
@include('lotes.partials.mapa-superficie-helper')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    var mapasPendientes = window._siembraResumenMapasPendientes = window._siembraResumenMapasPendientes || [];

    window.AgroFusionSiembraResumenMapa = window.AgroFusionSiembraResumenMapa || {
        init: function (mapaId, config) {
            var el = document.getElementById(mapaId);
            if (!el || !window.L || !window.AgroFusionLoteMapa) {
                return null;
            }
            if (el._siembraMapaInicializado) {
                if (el._siembraMapaLeaflet) {
                    el._siembraMapaLeaflet.invalidateSize();
                }
                return el._siembraMapaLeaflet;
            }

            var lat = parseFloat(config.lat);
            var lng = parseFloat(config.lng);
            var ha = parseFloat(config.superficie_ha) || 0;
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
                return null;
            }

            var map = L.map(el, {
                zoomControl: true,
                dragging: true,
                scrollWheelZoom: false,
                doubleClickZoom: false,
                touchZoom: true,
            }).setView([lat, lng], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap',
            }).addTo(map);

            L.marker([lat, lng]).addTo(map).bindPopup(config.ubicacion || 'Parcela');

            var circleRef = { current: null };
            window.AgroFusionLoteMapa.actualizarCirculo(map, circleRef, lat, lng, ha, {
                color: '#0f766e',
                fillColor: '#14b8a6',
                fillOpacity: 0.28,
                ajustarVista: true,
            });

            el._siembraMapaInicializado = true;
            el._siembraMapaLeaflet = map;
            setTimeout(function () { map.invalidateSize(); }, 120);

            return map;
        },
        registrar: function (mapaId, config, initEvent) {
            var ejecutar = function () {
                window.AgroFusionSiembraResumenMapa.init(mapaId, config);
            };
            if (initEvent && window.jQuery) {
                window.jQuery(initEvent).on('shown.bs.modal', ejecutar);
            } else {
                mapasPendientes.push({ mapaId: mapaId, config: config });
            }
        },
        procesarPendientes: function () {
            mapasPendientes.forEach(function (item) {
                window.AgroFusionSiembraResumenMapa.init(item.mapaId, item.config);
            });
            mapasPendientes.length = 0;
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.AgroFusionSiembraResumenMapa.procesarPendientes();
    });
})();
</script>
@endpush
@endonce
@push('scripts')
@php
    $mapaConfigJs = [
        'lat' => $mapa['lat'] ?? null,
        'lng' => $mapa['lng'] ?? null,
        'superficie_ha' => $mapa['superficie_ha'] ?? 0,
        'ubicacion' => $mapa['ubicacion'] ?? '',
    ];
@endphp
<script>
(function () {
    var cfg = @json($mapaConfigJs);
    if (window.AgroFusionSiembraResumenMapa) {
        window.AgroFusionSiembraResumenMapa.registrar(@json($mapaId), cfg, @json($mapaInitEvent));
    }
})();
</script>
@endpush
@endif
