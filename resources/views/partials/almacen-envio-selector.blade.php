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
    $modoPreview = $modoPreview ?? false;
    $almacenPreview = $almacenPreview ?? null;
    $resumenPreview = $resumenPreview ?? null;
    $almacenesMasUsados = $almacenesMasUsados ?? $almacenes;
    $almacenesMenosUsados = $almacenesMenosUsados ?? collect();
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

    @if($modoPreview)
        <input type="hidden" name="almacenid" id="{{ $hiddenInputId }}" value="{{ $selectedId }}">

        <div id="almacenOptions-{{ $sectionId }}">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap:.5rem;">
                <p class="small text-muted mb-0" id="almacen-destacados-hint-{{ $sectionId }}">
                    <i class="fas fa-info-circle mr-1"></i>
                    Se muestran los {{ $almacenesMasUsados->count() }} almacenes {{ $etiquetaAmbito }}s <strong>más usados</strong>.
                </p>
                @if($almacenesTodos && $almacenesTodos->count() > 4)
                    <div class="btn-group btn-group-sm almacen-destacados-filtro" role="group" aria-label="Orden de almacenes destacados">
                        <button type="button" class="btn btn-success active" data-filtro="mas" data-section="{{ $sectionId }}">
                            <i class="fas fa-fire mr-1"></i> Más usados
                        </button>
                        <button type="button" class="btn btn-outline-success" data-filtro="menos" data-section="{{ $sectionId }}">
                            <i class="fas fa-leaf mr-1"></i> Menos usados
                        </button>
                    </div>
                @endif
            </div>

            <div class="row almacen-destacados-grid" data-orden="mas" id="almacenesContainer-mas-{{ $sectionId }}">
                @forelse($almacenesMasUsados as $almacen)
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

            @if($almacenesTodos && $almacenesTodos->count() > 4)
                <div class="row almacen-destacados-grid d-none" data-orden="menos" id="almacenesContainer-menos-{{ $sectionId }}">
                    @foreach($almacenesMenosUsados as $almacen)
                        @include('partials.almacen-envio-card', [
                            'almacen' => $almacen,
                            'isSelected' => (int) $selectedId === (int) $almacen->almacenid,
                            'resumenCapacidad' => $resumenesCapacidad[$almacen->almacenid] ?? null,
                        ])
                    @endforeach
                </div>
            @endif
        </div>

        <div class="d-flex flex-wrap align-items-center almacen-preview-actions mt-2" style="gap:.5rem;">
            @if($habilitarBusqueda)
                <button type="button" class="btn btn-outline-success btn-sm font-weight-bold btn-buscar-almacenes"
                        data-section="{{ $sectionId }}" data-modal="{{ $modalId }}">
                    <i class="fas fa-search mr-1"></i> Buscar todos
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold btn-ver-mapa-almacenes"
                        data-section="{{ $sectionId }}" data-modal="{{ $modalId }}">
                    <i class="fas fa-map-marked-alt mr-1"></i> Ver en mapa
                </button>
                <span class="small text-muted">Buscar abre el listado con filtros; Ver en mapa muestra todos los almacenes en el mapa.</span>
            @endif
        </div>
        @if($habilitarBusqueda)
            @include('partials.almacen-envio-modal', [
                'sectionId' => $sectionId,
                'modalId' => $modalId,
                'mapaId' => 'mapaAlmacenes-' . $sectionId,
                'etiquetaAmbito' => $etiquetaAmbito,
            ])
        @endif
    @else
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

    @if($habilitarBusqueda)
        @include('partials.almacen-envio-modal', [
            'sectionId' => $sectionId,
            'modalId' => $modalId,
            'mapaId' => 'mapaAlmacenes-' . $sectionId,
            'etiquetaAmbito' => $etiquetaAmbito,
        ])
    @endif
    @endif

    <div id="almacenHoverPreview-{{ $sectionId }}" class="almacen-hover-preview" aria-hidden="true"></div>

    @stack('almacen-envio-extra-'.$sectionId)
</div>
