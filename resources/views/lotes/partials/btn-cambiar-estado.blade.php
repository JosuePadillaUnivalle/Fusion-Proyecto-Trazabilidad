@php
    use App\Support\EstadoLoteCatalogo;

    $estadoActual = EstadoLoteCatalogo::slugFromNombre($lote->estadoTipo->nombre ?? '') ?? '';
@endphp
@can('lotes.update')
<div class="btn-group btn-group-sm">
    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" title="Cambiar estado" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-exchange-alt text-success"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-right">
        <h6 class="dropdown-header">Cambiar estado</h6>
        @foreach(EstadoLoteCatalogo::ESTADOS as $slug => $meta)
            <a class="dropdown-item {{ $estadoActual === $slug ? 'active' : '' }}"
               href="{{ EstadoLoteCatalogo::urlCambioEstado($lote, $slug) }}">
                {{ $meta['label'] }}
                @if($estadoActual === $slug)
                    <i class="fas fa-check float-right text-success mt-1"></i>
                @endif
            </a>
        @endforeach
    </div>
</div>
@endcan
