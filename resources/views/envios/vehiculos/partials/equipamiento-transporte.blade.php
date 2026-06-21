@php
    use App\Support\VehiculoTransporteCatalogo;

    $tiposTransporte = $tiposTransporte ?? [];
    $activo = collect($tiposTransporte)->first();
    $codigoActivo = strtoupper((string) ($activo['codigo'] ?? VehiculoTransporteCatalogo::codigoDesdeNombre($activo['nombre'] ?? null) ?? ''));
    $metaActivo = VehiculoTransporteCatalogo::metaUi($codigoActivo ?: null);
@endphp

<div class="veh-equipamiento">
    @if($activo)
        <div class="veh-equipamiento__hero veh-equipamiento__hero--{{ $metaActivo['tone'] }}">
            <div class="veh-equipamiento__hero-icon">
                <i class="fas {{ $metaActivo['icon'] }}"></i>
            </div>
            <div class="veh-equipamiento__hero-body">
                <span class="veh-equipamiento__hero-label">Modo activo de esta unidad</span>
                <h4 class="veh-equipamiento__hero-title">{{ $activo['nombre'] ?? $metaActivo['nombre'] }}</h4>
                <p class="veh-equipamiento__hero-hint mb-0">{{ $metaActivo['hint'] }}</p>
            </div>
        </div>
    @else
        <div class="veh-equipamiento__empty">
            <i class="fas fa-shipping-fast"></i>
            <p class="mb-0">Sin tipo de transporte configurado.</p>
        </div>
    @endif

    <div class="veh-equipamiento__modos">
        <span class="veh-equipamiento__modos-title">Modos de transporte</span>
        <div class="veh-equipamiento__modos-grid">
            @foreach(VehiculoTransporteCatalogo::modosReferencia() as $modo)
                @php
                    $meta = VehiculoTransporteCatalogo::metaUi($modo['codigo']);
                    $isActivo = $codigoActivo === $modo['codigo'];
                @endphp
                <div class="veh-equipamiento__modo @if($isActivo) is-active @endif veh-equipamiento__modo--{{ $meta['tone'] }}">
                    <span class="veh-equipamiento__modo-icon"><i class="fas {{ $meta['icon'] }}"></i></span>
                    <span class="veh-equipamiento__modo-name">{{ $modo['nombre'] }}</span>
                    @if($isActivo)
                        <span class="veh-equipamiento__modo-check"><i class="fas fa-check"></i></span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    @isset($vehiculo)
        <div class="veh-equipamiento__stats">
            <div class="veh-equipamiento__stat">
                <span class="veh-equipamiento__stat-label"><i class="fas fa-weight-hanging mr-1"></i> Capacidad peso</span>
                <strong>{{ ($cap['kg'] ?? 0) > 0 ? number_format($cap['kg'], 0).' kg' : '—' }}</strong>
            </div>
            <div class="veh-equipamiento__stat">
                <span class="veh-equipamiento__stat-label"><i class="fas fa-cube mr-1"></i> Volumen útil</span>
                <strong>{{ ($dims['m3_util'] ?? 0) > 0 ? number_format($dims['m3_util'], 1).' m³' : '—' }}</strong>
            </div>
        </div>
        <p class="veh-equipamiento__edit small mb-0">
            @can('vehiculos.update')
                <a href="{{ route('envios.vehiculos.edit', $vehiculo) }}"><i class="fas fa-edit mr-1"></i> Cambiar equipamiento</a>
            @endcan
        </p>
    @endisset
</div>
