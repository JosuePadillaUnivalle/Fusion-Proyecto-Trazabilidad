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
    $placeholder = $placeholder ?? null;
    $placeholderEmpty = $placeholderEmpty ?? 'Opcional — sin asignar';
    $placeholderResolved = $placeholder ?? ($allowEmpty ? $placeholderEmpty : 'Elegir…');
    $tieneSeleccion = ($labelSelected !== '' && $labelSelected !== null) || ($value !== '' && $value !== null && $value !== '0');
    $params = $params ?? [];
    $filter = $filter ?? null;
    $filterAlmacen = $filterAlmacen ?? null;
    $inputGroup = $inputGroup ?? false;
    $size = $size ?? '';
    $showLabel = $showLabel ?? ! $inputGroup;
    $searchLabel = $searchLabel ?? null;
    $modalIcon = $modalIcon ?? null;
    $rowIcon = $rowIcon ?? null;
    $colDetalle = $colDetalle ?? null;
    $colNombre = $colNombre ?? null;
    $theme = $theme ?? null;
    $variant = $variant ?? 'filtros';
    $esVarianteFiltros = $variant !== 'legacy';
@endphp

<div class="selector-catalogo-wrapper {{ $inputGroup ? 'flex-grow-1 w-100 mb-0' : 'form-group' }}{{ $esVarianteFiltros ? ' selector-catalogo--filtros' : '' }}" id="selector_wrap_{{ $id }}">
    @if($showLabel)
        <label class="small font-weight-bold mb-1 d-block">
            @if($icon)<i class="fas {{ $icon }} mr-1"></i>@endif
            {!! $label !!}
            @if($required)<span class="text-danger">*</span>@endif
        </label>
    @endif

    @if($esVarianteFiltros)
        <div class="selector-filtros-field" role="combobox" aria-haspopup="dialog" aria-expanded="false">
            <input type="text"
                   class="selector-filtros-field__input selector-catalogo-label {{ ! $tieneSeleccion ? 'is-empty' : '' }}"
                   value="{{ $tieneSeleccion ? $labelSelected : '' }}"
                   placeholder="{{ $placeholderResolved }}"
                   readonly
                   tabindex="-1">
            <input type="hidden"
                   name="{{ $name }}"
                   value="{{ $value }}"
                   class="selector-catalogo-value"
                   @if($required) required @endif>
            <div class="selector-filtros-field__actions">
                @if($allowEmpty)
                    <button type="button"
                            class="selector-filtros-field__clear{{ $tieneSeleccion ? '' : ' is-hidden' }}"
                            data-selector-clear="{{ $id }}"
                            title="Quitar filtro"
                            aria-label="Quitar filtro">
                        <i class="fas fa-times"></i>
                    </button>
                @endif
                <button type="button"
                        class="selector-filtros-field__open"
                        data-selector-open="{{ $id }}"
                        title="Abrir catálogo">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </div>
        </div>
    @else
    <div class="input-group{{ $size === 'sm' ? ' input-group-sm' : '' }}">
        <input type="text"
               class="form-control selector-catalogo-label{{ $size === 'sm' ? ' form-control-sm' : '' }} {{ ! $tieneSeleccion ? 'text-muted' : '' }}"
               value="{{ $tieneSeleccion ? $labelSelected : '' }}"
               placeholder="{{ $placeholder ?? ($allowEmpty ? $placeholderEmpty : 'Clic en Buscar para elegir…') }}"
               readonly
               tabindex="-1">
        <input type="hidden"
               name="{{ $name }}"
               value="{{ $value }}"
               class="selector-catalogo-value"
               @if($required) required @endif>
        <div class="input-group-append">
            <button type="button" class="btn btn-outline-primary{{ $size === 'sm' ? ' btn-sm' : '' }}" data-selector-open="{{ $id }}" title="Buscar y filtrar">
                <i class="fas fa-search mr-1"></i> Buscar
            </button>
            @if($allowEmpty)
                <button type="button" class="btn btn-outline-secondary{{ $size === 'sm' ? ' btn-sm' : '' }}" data-selector-clear="{{ $id }}" title="Quitar selección">
                    <i class="fas fa-times"></i>
                </button>
            @endif
        </div>
    </div>
    @endif
    @if($help && ! $showLabel)
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
        colDetalle: @json($colDetalle ?? 'Detalle'),
        colNombre: @json($colNombre ?? 'Nombre'),
        theme: @json($theme ?? null),
    });
});
</script>
@endpush
