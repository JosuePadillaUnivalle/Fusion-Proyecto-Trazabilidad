@php
    $selectorId = $selectorId ?? 'lote_semilla';
    $insumoSemillaId = $insumoSemillaId ?? '';
    $insumoSemillaLabel = $insumoSemillaLabel ?? '';
    $semillaStockInicial = $semillaStockInicial ?? null;
@endphp

<div class="form-group">
    <label><i class="fas fa-seedling mr-1"></i> Semilla / cultivo a cosechar <span class="text-danger">*</span></label>
    <div class="d-flex flex-wrap align-items-start" style="gap: 8px;">
        <div class="flex-grow-1" style="min-width: 220px;">
            @include('partials.selector-catalogo', [
                'id' => $selectorId,
                'name' => 'insumosemillaid',
                'value' => $insumoSemillaId,
                'labelSelected' => $insumoSemillaLabel,
                'endpoint' => route('catalogo-selector.insumos'),
                'params' => ['tipo_slug' => 'material_siembra'],
                'allowEmpty' => false,
                'required' => true,
                'placeholder' => 'Selecciona semilla del inventario',
                'title' => 'Seleccionar semilla',
                'searchPlaceholder' => 'Nombre de semilla o material de siembra…',
                'searchLabel' => 'Buscar semilla',
                'modalIcon' => 'fa-seedling',
                'rowIcon' => 'fa-seedling',
                'inputGroup' => true,
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
</div>
