{{--
    @param string $action
    @param array $campos  each: name, label, type (text|select|date|checkbox), options?, placeholder?, col?, value?
    @param string $botonesAlineacion  start|end|center (default end)
--}}
@php
    $botonesAlineacion = $botonesAlineacion ?? 'end';
    $justify = match ($botonesAlineacion) {
        'start' => 'justify-content-start',
        'center' => 'justify-content-center',
        default => 'justify-content-end',
    };
@endphp
<div class="modulo-filtros-panel">
    <form method="GET" action="{{ $action }}">
        <div class="form-row align-items-end">
            @foreach($campos as $campo)
                @php
                    $col = $campo['col'] ?? 'col-md-3';
                    $name = $campo['name'];
                    $val = $campo['value'] ?? request($name);
                @endphp
                <div class="{{ $col }} form-group mb-3 mb-lg-0">
                    <label>{{ $campo['label'] }}</label>
                    @if(($campo['type'] ?? 'text') === 'select')
                        <select name="{{ $name }}" class="form-control">
                            <option value="">{{ $campo['placeholder'] ?? 'Todos' }}</option>
                            @foreach($campo['options'] ?? [] as $optVal => $optLabel)
                                <option value="{{ $optVal }}" @selected((string)$val === (string)$optVal)>{{ $optLabel }}</option>
                            @endforeach
                        </select>
                    @elseif(($campo['type'] ?? '') === 'date')
                        <input type="date" name="{{ $name }}" class="form-control" value="{{ $val }}">
                    @elseif(($campo['type'] ?? '') === 'checkbox')
                        <div class="form-check filtro-checkbox-compact mt-1 mb-0">
                            <input type="checkbox" class="form-check-input" id="filtro-{{ $name }}"
                                   name="{{ $name }}" value="1" @checked($val)>
                            <label class="form-check-label" for="filtro-{{ $name }}">{{ $campo['checkbox_label'] ?? 'Sí' }}</label>
                        </div>
                    @else
                        <input type="text" name="{{ $name }}" class="form-control"
                               placeholder="{{ $campo['placeholder'] ?? '' }}"
                               value="{{ $val }}">
                    @endif
                </div>
            @endforeach
        </div>
        <div class="d-flex flex-wrap {{ $justify }} modulo-filtros-acciones mt-2 pt-1">
            <button type="submit" class="btn btn-success btn-filtro-modulo">
                <i class="fas fa-filter mr-1"></i> Filtrar
            </button>
            @if(request()->except('page'))
            <a href="{{ $action }}" class="btn btn-outline-secondary btn-filtro-modulo">
                <i class="fas fa-times mr-1"></i> Limpiar
            </a>
            @endif
        </div>
    </form>
</div>
