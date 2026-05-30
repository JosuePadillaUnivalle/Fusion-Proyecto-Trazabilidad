@props([
    'limpiarUrl',
    'resultados' => null,
])

<div class="d-flex flex-wrap align-items-center mt-1 filtros-form-actions" style="gap: 8px;">
    <button type="submit" class="btn btn-primary btn-sm">
        <i class="fas fa-search mr-1"></i> Aplicar
    </button>
    <a href="{{ $limpiarUrl }}" class="btn btn-outline-secondary btn-sm filtros-limpiar">
        <i class="fas fa-times mr-1"></i> Limpiar
    </a>
    @if($resultados !== null)
        <span class="small text-muted ml-1">
            <i class="fas fa-filter mr-1"></i>{{ $resultados }} resultado(s)
        </span>
    @endif
    {{ $slot ?? '' }}
</div>
