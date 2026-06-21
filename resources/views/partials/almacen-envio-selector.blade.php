@php
    $sectionId = $sectionId ?? 'almacenSection';
    $hiddenInputId = $hiddenInputId ?? 'almacenid';
    $selectedId = $selectedAlmacenId ?? old('almacenid');
    $guiaTexto = $guiaTexto ?? 'Toda cosecha debe ingresar al inventario del almacén elegido. El sistema valida la capacidad disponible y puede sugerir un almacén según el cultivo.';
    $instruccion = $instruccion ?? 'Seleccione el almacén o silo donde guardar la producción';
    $crearAlmacenUrl = $crearAlmacenUrl ?? route('almacen-agricola.create');
    $emptyTexto = $emptyTexto ?? 'No hay almacenes registrados.';
    $almacenesTodos = $almacenesTodos ?? null;
    $habilitarBusqueda = $habilitarBusqueda ?? ($almacenesTodos && $almacenesTodos->count() > 0);
    $etiquetaAmbito = $etiquetaAmbito ?? 'agrícola';
    $modalId = 'modalAlmacenes-' . $sectionId;
    $resumenesCapacidad = $resumenesCapacidad ?? [];
    $almacenRequerido = $almacenRequerido ?? true;
@endphp

<div class="almacen-section active" id="{{ $sectionId }}">
    <h6 class="mb-2">
        <i class="fas fa-warehouse mr-2"></i>Enviar a almacén
        @if($almacenRequerido)<span class="text-danger">*</span>@else<span class="text-muted small font-weight-normal">(opcional)</span>@endif
    </h6>
    <div class="guia-campo mb-3">
        @if($almacenRequerido)<strong>Obligatorio.</strong>@else<strong>Opcional.</strong>@endif
        {{ $guiaTexto }}
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
        @elseif($selectedId && $almacenesTodos?->firstWhere('almacenid', (int) $selectedId))
            {{ $almacenesTodos->firstWhere('almacenid', (int) $selectedId)->nombre }}
        @else
            ninguno seleccionado
        @endif
    </p>

    <div id="almacen-seleccion-externa-{{ $sectionId }}" class="d-none mb-2">
        <div class="alert alert-success py-2 px-3 mb-0 small d-flex align-items-center justify-content-between">
            <span>
                <i class="fas fa-check-circle mr-1"></i>
                Seleccionado: <strong id="almacen-seleccion-externa-nombre-{{ $sectionId }}"></strong>
            </span>
            <button type="button" class="btn btn-sm btn-outline-success btn-cambiar-almacen-modal" data-section="{{ $sectionId }}">
                Cambiar
            </button>
        </div>
    </div>

    <div id="almacenOptions-{{ $sectionId }}">
        <p class="text-muted small mb-3">
            <i class="fas fa-info-circle mr-1"></i>
            {{ $instruccion }}
            @if($habilitarBusqueda)
                <span class="d-block mt-1">
                    Se muestran los {{ $almacenes->count() }} almacenes {{ $etiquetaAmbito }}s más usados.
                    Use <strong>Buscar</strong> para ver todos por nombre, ubicación o en el mapa.
                </span>
            @endif
        </p>

        <div class="row" id="almacenesContainer-{{ $sectionId }}">
            @forelse($almacenes as $almacen)
                @include('partials.almacen-envio-card', [
                    'almacen' => $almacen,
                    'isSelected' => (int) $selectedId === (int) $almacen->almacenid,
                    'resumenCapacidad' => $resumenesCapacidad[$almacen->almacenid] ?? null,
                ])
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

        @if($habilitarBusqueda)
            <div class="almacen-section-actions">
                <button type="button" class="btn btn-outline-success btn-sm font-weight-bold btn-buscar-almacenes"
                        data-toggle="modal" data-target="#{{ $modalId }}" data-section="{{ $sectionId }}">
                    <i class="fas fa-search mr-1"></i> Buscar
                </button>
            </div>
        @endif

        <input type="hidden" name="almacenid" id="{{ $hiddenInputId }}" value="{{ $selectedId }}">
    </div>

    @stack('almacen-envio-extra-'.$sectionId)
</div>

@if($habilitarBusqueda)
    @include('partials.almacen-envio-modal', [
        'sectionId' => $sectionId,
        'modalId' => $modalId,
        'mapaId' => 'mapaAlmacenes-' . $sectionId,
        'etiquetaAmbito' => $etiquetaAmbito,
    ])
@endif
