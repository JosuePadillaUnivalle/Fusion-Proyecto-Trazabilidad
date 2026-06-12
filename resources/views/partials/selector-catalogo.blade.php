@php
    $id = $id ?? 'selector_'.uniqid();
    $name = $name ?? 'id';
    $label = $label ?? 'Seleccionar';
    $icon = $icon ?? 'fa-list';
    $value = $value ?? '';
    $labelSelected = $labelSelected ?? '';
    $endpoint = $endpoint ?? '';
    $title = $title ?? $label;
    $searchPlaceholder = $searchPlaceholder ?? 'Buscar por nombre…';
    $help = $help ?? null;
    $required = $required ?? false;
    $allowEmpty = $allowEmpty ?? false;
    $emptyLabel = $emptyLabel ?? '— Sin selección —';
    $placeholderEmpty = $placeholderEmpty ?? 'Opcional — sin asignar';
    $tieneSeleccion = ($labelSelected !== '' && $labelSelected !== null) || ($value !== '' && $value !== null && $value !== '0');
    $params = $params ?? [];
    $filter = $filter ?? null;
    $filterAlmacen = $filterAlmacen ?? null;
    $inputGroup = $inputGroup ?? false;
    $searchLabel = $searchLabel ?? null;
    $modalIcon = $modalIcon ?? null;
    $rowIcon = $rowIcon ?? null;
    $colDetalle = $colDetalle ?? null;
@endphp

<div class="selector-catalogo-wrapper {{ $inputGroup ? 'flex-grow-1 w-100 mb-0' : 'form-group' }}" id="selector_wrap_{{ $id }}">
    @unless($inputGroup)
        <label>
            @if($icon)<i class="fas {{ $icon }} mr-1"></i>@endif
            {!! $label !!}
            @if($required)<span class="text-danger">*</span>@endif
        </label>
    @endunless
    <div class="input-group">
        <input type="text"
               class="form-control selector-catalogo-label {{ ! $tieneSeleccion ? 'text-muted' : '' }}"
               value="{{ $tieneSeleccion ? $labelSelected : '' }}"
               placeholder="{{ $allowEmpty ? $placeholderEmpty : 'Clic en Buscar para elegir…' }}"
               readonly
               tabindex="-1">
        <input type="hidden"
               name="{{ $name }}"
               value="{{ $value }}"
               class="selector-catalogo-value"
               @if($required) required @endif>
        <div class="input-group-append">
            <button type="button" class="btn btn-outline-primary" data-selector-open="{{ $id }}" title="Buscar y filtrar">
                <i class="fas fa-search mr-1"></i> Buscar
            </button>
            @if($allowEmpty)
                <button type="button" class="btn btn-outline-secondary" data-selector-clear="{{ $id }}" title="Quitar selección">
                    <i class="fas fa-times"></i>
                </button>
            @endif
        </div>
    </div>
    @if($help && !$inputGroup)
        <p class="campo-guia mb-0">{{ $help }}</p>
    @endif
</div>

@include('partials.selector-catalogo-assets')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.CatalogoSelector) return;
    CatalogoSelector.register(@json($id), {
        endpoint: @json($endpoint),
        title: @json($title),
        searchPlaceholder: @json($searchPlaceholder),
        allowEmpty: @json((bool) $allowEmpty),
        emptyLabel: @json($emptyLabel),
        placeholderEmpty: @json($placeholderEmpty),
        params: @json($params),
        filter: @json($filter),
        filterAlmacen: @json($filterAlmacen),
        searchLabel: @json($searchLabel),
        modalIcon: @json($modalIcon),
        rowIcon: @json($rowIcon),
        colDetalle: @json($colDetalle),
    });
});
</script>
@endpush
