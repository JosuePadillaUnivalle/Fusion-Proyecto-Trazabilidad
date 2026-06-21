@if($item['asignacion'])
    @include('logistica.partials.acciones-tabla-asignacion', ['asignacion' => $item['asignacion']])
@elseif($item['ruta'])
    <a href="{{ $item['ver_url'] }}" class="btn btn-sm btn-outline-primary" title="Ver ruta">
        <i class="fas fa-eye mr-1"></i> Ver
    </a>
@elseif($item['pedido'])
    <a href="{{ route('pedidos.show', $item['pedido']) }}" class="btn btn-sm btn-outline-primary" title="Ver">
        <i class="fas fa-eye mr-1"></i> Ver
    </a>
@endif
@if(($item['fase_logistica'] ?? null) === 'en_camino_planta' && ! $item['asignacion'] && $item['pedido'])
    @can('recepcion_planta.confirm')
    <form method="POST" action="{{ route('pedidos.confirmar-llegada-planta', $item['pedido']) }}" class="d-inline m-0">
        @csrf
        <button type="button" class="btn btn-sm btn-outline-success" data-confirm-modal
            data-confirm-tone="success" data-confirm-title="Confirmar llegada"
            data-confirm-message="¿Confirma llegada del pedido {{ $item['pedido']->numero_solicitud }} a planta?">
            <i class="fas fa-check mr-1"></i> Llegada
        </button>
    </form>
    @endcan
@endif
