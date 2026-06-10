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

@once
    @push('styles')
    <style>
        #modalSelectorCatalogo .selector-catalogo-row { cursor: pointer; }
        #modalSelectorCatalogo .selector-catalogo-row:hover { background: #f4f9f4; }
        #modalSelectorCatalogo .modal-body { max-height: 68vh; overflow-y: auto; }
        #modalSelectorCatalogo .selector-filtros-panel {
            background: linear-gradient(145deg, #f8fbf8 0%, #eef6ef 100%);
            border: 1px solid #cfe8d4;
            border-radius: 12px;
            padding: 1rem 1.1rem;
            box-shadow: 0 2px 10px rgba(44, 85, 48, 0.06);
        }
        #modalSelectorCatalogo .selector-filtros-panel-head {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 0.85rem;
        }
        #modalSelectorCatalogo .selector-filtros-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: linear-gradient(135deg, #2c5530, #4a7c59);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        #modalSelectorCatalogo .selector-filtros-title {
            font-weight: 700;
            color: #1e4620;
            font-size: 0.95rem;
            line-height: 1.2;
        }
        #modalSelectorCatalogo .selector-filtros-sub {
            font-size: 0.78rem;
            color: #5a6f5c;
            margin-top: 2px;
        }
        #modalSelectorCatalogo .selector-almacen-toolbar { margin-bottom: 0.75rem; }
        #modalSelectorCatalogo .selector-almacen-search .input-group-text {
            background: #fff;
            border-color: #b8d4be;
        }
        #modalSelectorCatalogo .selector-almacen-search .form-control {
            border-color: #b8d4be;
        }
        #modalSelectorCatalogo .selector-almacen-search .form-control:focus {
            border-color: #4a7c59;
            box-shadow: 0 0 0 0.15rem rgba(74, 124, 89, 0.2);
        }
        #modalSelectorCatalogo .selector-almacen-lista {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            max-height: 168px;
            overflow-y: auto;
            padding: 2px;
        }
        #modalSelectorCatalogo .selector-almacen-card {
            border: 2px solid #d4e5d8;
            background: #fff;
            border-radius: 10px;
            padding: 0.5rem 0.75rem;
            min-width: 140px;
            max-width: 100%;
            flex: 1 1 calc(33.333% - 8px);
            cursor: pointer;
            text-align: left;
            transition: border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
        }
        @media (max-width: 576px) {
            #modalSelectorCatalogo .selector-almacen-card { flex: 1 1 100%; }
        }
        #modalSelectorCatalogo .selector-almacen-card:hover {
            border-color: #4a7c59;
            background: #fafffa;
            box-shadow: 0 2px 8px rgba(44, 85, 48, 0.1);
        }
        #modalSelectorCatalogo .selector-almacen-card.active {
            border-color: #2c5530;
            background: linear-gradient(135deg, #2c5530, #4a7c59);
            color: #fff;
            box-shadow: 0 3px 12px rgba(44, 85, 48, 0.25);
        }
        #modalSelectorCatalogo .selector-almacen-card .alm-nombre {
            font-weight: 700;
            font-size: 0.82rem;
            line-height: 1.25;
            display: block;
        }
        #modalSelectorCatalogo .selector-almacen-card .alm-meta {
            font-size: 0.72rem;
            opacity: 0.85;
            margin-top: 2px;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        #modalSelectorCatalogo .selector-almacen-card:not(.active) .alm-meta { color: #6c757d; }
        #modalSelectorCatalogo .selector-almacen-card--todos .alm-nombre i { margin-right: 4px; }
        #modalSelectorCatalogo .selector-almacen-seleccionado {
            margin-top: 0.75rem;
            padding: 0.5rem 0.75rem;
            background: #fff;
            border: 1px solid #b8dfc0;
            border-radius: 8px;
            font-size: 0.82rem;
            color: #1e4620;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        #modalSelectorCatalogo .selector-almacen-seleccionado i { color: #28a745; }
        #modalSelectorCatalogo .selector-campo-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.35rem;
            display: block;
        }
        #modalSelectorCatalogo .selector-cultivo-select {
            border-color: #ced4da;
            border-radius: 6px;
        }
        #modalSelectorCatalogo .selector-producto-panel {
            padding: 0.15rem 0.1rem 0;
        }
    </style>
    @endpush

    @include('partials.selector-catalogo-modal')

    @push('scripts')
    <script src="{{ asset('js/selector-catalogo.js') }}"></script>
    @endpush
@endonce

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
    });
});
</script>
@endpush
