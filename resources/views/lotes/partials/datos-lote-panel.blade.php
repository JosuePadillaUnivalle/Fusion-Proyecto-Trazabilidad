@php
    use App\Support\EstadoLoteCatalogo;
    $propietario = trim(($lote->usuario->nombre ?? '').' '.($lote->usuario->apellido ?? '')) ?: '—';
    $tieneCoords = $lote->latitud && $lote->longitud;
    $ubicacionVisible = $lote->ubicacion_visible;
    $estadoSlug = EstadoLoteCatalogo::slugFromNombre($lote->estadoTipo->nombre ?? '') ?: 'default';
@endphp

<div class="lote-datos-panel">
    <div class="lote-datos-panel__header">
        <div class="lote-datos-panel__header-icon">
            <i class="fas fa-clipboard-list"></i>
        </div>
        <div>
            <h5 class="lote-datos-panel__title">Datos del lote</h5>
            <p class="lote-datos-panel__subtitle">Identificación, cultivo y ubicación en campo</p>
        </div>
    </div>

    <div class="lote-chip-row">
        @if($lote->cultivo_etiqueta)
            <span class="lote-chip lote-chip--cultivo">
                <span class="lote-chip__icon"><i class="fas fa-seedling"></i></span>
                {{ $lote->cultivo_etiqueta }}
            </span>
        @else
            <span class="lote-chip lote-chip--muted">
                <span class="lote-chip__icon"><i class="fas fa-seedling"></i></span>
                Sin semilla
            </span>
        @endif
        <span class="lote-chip lote-chip--estado lote-chip--estado-{{ $estadoSlug }}">
            {{ ucfirst($lote->estadoTipo->nombre ?? 'Sin estado') }}
        </span>
    </div>

    <div class="lote-datos-panel__metrics">
        <div class="lote-datos-panel__metric">
            <span class="lote-datos-panel__metric-label"><i class="fas fa-hashtag mr-1"></i>ID</span>
            <span class="lote-datos-panel__metric-value">#{{ $lote->loteid }}</span>
        </div>
        <div class="lote-datos-panel__metric">
            <span class="lote-datos-panel__metric-label"><i class="fas fa-ruler-combined mr-1"></i>Superficie</span>
            <span class="lote-datos-panel__metric-value">{{ $lote->superficie_etiqueta }}</span>
        </div>
        <div class="lote-datos-panel__metric">
            <span class="lote-datos-panel__metric-label"><i class="fas fa-calendar-day mr-1"></i>Siembra</span>
            <span class="lote-datos-panel__metric-value lote-datos-panel__metric-value--sm">
                @if($lote->fechasiembra)
                    {{ \Carbon\Carbon::parse($lote->fechasiembra)->format('d/m/Y') }}
                    <span class="lote-datos-panel__metric-hint">{{ $estadisticas['dias_desde_siembra'] }} días</span>
                @else
                    <span class="text-muted">No registrada</span>
                @endif
            </span>
        </div>
    </div>

    <div class="lote-datos-panel__grid">
        <div class="lote-datos-panel__item">
            <div class="lote-datos-panel__item-icon"><i class="fas fa-user"></i></div>
            <div class="lote-datos-panel__item-body">
                <span class="lote-datos-panel__item-label">Propietario</span>
                <span class="lote-datos-panel__item-value">{{ $propietario }}</span>
            </div>
        </div>

        <div class="lote-datos-panel__item lote-datos-panel__item--wide">
            <div class="lote-datos-panel__item-icon"><i class="fas fa-qrcode"></i></div>
            <div class="lote-datos-panel__item-body">
                <span class="lote-datos-panel__item-label">Código trazabilidad</span>
                <span class="lote-datos-panel__traz-code">{{ $lote->codigo_trazabilidad ?? '—' }}</span>
            </div>
        </div>

    </div>

    <div class="lote-datos-panel__geo">
        <div class="lote-datos-panel__geo-info">
            <i class="fas fa-map-marker-alt"></i>
            <div>
                <span class="lote-datos-panel__item-label">Ubicación</span>
                <span class="lote-datos-panel__item-value">{{ $ubicacionVisible }}</span>
            </div>
        </div>
        @if($tieneCoords)
            <a href="{{ route('lotes.ubicacion', $lote) }}" class="lote-datos-panel__map-btn">
                <i class="fas fa-map mr-1"></i> Ver en mapa
            </a>
        @endif
    </div>
</div>
