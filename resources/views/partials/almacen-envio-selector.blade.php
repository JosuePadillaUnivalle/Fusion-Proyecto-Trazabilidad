@php
    $sectionId = $sectionId ?? 'almacenSection';
    $hiddenInputId = $hiddenInputId ?? 'almacenid';
    $selectedId = $selectedAlmacenId ?? old('almacenid');
    $guiaTexto = $guiaTexto ?? 'Toda cosecha debe ingresar al inventario del almacén elegido. El sistema valida la capacidad disponible y puede sugerir un almacén según el cultivo.';
    $instruccion = $instruccion ?? 'Seleccione el almacén o silo donde guardar la producción';
    $crearAlmacenUrl = $crearAlmacenUrl ?? route('almacen-agricola.create');
    $emptyTexto = $emptyTexto ?? 'No hay almacenes registrados.';
@endphp

<div class="almacen-section active" id="{{ $sectionId }}">
    <h6 class="mb-2"><i class="fas fa-warehouse mr-2"></i>Enviar a almacén <span class="text-danger">*</span></h6>
    <div class="guia-campo mb-3">
        <strong>Obligatorio.</strong> {{ $guiaTexto }}
    </div>
    @if(!empty($productoResumen))
        <p class="small mb-2">
            <strong>Producto del lote:</strong> {{ $productoResumen }}
            @if(!empty($cantidadResumen))
                — <strong>{{ $cantidadResumen }}</strong>
            @endif
        </p>
    @endif
    <p class="small text-muted mb-2" id="almacen-seleccionado-{{ $sectionId }}">
        <i class="fas fa-warehouse mr-1"></i> <strong>Almacén:</strong>
        @if($selectedId && $almacenes->firstWhere('almacenid', (int) $selectedId))
            {{ $almacenes->firstWhere('almacenid', (int) $selectedId)->nombre }}
        @else
            ninguno seleccionado
        @endif
    </p>

    <div id="almacenOptions-{{ $sectionId }}">
        <p class="text-muted small mb-3">
            <i class="fas fa-info-circle mr-1"></i>
            {{ $instruccion }}
        </p>

        <div class="row" id="almacenesContainer-{{ $sectionId }}">
            @forelse($almacenes as $almacen)
                @php
                    $usado = $almacen->almacenamientos->whereNull('fechasalida')->sum('cantidad');
                    $disponible = $almacen->capacidad - $usado;
                    $porcentaje = $almacen->capacidad > 0 ? ($usado / $almacen->capacidad) * 100 : 0;
                    $fillClass = $porcentaje < 50 ? 'low' : ($porcentaje < 80 ? 'medium' : 'high');
                    $isSelected = (int) $selectedId === (int) $almacen->almacenid;
                @endphp
                <div class="col-md-6 mb-2">
                    <div class="almacen-card {{ $isSelected ? 'selected' : '' }}"
                         data-id="{{ $almacen->almacenid }}"
                         data-disponible="{{ $disponible }}"
                         data-nombre="{{ $almacen->nombre }}"
                         data-um-almacen="{{ $almacen->unidadMedida->abreviatura ?? 'kg' }}"
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
                                    <span class="text-muted">/ {{ number_format($almacen->capacidad, 0) }} {{ $almacen->unidadMedida->abreviatura ?? 'kg' }}</span>
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
            @empty
                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle mr-2"></i>
                        {{ $emptyTexto }}
                        <a href="{{ $crearAlmacenUrl }}">Crear uno</a>
                    </div>
                </div>
            @endforelse
        </div>

        <input type="hidden" name="almacenid" id="{{ $hiddenInputId }}" value="{{ $selectedId }}">
    </div>

    @stack('almacen-envio-extra-'.$sectionId)
</div>
