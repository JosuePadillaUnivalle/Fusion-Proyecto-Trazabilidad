@php
    $esMiAsignacion = (int) auth()->id() === (int) ($asignacion->transportista_usuarioid ?? 0);
    $puedeConfirmar = auth()->user()?->can('asignaciones.update') || $esMiAsignacion;
    $enCamino = in_array($asignacion->estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true);
@endphp

@if($enCamino && $puedeConfirmar && ! $asignacion->fecha_recepcion_planta)
    <form method="POST" action="{{ route('logistica.asignaciones.llegada-destino', $asignacion) }}" class="d-inline">
        @csrf
        @method('PATCH')
        <button type="button" class="btn btn-sm btn-success"
                data-confirm-modal
                data-confirm-tone="success"
                data-confirm-title="Llegada a destino"
                data-confirm-message="¿Confirma que el envío {{ $asignacion->externo_envio_id }} llegó a destino?">
            <i class="fas fa-map-marker-alt mr-1"></i>Llegada a destino
        </button>
    </form>
@endif
