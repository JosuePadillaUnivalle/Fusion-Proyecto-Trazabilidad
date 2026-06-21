@props([
    'titulo',
    'icono' => 'fa-list',
    'iconClass' => 'text-success',
    'registros' => 0,
    'registrosLabel' => 'registros',
    'filtrosTarget' => null,
    'viewToggle' => false,
    'viewDefault' => 'table',
    'nuevoHref' => null,
    'nuevoText' => 'Nuevo',
    'nuevoIcon' => 'fa-plus',
    'nuevoCan' => null,
    'registrosText' => null,
])

<div class="card-header d-flex flex-wrap align-items-center justify-content-between">
    <h3 class="card-title mb-0 mr-2">
        <i class="fas {{ $icono }} {{ $iconClass }} mr-1"></i>
        {{ $titulo }}
    </h3>
    <div class="card-tools d-flex align-items-center flex-wrap ml-auto">
        @if(!empty($registrosText))
            <span class="badge badge-light border text-muted badge-registros mr-1">{{ $registrosText }}</span>
        @else
            <span class="badge badge-light border text-muted badge-registros mr-1">{{ $registros }} {{ $registrosLabel }}</span>
        @endif

        @if($viewToggle)
            <div class="btn-group btn-group-sm view-toggle mr-1">
                <button type="button" class="btn btn-default {{ $viewDefault === 'cards' ? 'active' : '' }}" id="btnCardView" title="Tarjetas">
                    <i class="fas fa-th-large"></i>
                </button>
                <button type="button" class="btn btn-default {{ $viewDefault === 'table' ? 'active' : '' }}" id="btnTableView" title="Tabla">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        @endif

        @if($filtrosTarget)
            @include('partials.btn-filtros-toggle', ['target' => $filtrosTarget])
        @endif

        @if(isset($tools))
            {{ $tools }}
        @endif

        @if($nuevoHref)
            @if($nuevoCan)
                @can($nuevoCan)
                    <a href="{{ $nuevoHref }}" class="btn btn-success btn-sm ml-1">
                        <i class="fas {{ $nuevoIcon }} mr-1"></i> {{ $nuevoText }}
                    </a>
                @endcan
            @else
                <a href="{{ $nuevoHref }}" class="btn btn-success btn-sm ml-1">
                    <i class="fas {{ $nuevoIcon }} mr-1"></i> {{ $nuevoText }}
                </a>
            @endif
        @endif
    </div>
</div>
