@php
    $puedeOperar = ($puedeOperarSalidaRuta ?? false);
    $puedeEmpezar = ($puedeEmpezarRuta ?? false);
    $simActiva = ($simulacionActiva ?? false);
@endphp

@if($ruta && $puedeOperar && ($puedeEmpezar || $simActiva))
<div class="card pdv-card card-outline card-primary mb-3">
    <div class="card-header bg-white py-2">
        <h3 class="card-title mb-0 font-weight-bold"><i class="fas fa-route text-primary mr-1"></i> Cierre y salida en ruta</h3>
    </div>
    <div class="card-body">
        @if($puedeEmpezar)
            <p class="text-muted small mb-3">
                @if($esAdmin ?? false)
                    El transportista debe registrar condiciones del vehículo, incidentes y firmas en el cierre operativo antes de salir hacia el PDV.
                @else
                    Complete el cierre operativo: revise el vehículo, registre condiciones e incidentes, y firme antes de marcar en ruta.
                @endif
            </p>
        @elseif($simActiva)
            <p class="text-muted small mb-3">
                <i class="fas fa-satellite-dish text-info mr-1"></i>
                El pedido está en camino. Continúe el cierre operativo para confirmar llegada, incidentes y recepción.
            </p>
        @endif
        @include('logistica.partials.accion-empezar-ruta-distribucion', [
            'ruta' => $ruta,
            'pedido' => $pedido,
            'rutaPrefijo' => 'punto-venta.rutas',
        ])
        @if($simActiva && !empty($urlTiempoRealPedido))
            <p class="small mb-0 mt-2">
                @include('logistica.partials.enlace-tiempo-real', ['href' => $urlTiempoRealPedido, 'compacto' => true])
            </p>
        @endif
    </div>
</div>
@endif
