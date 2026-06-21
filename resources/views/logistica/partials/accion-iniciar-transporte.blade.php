@php
    $esMiAsignacion = (int) auth()->id() === (int) ($asignacion->transportista_usuarioid ?? 0);
    $puedeIniciar = auth()->user()?->can('asignaciones.update') || auth()->user()?->can('pedidos.update') || $esMiAsignacion;
    $estadoIniciable = in_array($asignacion->estado, ['asignado', 'asignada', 'pendiente', 'creada'], true);
    $listoParaSalir = true;
    if ($asignacion->pedidoid) {
        $asignacion->loadMissing('pedido');
        $listoParaSalir = $asignacion->pedido && \App\Support\PedidoCatalogo::listoParaLogistica($asignacion->pedido);
    }
    $compacto = !empty($compacto);
@endphp

@if($estadoIniciable && $puedeIniciar && $listoParaSalir)
    <form method="POST" action="{{ route('logistica.asignaciones.en-transporte', $asignacion) }}" class="d-inline m-0">
        @csrf
        @method('PATCH')
        <button type="submit"
                class="btn btn-sm {{ $compacto ? 'btn-outline-primary' : 'btn-primary' }}"
                title="Iniciar transporte"{!! $compacto ? ' data-toggle="tooltip" data-placement="top"' : '' !!}>
            <i class="fas fa-shipping-fast{{ $compacto ? '' : ' mr-1' }}"></i>{{ $compacto ? '' : 'Iniciar transporte' }}
        </button>
    </form>
@endif
