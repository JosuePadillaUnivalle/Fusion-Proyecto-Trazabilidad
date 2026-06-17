@php
    $usuario = auth()->user();
    $puedeEmpezar = \App\Support\SimulacionRutaCatalogo::usuarioPuedeEmpezarDistribucion($usuario, $ruta);
    $simulacionActiva = \App\Support\SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta);
@endphp

@if($puedeEmpezar)
    <form method="POST" action="{{ route('logistica.rutas-distribucion.empezar-ruta', $ruta) }}" class="mb-0">
        @csrf
        @method('PATCH')
        <button type="submit" class="btn btn-success btn-block btn-lg font-weight-bold">
            <i class="fas fa-play mr-1"></i> Empezar ruta
        </button>
        <p class="small text-muted mb-0 mt-2">Inicie cuando tenga el vehículo cargado y listo para salir.</p>
    </form>
@elseif($simulacionActiva)
    @include('logistica.partials.progreso-simulacion-transportista', [
        'tipo' => 'distribucion',
        'id' => $ruta->rutadistribucionid,
    ])
@endif
