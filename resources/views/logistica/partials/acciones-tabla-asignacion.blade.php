@php
    use App\Support\EnvioAsignacionEstadoCatalogo;
    $llegoDestino = EnvioAsignacionEstadoCatalogo::llegoADestino($asignacion);
    $puedeGestionar = EnvioAsignacionEstadoCatalogo::puedeGestionarAdmin($asignacion);
@endphp

<div class="d-flex flex-wrap align-items-center" style="gap:.35rem;">
    <a href="{{ route('logistica.asignaciones.show', $asignacion) }}"
       class="btn btn-sm btn-outline-info" title="Ver detalle">
        <i class="fas fa-eye"></i>
    </a>

    @if($puedeGestionar)
        @can('asignaciones.update')
        <a href="{{ route('logistica.asignaciones.edit', $asignacion) }}"
           class="btn btn-sm btn-outline-warning" title="Editar">
            <i class="fas fa-edit"></i>
        </a>
        @endcan
        @can('asignaciones.delete')
        <form method="POST" action="{{ route('logistica.asignaciones.destroy', $asignacion) }}" class="d-inline m-0">
            @csrf
            @method('DELETE')
            <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar"
                    data-confirm-modal
                    data-confirm-tone="danger"
                    data-confirm-title="Eliminar envío"
                    data-confirm-message="¿Eliminar el envío {{ $asignacion->externo_envio_id }}? Esta acción no se puede deshacer.">
                <i class="fas fa-trash"></i>
            </button>
        </form>
        @endcan
    @endif
</div>

@if(! $llegoDestino)
    <div class="mt-1">
        @if(in_array($asignacion->estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true))
            @include('logistica.partials.accion-llegada-destino', ['asignacion' => $asignacion])
        @elseif(in_array($asignacion->estado, ['asignado', 'asignada', 'pendiente', 'creada'], true))
            @include('logistica.partials.accion-iniciar-transporte', ['asignacion' => $asignacion])
        @endif
    </div>
@else
    <div class="mt-1">
        <span class="text-success small"><i class="fas fa-check mr-1"></i>Recibido en planta</span>
        @if($asignacion->fecha_recepcion_planta)
            <br><small class="text-muted">{{ $asignacion->fecha_recepcion_planta->format('d/m/Y H:i') }}</small>
        @endif
    </div>
@endif
