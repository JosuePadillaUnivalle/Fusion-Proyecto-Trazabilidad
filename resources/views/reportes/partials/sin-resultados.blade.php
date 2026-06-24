@php
    $filtrosActivos = $filtrosActivos ?? false;
    $filtrosEtiquetas = $filtrosEtiquetas ?? [];
    $sinDatos = $sinDatos ?? false;
@endphp

@if($filtrosActivos && $sinDatos)
    <div class="alert alert-light border rpt-sin-resultados mb-3 no-print" role="status">
        <i class="fas fa-info-circle mr-1 text-muted"></i>
        No hay registros con los filtros aplicados.
        @if(!empty($filtrosEtiquetas))
            <span class="text-muted">({{ implode(' · ', $filtrosEtiquetas) }})</span>
        @endif
        <a href="{{ route($item['route']) }}" class="alert-link ml-1">Limpiar filtros</a>
    </div>
@endif
