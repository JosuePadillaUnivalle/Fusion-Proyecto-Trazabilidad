@php
    $prod = $produccion_pendiente_almacen ?? null;
    $sectionId = 'almacenSectionEnvio';
    $modalBuscarId = 'modalAlmacenes-' . $sectionId;
    $selectedId = old('almacenid', $almacen_destino_id ?? null);
    $almacenDest = $almacen_destino_preseleccionado ?? null;
    $resumenDest = $almacen_destino_resumen ?? null;
@endphp
<div class="modal fade" id="modalEnviarAlmacenCampo" tabindex="-1" role="dialog"
     aria-labelledby="modalEnviarAlmacenCampoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            @if($prod)
                <form method="POST" action="{{ route('lotes.enviar-almacen', $lote) }}" id="formEnviarAlmacenCampo">
                    @csrf
                    <input type="hidden" name="produccionid" value="{{ $prod->produccionid }}">

                    <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #b45309, #f59e0b); color: #fff;">
                        <h5 class="modal-title font-weight-bold mb-0" id="modalEnviarAlmacenCampoLabel">
                            <i class="fas fa-warehouse mr-2"></i>Confirmar envío al almacén
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body px-4 py-4">
                        <p class="small text-muted mb-3">
                            Lote <strong>certificado</strong> — cosecha de
                            <strong>{{ number_format((float) $prod->cantidad, 2) }} {{ $prod->unidadMedida->abreviatura ?? 'kg' }}</strong>
                            lista para ingresar al inventario agrícola.
                        </p>

                        @include('partials.almacen-envio-resumen-destino', [
                            'sectionId' => $sectionId,
                            'almacen' => $almacenDest,
                            'resumenCapacidad' => $resumenDest,
                            'productoResumen' => $lote->nombre,
                            'cantidadResumen' => number_format((float) $prod->cantidad, 2).' '.($prod->unidadMedida->abreviatura ?? 'kg'),
                        ])

                        <input type="hidden" name="almacenid" id="almacenidEnvio" value="{{ $selectedId }}">

                        <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
                            <button type="button"
                                    class="btn btn-outline-success btn-sm font-weight-bold btn-buscar-almacenes"
                                    data-toggle="modal"
                                    data-target="#{{ $modalBuscarId }}"
                                    data-section="{{ $sectionId }}">
                                <i class="fas fa-exchange-alt mr-1"></i> Cambiar almacén
                            </button>
                            <span class="small text-muted">Busque entre todos los almacenes agrícolas disponibles.</span>
                        </div>

                        @error('almacenid')
                            <small class="text-danger d-block mt-2">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="modal-footer border-0 bg-light px-4 py-3">
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
    'cantidadFija' => $prod ? (float) $prod->cantidad : null,
    'productoHint' => $lote->cultivo->nombre ?? $lote->nombre,
    'resumenDestinoId' => 'envioAlmacenResumen-' . $sectionId,
])
@endpush
