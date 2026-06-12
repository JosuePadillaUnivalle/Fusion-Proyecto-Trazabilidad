@php
    $envio = $pedido->envioAsignacion ?? null;
    $logistica = \App\Support\EnvioPedidoService::datosLogistica($envio);
@endphp

@if($logistica)
<div class="card {{ $cardClass ?? 'card-outline card-primary' }} mb-3">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title mb-0">
            <i class="fas fa-truck mr-1"></i> Transporte y envío
        </h3>
        @php
            $badgeEnvio = match(true) {
                $logistica['recibido_planta'] => 'success',
                $logistica['cargado_en_ruta'] => 'info',
                default => 'secondary',
            };
        @endphp
        <span class="badge badge-{{ $badgeEnvio }} badge-lg mt-1 mt-md-0">
            @if($logistica['cargado_en_ruta'])
                En camino hacia planta
            @elseif($logistica['recibido_planta'])
                Recibido en planta
            @else
                Transportista asignado
            @endif
        </span>
    </div>
    <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-4">Transportista</dt>
            <dd class="col-sm-8">
                <strong>{{ $logistica['transportista_nombre'] }}</strong>
            </dd>
            <dt class="col-sm-4">Vehículo</dt>
            <dd class="col-sm-8">{{ $logistica['vehiculo_nombre'] }}</dd>
            <dt class="col-sm-4">Placa</dt>
            <dd class="col-sm-8">
                <span class="badge badge-light border text-dark px-2 py-1">{{ $logistica['placa'] }}</span>
            </dd>
            <dt class="col-sm-4">Estado del envío</dt>
            <dd class="col-sm-8">{{ $logistica['estado_etiqueta'] }}</dd>
            @if($logistica['fecha_asignacion'])
                <dt class="col-sm-4">Asignado el</dt>
                <dd class="col-sm-8">{{ $logistica['fecha_asignacion']->format('d/m/Y H:i') }}</dd>
            @endif
            @if($logistica['asignado_por'])
                <dt class="col-sm-4">Asignado por</dt>
                <dd class="col-sm-8">{{ $logistica['asignado_por'] }}</dd>
            @endif
        </dl>

        @if($logistica['cargado_en_ruta'])
            <div class="alert alert-info mb-0 mt-3 py-2 small">
                <i class="fas fa-box-open mr-1"></i>
                <strong>Pedido cargado.</strong> El envío está en ruta hacia la planta de procesamiento.
            </div>
        @elseif($logistica['recibido_planta'])
            <div class="alert alert-success mb-0 mt-3 py-2 small">
                <i class="fas fa-warehouse mr-1"></i>
                Mercadería recibida en planta.
            </div>
        @elseif(\App\Support\PedidoCatalogo::pendienteAprobacionAgricola($pedido))
            <div class="alert alert-secondary mb-0 mt-3 py-2 small">
                <i class="fas fa-hourglass-half mr-1"></i>
                Transporte programado. Se activará cuando producción agrícola <strong>acepte el pedido</strong>.
            </div>
        @else
            <div class="alert alert-warning mb-0 mt-3 py-2 small">
                <i class="fas fa-dolly mr-1"></i>
                Transportista asignado. Pendiente de <strong>carga en almacén agrícola</strong> e inicio de ruta.
            </div>
        @endif

        @if($mostrarConfirmarCarga ?? false)
            @can('pedidos.update')
                @if($logistica['asignado'] && ! $logistica['cargado_en_ruta'] && ! $logistica['recibido_planta'])
                    <form method="POST"
                          action="{{ $rutaConfirmarCarga ?? route('pedidos.confirmar-carga-envio', $pedido) }}"
                          class="mt-3 mb-0">
                        @csrf
                        <button type="button"
                                class="btn btn-primary btn-block"
                                data-confirm-modal
                                data-confirm-tone="success"
                                data-confirm-title="Confirmar carga e iniciar ruta"
                                data-confirm-message="¿Confirma que el pedido ya fue cargado en el vehículo y sale hacia planta?">
                            <i class="fas fa-shipping-fast mr-1"></i> Confirmar carga e iniciar ruta a planta
                        </button>
                    </form>
                @endif
            @endcan
        @endif
    </div>
</div>
@endif
