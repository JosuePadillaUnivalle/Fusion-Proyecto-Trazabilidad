@php
    use App\Support\RutaDistribucionCatalogo;

    $usuario = auth()->user();
    $rutaPrefijo = $rutaPrefijo ?? (
        isset($pedido) ? 'punto-venta.rutas' : 'logistica.rutas-distribucion'
    );
    $cierreSvc = app(\App\Services\CierreEnvioDistribucionPdvService::class);
    $puedeEmpezar = \App\Support\SimulacionRutaCatalogo::usuarioPuedeEmpezarDistribucion($usuario, $ruta);
    $simulacionActiva = \App\Support\SimulacionRutaCatalogo::simulacionActivaDistribucion($ruta);
    $estado = (string) ($ruta->estado ?? '');
    $enCierre = ! ($estado === RutaDistribucionCatalogo::ESTADO_COMPLETADA) && (
        $cierreSvc->tieneCondicionesVehiculo($ruta)
        || $simulacionActiva
        || $ruta->llegada_confirmada_at
    );
    $urlCierre = route($rutaPrefijo.'.cierre.panel', $ruta);
    $etiquetaBoton = $etiquetaBoton ?? 'Iniciar cierre operativo';
@endphp

@if($enCierre && $estado !== RutaDistribucionCatalogo::ESTADO_COMPLETADA)
    @include('logistica.partials.accion-cierre-operativo-distribucion-pdv', [
        'ruta' => $ruta,
        'rutaPrefijo' => $rutaPrefijo,
        'conBordeSuperior' => $conBordeSuperior ?? false,
    ])
@elseif($puedeEmpezar)
    <a href="{{ $urlCierre }}" class="btn btn-success btn-block btn-lg font-weight-bold">
        <i class="fas fa-clipboard-check mr-1"></i> {{ $etiquetaBoton }}
    </a>
    <p class="small text-muted mb-0 mt-2">
        Revise el estado del vehículo, registre condiciones e incidentes, y complete las firmas antes de marcar en ruta.
    </p>
@elseif($estado === RutaDistribucionCatalogo::ESTADO_COMPLETADA)
    @php
        $estadoVehiculo = \App\Support\EnvioCierreAgricolaCatalogo::etiquetaEstadoVehiculo(
            $ruta->checklistCondicionVehiculo?->estado_general
        );
    @endphp
    <div class="alert alert-success small mb-3">
        <i class="fas fa-check-circle mr-1"></i>
        <strong>Entrega completada.</strong>
        El producto fue recibido en el punto de venta.
        @if($ruta->fecha_salida)
            <br><span class="text-muted">Salida del mayorista:</span>
            {{ $ruta->fecha_salida->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
        @endif
    </div>
    @if($estadoVehiculo !== '—')
        <p class="small text-muted mb-2">
            <i class="fas fa-clipboard-check mr-1"></i>
            <strong>Estado del vehículo:</strong> {{ $estadoVehiculo }}
        </p>
    @endif
    <a href="{{ $urlCierre }}" class="btn btn-outline-primary btn-sm btn-block font-weight-bold">
        <i class="fas fa-list-alt mr-1"></i> Ver cierre operativo
    </a>
@elseif($estado === RutaDistribucionCatalogo::ESTADO_EN_RUTA || $simulacionActiva)
    @include('logistica.partials.progreso-simulacion-transportista', [
        'tipo' => 'distribucion',
        'id' => $ruta->rutadistribucionid,
    ])
    <a href="{{ $urlCierre }}" class="btn btn-success btn-block btn-sm mt-2 font-weight-bold">
        <i class="fas fa-tasks mr-1"></i> Continuar cierre operativo
    </a>
@elseif($estado === RutaDistribucionCatalogo::ESTADO_PLANIFICADA)
    @if(! $ruta->transportista_usuarioid)
        <p class="text-muted small mb-0">
            <i class="fas fa-user-clock mr-1"></i>
            Pedido aceptado. Falta designar chofer y vehículo mayorista.
        </p>
    @else
        <p class="text-muted small mb-0">
            <i class="fas fa-hourglass-half mr-1"></i>
            Transportista asignado. Debe completar el cierre operativo (condiciones del vehículo) antes de salir en ruta.
        </p>
        @if(\App\Support\UsuarioRol::puedeGestionarDistribucionMayorista($usuario))
            <a href="{{ $urlCierre }}" class="btn btn-outline-secondary btn-sm btn-block mt-2">
                <i class="fas fa-eye mr-1"></i> Ver avance del cierre
            </a>
        @endif
    @endif
@elseif($estado === RutaDistribucionCatalogo::ESTADO_CANCELADA)
    <div class="alert alert-secondary small mb-0">
        <i class="fas fa-ban mr-1"></i> Entrega cancelada.
    </div>
@else
    <p class="text-muted small mb-0">
        <i class="fas fa-info-circle mr-1"></i>
        Estado actual: <strong>{{ RutaDistribucionCatalogo::etiquetaEstado($estado) }}</strong>.
    </p>
@endif
