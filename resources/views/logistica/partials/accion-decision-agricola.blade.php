@php
    $pedido = $asignacion->pedido;
    $tieneChofer = (bool) $asignacion->transportista_usuarioid;
@endphp

@if($pedido && ($puedeAceptarAgricola ?? auth()->user()?->can('pedidos.update')))
<div class="env-decision-agricola mb-3">
    <div class="env-decision-head">
        <span class="env-decision-icon"><i class="fas fa-seedling"></i></span>
        <div>
            <strong class="d-block">Aprobación agrícola pendiente</strong>
            <span class="small text-muted">Reserva stock del almacén agrícola para habilitar la salida del camión.</span>
        </div>
    </div>

    @if(($erroresStock ?? []) !== [])
        <div class="alert alert-danger py-2 px-3 small mb-3 border-0">
            <strong><i class="fas fa-exclamation-triangle mr-1"></i>Stock insuficiente:</strong>
            <ul class="mb-0 pl-3 mt-1">
                @foreach($erroresStock as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="env-paso-mini mb-2">
        <span class="env-paso-num">1</span>
        <span>Aceptar y reservar material del almacén agrícola.</span>
    </div>
    @if($tieneChofer)
        <div class="env-paso-mini mb-3">
            <span class="env-paso-num env-paso-num--blue">2</span>
            <span>El chofer <strong>{{ trim(($asignacion->transportista?->nombre ?? '').' '.($asignacion->transportista?->apellido ?? '')) }}</strong> podrá empezar ruta al salir cargado.</span>
        </div>
    @endif

    <form method="POST" action="{{ route('agricola.pedidos.aceptar', $pedido) }}" class="mb-2">
        @csrf
        <input type="hidden" name="volver" value="logistica">
        <button type="button" class="btn btn-env-ejecutivo btn-block font-weight-bold"
                @disabled(($erroresStock ?? []) !== [])
                data-confirm-modal
                data-confirm-tone="success"
                data-confirm-title="Confirmar aceptación del envío"
                data-confirm-message="¿Confirma que puede reservar el stock del almacén agrícola y habilitar este envío?">
            <i class="fas fa-check-circle mr-1"></i> Aceptar y reservar
        </button>
    </form>

    <details class="env-rechazo-details">
        <summary class="small text-muted" style="cursor:pointer;">Rechazar solicitud</summary>
        <form method="POST" action="{{ route('agricola.pedidos.rechazar', $pedido) }}" class="mt-2">
            @csrf
            <input type="hidden" name="volver" value="logistica">
            <div class="form-group mb-2">
                <label class="small text-muted mb-1">Motivo (opcional)</label>
                <textarea name="motivo_rechazo" class="form-control form-control-sm" rows="2" maxlength="500" placeholder="Indique por qué no puede atenderse"></textarea>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm btn-block"
                    data-confirm-modal
                    data-confirm-tone="danger"
                    data-confirm-title="Rechazar envío"
                    data-confirm-message="¿Confirma el rechazo de este pedido? Se liberará la planificación logística.">
                <i class="fas fa-times mr-1"></i> Rechazar
            </button>
        </form>
    </details>
</div>
@endif
