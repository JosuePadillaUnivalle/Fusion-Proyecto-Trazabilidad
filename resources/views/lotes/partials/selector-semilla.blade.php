@php
    $selectorId = $selectorId ?? 'lote_semilla';
    $insumoSemillaId = $insumoSemillaId ?? '';
    $insumoSemillaLabel = $insumoSemillaLabel ?? '';
    $cantidadSemillaPlanificada = $cantidadSemillaPlanificada ?? old('cantidad_semilla_planificada', '');
    $cantidadSemillaUnidad = $cantidadSemillaUnidad ?? old('cantidad_semilla_unidad', 'kg');
    $semillaStockInicial = $semillaStockInicial ?? null;
    $omitirCantidadSemilla = $omitirCantidadSemilla ?? false;
    $semillaRequerida = $semillaRequerida ?? false;
@endphp

<div class="form-group">
    <label><i class="fas fa-seedling mr-1"></i> Semilla / cultivo a cosechar @if($semillaRequerida)<span class="text-danger">*</span>@endif</label>
    <div class="d-flex flex-wrap align-items-start" style="gap: 8px;">
        <div class="flex-grow-1" style="min-width: 220px;">
            @include('partials.selector-catalogo', [
                'id' => $selectorId,
                'name' => 'insumosemillaid',
                'value' => $insumoSemillaId,
                'labelSelected' => $insumoSemillaLabel,
                'endpoint' => route('catalogo-selector.insumos'),
                'params' => ['tipo_slug' => 'material_siembra'],
                'allowEmpty' => ! $semillaRequerida,
                'placeholderEmpty' => $semillaRequerida ? 'Seleccione semilla del inventario' : 'Opcional — seleccione semilla del inventario',
                'title' => 'Seleccionar semilla',
                'searchPlaceholder' => 'Nombre de semilla o material de siembra…',
                'searchLabel' => 'Buscar semilla',
                'modalIcon' => 'fa-seedling',
                'rowIcon' => 'fa-seedling',
                'inputGroup' => true,
                'required' => $semillaRequerida,
            ])
            <div id="semillaStockDisponible_{{ $selectorId }}"
                 class="small mt-1 mb-0 semilla-stock-disponible {{ $semillaStockInicial ? '' : 'd-none' }} {{ ($semillaStockInicial['sin_stock'] ?? false) ? 'text-danger' : 'text-muted' }}">
                @if($semillaStockInicial)
                    <i class="fas fa-boxes mr-1"></i>
                    Disponible en inventario:
                    <strong>{{ number_format($semillaStockInicial['stock'], 2, ',', '.') }} {{ $semillaStockInicial['unidad'] }}</strong>
                    @if($semillaStockInicial['sin_stock'])
                        <span class="text-danger">(sin stock)</span>
                    @endif
                @endif
            </div>
        </div>
        <a href="{{ route('insumos.create') }}"
           target="_blank" rel="noopener"
           class="btn btn-outline-success align-self-start"
           title="Registrar un nuevo insumo en inventario">
            <i class="fas fa-plus mr-1"></i> Agregar insumo
        </a>
    </div>

    @unless($omitirCantidadSemilla)
    <div id="cantidadSemillaWrap" class="{{ $insumoSemillaId ? '' : 'd-none' }} mt-2">
        <label class="small font-weight-bold mb-1 d-block" for="cantidad_semilla_planificada">
            <i class="fas fa-calculator mr-1"></i> Material de siembra a utilizar
        </label>
        <div class="input-group">
            <input type="number"
                   step="0.001"
                   min="0"
                   name="cantidad_semilla_planificada"
                   id="cantidad_semilla_planificada"
                   class="form-control"
                   value="{{ $cantidadSemillaPlanificada !== '' && $cantidadSemillaPlanificada !== null ? $cantidadSemillaPlanificada : '' }}"
                   placeholder="Se calcula al elegir semilla y hectáreas">
            <div class="input-group-append">
                <span class="input-group-text" id="cantidadSemillaUnidad">{{ $cantidadSemillaUnidad }}</span>
            </div>
        </div>
        <p class="campo-guia mb-1">
            Kg del insumo que planea usar (según dosis × hectáreas). Puede ajustarlo.
        </p>
        <div id="dosisSiembraPreview" class="alert alert-light border small mb-0 d-none">
            <i class="fas fa-info-circle text-success mr-1"></i>
            <span id="dosisSiembraTexto"></span>
        </div>
    </div>
    @endunless
</div>
