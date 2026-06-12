@php
    $esMiAsignacion = (int) auth()->id() === (int) ($asignacion->transportista_usuarioid ?? 0);
    $puedeIniciar = auth()->user()?->can('asignaciones.update') || auth()->user()?->can('pedidos.update') || $esMiAsignacion;
    $estadoIniciable = in_array($asignacion->estado, ['asignado', 'asignada', 'pendiente', 'creada'], true);
    $listoParaSalir = true;
    if ($asignacion->pedidoid) {
        $asignacion->loadMissing('pedido');
        $listoParaSalir = $asignacion->pedido && \App\Support\PedidoCatalogo::listoParaLogistica($asignacion->pedido);
    }
@endphp

@if($estadoIniciable && $puedeIniciar && $listoParaSalir)
    <form method="POST" action="{{ route('logistica.asignaciones.en-transporte', $asignacion) }}" class="d-inline">
        @csrf
        @method('PATCH')
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="fas fa-shipping-fast mr-1"></i>Iniciar transporte
        </button>
    </form>
@endif
