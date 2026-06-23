@php
    $lote = $lote ?? null;
    $estimacion = $estimacion ?? null;
    if (! $lote) {
        return;
    }
    $lote->loadMissing(['usuario', 'cultivo', 'insumoSemilla', 'estadoTipo']);
    $responsable = trim(($lote->usuario->nombre ?? '').' '.($lote->usuario->apellido ?? ''));
    $tieneGps = $lote->latitud && $lote->longitud;
@endphp
<div class="cosecha-lote-preview border rounded p-3 bg-light mb-0">
    <input type="hidden" name="loteid" value="{{ $lote->loteid }}">
    <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap:.75rem;">
        <div class="flex-grow-1">
            <span class="badge badge-success mb-2"><i class="fas fa-seedling mr-1"></i> Lote a cosechar</span>
            <h5 class="font-weight-bold text-success mb-1">{{ $lote->nombre }}</h5>
            <p class="small text-muted mb-2">
                <i class="fas fa-leaf mr-1"></i>{{ $lote->cultivo_etiqueta ?? ($lote->cultivo->nombre ?? 'Sin cultivo') }}
                @if($responsable)
                    · <i class="fas fa-user mr-1"></i>{{ $responsable }}
                @endif
                @if($lote->superficie)
                    · <i class="fas fa-ruler-combined mr-1"></i>@superficie($lote->superficie, 1)
                @endif
            </p>
            @if($lote->ubicacion_visible && $lote->ubicacion_visible !== 'Sin ubicación registrada')
                <p class="small mb-0 text-muted">
                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $lote->ubicacion_visible }}
                </p>
            @endif
        </div>
        <div class="d-flex flex-column flex-sm-row" style="gap:.35rem;">
            <a href="{{ route('lotes.trazabilidad', $lote) }}" class="btn btn-sm btn-outline-success" target="_blank" rel="noopener">
                <i class="fas fa-route mr-1"></i> Trazabilidad
            </a>
            @if($tieneGps)
                <a href="{{ route('lotes.mapa') }}#lote-{{ $lote->loteid }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">
                    <i class="fas fa-map-marked-alt mr-1"></i> Ver en mapa
                </a>
            @endif
        </div>
    </div>
</div>
