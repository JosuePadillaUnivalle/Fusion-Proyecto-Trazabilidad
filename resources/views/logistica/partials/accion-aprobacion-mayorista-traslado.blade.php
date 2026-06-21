@php
    $rutaPrefijo = $rutaPrefijo ?? 'logistica.traslados-planta';
@endphp

@if($pendienteAprobacion ?? false)
    @if($puedeGestionarMayorista ?? false)
        <div class="alert alert-info small">
            <i class="fas fa-info-circle mr-1"></i>
            La planta solicita enviar productos a su almacén. Revise el detalle y acepte o rechace la recepción.
        </div>
        <form method="POST" action="{{ route($rutaPrefijo.'.aceptar', $ruta) }}" class="mb-3">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-success btn-block font-weight-bold">
                <i class="fas fa-check mr-1"></i> Aceptar traslado
            </button>
        </form>
        <form method="POST" action="{{ route($rutaPrefijo.'.rechazar', $ruta) }}">
            @csrf
            @method('PATCH')
            <div class="form-group">
                <label for="motivo_rechazo" class="small text-muted">Motivo de rechazo (opcional)</label>
                <textarea name="motivo_rechazo" id="motivo_rechazo" rows="2" class="form-control form-control-sm" maxlength="500" placeholder="Ej: Sin capacidad disponible en el almacén"></textarea>
            </div>
            <button type="submit" class="btn btn-outline-danger btn-block" onclick="return confirm('¿Rechazar este traslado desde planta?')">
                <i class="fas fa-times mr-1"></i> Rechazar traslado
            </button>
        </form>
    @else
        <p class="text-muted small mb-0">
            <i class="fas fa-hourglass-half mr-1"></i>
            Pendiente de aprobación por el almacén mayorista destino.
        </p>
    @endif
@elseif(($ruta->estado ?? '') === \App\Support\RutaDistribucionCatalogo::ESTADO_RECHAZADA)
    <div class="alert alert-danger small mb-0">
        <i class="fas fa-ban mr-1"></i>
        Traslado rechazado por el mayorista.
        @if($ruta->motivo_rechazo_mayorista)
            <br><span class="text-muted">Motivo:</span> {{ $ruta->motivo_rechazo_mayorista }}
        @endif
    </div>
@else
    @include('logistica.partials.accion-empezar-ruta-traslado-planta', ['ruta' => $ruta])
@endif
