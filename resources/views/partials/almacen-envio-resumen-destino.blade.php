@php
    $sectionId = $sectionId ?? 'almacenSectionEnvio';
    $resumenId = 'envioAlmacenResumen-' . $sectionId;
    $almacen = $almacen ?? null;
    $resumen = $resumenCapacidad ?? null;
    $cantidadResumen = $cantidadResumen ?? null;
    $ingresoKg = isset($ingresoKg) ? (float) $ingresoKg : 0;
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
            $pctProyectado = $capacidad > 0 ? min(100, (($usado + $ingresoKg) / $capacidad) * 100) : 0;
            $excede = $ingresoKg > 0 && $ingresoKg > $disponible;
            $libreDespues = max(0, $capacidad - $usado - $ingresoKg);
            $pctProyWidth = max(0, $pctProyectado - min(100, $porcentaje));
        @endphp
        <div class="envio-destino-card">
            <div class="envio-destino-card__head">
                <div>
                    <span class="envio-destino-card__badge">
                        <i class="fas fa-map-marker-alt mr-1"></i> Destino de envío
                    </span>
                    <h6 class="envio-destino-card__nombre mb-1">
                        <i class="fas fa-warehouse mr-1"></i>{{ $almacen->nombre }}
                    </h6>
                    <p class="envio-destino-card__meta mb-0">
                        {{ $almacen->tipoAlmacen->nombre ?? 'General' }}
                        @if($almacen->ubicacion)
                            · {{ $almacen->ubicacion }}
                        @endif
                    </p>
                </div>
                @if($ingresoKg > 0)
                    <div class="envio-destino-card__ingreso text-right">
                        <span class="envio-destino-card__ingreso-label">A ingresar</span>
                        <strong class="envio-destino-card__ingreso-valor">+{{ number_format($ingresoKg, 0, ',', '.') }} kg</strong>
                    </div>
                @endif
            </div>

            <div class="envio-destino-card__capacidad">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Capacidad total: <strong>{{ number_format($capacidad, 0, ',', '.') }} kg</strong></span>
                    <span class="text-muted">Ocupado hoy: <strong>{{ number_format($porcentaje, 1, ',', '.') }}%</strong></span>
                </div>
                <div class="capacidad-bar capacidad-bar--stacked">
                    <div class="fill fill-actual {{ $fillClass }}" style="width: {{ min($porcentaje, 100) }}%"></div>
                    @if($ingresoKg > 0)
                        <div class="fill fill-proyeccion {{ $excede ? 'excede' : '' }}" style="width: {{ $pctProyWidth }}%"></div>
                    @endif
                </div>
                @if($ingresoKg > 0)
                    <div class="envio-destino-card__preview mt-2">
                        <div class="envio-destino-card__preview-principal">
                            Tras el envío: ocupación <strong>{{ number_format($pctProyectado, 1, ',', '.') }}%</strong>
                        </div>
                        <div class="envio-destino-card__preview-detalle">
                            @if($excede)
                                <span class="text-danger font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i>Sin espacio suficiente</span>
                            @else
                                Quedan <strong>{{ number_format($libreDespues, 0, ',', '.') }} kg</strong> libres
                            @endif
                        </div>
                    </div>
                @else
                    <p class="small text-muted mb-0 mt-2">
                        {{ number_format($disponible, 0, ',', '.') }} kg disponibles de {{ number_format($capacidad, 0, ',', '.') }} kg
                    </p>
                @endif
            </div>
        </div>
    @else
        <div class="envio-destino-card envio-destino-card--empty">
            <i class="fas fa-warehouse fa-2x text-muted mb-2"></i>
            <p class="mb-0 small text-muted">
                Aún no hay almacén de destino. Use <strong>Cambiar almacén</strong> para elegir dónde ingresará la cosecha.
            </p>
        </div>
    @endif
</div>
