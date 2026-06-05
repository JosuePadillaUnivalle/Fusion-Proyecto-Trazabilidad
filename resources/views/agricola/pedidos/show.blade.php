@extends('layouts.app')

@section('title', 'Pedido '.$pedido->numero_solicitud.' | Producción agrícola')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">
            <i class="fas fa-leaf mr-2"></i>
            Pedido de planta {{ $pedido->numero_solicitud }}
        </h1>
        <p class="text-muted mb-0">Revise productos, stock en almacén agrícola y confirme si puede ir a recoger la cosecha.</p>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">Detalle del pedido</h3>
                        <span class="badge badge-lg {{
                            $pedido->estado === 'confirmado' ? 'badge-success' :
                            ($pedido->estado === 'rechazado' ? 'badge-danger' : 'badge-secondary')
                        }}">
                            {{ \App\Support\PedidoCatalogo::etiquetaEstado($pedido->estado) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Fecha pedido</dt>
                            <dd class="col-sm-8">{{ optional($pedido->fechapedido)->format('d/m/Y H:i') }}</dd>
                            <dt class="col-sm-4">Entrega deseada</dt>
                            <dd class="col-sm-8">{{ $pedido->fechaEntregaDeseada ? \Carbon\Carbon::parse($pedido->fechaEntregaDeseada)->format('d/m/Y') : '—' }}</dd>
                            <dt class="col-sm-4">Origen (recogida)</dt>
                            <dd class="col-sm-8">{{ $pedido->origen_direccion ?? '—' }}</dd>
                            <dt class="col-sm-4">Destino (entrega)</dt>
                            <dd class="col-sm-8">{{ $pedido->direccion_texto ?? '—' }}</dd>
                            @if($pedido->fecha_aceptacion_agricola)
                                <dt class="col-sm-4">Aceptado el</dt>
                                <dd class="col-sm-8">
                                    {{ $pedido->fecha_aceptacion_agricola->format('d/m/Y H:i') }}
                                    @if($pedido->aceptadoPor)
                                        por {{ $pedido->aceptadoPor->nombre }} {{ $pedido->aceptadoPor->apellido }}
                                    @endif
                                </dd>
                            @endif
                        </dl>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h3 class="card-title mb-0">Productos solicitados</h3></div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Origen almacén</th>
                                    <th>Disponibilidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pedido->detalles as $detalle)
                                    @php
                                        $ok = true;
                                        $disp = '—';
                                        if ($detalle->insumo) {
                                            $disp = number_format((float) $detalle->insumo->stock, 2).' kg';
                                            $ok = $detalle->insumo->tieneStockSuficiente((float) $detalle->cantidad);
                                        } elseif ($detalle->cosechaAlmacen) {
                                            $disp = number_format((float) $detalle->cosechaAlmacen->cantidad, 2).' kg';
                                            $ok = $detalle->cosechaAlmacen->fechasalida === null
                                                && (float) $detalle->cosechaAlmacen->cantidad >= (float) $detalle->cantidad;
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $detalle->cultivo_personalizado }}</strong>
                                            @if($detalle->insumo)
                                                <br><small class="text-muted">{{ $detalle->insumo->nombre }}</small>
                                            @endif
                                        </td>
                                        <td>{{ number_format($detalle->cantidad, 2) }} kg</td>
                                        <td>
                                            @if($detalle->cosechaAlmacen?->almacen)
                                                {{ $detalle->cosechaAlmacen->almacen->nombre }}
                                            @elseif($detalle->insumo?->almacen)
                                                {{ $detalle->insumo->almacen->nombre }}
                                            @else
                                                Almacén agrícola
                                            @endif
                                        </td>
                                        <td>
                                            @if($ok)
                                                <span class="text-success"><i class="fas fa-check mr-1"></i>{{ $disp }}</span>
                                            @else
                                                <span class="text-danger"><i class="fas fa-times mr-1"></i>Insuficiente ({{ $disp }})</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($erroresStock !== [])
                    <div class="alert alert-danger">
                        <strong>No se puede aceptar todavía:</strong>
                        <ul class="mb-0 pl-3">
                            @foreach($erroresStock as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @include('partials.pedido-envio-logistica', [
                    'pedido' => $pedido,
                    'mostrarConfirmarCarga' => true,
                    'rutaConfirmarCarga' => route('agricola.pedidos.confirmar-carga-envio', $pedido),
                ])
            </div>

            <div class="col-lg-4">
                @if(\App\Support\PedidoCatalogo::pendienteAprobacionAgricola($pedido))
                    <div class="card card-outline card-success">
                        <div class="card-header"><h3 class="card-title mb-0">Decisión agrícola</h3></div>
                        <div class="card-body">
                            <p class="text-muted small">
                                Al aceptar se reservará y descontará stock del almacén agrícola.
                                Luego, se podrá asignar un transportista.
                            </p>
                            <form method="POST" action="{{ route('agricola.pedidos.aceptar', $pedido) }}" class="mb-3">
                                @csrf
                                <button type="button" class="btn btn-success btn-block btn-lg" @disabled($erroresStock !== [])
                                        data-confirm-modal
                                        data-confirm-tone="success"
                                        data-confirm-title="Confirmar aceptación del pedido"
                                        data-confirm-message="¿Confirma que puede ir a recoger este pedido y reservar el stock del almacén agrícola?">
                                    <i class="fas fa-check mr-1"></i> Aceptar y reservar
                                </button>
                            </form>
                            <hr>
                            <form method="POST" action="{{ route('agricola.pedidos.rechazar', $pedido) }}">
                                @csrf
                                <div class="form-group">
                                    <label class="small">Motivo (opcional)</label>
                                    <textarea name="motivo_rechazo" class="form-control" rows="2" maxlength="500" placeholder="Ej: stock insuficiente"></textarea>
                                </div>
                                <button type="button" class="btn btn-outline-danger btn-block"
                                        data-confirm-modal
                                        data-confirm-title="Rechazar pedido"
                                        data-confirm-message="¿Rechazar este pedido? No se podrá asignar transportista después.">
                                    <i class="fas fa-times mr-1"></i> Rechazar pedido
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body">
                            @if($pedido->estado === 'rechazado')
                                <p class="text-danger mb-0">Pedido rechazado. El envío quedó cancelado para logística.</p>
                            @elseif($pedido->envioAsignacion?->transportista_usuarioid)
                                @php $log = \App\Support\EnvioPedidoService::datosLogistica($pedido->envioAsignacion); @endphp
                                @if($log && $log['cargado_en_ruta'])
                                    <p class="text-info mb-0">
                                        <i class="fas fa-shipping-fast mr-1"></i>
                                        Pedido cargado. En camino hacia planta con <strong>{{ $log['transportista_nombre'] }}</strong>.
                                    </p>
                                @else
                                    <p class="text-primary mb-0">
                                        <i class="fas fa-truck mr-1"></i>
                                        Transportista asignado. Pendiente de carga en almacén agrícola.
                                    </p>
                                @endif
                            @elseif($pedido->estado === 'confirmado' || $pedido->estado === 'en produccion')
                                <p class="text-success mb-0">
                                    <i class="fas fa-check mr-1"></i>
                                    Stock reservado. Planta puede asignar transportista.
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

                <a href="{{ route('agricola.pedidos.index') }}" class="btn btn-secondary btn-block mt-2">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al listado
                </a>
            </div>
        </div>
    </div>
</section>

@include('partials.modal-confirmar-accion')
@endsection
