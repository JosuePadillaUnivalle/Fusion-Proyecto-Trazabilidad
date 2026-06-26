@php
    $prod = $produccion_pendiente_almacen ?? null;
    $sectionId = 'almacenSectionCampo';
    $selectedId = old('almacenid', $almacen_destino_id ?? null);
    $cantidadKgEnvio = $prod
        ? (float) ($prod->cantidad_base ?? $prod->cantidad)
        : null;
@endphp
@if($prod)
<div class="card lote-section-card mb-3 border-0 shadow-sm" id="panel-enviar-almacen-campo" style="border-radius:14px;overflow:hidden">
    <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
        <span><i class="fas fa-warehouse text-warning mr-2"></i>Almacenaje de la cosecha</span>
    </div>
    <div class="card-body p-0">
        <div class="alert alert-light border small mx-3 mt-3 mb-0">
            <i class="fas fa-check-circle text-success mr-1"></i>
            <strong>Lote certificado</strong> — cosecha de
            <strong>{{ number_format((float) $prod->cantidad, 2) }} {{ $prod->unidadMedida->abreviatura ?? 'kg' }}</strong>
            lista para ingresar al inventario agrícola.
        </div>
        <form method="POST" action="{{ route('lotes.enviar-almacen', $lote) }}" id="formEnviarAlmacenCampo" class="p-3">
            @csrf
            <input type="hidden" name="produccionid" value="{{ $prod->produccionid }}">
            @push('almacen-envio-extra-'.$sectionId)
            <div class="almacen-section-actions">
                <button type="submit" class="btn btn-warning btn-sm font-weight-bold text-dark">
                    <i class="fas fa-warehouse mr-1"></i>Confirmar envío al almacén
                </button>
            </div>
            @endpush
            @include('partials.almacen-envio-selector', [
                'almacenes' => $almacenes ?? collect(),
                'almacenesMasUsados' => $almacenesMasUsados ?? $almacenes ?? collect(),
                'almacenesMenosUsados' => $almacenesMenosUsados ?? collect(),
                'almacenesTodos' => $almacenesTodos ?? collect(),
                'resumenesCapacidad' => $resumenesCapacidad ?? [],
                'sectionId' => $sectionId,
                'hiddenInputId' => 'almacenidEnvio',
                'selectedAlmacenId' => $selectedId,
                'etiquetaAmbito' => 'agrícola',
                'almacenRequerido' => true,
                'modoPreview' => true,
                'crearAlmacenUrl' => route('almacen-agricola.create'),
                'emptyTexto' => 'No hay almacenes agrícolas registrados.',
                'guiaTexto' => 'Toda la cosecha certificada debe ingresar al inventario del almacén elegido. El sistema valida la capacidad disponible y puede sugerir un almacén según el cultivo.',
                'instruccion' => 'Seleccione el almacén agrícola donde guardar la cosecha',
                'productoResumen' => trim(($lote->cultivo->nombre ?? $lote->nombre).' — Lote '.$lote->nombre),
                'cantidadResumen' => number_format((float) $prod->cantidad, 2).' '.($prod->unidadMedida->abreviatura ?? 'kg'),
            ])
            @error('almacenid')
                <small class="text-danger d-block mt-2">{{ $message }}</small>
            @enderror
        </form>
    </div>
</div>

@push('scripts')
@include('partials.almacen-envio-scripts', [
    'sectionId' => $sectionId,
    'hiddenInputId' => 'almacenidEnvio',
    'formSelector' => '#formEnviarAlmacenCampo',
    'almacenesCatalogo' => $almacenesCatalogo ?? [],
    'requiereAlmacen' => true,
    'cantidadFija' => $cantidadKgEnvio,
    'productoHint' => $lote->cultivo->nombre ?? $lote->nombre,
])
@endpush
@endif
