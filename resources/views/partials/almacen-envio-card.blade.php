@php
    $resumen = $resumenCapacidad ?? null;
    if ($resumen) {
        $disponible = $resumen['disponible_kg'];
        $capacidad = $resumen['capacidad_kg'];
        $usado = $resumen['ocupado_kg'];
        $porcentaje = $resumen['porcentaje'];
        $umEtiqueta = 'kg';
    } else {
        $usado = $almacen->almacenamientos->whereNull('fechasalida')->sum('cantidad');
        $disponible = max(0, (float) $almacen->capacidad - $usado);
        $capacidad = (float) $almacen->capacidad;
        $porcentaje = $capacidad > 0 ? ($usado / $capacidad) * 100 : 0;
        $umEtiqueta = $almacen->unidadMedida->abreviatura ?? 'kg';
    }
    $fillClass = $porcentaje < 50 ? 'low' : ($porcentaje < 80 ? 'medium' : 'high');
    $isSelected = isset($isSelected) ? (bool) $isSelected : false;
    $colClass = $colClass ?? 'col-md-6 mb-2';
@endphp
    <div class="{{ $colClass }}">
    <div class="almacen-card {{ $isSelected ? 'selected' : '' }}"
         role="button"
         tabindex="0"
         aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
         data-id="{{ $almacen->almacenid }}"
         data-disponible="{{ $disponible }}"
         data-nombre="{{ $almacen->nombre }}"
         data-um-almacen="{{ $umEtiqueta }}"
         data-tipo="{{ strtolower($almacen->tipoAlmacen->nombre ?? 'general') }}"
         data-tags="{{ strtolower($almacen->nombre . ' ' . ($almacen->tipoAlmacen->nombre ?? '') . ' ' . ($almacen->ubicacion ?? '')) }}">
        <div class="d-flex align-items-start">
            <div class="almacen-icon mr-2 text-center">
                @if(str_contains(strtolower($almacen->tipoAlmacen->nombre ?? ''), 'silo'))
                    <i class="fas fa-database"></i>
                @elseif(str_contains(strtolower($almacen->tipoAlmacen->nombre ?? ''), 'bodega'))
                    <i class="fas fa-warehouse"></i>
                @elseif(str_contains(strtolower($almacen->tipoAlmacen->nombre ?? ''), 'fría') || str_contains(strtolower($almacen->tipoAlmacen->nombre ?? ''), 'frio'))
                    <i class="fas fa-snowflake"></i>
                @else
                    <i class="fas fa-box"></i>
                @endif
            </div>
            <div class="flex-grow-1">
                <div class="almacen-nombre">{{ $almacen->nombre }}</div>
                <div class="almacen-tipo">
                    {{ $almacen->tipoAlmacen->nombre ?? 'General' }}
                    @if($almacen->ubicacion)
                        • {{ $almacen->ubicacion }}
                    @endif
                </div>
                <div class="small mt-1">
                    <span class="text-success font-weight-bold">{{ number_format($disponible, 0) }}</span>
                    <span class="text-muted">/ {{ number_format($capacidad, 0) }} {{ $umEtiqueta }}</span>
                    @if($resumen)
                        <span class="text-muted d-block" style="font-size:.72rem;">
                            Ocupado: {{ number_format($usado, 0) }} kg ({{ number_format($porcentaje, 1) }}&nbsp;%)
                        </span>
                    @endif
                </div>
                <div class="capacidad-bar">
                    <div class="fill {{ $fillClass }}" style="width: {{ min($porcentaje, 100) }}%"></div>
                </div>
            </div>
            <div class="ml-2">
                <i class="fas fa-check-circle text-success fa-lg" style="{{ $isSelected ? '' : 'display: none;' }}"></i>
            </div>
        </div>
    </div>
</div>
