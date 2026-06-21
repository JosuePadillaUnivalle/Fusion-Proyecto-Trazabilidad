@php
    use App\Support\EnvioAsignacionEstadoCatalogo;
    use App\Support\PedidoCatalogo;
    $llegoDestino = EnvioAsignacionEstadoCatalogo::llegoADestino($asignacion);
    $puedeGestionar = EnvioAsignacionEstadoCatalogo::puedeGestionarAdmin($asignacion);
    $puedeEditar = PedidoCatalogo::puedeEditarAsignacionEnvio($asignacion);
@endphp

<div class="d-flex flex-wrap align-items-center logistica-acciones-row" style="gap:.35rem;">
    <a href="{{ route('logistica.asignaciones.show', $asignacion) }}"
       class="btn btn-sm btn-outline-info" title="Ver detalle">
        <i class="fas fa-eye"></i>
    </a>

    @if($puedeEditar)
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

    @if(! $llegoDestino)
        @if(in_array($asignacion->estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true))
            @include('logistica.partials.accion-llegada-destino', ['asignacion' => $asignacion, 'compacto' => true])
        @elseif(in_array($asignacion->estado, ['asignado', 'asignada', 'pendiente', 'creada'], true))
            @include('logistica.partials.accion-empezar-ruta', ['asignacion' => $asignacion, 'compacto' => true])
        @endif
    @else
        <span class="text-success small ml-1" title="Recibido en planta">
            <i class="fas fa-check"></i>
        </span>
    @endif
</div>
