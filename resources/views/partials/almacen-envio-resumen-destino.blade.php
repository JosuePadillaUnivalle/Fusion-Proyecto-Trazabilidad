@php
    $sectionId = $sectionId ?? 'almacenSectionEnvio';
    $resumenId = 'envioAlmacenResumen-' . $sectionId;
    $almacen = $almacen ?? null;
    $resumen = $resumenCapacidad ?? null;
    $cantidadResumen = $cantidadResumen ?? null;
    $productoResumen = $productoResumen ?? null;
@endphp
<div id="{{ $resumenId }}" class="envio-almacen-resumen mb-3">
    @if($almacen)
        @php
            if ($resumen) {
                $disponible = $resumen['disponible_kg'];
                $capacidad = $resumen['capacidad_kg'];
                $usado = $resumen['ocupado_kg'];
                $porcentaje = $resumen['porcentaje'];
            } else {
                $usado = $almacen->almacenamientos->whereNull('fechasalida')->sum('cantidad');
                $disponible = max(0, (float) $almacen->capacidad - $usado);
                $capacidad = (float) $almacen->capacidad;
                $porcentaje = $capacidad > 0 ? ($usado / $capacidad) * 100 : 0;
            }
            $fillClass = $porcentaje < 50 ? 'low' : ($porcentaje < 80 ? 'medium' : 'high');
        @endphp
        <div class="border rounded p-3 bg-light">
            <div class="d-flex flex-wrap justify-content-between align-items-start mb-2" style="gap:.5rem;">
                <div>
                    <span class="badge badge-success mb-2"><i class="fas fa-map-marker-alt mr-1"></i> Destino de envío</span>
                    <h6 class="font-weight-bold mb-1">
                        <i class="fas fa-warehouse text-warning mr-1"></i>{{ $almacen->nombre }}
                    </h6>
                    <p class="small text-muted mb-0">
                        {{ $almacen->tipoAlmacen->nombre ?? 'General' }}
                        @if($almacen->ubicacion)
                            · {{ $almacen->ubicacion }}
                        @endif
                    </p>
                </div>
                @if($cantidadResumen)
                    <div class="text-right">
                        <span class="text-muted small d-block">Cantidad a ingresar</span>
                        <strong class="text-dark">{{ $cantidadResumen }}</strong>
                    </div>
                @endif
            </div>
            @if($productoResumen)
                <p class="small mb-2"><strong>Producto:</strong> {{ $productoResumen }}</p>
            @endif
            <div class="small">
                <span class="text-success font-weight-bold">{{ number_format($disponible, 0) }}</span>
                <span class="text-muted">/ {{ number_format($capacidad, 0) }} kg disponibles</span>
                <span class="text-muted">({{ number_format($porcentaje, 1) }} % ocupado)</span>
            </div>
            <div class="capacidad-bar mt-1">
                <div class="fill {{ $fillClass }}" style="width: {{ min($porcentaje, 100) }}%"></div>
            </div>
            <p class="small text-muted mb-0 mt-2">
                <i class="fas fa-info-circle mr-1"></i>
                Almacén elegido al registrar la cosecha. Puede confirmar o cambiar el destino.
            </p>
        </div>
    @else
        <div class="alert alert-info mb-0 py-3">
            <i class="fas fa-warehouse mr-1"></i>
            Aún no hay almacén de destino. Use <strong>Cambiar almacén</strong> para elegir dónde ingresará la cosecha.
        </div>
    @endif
</div>
