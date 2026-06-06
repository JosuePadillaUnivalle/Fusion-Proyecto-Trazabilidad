@extends('layouts.app')

@section('title', $pedido->numero_solicitud)
@section('page_title', 'Pedido '.$pedido->numero_solicitud)

@push('styles')
@include('punto_venta.partials.modulo-styles')
<style>
.pdv-pedido-flujo {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .75rem;
    margin-bottom: 1.25rem;
}
@media (max-width: 991px) {
    .pdv-pedido-flujo { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
.pdv-paso-card {
    background: #fff;
    border: 2px solid #d1fae5;
    border-radius: 14px;
    padding: .9rem .65rem;
    text-align: center;
    box-shadow: 0 2px 10px rgba(5, 150, 105, .1);
    transition: transform .2s ease, box-shadow .2s ease;
}
.pdv-paso-card .paso-num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #ecfdf5;
    color: #059669;
    font-weight: 700;
    font-size: .78rem;
    margin-bottom: .4rem;
}
.pdv-paso-card .paso-icon {
    display: block;
    font-size: 1.15rem;
    color: #10b981;
    margin-bottom: .35rem;
}
.pdv-paso-card .paso-label {
    display: block;
    font-size: .72rem;
    font-weight: 600;
    color: #047857;
    line-height: 1.35;
}
.pdv-paso-card.pendiente {
    opacity: .65;
    border-color: #e5e7eb;
    box-shadow: none;
    background: #fafafa;
}
.pdv-paso-card.pendiente .paso-num { background: #f3f4f6; color: #9ca3af; }
.pdv-paso-card.pendiente .paso-icon,
.pdv-paso-card.pendiente .paso-label { color: #9ca3af; }
.pdv-paso-card.activo {
    background: linear-gradient(145deg, #047857, #10b981);
    border-color: #065f46;
    box-shadow: 0 6px 18px rgba(5, 150, 105, .35);
    transform: translateY(-2px);
}
.pdv-paso-card.activo .paso-num {
    background: rgba(255, 255, 255, .22);
    color: #fff;
}
.pdv-paso-card.activo .paso-icon,
.pdv-paso-card.activo .paso-label { color: #fff; }
.pdv-paso-card.hecho {
    background: linear-gradient(180deg, #ecfdf5, #d1fae5);
    border-color: #6ee7b7;
}
.pdv-paso-card.hecho .paso-num {
    background: #059669;
    color: #fff;
}
.pdv-paso-card.hecho .paso-icon { color: #059669; }
.pdv-paso-card.hecho .paso-label { color: #065f46; }
.pdv-detalle-grid dt { font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; color: #6c757d; }
.pdv-detalle-grid dd { margin-bottom: .85rem; }
.pdv-banner-llegada {
    border-radius: 14px;
    border: 2px solid #6ee7b7;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
}
</style>
@endpush

@section('content')
    @php
        $det = $pedido->detalles->first();
        $badge = \App\Support\PedidoDistribucionCatalogo::badgeEstado($pedido);
        $pendientePlanta = \App\Support\PedidoDistribucionCatalogo::pendienteAprobacionPlanta($pedido);
        $unidad = $det?->insumo?->unidadMedida?->abreviatura ?? '';
        $stockOk = $erroresStock === [];
        $pasos = [
            ['num' => 1, 'icon' => 'fa-paper-plane', 'label' => 'Solicitud minorista', 'hecho' => true, 'activo' => false],
            ['num' => 2, 'icon' => 'fa-industry', 'label' => 'Revisión planta', 'hecho' => in_array($pedido->estado, ['confirmado', 'en_transito', 'recibido'], true), 'activo' => $pendientePlanta],
            ['num' => 3, 'icon' => 'fa-shipping-fast', 'label' => 'En tránsito', 'hecho' => in_array($pedido->estado, ['en_transito', 'recibido'], true), 'activo' => $pedido->estado === 'confirmado'],
            ['num' => 4, 'icon' => 'fa-dolly', 'label' => 'Recepción PDV', 'hecho' => $pedido->estado === 'recibido', 'activo' => $pedido->estado === 'en_transito'],
        ];
    @endphp

    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('punto-venta.pedidos.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Volver al listado
            </a>
        </div>
    </div>

    @if($puedeAnunciarLlegada ?? false)
    <div class="alert pdv-banner-llegada d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3 py-3 px-4">
        <div>
            <strong class="text-success d-block mb-1">
                <i class="fas fa-truck-loading mr-1"></i> El pedido está en camino a su tienda
            </strong>
            <span class="text-muted small">Cuando reciba el producto, anuncie la llegada para ingresarlo a su inventario.</span>
        </div>
        <form method="POST" action="{{ route('punto-venta.pedidos.confirmar-recepcion', $pedido) }}" class="mb-0">
            @csrf
            <button type="button" class="btn btn-success btn-lg font-weight-bold px-4"
                    data-confirm-modal
                    data-confirm-tone="success"
                    data-confirm-title="Anunciar llegada del pedido"
                    data-confirm-message="¿Confirma que el pedido ya llegó a su punto de venta? Se ingresará al inventario.">
                <i class="fas fa-bullhorn mr-1"></i> Anunciar llegada
            </button>
        </form>
    </div>
    @endif

    <div class="row align-items-start">
        <div class="col-lg-8">
            <div class="card pdv-card card-outline card-success mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h3 class="card-title mb-0 font-weight-bold">
                        <i class="fas fa-truck-loading text-success mr-2"></i>Detalle del pedido
                    </h3>
                    <span class="badge badge-{{ $badge['clase'] }} badge-lg">{{ $badge['etiqueta'] }}</span>
                </div>
                <div class="card-body">
                    @if(! in_array($pedido->estado, ['rechazado', 'cancelado'], true))
                    <div class="pdv-pedido-flujo">
                        @foreach($pasos as $paso)
                            @php
                                $clasePaso = $paso['activo']
                                    ? 'activo'
                                    : ($paso['hecho'] ? 'hecho' : 'pendiente');
                            @endphp
                            <div class="pdv-paso-card {{ $clasePaso }}">
                                <span class="paso-num">{{ $paso['num'] }}</span>
                                <i class="fas {{ $paso['icon'] }} paso-icon"></i>
                                <span class="paso-label">{{ $paso['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    @endif

                    <dl class="row mb-0 pdv-detalle-grid">
                        <dt class="col-sm-4">Punto de venta</dt>
                        <dd class="col-sm-8">
                            <a href="{{ route('punto-venta.puntos.show', $pedido->puntoVenta) }}">{{ $pedido->puntoVenta?->nombre }}</a>
                            <br><small class="text-muted">{{ $pedido->puntoVenta?->nombreMinorista() }}</small>
                        </dd>
                        <dt class="col-sm-4">Producto solicitado</dt>
                        <dd class="col-sm-8"><strong>{{ $det?->producto_nombre ?? '—' }}</strong></dd>
                        <dt class="col-sm-4">Cantidad</dt>
                        <dd class="col-sm-8">{{ $det ? number_format($det->cantidad, 2).' '.$unidad : '—' }}</dd>
                        <dt class="col-sm-4">Almacén planta</dt>
                        <dd class="col-sm-8">{{ $pedido->almacenPlantaOrigen?->nombre ?? $det?->insumo?->almacen?->nombre ?? '—' }}</dd>
                        @if($det?->insumo)
                        <dt class="col-sm-4">Stock en planta</dt>
                        <dd class="col-sm-8">
                            @if($stockOk)
                                <span class="text-success"><i class="fas fa-check mr-1"></i>{{ number_format((float) $det->insumo->stock, 2) }} {{ $unidad }} disponible</span>
                            @else
                                <span class="text-danger"><i class="fas fa-times mr-1"></i>{{ number_format((float) $det->insumo->stock, 2) }} {{ $unidad }} — insuficiente</span>
                            @endif
                        </dd>
                        @endif
                        <dt class="col-sm-4">Fecha solicitud</dt>
                        <dd class="col-sm-8">{{ $pedido->fechapedido?->format('d/m/Y H:i') ?? '—' }}</dd>
                        @if($pedido->creadoPor)
                        <dt class="col-sm-4">Solicitado por</dt>
                        <dd class="col-sm-8">{{ trim($pedido->creadoPor->nombre.' '.$pedido->creadoPor->apellido) }}</dd>
                        @endif
                        @if($pedido->fecha_entrega_deseada)
                        <dt class="col-sm-4">Entrega deseada</dt>
                        <dd class="col-sm-8">{{ $pedido->fecha_entrega_deseada->format('d/m/Y') }}</dd>
                        @endif
                        @if($pedido->fecha_aceptacion)
                        <dt class="col-sm-4">Aceptado por planta</dt>
                        <dd class="col-sm-8">
                            {{ $pedido->fecha_aceptacion->format('d/m/Y H:i') }}
                            @if($pedido->aceptadoPor)
                                — {{ trim($pedido->aceptadoPor->nombre.' '.$pedido->aceptadoPor->apellido) }}
                            @endif
                        </dd>
                        @endif
                        @if($pedido->fecha_envio)
                        <dt class="col-sm-4">Enviado</dt>
                        <dd class="col-sm-8">{{ $pedido->fecha_envio->format('d/m/Y H:i') }}</dd>
                        @endif
                        @if($pedido->fecha_recepcion)
                        <dt class="col-sm-4">Recibido en PDV</dt>
                        <dd class="col-sm-8">{{ $pedido->fecha_recepcion->format('d/m/Y H:i') }}</dd>
                        @endif
                        @if($pedido->observaciones)
                        <dt class="col-sm-4">Observaciones</dt>
                        <dd class="col-sm-8"><pre class="mb-0 small bg-light p-2 rounded border-0">{{ $pedido->observaciones }}</pre></dd>
                        @endif
                    </dl>
                </div>
            </div>

            @if($puedeGestionarPlanta && $pendientePlanta && $erroresStock !== [])
            <div class="alert alert-danger">
                <strong><i class="fas fa-exclamation-triangle mr-1"></i>No se puede aceptar todavía:</strong>
                <ul class="mb-0 pl-3 mt-1">@foreach($erroresStock as $err)<li>{{ $err }}</li>@endforeach</ul>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            @if($puedeGestionarPlanta && $pendientePlanta)
            <div class="card pdv-card card-outline card-success mb-3">
                <div class="card-header bg-white py-2">
                    <h3 class="card-title mb-0 font-weight-bold"><i class="fas fa-industry text-success mr-1"></i> Decisión planta</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Revise stock y confirme si puede despachar el producto hacia el punto de venta.
                    </p>
                    <form method="POST" action="{{ route('punto-venta.pedidos.aceptar', $pedido) }}" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block btn-lg" @disabled(! $stockOk)>
                            <i class="fas fa-check mr-1"></i> Aceptar y preparar envío
                        </button>
                    </form>
                    <hr>
                    <form method="POST" action="{{ route('punto-venta.pedidos.rechazar', $pedido) }}">
                        @csrf
                        <div class="form-group mb-2">
                            <label class="small text-muted">Motivo (opcional)</label>
                            <textarea name="motivo_rechazo" class="form-control form-control-sm" rows="2" maxlength="500" placeholder="Ej: stock insuficiente"></textarea>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-block"
                                data-confirm-modal
                                data-confirm-title="Rechazar solicitud"
                                data-confirm-message="¿Rechazar esta solicitud del minorista? Esta acción no se puede deshacer.">
                            <i class="fas fa-times mr-1"></i> Rechazar solicitud
                        </button>
                    </form>
                </div>
            </div>
            @elseif($puedeGestionarPlanta && \App\Support\PedidoDistribucionCatalogo::puedeMarcarEnviado($pedido))
            <div class="card pdv-card card-outline card-success mb-3">
                <div class="card-header bg-white py-2"><h3 class="card-title mb-0 font-weight-bold"><i class="fas fa-shipping-fast text-success mr-1"></i> Envío a PDV</h3></div>
                <div class="card-body">
                    <p class="text-muted small mb-3">El pedido fue aceptado. Marque cuando el producto salga de planta hacia el minorista.</p>
                    <form method="POST" action="{{ route('punto-venta.pedidos.marcar-enviado', $pedido) }}">
                        @csrf
                        <button type="button" class="btn btn-success btn-block btn-lg"
                                data-confirm-modal
                                data-confirm-tone="success"
                                data-confirm-title="Marcar envío en tránsito"
                                data-confirm-message="¿Confirmar que el envío salió de planta hacia el punto de venta?">
                            <i class="fas fa-shipping-fast mr-1"></i> Marcar en tránsito
                        </button>
                    </form>
                </div>
            </div>
            @elseif($puedeAnunciarLlegada ?? false)
            <div class="card pdv-card card-outline card-success mb-3">
                <div class="card-header bg-white py-2"><h3 class="card-title mb-0 font-weight-bold"><i class="fas fa-store text-success mr-1"></i> Recepción en tienda</h3></div>
                <div class="card-body">
                    <p class="text-muted small mb-3">El pedido ya salió de planta. Anuncie la llegada cuando lo reciba en su punto de venta.</p>
                    <form method="POST" action="{{ route('punto-venta.pedidos.confirmar-recepcion', $pedido) }}">
                        @csrf
                        <button type="button" class="btn btn-success btn-block btn-lg"
                                data-confirm-modal
                                data-confirm-tone="success"
                                data-confirm-title="Anunciar llegada del pedido"
                                data-confirm-message="¿Confirma que el pedido ya llegó a su punto de venta? Se ingresará al inventario.">
                            <i class="fas fa-bullhorn mr-1"></i> Anunciar llegada
                        </button>
                    </form>
                </div>
            </div>
            @elseif($pedido->estado === 'recibido')
            <div class="card pdv-card card-outline card-success mb-3">
                <div class="card-body text-success small mb-0">
                    <i class="fas fa-check-circle mr-1"></i> Pedido completado. El stock ya está en el inventario del punto de venta.
                </div>
            </div>
            @elseif($pedido->estado === 'rechazado')
            <div class="card pdv-card card-outline card-danger mb-3">
                <div class="card-body text-danger small mb-0">
                    <i class="fas fa-ban mr-1"></i> Solicitud rechazada por planta.
                </div>
            </div>
            @elseif($pendientePlanta && $esMinoristaDueño)
            <div class="card pdv-card card-outline card-warning mb-3">
                <div class="card-body small mb-0">
                    <i class="fas fa-hourglass-half mr-1 text-warning"></i>
                    Su solicitud está pendiente de revisión por planta.
                </div>
            </div>
            @elseif($pedido->estado === 'confirmado')
            <div class="card pdv-card card-outline card-success mb-3">
                <div class="card-body small mb-0 text-success">
                    <i class="fas fa-box mr-1"></i> Planta aceptó el pedido. Pendiente de salida hacia su punto de venta.
                </div>
            </div>
            @elseif($pedido->estado === 'en_transito' && ! ($puedeAnunciarLlegada ?? false))
            <div class="card pdv-card card-outline card-success mb-3">
                <div class="card-body small mb-0 text-success">
                    <i class="fas fa-shipping-fast mr-1"></i> Producto en camino al punto de venta. El minorista confirmará la recepción.
                </div>
            </div>
            @endif
        </div>
    </div>

    @include('partials.modal-confirmar-accion')
@endsection
