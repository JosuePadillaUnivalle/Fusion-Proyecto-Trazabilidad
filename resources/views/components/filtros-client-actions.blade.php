@props([
    'limpiarId' => 'btnLimpiarFiltros',
    'aplicarId' => 'btnAplicarFiltros',
])

<div class="d-flex flex-wrap align-items-center mt-1 filtros-form-actions" style="gap: 8px;">
    <button type="button" class="btn btn-primary btn-sm" id="{{ $aplicarId }}">
        <i class="fas fa-search mr-1"></i> Aplicar
    </button>
    <button type="button" class="btn btn-outline-secondary btn-sm" id="{{ $limpiarId }}">
        <i class="fas fa-times mr-1"></i> Limpiar
    </button>
    {{ $slot ?? '' }}
</div>
