@php
    $prod = $produccion_pendiente_almacen ?? null;
    $presentacion = $presentacion_cosecha_almacen ?? null;
    $sectionId = 'almacenSectionEnvio';
    $modalBuscarId = 'modalAlmacenes-' . $sectionId;
    $selectedId = old('almacenid', $almacen_destino_id ?? null);
    $almacenDest = $almacen_destino_preseleccionado ?? null;
    $resumenDest = $almacen_destino_resumen ?? null;
    $ingresoKg = $prod ? (float) ($prod->cantidad_base ?? $prod->cantidad) : 0;
@endphp

<div class="modal fade" id="modalEnviarAlmacenCampo" tabindex="-1" role="dialog"
     aria-labelledby="modalEnviarAlmacenCampoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg envio-almacen-modal">
            @if($prod)
                <form method="POST" action="{{ route('lotes.enviar-almacen', $lote) }}" id="formEnviarAlmacenCampo">
                    @csrf
                    <input type="hidden" name="produccionid" value="{{ $prod->produccionid }}">

                    <div class="modal-header envio-almacen-modal__header py-3 px-4">
                        <div>
                            <h5 class="modal-title font-weight-bold mb-0" id="modalEnviarAlmacenCampoLabel">
                                <i class="fas fa-warehouse mr-2"></i>Confirmar envío al almacén
                            </h5>
                            <p class="envio-almacen-modal__subtitle mb-0">Lote certificado — cosecha lista para inventario agrícola</p>
                        </div>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body px-4 py-4">
                        <div class="envio-cosecha-resumen mb-3">
                            <div class="envio-cosecha-resumen__head">
                                <span class="envio-cosecha-resumen__icon"><i class="fas fa-seedling"></i></span>
                                <div>
                                    <strong class="envio-cosecha-resumen__titulo">{{ $lote->cultivo->nombre ?? $lote->nombre }}</strong>
                                    @if($presentacion['calibre_nombre'] ?? null)
                                        <span class="envio-cosecha-resumen__calibre">{{ $presentacion['calibre_nombre'] }}</span>
                                    @endif
                                </div>
                            </div>
                            @if($presentacion['ok'] ?? false)
                                <div class="envio-cosecha-resumen__metrics">
                                    <div class="envio-cosecha-metric">
                                        <span class="envio-cosecha-metric__value">{{ number_format($presentacion['empaques'], 0, ',', '.') }}</span>
                                        <span class="envio-cosecha-metric__label">{{ $presentacion['empaque_label'] ?? 'Cajas' }}</span>
                                    </div>
                                    <div class="envio-cosecha-metric">
                                        <span class="envio-cosecha-metric__value">{{ number_format($presentacion['unidades'], 0, ',', '.') }}</span>
                                        <span class="envio-cosecha-metric__label">Unidades</span>
                                    </div>
                                    <div class="envio-cosecha-metric envio-cosecha-metric--highlight">
                                        <span class="envio-cosecha-metric__value">{{ number_format($ingresoKg, 0, ',', '.') }}<small> kg</small></span>
                                        <span class="envio-cosecha-metric__label">Peso cosecha</span>
                                    </div>
                                </div>
                            @else
                                <p class="mb-0 mt-2">
                                    <strong>{{ number_format($ingresoKg, 0, ',', '.') }} kg</strong>
                                    <span class="text-muted small d-block mt-1">Configure el calibre del lote para ver cajas y unidades.</span>
                                </p>
                            @endif
                        </div>

                        @include('partials.almacen-envio-resumen-destino', [
                            'sectionId' => $sectionId,
                            'almacen' => $almacenDest,
                            'resumenCapacidad' => $resumenDest,
                            'ingresoKg' => $ingresoKg,
                        ])

                        <input type="hidden" name="almacenid" id="almacenidEnvio" value="{{ $selectedId }}">

                        <div class="envio-cambiar-almacen mt-2">
                            <button type="button"
                                    class="btn btn-outline-success btn-sm font-weight-bold btn-buscar-almacenes"
                                    data-toggle="modal"
                                    data-target="#{{ $modalBuscarId }}"
                                    data-section="{{ $sectionId }}">
                                <i class="fas fa-exchange-alt mr-1"></i> Cambiar almacén
                            </button>
                            <span class="small text-muted d-block mt-2">
                                En la lista o el mapa verá cuánto ocupará esta cosecha en cada almacén antes de elegir.
                            </span>
                        </div>

                        @error('almacenid')
                            <small class="text-danger d-block mt-2">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="modal-footer border-0 envio-almacen-modal__footer px-4 py-3">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning font-weight-bold text-dark px-4">
                            <i class="fas fa-warehouse mr-1"></i> Confirmar envío
                        </button>
                    </div>
                </form>
            @else
                <div class="modal-header py-3 px-4 bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold mb-0">
                        <i class="fas fa-warehouse mr-2"></i>Envío al almacén
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 py-4">
                    <p class="small text-muted mb-0">No hay cosecha pendiente de almacenar.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@if($prod && ($almacenesTodos ?? collect())->isNotEmpty())
    @include('partials.almacen-envio-modal', [
        'sectionId' => $sectionId,
        'modalId' => $modalBuscarId,
        'mapaId' => 'mapaAlmacenes-' . $sectionId,
        'etiquetaAmbito' => 'agrícola',
    ])
@endif

@push('scripts')
@include('partials.almacen-envio-scripts', [
    'sectionId' => $sectionId,
    'hiddenInputId' => 'almacenidEnvio',
    'formSelector' => '#formEnviarAlmacenCampo',
    'almacenesCatalogo' => $almacenesCatalogo ?? [],
    'requiereAlmacen' => true,
    'cantidadFijaKg' => $ingresoKg,
    'productoHint' => $lote->cultivo->nombre ?? $lote->nombre,
    'resumenDestinoId' => 'envioAlmacenResumen-' . $sectionId,
])
@endpush
