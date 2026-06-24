@php
    $periodoDesde = $periodo['desde'] ?? request('fecha_desde');
    $periodoHasta = $periodo['hasta'] ?? request('fecha_hasta', request('fecha_fin'));
    $filtrosCampos = $filtrosCampos ?? [];
    $filtrosOpciones = $filtrosOpciones ?? [];
    $accent = $item['accent'] ?? 'forest';
@endphp

<div class="rpt-page rpt-accent--{{ $accent }}">
    <nav class="rpt-breadcrumb no-print" aria-label="breadcrumb">
        <a href="{{ route('reportes.index') }}"><i class="fas fa-chart-bar mr-1"></i>Centro de reportes</a>
        <span class="text-muted mx-1">/</span>
        <span class="text-muted">{{ $item['title'] ?? 'Reporte' }}</span>
    </nav>

    <div class="rpt-header">
        <h2>
            <span class="rpt-header__icon mr-2"><i class="fas {{ $item['icon'] ?? 'fa-file-alt' }}"></i></span>
            {{ $item['title'] }}
        </h2>
        @if(!empty($item['subtitle']))
            <p>{{ $item['subtitle'] }}</p>
        @endif
    </div>

    <div class="rpt-filtros no-print modulo-filtros-panel">
        <form method="GET" action="{{ route($item['route']) }}" class="rpt-filtros__row">
            @foreach($filtrosCampos as $campo)
                @if(($campo['tipo'] ?? '') === 'periodo')
                    <div>
                        <label for="fecha_desde">Desde</label>
                        <input type="date" class="form-control" id="fecha_desde" name="fecha_desde" value="{{ $periodoDesde }}">
                    </div>
                    <div>
                        <label for="fecha_hasta">Hasta</label>
                        <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta" value="{{ $periodoHasta }}">
                    </div>
                @elseif(($campo['tipo'] ?? '') === 'select')
                    @php
                        $name = $campo['name'];
                        $opciones = $filtrosOpciones[$campo['opciones'] ?? ''] ?? [];
                        $valor = request($name, '');
                    @endphp
                    <div>
                        <label for="filtro_{{ $name }}">{{ $campo['label'] }}</label>
                        <select class="form-control" id="filtro_{{ $name }}" name="{{ $name }}">
                            @foreach($opciones as $clave => $etiqueta)
                                <option value="{{ $clave }}" @selected((string) $valor === (string) $clave)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>
                @elseif(($campo['tipo'] ?? '') === 'text')
                    <div class="rpt-filtros__grow">
                        <label for="filtro_{{ $campo['name'] }}">{{ $campo['label'] }}</label>
                        <input type="text" class="form-control" id="filtro_{{ $campo['name'] }}" name="{{ $campo['name'] }}"
                               value="{{ request($campo['name'], '') }}" placeholder="{{ $campo['placeholder'] ?? '' }}">
                    </div>
                @elseif(($campo['tipo'] ?? '') === 'checkbox')
                    <div class="rpt-filtros__check">
                        <div class="custom-control custom-checkbox mt-4">
                            <input type="checkbox" class="custom-control-input" id="filtro_{{ $campo['name'] }}"
                                   name="{{ $campo['name'] }}" value="1" @checked(request()->boolean($campo['name']))>
                            <label class="custom-control-label" for="filtro_{{ $campo['name'] }}">{{ $campo['label'] }}</label>
                        </div>
                    </div>
                @endif
            @endforeach
            <div class="rpt-filtros__actions">
                <button type="submit" class="btn btn-sm rpt-btn-accent"><i class="fas fa-filter mr-1"></i>Filtrar</button>
                <a href="{{ route($item['route']) }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                <button type="button" class="btn btn-sm rpt-btn-outline-accent" onclick="rptPreviewPdf()"><i class="fas fa-eye mr-1"></i>Vista previa PDF</button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="rptExportPdf()"><i class="fas fa-file-pdf mr-1"></i>Descargar PDF</button>
                <button type="button" class="btn btn-sm btn-outline-success" onclick="rptExportExcel()"><i class="fas fa-file-excel mr-1"></i>Excel</button>
            </div>
        </form>
    </div>
