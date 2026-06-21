@php
    $puedeOperar = ($puedeOperarSalidaRuta ?? false);
    $puedeEmpezar = ($puedeEmpezarRuta ?? false);
    $simActiva = ($simulacionActiva ?? false);
@endphp

@if($ruta && $puedeOperar && ($puedeEmpezar || $simActiva))
<div class="card pdv-card card-outline card-primary mb-3">
    <div class="card-header bg-white py-2">
        <h3 class="card-title mb-0 font-weight-bold"><i class="fas fa-route text-primary mr-1"></i> Salida en ruta</h3>
    </div>
    <div class="card-body">
        @if($puedeEmpezar)
            <p class="text-muted small mb-3">
                @if($esAdmin ?? false)
                    Marque en ruta cuando el vehículo salga hacia el punto de venta.
                @else
                    Confirme la salida cuando ya esté de camino al punto de venta.
                @endif
            </p>
        @elseif($simActiva)
            <p class="text-muted small mb-3">
                <i class="fas fa-satellite-dish text-info mr-1"></i>
                El pedido está en camino. Puede seguir el recorrido en tiempo real.
            </p>
        @endif
        @include('logistica.partials.accion-empezar-ruta-distribucion', ['ruta' => $ruta, 'pedido' => $pedido])
        @if($simActiva && !empty($urlTiempoRealPedido))
            <p class="small mb-0 mt-2">
                @include('logistica.partials.enlace-tiempo-real', ['href' => $urlTiempoRealPedido, 'compacto' => true])
            </p>
        @endif
    </div>
</div>
@endif
