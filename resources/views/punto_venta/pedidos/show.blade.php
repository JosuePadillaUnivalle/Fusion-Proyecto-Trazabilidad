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
.pdv-flota-panel {
    border-radius: 12px;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    background: #fafafa;
    margin-bottom: 1rem;
}
.pdv-flota-panel--chofer { border-color: #bfdbfe; background: linear-gradient(160deg, #f8fbff 0%, #eff6ff 100%); }
.pdv-flota-panel--vehiculo { border-color: #a7f3d0; background: linear-gradient(160deg, #f6fffb 0%, #ecfdf5 100%); }
.pdv-flota-panel .panel-icon {
    width: 30px; height: 30px; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.85rem; margin-right: 0.4rem;
}
.pdv-flota-panel--chofer .panel-icon { background: #dbeafe; color: #1d4ed8; }
.pdv-flota-panel--vehiculo .panel-icon { background: #d1fae5; color: #047857; }
</style>
@endpush

@section('content')
    @php
        $det = $pedido->detalles->first();
        $badge = \App\Support\PedidoDistribucionCatalogo::badgeEstado($pedido);
        $pendientePlanta = \App\Support\PedidoDistribucionCatalogo::pendienteAprobacionPlanta($pedido);
        $unidad = $det?->insumo?->unidadMedida?->abreviatura ?? '';
        $stockOk = $erroresStock === [];
        $transportistaEfectivo = $pedido->transportista ?? $pedido->rutaDistribucion?->transportista;
        $vehiculoEfectivo = $pedido->vehiculo ?? $pedido->rutaDistribucion?->vehiculo;
        $puedeDespacharDirecto = \App\Support\PedidoDistribucionCatalogo::puedeDespacharDirecto($pedido);
        $enRutaPlanificada = $pedido->estado === 'confirmado' && $pedido->rutadistribucionid !== null;
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
                        @if($transportistaEfectivo)
                        <dt class="col-sm-4">Transportista</dt>
                        <dd class="col-sm-8">{{ trim($transportistaEfectivo->nombre.' '.$transportistaEfectivo->apellido) }}</dd>
                        @endif
                        @if($vehiculoEfectivo)
                        <dt class="col-sm-4">Vehículo</dt>
                        <dd class="col-sm-8">
                            {{ $vehiculoEfectivo->placa }}
                            @if($vehiculoEfectivo->marca || $vehiculoEfectivo->modelo)
                                <span class="text-muted">— {{ trim(($vehiculoEfectivo->marca ?? '').' '.($vehiculoEfectivo->modelo ?? '')) }}</span>
                            @endif
                        </dd>
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
            @elseif($puedeGestionarPlanta && $puedeDespacharDirecto)
            <div class="card pdv-card card-outline card-success mb-3">
                <div class="card-header bg-white py-2"><h3 class="card-title mb-0 font-weight-bold"><i class="fas fa-shipping-fast text-success mr-1"></i> Envío a PDV</h3></div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Asigne un <strong>transportista de flota planta</strong> y su <strong>vehículo</strong> antes de marcar la salida hacia el minorista.
                    </p>
                    <form method="POST" action="{{ route('punto-venta.pedidos.marcar-enviado', $pedido) }}" id="formDespachoPedidoPdv">
                        @csrf
                        <div class="pdv-flota-panel pdv-flota-panel--chofer">
                            <label class="small font-weight-bold mb-2 d-block">
                                <span class="panel-icon"><i class="fas fa-id-card"></i></span>
                                Chofer planta <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="text" id="txtTransportistaPedidoPdv" class="form-control bg-white" readonly
                                       placeholder="Buscar transportista de planta…" value="{{ old('transportista_label') }}" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-primary" id="btnBuscarTransportistaPedidoPdv" title="Buscar chofer">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="transportista_usuarioid" id="transportista_pedido_pdv_id"
                                   value="{{ old('transportista_usuarioid') }}" required>
                        </div>
                        <div class="pdv-flota-panel pdv-flota-panel--vehiculo">
                            <label class="small font-weight-bold mb-2 d-block">
                                <span class="panel-icon"><i class="fas fa-truck"></i></span>
                                Vehículo <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="text" id="txtVehiculoPedidoPdv" class="form-control bg-white" readonly
                                       placeholder="Seleccione el vehículo del chofer…" required>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-success" id="btnBuscarVehiculoPedidoPdv" disabled title="Buscar vehículo">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="vehiculoid" id="vehiculo_pedido_pdv_id" value="{{ old('vehiculoid') }}" required>
                            <p class="small text-muted mb-0 mt-2"><i class="fas fa-info-circle mr-1"></i>Primero elija el chofer; luego asigne su vehículo de planta.</p>
                        </div>
                        <button type="button" class="btn btn-success btn-block btn-lg"
                                id="btnConfirmarDespachoPedidoPdv"
                                data-confirm-title="Marcar envío en tránsito"
                                data-confirm-message="¿Confirmar salida de planta con el transportista y vehículo asignados?"
                                data-confirm-tone="success">
                            <i class="fas fa-shipping-fast mr-1"></i> Marcar en tránsito
                        </button>
                    </form>
                </div>
            </div>
            @elseif($puedeGestionarPlanta && $enRutaPlanificada)
            <div class="card pdv-card card-outline card-info mb-3">
                <div class="card-body small mb-0">
                    <i class="fas fa-route mr-1 text-info"></i>
                    Este pedido está incluido en una <strong>ruta de distribución</strong>.
                    Inicie la ruta desde
                    <a href="{{ route('punto-venta.rutas.index') }}">Rutas planta → minoristas</a>
                    para marcarlo en tránsito.
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
    @include('partials.selector-catalogo-assets')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formDespachoPedidoPdv');
    if (!form || !window.CatalogoSelector) return;

    if (!CatalogoSelector.modalEl) {
        CatalogoSelector.init();
    }

    const inputTransportista = document.getElementById('transportista_pedido_pdv_id');
    const inputVehiculo = document.getElementById('vehiculo_pedido_pdv_id');
    const btnVehiculo = document.getElementById('btnBuscarVehiculoPedidoPdv');
    const btnConfirmar = document.getElementById('btnConfirmarDespachoPedidoPdv');

    CatalogoSelector.register('pdv_pedido_transportista', {
        endpoint: @json(route('catalogo-selector.usuarios')),
        title: 'Transportista de planta',
        searchPlaceholder: 'Nombre o correo…',
        searchLabel: 'Buscar chofer',
        modalIcon: 'fa-id-card',
        rowIcon: 'fa-user-tie',
        theme: 'planta',
        colNombre: 'Chofer',
        colDetalle: 'Contacto',
        params: { roles: 'transportista', ambito_flota: 'planta' },
        onSelect: function (item) {
            inputTransportista.value = item.id;
            document.getElementById('txtTransportistaPedidoPdv').value = item.label;
            inputVehiculo.value = '';
            document.getElementById('txtVehiculoPedidoPdv').value = '';
            btnVehiculo.disabled = false;
            CatalogoSelector.instances.pdv_pedido_vehiculo.params = {
                transportista_usuarioid: item.id,
                solo_transportista: '1',
            };
        },
    });

    CatalogoSelector.register('pdv_pedido_vehiculo', {
        endpoint: @json(route('catalogo-selector.vehiculos')),
        title: 'Vehículo de reparto',
        searchPlaceholder: 'Placa, marca o modelo…',
        searchLabel: 'Buscar vehículo',
        modalIcon: 'fa-truck',
        rowIcon: 'fa-truck',
        theme: 'vehiculo',
        colNombre: 'Placa',
        colDetalle: 'Vehículo',
        params: { transportista_usuarioid: inputTransportista?.value || '', solo_transportista: '1' },
        onSelect: function (item) {
            inputVehiculo.value = item.id;
            document.getElementById('txtVehiculoPedidoPdv').value = item.label;
        },
    });

    document.getElementById('btnBuscarTransportistaPedidoPdv')?.addEventListener('click', function () {
        CatalogoSelector.open('pdv_pedido_transportista');
    });
    btnVehiculo?.addEventListener('click', function () {
        if (inputTransportista.value) {
            CatalogoSelector.open('pdv_pedido_vehiculo');
        }
    });

    btnConfirmar?.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        if (!inputTransportista.value) {
            window.ModalConfirmar?.aviso({ titulo: 'Falta chofer', mensaje: 'Asigne un transportista de flota planta.', tono: 'warning' });
            return;
        }
        if (!inputVehiculo.value) {
            window.ModalConfirmar?.aviso({ titulo: 'Falta vehículo', mensaje: 'Asigne el vehículo del chofer antes de marcar en tránsito.', tono: 'warning' });
            return;
        }
        window.ModalConfirmar.abrir(
            form,
            btnConfirmar.getAttribute('data-confirm-title'),
            btnConfirmar.getAttribute('data-confirm-message'),
            btnConfirmar.getAttribute('data-confirm-tone') || 'success'
        );
    });
});
</script>
@endpush
