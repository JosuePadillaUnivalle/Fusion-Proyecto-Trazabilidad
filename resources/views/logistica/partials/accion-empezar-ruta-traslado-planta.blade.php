@php
    use App\Support\RutaDistribucionCatalogo;

    $usuario = auth()->user();
    $puedeEmpezar = \App\Support\SimulacionRutaCatalogo::usuarioPuedeEmpezarDistribucion($usuario, $ruta);
    $simulacionActiva = \App\Support\SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta);
    $estado = (string) ($ruta->estado ?? '');
@endphp

@if($puedeEmpezar)
    <form method="POST" action="{{ route('logistica.traslados-planta.empezar-ruta', $ruta) }}" class="mb-0">
        @csrf
        @method('PATCH')
        <button type="submit" class="btn btn-success btn-block btn-lg font-weight-bold">
            <i class="fas fa-shipping-fast mr-1"></i> Marcar en ruta
        </button>
        <p class="small text-muted mb-0 mt-2">
            Confirme cuando el vehículo salió de planta con destino al almacén mayorista.
        </p>
    </form>
@elseif($simulacionActiva)
    @include('logistica.partials.progreso-simulacion-transportista', [
        'tipo' => 'planta_mayorista',
        'id' => $ruta->rutadistribucionid,
    ])
    <a href="{{ route('logistica.rutas-tiempo-real.show', ['tipo' => 'planta_mayorista', 'id' => $ruta->rutadistribucionid]) }}" class="btn btn-outline-primary btn-block btn-sm mt-2">
        <i class="fas fa-map-marked-alt mr-1"></i> Ver recorrido en mapa
    </a>
@elseif($estado === RutaDistribucionCatalogo::ESTADO_COMPLETADA)
    <div class="alert alert-success small mb-0">
        <i class="fas fa-check-circle mr-1"></i>
        <strong>Traslado completado.</strong>
        La mercancía llegó al almacén mayorista destino.
        @if($ruta->fecha_salida)
            <br><span class="text-muted">Salida de planta:</span>
            {{ $ruta->fecha_salida->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
        @endif
    </div>
    <p class="small text-muted mb-0 mt-2">
        El cierre con firmas de transportista y receptor se registrará en la fase de entrega formal.
    </p>
@elseif($estado === RutaDistribucionCatalogo::ESTADO_EN_RUTA)
    <div class="alert alert-info small mb-0">
        <i class="fas fa-truck mr-1"></i>
        <strong>En ruta.</strong> El vehículo ya salió de planta; el seguimiento GPS no está activo en este momento.
    </div>
@elseif($estado === RutaDistribucionCatalogo::ESTADO_PLANIFICADA)
    @if(! $ruta->transportista_usuarioid)
        <p class="text-muted small mb-0">
            <i class="fas fa-user-clock mr-1"></i>
            Traslado aprobado. Falta asignar chofer y vehículo antes de marcar salida.
        </p>
    @else
        <p class="text-muted small mb-0">
            <i class="fas fa-hourglass-half mr-1"></i>
            Traslado listo. El transportista debe marcar <strong>«En ruta»</strong> cuando salga de planta.
        </p>
    @endif
@elseif($estado === RutaDistribucionCatalogo::ESTADO_CANCELADA)
    <div class="alert alert-secondary small mb-0">
        <i class="fas fa-ban mr-1"></i> Traslado cancelado.
    </div>
@else
    <p class="text-muted small mb-0">
        <i class="fas fa-info-circle mr-1"></i>
        Estado actual: <strong>{{ RutaDistribucionCatalogo::etiquetaEstado($estado) }}</strong>.
    </p>
@endif
