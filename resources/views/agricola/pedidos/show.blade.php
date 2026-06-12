@extends('layouts.app')



@section('title', 'Pedido '.$pedido->numero_solicitud.' | Producción agrícola')

@section('page_title', 'Pedido de planta '.$pedido->numero_solicitud)



@push('styles')

<style>

.page-ag-pedido-show .hero-pedido {

    background: linear-gradient(135deg, #1e4d2b, #2c5530 45%, #4a7c59);

    border-radius: var(--r-lg, 14px);

    color: #fff;

    padding: 1.35rem 1.5rem;

    box-shadow: var(--sh-md);

    margin-bottom: 1.25rem;

}

.page-ag-pedido-show .hero-pedido .hero-codigo {

    font-size: 1.35rem;

    font-weight: 700;

    letter-spacing: -.02em;

}

.page-ag-pedido-show .hero-pedido .hero-sub {

    opacity: .88;

    font-size: .88rem;

}

.page-ag-pedido-show .hero-meta {

    display: flex;

    flex-wrap: wrap;

    gap: .5rem .75rem;

    margin-top: .85rem;

}

.page-ag-pedido-show .hero-chip {

    display: inline-flex;

    align-items: center;

    gap: .35rem;

    background: rgba(255, 255, 255, .14);

    border: 1px solid rgba(255, 255, 255, .22);

    border-radius: 999px;

    padding: .3rem .75rem;

    font-size: .78rem;

    font-weight: 500;

}

.page-ag-pedido-show .trayecto-ruta {

    display: flex;

    align-items: stretch;

    background: #fff;

    border-radius: 12px;

    overflow: hidden;

    box-shadow: 0 2px 12px rgba(0, 0, 0, .07);

    border: 1px solid var(--border, #e2e8f0);

    margin-bottom: 1.25rem;

}

@media (max-width: 767px) {

    .page-ag-pedido-show .trayecto-ruta { flex-direction: column; }

    .page-ag-pedido-show .trayecto-flecha {

        padding: .5rem;

        transform: rotate(90deg);

    }

}

.page-ag-pedido-show .trayecto-punto {

    flex: 1;

    padding: 1.1rem 1.25rem;

    min-width: 0;

}

.page-ag-pedido-show .trayecto-punto.origen {

    border-top: 4px solid #10b981;

    background: linear-gradient(180deg, #fff, #f0fdf4);

}

.page-ag-pedido-show .trayecto-punto.destino {

    border-top: 4px solid #ef4444;

    background: linear-gradient(180deg, #fff, #fef2f2);

}

.page-ag-pedido-show .trayecto-etiqueta {

    font-size: .68rem;

    text-transform: uppercase;

    letter-spacing: .06em;

    font-weight: 600;

    color: #64748b;

    margin-bottom: .35rem;

}

.page-ag-pedido-show .trayecto-punto.destino .trayecto-etiqueta { color: #b91c1c; }

.page-ag-pedido-show .trayecto-nombre {

    font-size: 1.05rem;

    font-weight: 700;

    color: #0f172a;

    line-height: 1.35;

    word-break: break-word;

}

.page-ag-pedido-show .trayecto-punto.destino .trayecto-nombre { color: #991b1b; }

.page-ag-pedido-show .trayecto-flecha {

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 0 1.1rem;

    background: #f8fafc;

    color: #94a3b8;

    font-size: 1.25rem;

    flex-shrink: 0;

}

.page-ag-pedido-show .ag-pedido-flujo {

    display: grid;

    grid-template-columns: repeat(4, minmax(0, 1fr));

    gap: .65rem;

    margin-bottom: 1.15rem;

}

@media (max-width: 991px) {

    .page-ag-pedido-show .ag-pedido-flujo { grid-template-columns: repeat(2, minmax(0, 1fr)); }

}

.page-ag-pedido-show .ag-paso-card {

    background: #fff;

    border: 2px solid #d1fae5;

    border-radius: 12px;

    padding: .75rem .55rem;

    text-align: center;

    box-shadow: 0 2px 8px rgba(5, 150, 105, .08);

    transition: transform .2s ease, box-shadow .2s ease;

}

.page-ag-pedido-show .ag-paso-card .paso-num {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    width: 26px;

    height: 26px;

    border-radius: 50%;

    background: #ecfdf5;

    color: #059669;

    font-weight: 700;

    font-size: .72rem;

    margin-bottom: .35rem;

}

.page-ag-pedido-show .ag-paso-card .paso-icon {

    display: block;

    font-size: 1rem;

    color: #10b981;

    margin-bottom: .3rem;

}

.page-ag-pedido-show .ag-paso-card .paso-label {

    display: block;

    font-size: .68rem;

    font-weight: 600;

    color: #047857;

    line-height: 1.3;

}

.page-ag-pedido-show .ag-paso-card.pendiente {

    opacity: .6;

    border-color: #e5e7eb;

    box-shadow: none;

    background: #fafafa;

}

.page-ag-pedido-show .ag-paso-card.pendiente .paso-num { background: #f3f4f6; color: #9ca3af; }

.page-ag-pedido-show .ag-paso-card.pendiente .paso-icon,

.page-ag-pedido-show .ag-paso-card.pendiente .paso-label { color: #9ca3af; }

.page-ag-pedido-show .ag-paso-card.activo {

    background: linear-gradient(145deg, #047857, #10b981);

    border-color: #065f46;

    box-shadow: 0 5px 16px rgba(5, 150, 105, .32);

    transform: translateY(-1px);

}

.page-ag-pedido-show .ag-paso-card.activo .paso-num { background: rgba(255, 255, 255, .22); color: #fff; }

.page-ag-pedido-show .ag-paso-card.activo .paso-icon,

.page-ag-pedido-show .ag-paso-card.activo .paso-label { color: #fff; }

.page-ag-pedido-show .ag-paso-card.hecho {

    background: linear-gradient(180deg, #ecfdf5, #d1fae5);

    border-color: #6ee7b7;

}

.page-ag-pedido-show .ag-paso-card.hecho .paso-num { background: #059669; color: #fff; }

.page-ag-pedido-show .ag-paso-card.hecho .paso-icon { color: #059669; }

.page-ag-pedido-show .ag-paso-card.hecho .paso-label { color: #065f46; }

.page-ag-pedido-show .card-modulo-pedido {

    border-radius: 12px;

    border-top: 3px solid #2c5530;

    box-shadow: var(--sh-sm);

    margin-bottom: 1.25rem;

}

.page-ag-pedido-show .card-modulo-pedido > .card-header {

    background: #fff;

    border-bottom: 1px solid var(--border, #e2e8f0);

    padding: .85rem 1.15rem;

}

.page-ag-pedido-show .card-modulo-pedido .card-title {

    font-size: 1rem;

    font-weight: 700;

}

.page-ag-pedido-show .tabla-productos thead th {

    font-size: .68rem;

    text-transform: uppercase;

    letter-spacing: .04em;

    color: #64748b;

    border-top: none;

    background: #f8fafc;

}

.page-ag-pedido-show .tabla-productos tbody td { vertical-align: middle; }

.page-ag-pedido-show .stock-ok {

    display: inline-flex;

    align-items: center;

    gap: .25rem;

    background: #ecfdf5;

    color: #047857;

    border-radius: 999px;

    padding: .2rem .65rem;

    font-size: .82rem;

    font-weight: 600;

}

.page-ag-pedido-show .stock-bajo {

    display: inline-flex;

    align-items: center;

    gap: .25rem;

    background: #fef2f2;

    color: #b91c1c;

    border-radius: 999px;

    padding: .2rem .65rem;

    font-size: .82rem;

    font-weight: 600;

}

.page-ag-pedido-show .badge-estado-pedido {

    font-size: .78rem;

    font-weight: 600;

    padding: .45em .85em;

    border-radius: 999px;

}

.page-ag-pedido-show .pedido-estado-agricola { background: #64748b; color: #fff; }

.page-ag-pedido-show .pedido-estado-logistica { background: #7c3aed; color: #fff; }

.page-ag-pedido-show .pedido-estado-confirmado { background: #059669; color: #fff; }

.page-ag-pedido-show .pedido-estado-camino { background: #2563eb; color: #fff; }

.page-ag-pedido-show .pedido-estado-recibido { background: #0d9488; color: #fff; }

.page-ag-pedido-show .pedido-estado-rechazado { background: #dc2626; color: #fff; }
.page-ag-pedido-show .pedido-estado-produccion { background: #d97706; color: #fff; }

.page-ag-pedido-show .panel-decision {

    border-radius: 12px;

    border: 2px solid #6ee7b7;

    box-shadow: 0 4px 18px rgba(5, 150, 105, .12);

    overflow: hidden;

}

.page-ag-pedido-show .panel-decision > .card-header {

    background: linear-gradient(135deg, #ecfdf5, #d1fae5);

    border-bottom: 1px solid #a7f3d0;

}

.page-ag-pedido-show .paso-accion {

    display: flex;

    gap: .65rem;

    align-items: flex-start;

    padding: .65rem .75rem;

    border-radius: 8px;

    background: #f8fafc;

    margin-bottom: .65rem;

    font-size: .82rem;

}

.page-ag-pedido-show .paso-accion .paso-badge {

    flex-shrink: 0;

    width: 22px;

    height: 22px;

    border-radius: 50%;

    background: #059669;

    color: #fff;

    font-size: .7rem;

    font-weight: 700;

    display: inline-flex;

    align-items: center;

    justify-content: center;

}

.page-ag-pedido-show .paso-accion.paso-2 .paso-badge { background: #2563eb; }

.page-ag-pedido-show .transporte-resumen {

    display: grid;

    grid-template-columns: repeat(3, minmax(0, 1fr));

    gap: .75rem;

    margin-bottom: 1rem;

}

@media (max-width: 575px) {

    .page-ag-pedido-show .transporte-resumen { grid-template-columns: 1fr; }

}

.page-ag-pedido-show .transporte-dato {

    background: #f8fafc;

    border: 1px solid #e2e8f0;

    border-radius: 10px;

    padding: .75rem .85rem;

    text-align: center;

}

.page-ag-pedido-show .transporte-dato .dato-label {

    font-size: .65rem;

    text-transform: uppercase;

    letter-spacing: .05em;

    color: #94a3b8;

    margin-bottom: .25rem;

}

.page-ag-pedido-show .transporte-dato .dato-valor {

    font-weight: 700;

    font-size: .92rem;

    color: #0f172a;

}

.page-ag-pedido-show .placa-badge {

    display: inline-block;

    background: #fff;

    border: 1px solid #cbd5e1;

    border-radius: 6px;

    padding: .15rem .5rem;

    font-family: ui-monospace, monospace;

    font-size: .85rem;

    letter-spacing: .04em;

}

</style>

@endpush



@section('content')

@php

    $trayectoPedido = \App\Support\EnvioPedidoService::trayectoPartesPedido($pedido);

    $origen = $trayectoPedido['recogidas'][0] ?? '—';

    $destino = $trayectoPedido['destino'] ?? '—';

    $logistica = $pedido->envioAsignacion

        ? \App\Support\EnvioPedidoService::datosLogistica($pedido->envioAsignacion)

        : null;

    $badge = \App\Support\PedidoCatalogo::badgeEstadoLista($logistica, $pedido);

    $pendienteAgr = \App\Support\PedidoCatalogo::pendienteAprobacionAgricola($pedido);

    $totalKg = $pedido->detalles->sum(fn ($d) => (float) $d->cantidad);

    $cargado = ! empty($logistica['cargado_en_ruta']);

    $recibido = ! empty($logistica['recibido_planta']);

    $aceptado = ! $pendienteAgr && $pedido->estado !== 'rechazado';

    $pasos = [

        ['num' => 1, 'icon' => 'fa-industry', 'label' => 'Solicitud planta', 'hecho' => true, 'activo' => false],

        ['num' => 2, 'icon' => 'fa-leaf', 'label' => 'Revisión agrícola', 'hecho' => $aceptado, 'activo' => $pendienteAgr],

        ['num' => 3, 'icon' => 'fa-dolly', 'label' => 'Carga y ruta', 'hecho' => $cargado || $recibido, 'activo' => $aceptado && ! $cargado && ! $recibido],

        ['num' => 4, 'icon' => 'fa-warehouse', 'label' => 'Recibido planta', 'hecho' => $recibido, 'activo' => $cargado && ! $recibido],

    ];

@endphp



<div class="page-ag-pedido-show">

    <div class="mb-3">

        <a href="{{ route('agricola.pedidos.index') }}" class="btn btn-secondary btn-sm">

            <i class="fas fa-arrow-left mr-1"></i> Volver al listado

        </a>

    </div>



    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show">

            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}

            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>

        </div>

    @endif

    @if(session('warning'))

        <div class="alert alert-warning alert-dismissible fade show">

            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('warning') }}

            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>

        </div>

    @endif



    <div class="hero-pedido d-flex flex-wrap justify-content-between align-items-start gap-3">

        <div>

            <div class="hero-codigo">

                <i class="fas fa-paper-plane mr-2 opacity-75"></i>{{ $pedido->numero_solicitud }}

            </div>

            <p class="hero-sub mb-0 mt-1">

                Revise productos, stock en almacén agrícola y confirme si puede ir a recoger la cosecha.

            </p>

            <div class="hero-meta">

                <span class="hero-chip"><i class="fas fa-calendar-alt"></i> {{ optional($pedido->fechapedido)->format('d/m/Y H:i') ?? '—' }}</span>

                @if($pedido->fechaEntregaDeseada)

                    <span class="hero-chip"><i class="fas fa-clock"></i> Entrega {{ \Carbon\Carbon::parse($pedido->fechaEntregaDeseada)->format('d/m/Y') }}</span>

                @endif

                <span class="hero-chip"><i class="fas fa-box"></i> {{ $pedido->detalles->count() }} producto(s) · {{ number_format($totalKg, 2) }} kg</span>

            </div>

        </div>

        <span class="badge badge-estado-pedido {{ $badge['clase'] }}">{{ $badge['etiqueta'] }}</span>

    </div>



    <div class="trayecto-ruta">

        <div class="trayecto-punto origen">

            <div class="trayecto-etiqueta"><i class="fas fa-map-marker-alt mr-1"></i> Origen (recogida)</div>

            <div class="trayecto-nombre">{{ $origen }}</div>

        </div>

        <div class="trayecto-flecha" aria-hidden="true">

            <i class="fas fa-long-arrow-alt-right"></i>

        </div>

        <div class="trayecto-punto destino">

            <div class="trayecto-etiqueta"><i class="fas fa-warehouse mr-1"></i> Planta destino</div>

            <div class="trayecto-nombre">{{ $destino }}</div>

        </div>

    </div>



    <div class="row">

        <div class="col-lg-8">

            @if($pedido->estado !== 'rechazado')

            <div class="ag-pedido-flujo">

                @foreach($pasos as $paso)

                    @php

                        $clasePaso = $paso['activo'] ? 'activo' : ($paso['hecho'] ? 'hecho' : 'pendiente');

                    @endphp

                    <div class="ag-paso-card {{ $clasePaso }}">

                        <span class="paso-num">{{ $paso['num'] }}</span>

                        <i class="fas {{ $paso['icon'] }} paso-icon"></i>

                        <span class="paso-label">{{ $paso['label'] }}</span>

                    </div>

                @endforeach

            </div>

            @endif



            <div class="card card-modulo-pedido">

                <div class="card-header d-flex justify-content-between align-items-center">

                    <h3 class="card-title mb-0">

                        <i class="fas fa-seedling text-success mr-2"></i>Productos solicitados

                    </h3>

                    <span class="text-muted small">{{ $pedido->detalles->count() }} línea(s)</span>

                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover tabla-productos mb-0">

                            <thead>

                                <tr>

                                    <th class="pl-3">Producto</th>

                                    <th>Cantidad</th>

                                    <th>Origen almacén</th>

                                    <th class="pr-3">Disponibilidad</th>

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

                                        $almacenOrigen = $detalle->cosechaAlmacen?->almacen?->nombre

                                            ?? $detalle->insumo?->almacen?->nombre

                                            ?? 'Almacén agrícola';

                                    @endphp

                                    <tr>

                                        <td class="pl-3">

                                            <strong class="text-primary">{{ $detalle->cultivo_personalizado }}</strong>

                                            @if($detalle->insumo)

                                                <br><small class="text-muted">{{ $detalle->insumo->nombre }}</small>

                                            @endif

                                        </td>

                                        <td><strong>{{ number_format($detalle->cantidad, 2) }}</strong> <span class="text-muted small">kg</span></td>

                                        <td><i class="fas fa-warehouse text-muted mr-1"></i>{{ $almacenOrigen }}</td>

                                        <td class="pr-3">

                                            @if($ok)

                                                <span class="stock-ok"><i class="fas fa-check"></i> {{ $disp }}</span>

                                            @else

                                                <span class="stock-bajo"><i class="fas fa-times"></i> Insuficiente ({{ $disp }})</span>

                                            @endif

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>



            @if($erroresStock !== [])

                <div class="alert alert-danger border-0 shadow-sm">

                    <strong><i class="fas fa-exclamation-triangle mr-1"></i>No se puede aceptar todavía:</strong>

                    <ul class="mb-0 pl-3 mt-1">

                        @foreach($erroresStock as $err)

                            <li>{{ $err }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif



            @if($pedido->fecha_aceptacion_agricola)

                <div class="alert alert-success border-0 py-2 small mb-3">

                    <i class="fas fa-check-circle mr-1"></i>

                    Aceptado el {{ $pedido->fecha_aceptacion_agricola->format('d/m/Y H:i') }}

                    @if($pedido->aceptadoPor)

                        por {{ $pedido->aceptadoPor->nombre }} {{ $pedido->aceptadoPor->apellido }}

                    @endif

                </div>

            @endif



            @if($logistica)

                <div class="card card-modulo-pedido">

                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">

                        <h3 class="card-title mb-0">

                            <i class="fas fa-truck text-primary mr-2"></i>Transporte y envío

                        </h3>

                        @php

                            $badgeEnvio = match(true) {

                                $logistica['recibido_planta'] => ['success', 'Recibido en planta'],

                                $logistica['cargado_en_ruta'] => ['info', 'En camino hacia planta'],

                                default => ['secondary', 'Transportista asignado'],

                            };

                        @endphp

                        <span class="badge badge-{{ $badgeEnvio[0] }} badge-estado-pedido">{{ $badgeEnvio[1] }}</span>

                    </div>

                    <div class="card-body">

                        <div class="transporte-resumen">

                            <div class="transporte-dato">

                                <div class="dato-label">Transportista</div>

                                <div class="dato-valor">{{ $logistica['transportista_nombre'] }}</div>

                            </div>

                            <div class="transporte-dato">

                                <div class="dato-label">Vehículo</div>

                                <div class="dato-valor">{{ $logistica['vehiculo_nombre'] }}</div>

                            </div>

                            <div class="transporte-dato">

                                <div class="dato-label">Placa</div>

                                <div class="dato-valor"><span class="placa-badge">{{ $logistica['placa'] }}</span></div>

                            </div>

                        </div>



                        <dl class="row mb-0 small text-muted">

                            <dt class="col-sm-4">Estado del envío</dt>

                            <dd class="col-sm-8 text-dark">{{ $logistica['estado_etiqueta'] }}</dd>

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

                            <div class="alert alert-info mb-0 mt-3 py-2 small border-0">

                                <i class="fas fa-shipping-fast mr-1"></i>

                                <strong>Pedido cargado.</strong> El envío está en ruta hacia la planta de procesamiento.

                            </div>

                        @elseif($logistica['recibido_planta'])

                            <div class="alert alert-success mb-0 mt-3 py-2 small border-0">

                                <i class="fas fa-warehouse mr-1"></i>

                                Mercadería recibida en planta.

                            </div>

                        @elseif($pendienteAgr)

                            <div class="alert alert-secondary mb-0 mt-3 py-2 small border-0">

                                <i class="fas fa-hourglass-half mr-1"></i>

                                Transporte programado. Se activará cuando producción agrícola <strong>acepte el pedido</strong>.

                            </div>

                        @else

                            <div class="alert alert-warning mb-0 mt-3 py-2 small border-0">

                                <i class="fas fa-dolly mr-1"></i>

                                Transportista asignado. Pendiente de <strong>carga en almacén agrícola</strong> e inicio de ruta.

                            </div>

                        @endif



                        @if(\App\Support\PedidoCatalogo::listoParaLogistica($pedido) && $logistica['asignado'] && ! $logistica['cargado_en_ruta'] && ! $logistica['recibido_planta'])
                            @php $esChoferAsignado = (int) auth()->id() === (int) ($pedido->envioAsignacion?->transportista_usuarioid ?? 0); @endphp
                            @can('pedidos.update')
                                <form method="POST" action="{{ route('agricola.pedidos.confirmar-carga-envio', $pedido) }}" class="mt-3 mb-0">
                                    @csrf
                                    <button type="button" class="btn btn-success btn-block btn-lg font-weight-bold"
                                            data-confirm-modal data-confirm-tone="success"
                                            data-confirm-title="Confirmar carga e iniciar ruta"
                                            data-confirm-message="¿Confirma que el pedido ya fue cargado en el vehículo y sale hacia planta?">
                                        <i class="fas fa-shipping-fast mr-1"></i> Confirmar carga e iniciar ruta a planta
                                    </button>
                                </form>
                            @elseif($esChoferAsignado && $pedido->envioAsignacion)
                                <div class="mt-3">
                                    <form method="POST" action="{{ route('logistica.asignaciones.en-transporte', $pedido->envioAsignacion) }}" class="mb-0">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success btn-block btn-lg font-weight-bold">
                                            <i class="fas fa-shipping-fast mr-1"></i> Confirmar carga e iniciar ruta a planta
                                        </button>
                                    </form>
                                </div>
                            @endcan
                        @endif

                    </div>

                </div>

            @endif

        </div>



        <div class="col-lg-4">

            @if($pendienteAgr)

                <div class="card panel-decision mb-3">

                    <div class="card-header py-3">

                        <h3 class="card-title mb-0 font-weight-bold">

                            <i class="fas fa-clipboard-check text-success mr-1"></i> Decisión agrícola

                        </h3>

                    </div>

                    <div class="card-body">

                        <div class="paso-accion">

                            <span class="paso-badge">1</span>

                            <div>

                                <strong>Aceptar y reservar</strong> — descuenta stock del almacén agrícola.

                                @if($pedido->envioAsignacion?->transportista_usuarioid)

                                    El chofer ya está programado y se activará al aceptar.

                                @endif

                            </div>

                        </div>

                        @if($pedido->envioAsignacion?->transportista_usuarioid)

                            <div class="paso-accion paso-2">

                                <span class="paso-badge">2</span>

                                <div>

                                    <strong>Confirmar carga</strong> — después de aceptar, cuando el camión salga cargado hacia planta.

                                </div>

                            </div>

                        @endif



                        <form method="POST" action="{{ route('agricola.pedidos.aceptar', $pedido) }}" class="mb-3">

                            @csrf

                            <button type="button" class="btn btn-success btn-block btn-lg font-weight-bold" @disabled($erroresStock !== [])

                                    data-confirm-modal

                                    data-confirm-tone="success"

                                    data-confirm-title="Confirmar aceptación del pedido"

                                    data-confirm-message="¿Confirma que puede ir a recoger este pedido y reservar el stock del almacén agrícola?">

                                <i class="fas fa-check mr-1"></i> Aceptar y reservar

                            </button>

                        </form>

                        <hr class="my-3">

                        <form method="POST" action="{{ route('agricola.pedidos.rechazar', $pedido) }}">

                            @csrf

                            <div class="form-group mb-2">

                                <label class="small text-muted mb-1">Motivo de rechazo (opcional)</label>

                                <textarea name="motivo_rechazo" class="form-control form-control-sm" rows="2" maxlength="500" placeholder="Ej: stock insuficiente"></textarea>

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

                <div class="card card-modulo-pedido mb-3">

                    <div class="card-header">

                        <h3 class="card-title mb-0"><i class="fas fa-info-circle text-muted mr-1"></i> Estado actual</h3>

                    </div>

                    <div class="card-body">

                        @if($pedido->estado === 'rechazado')

                            <p class="text-danger mb-0">

                                <i class="fas fa-ban mr-1"></i>

                                Pedido rechazado. El envío quedó cancelado para logística.

                            </p>

                        @elseif($logistica && $logistica['cargado_en_ruta'])

                            <p class="text-info mb-0">

                                <i class="fas fa-shipping-fast mr-1"></i>

                                Pedido cargado. En camino hacia planta con <strong>{{ $logistica['transportista_nombre'] }}</strong>.

                            </p>

                        @elseif($logistica)

                            <p class="text-primary mb-0">

                                <i class="fas fa-truck mr-1"></i>

                                Transportista asignado. Pendiente de carga en almacén agrícola.

                            </p>

                        @elseif($pedido->estado === 'confirmado' || $pedido->estado === 'en produccion')

                            <p class="text-success mb-0">

                                <i class="fas fa-check mr-1"></i>

                                Stock reservado. Listo para confirmar carga y salida.

                            </p>

                        @endif

                    </div>

                </div>

            @endif

        </div>

    </div>

</div>



@include('partials.modal-confirmar-accion')

@endsection

