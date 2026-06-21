@php
    $usuario = auth()->user();
    $puedeEmpezar = \App\Support\SimulacionRutaCatalogo::usuarioPuedeEmpezarDistribucion($usuario, $ruta);
    $simulacionActiva = \App\Support\SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta);
    $urlEmpezar = isset($pedido)
        ? route('punto-venta.pedidos.empezar-ruta', $pedido)
        : route('logistica.rutas-distribucion.empezar-ruta', $ruta);
    $etiquetaBoton = $etiquetaBoton ?? 'Marcar en ruta';
@endphp

@if($puedeEmpezar)
    <form method="POST" action="{{ $urlEmpezar }}" class="mb-0">
        @csrf
        @method('PATCH')
        <button type="submit" class="btn btn-success btn-block btn-lg font-weight-bold">
            <i class="fas fa-shipping-fast mr-1"></i> {{ $etiquetaBoton }}
        </button>
        <p class="small text-muted mb-0 mt-2">
            Confirme cuando el vehículo ya salió con el pedido hacia el punto de venta.
        </p>
    </form>
@elseif($simulacionActiva)
    @include('logistica.partials.progreso-simulacion-transportista', [
        'tipo' => 'distribucion',
        'id' => $ruta->rutadistribucionid,
    ])
@endif
