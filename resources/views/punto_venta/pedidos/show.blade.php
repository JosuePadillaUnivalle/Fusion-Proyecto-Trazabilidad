@extends('layouts.app')

@section('title', $pedido->numero_solicitud)
@section('page_title', 'Pedido '.$pedido->numero_solicitud)

@push('styles')
@include('punto_venta.partials.modulo-styles')
<style>
.pdv-pedido-flujo { display: flex; flex-wrap: wrap; gap: .5rem; margin-bottom: 1rem; }
.pdv-pedido-flujo .paso {
    flex: 1 1 120px; text-align: center; padding: .55rem .4rem; border-radius: 8px;
    background: #f4f6f5; font-size: .72rem; color: #6c757d; border: 1px solid #e8ecea;
}
.pdv-pedido-flujo .paso.activo { background: #fff8e6; border-color: #ffc107; color: #856404; font-weight: 600; }
.pdv-pedido-flujo .paso.hecho { background: #e8f5e9; border-color: #28a745; color: #155724; }
.pdv-detalle-grid dt { font-size: .72rem; text-transform: uppercase; letter-spacing: .03em; color: #6c757d; }
.pdv-detalle-grid dd { margin-bottom: .85rem; }
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
            ['id' => 'pendiente', 'label' => '1. Solicitud minorista', 'hecho' => true, 'activo' => $pendientePlanta],
            ['id' => 'confirmado', 'label' => '2. Revisión planta', 'hecho' => in_array($pedido->estado, ['confirmado', 'en_transito', 'recibido'], true), 'activo' => $pendientePlanta],
            ['id' => 'transito', 'label' => '3. En tránsito', 'hecho' => in_array($pedido->estado, ['en_transito', 'recibido'], true), 'activo' => $pedido->estado === 'confirmado'],
            ['id' => 'recibido', 'label' => '4. Recepción PDV', 'hecho' => $pedido->estado === 'recibido', 'activo' => $pedido->estado === 'en_transito'],
        ];
    @endphp

    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('punto-venta.pedidos.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i> Volver al listado
            </a>
        </div>
    </div>

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
                            <div class="paso {{ $paso['hecho'] && ! $paso['activo'] ? 'hecho' : ($paso['activo'] ? 'activo' : '') }}">
                                {{ $paso['label'] }}
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
                        <button type="submit" class="btn btn-outline-danger btn-block" onclick="return confirm('¿Rechazar esta solicitud?');">
                            <i class="fas fa-times mr-1"></i> Rechazar solicitud
                        </button>
                    </form>
                </div>
            </div>
            @elseif($puedeGestionarPlanta && \App\Support\PedidoDistribucionCatalogo::puedeMarcarEnviado($pedido))
            <div class="card pdv-card card-outline card-primary mb-3">
                <div class="card-header bg-white py-2"><h3 class="card-title mb-0 font-weight-bold">Envío a PDV</h3></div>
                <div class="card-body">
                    <p class="text-muted small mb-3">El pedido fue aceptado. Marque cuando el producto salga de planta hacia el minorista.</p>
                    <form method="POST" action="{{ route('punto-venta.pedidos.marcar-enviado', $pedido) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-block btn-lg" onclick="return confirm('¿Confirmar que el envío salió de planta?');">
                            <i class="fas fa-shipping-fast mr-1"></i> Marcar en tránsito
                        </button>
                    </form>
                </div>
            </div>
            @elseif($esMinoristaDueño && \App\Support\PedidoDistribucionCatalogo::puedeConfirmarRecepcion($pedido))
            <div class="card pdv-card card-outline card-success mb-3">
                <div class="card-header bg-white py-2"><h3 class="card-title mb-0 font-weight-bold">Recepción en tienda</h3></div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Confirme cuando reciba el producto. Se ingresará al inventario de su punto de venta.</p>
                    <form method="POST" action="{{ route('punto-venta.pedidos.confirmar-recepcion', $pedido) }}">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block btn-lg" onclick="return confirm('¿Confirmar recepción e ingresar al inventario?');">
                            <i class="fas fa-dolly mr-1"></i> Confirmar recepción
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
            <div class="card pdv-card card-outline card-info mb-3">
                <div class="card-body small mb-0">
                    <i class="fas fa-box mr-1"></i> Planta aceptó el pedido. Pendiente de salida hacia su punto de venta.
                </div>
            </div>
            @elseif($pedido->estado === 'en_transito' && ! $esMinoristaDueño)
            <div class="card pdv-card card-outline card-primary mb-3">
                <div class="card-body small mb-0">
                    <i class="fas fa-shipping-fast mr-1"></i> Producto en camino al punto de venta.
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
