@extends('layouts.app')



@section('title', 'Detalle envío '.$asignacion->externo_envio_id.' | AgroFusion')

@section('page_title', 'Detalle del envío')



@push('styles')

@include('logistica.partials.ayuda-logistica-styles')

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<style>

.page-envio-detalle .env-hero{

    background:linear-gradient(135deg,#f8fafc 0%,#ecfdf5 100%);

    border:1px solid #d1fae5;

    border-radius:12px;

    padding:.5rem .85rem;

    margin-bottom:.5rem;

    box-shadow:0 2px 12px rgba(15,23,42,.05);

}

.page-envio-detalle .env-hero-top{

    display:flex;flex-wrap:wrap;gap:.65rem;justify-content:space-between;align-items:center;

}

.page-envio-detalle .env-hero-codigo{

    font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#64748b;font-weight:600;

}

.page-envio-detalle .env-hero-titulo{font-size:1.1rem;font-weight:800;color:#0f172a;line-height:1.2}

.page-envio-detalle .env-hero-meta{font-size:.78rem;color:#64748b;line-height:1.35;margin-top:.15rem}

.page-envio-detalle .content{padding-top:.35rem!important}

.page-envio-detalle .env-estado-chip{

    display:inline-flex;align-items:center;gap:.45rem;

    padding:.45rem .9rem;border-radius:999px;

    font-size:.78rem;font-weight:700;letter-spacing:.02em;

    box-shadow:0 2px 8px rgba(15,23,42,.08);

}

.page-envio-detalle .env-estado-chip i{font-size:.85rem}

.page-envio-detalle .pedido-estado-agricola{background:linear-gradient(135deg,#475569,#334155);color:#fff}

.page-envio-detalle .pedido-estado-logistica{background:linear-gradient(135deg,#7c3aed,#6d28d9);color:#fff}

.page-envio-detalle .pedido-estado-confirmado{background:linear-gradient(135deg,#059669,#047857);color:#fff}

.page-envio-detalle .pedido-estado-camino{background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff}

.page-envio-detalle .pedido-estado-recibido{background:linear-gradient(135deg,#0d9488,#0f766e);color:#fff}

.page-envio-detalle .pedido-estado-produccion{background:linear-gradient(135deg,#d97706,#b45309);color:#fff}

.page-envio-detalle .pedido-estado-rechazado{background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff}

.page-envio-detalle .env-estado-asignado{background:linear-gradient(135deg,#0284c7,#0369a1);color:#fff}

.page-envio-detalle .env-det-card{

    border:0;border-radius:14px;box-shadow:0 4px 18px rgba(18,38,63,.08);overflow:hidden;

}

.page-envio-detalle .env-det-card > .card-header{

    background:#fff;border-bottom:1px solid #f1f5f9;padding:.65rem .95rem;

}

.page-envio-detalle .env-det-card--accent > .card-header{

    background:linear-gradient(135deg,#f0fdf4,#ecfdf5);

}

.page-envio-detalle .env-det-card--info > .card-header{

    background:linear-gradient(135deg,#eff6ff,#dbeafe);

}

.page-envio-detalle .env-det-card--acciones > .card-header{

    background:linear-gradient(135deg,#f8fafc,#f1f5f9);

    border-bottom-color:#e2e8f0;

}

.page-envio-detalle .env-det-card--acciones > .card-header .fa-bolt{color:#64748b!important}

.page-envio-detalle .env-det-card > .card-body{padding:.65rem .95rem}

.page-envio-detalle .env-det-card{margin-bottom:.65rem}

.page-envio-detalle .env-show-grid{margin-bottom:0}

.page-envio-detalle .env-panel-unificado .card-body{padding:0}

.page-envio-detalle .env-panel-top{display:flex;flex-wrap:wrap}

.page-envio-detalle .env-panel-seccion--logistica{flex:1 1 280px}

.page-envio-detalle .env-panel-seccion--pedido{flex:1 1 240px}

.page-envio-detalle .env-panel-recorrido-fila{

    border-top:1px solid #e2e8f0;padding:.8rem 1.1rem 1rem;

    background:linear-gradient(180deg,#f8fafc 0%,#fff 100%);

}

.page-envio-detalle .env-panel-recorrido-fila .env-panel-seccion-head{margin-bottom:.55rem}

.page-envio-detalle .env-panel-recorrido-fila .env-rh-track{padding:0 1.5rem}

.page-envio-detalle .env-panel-recorrido-fila .env-rh-step{max-width:none}

.page-envio-detalle .env-panel-recorrido-fila .env-rh-dot{width:30px;height:30px;font-size:.72rem}

.page-envio-detalle .env-panel-recorrido-fila .env-rh-step:not(:last-child)::before{top:15px}

.page-envio-detalle .env-panel-recorrido-fila .env-rh-title{font-size:.78rem}

.page-envio-detalle .env-panel-recorrido-fila .env-rh-date{font-size:.7rem}

.page-envio-detalle .env-panel-recorrido-fila .env-recorrido-bar{height:10px}

.page-envio-detalle .env-panel-recorrido-fila .env-rh-footer{

    text-align:center;margin-top:.65rem;padding-top:.55rem;

}

.page-envio-detalle .env-panel-seccion{

    flex:1 1 0;min-width:220px;padding:.6rem .85rem;border-right:1px solid #eef2f0;

}

.page-envio-detalle .env-panel-top .env-panel-seccion:last-child{border-right:0}

.page-envio-detalle .env-kv-grid{

    display:grid;grid-template-columns:1fr 1fr;gap:.3rem .65rem;

}

.page-envio-detalle .env-kv-grid--1col{grid-template-columns:1fr}

.page-envio-detalle .env-panel-seccion-head{

    display:flex;align-items:center;justify-content:space-between;gap:.5rem;

    font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;

    font-weight:700;color:#64748b;margin-bottom:.4rem;

}

.page-envio-detalle .env-panel-split{display:block}

.page-envio-detalle .env-link-pedido{font-size:.78rem;margin-top:.35rem;display:inline-block}

.page-envio-detalle .env-recorrido-horizontal .env-recorrido-progreso{margin-bottom:.35rem!important}

.page-envio-detalle .env-rh-track{

    display:flex;align-items:flex-start;justify-content:space-between;gap:.25rem;margin-top:.15rem;

}

.page-envio-detalle .env-rh-step{

    flex:1;text-align:center;position:relative;padding:0 .15rem;

}

.page-envio-detalle .env-rh-step:not(:last-child)::before{

    content:'';position:absolute;top:13px;left:calc(50% + 14px);right:calc(-50% + 14px);height:2px;

    background:#bbf7d0;z-index:0;

}

.page-envio-detalle .env-rh-step--pendiente:not(:last-child)::before{background:#e2e8f0}

.page-envio-detalle .env-rh-dot{

    width:26px;height:26px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;

    font-size:.68rem;position:relative;z-index:1;margin:0 auto .25rem;

}

.page-envio-detalle .env-rh-step--hecho .env-rh-dot{background:#059669;color:#fff}

.page-envio-detalle .env-rh-step--activo .env-rh-dot{background:#2563eb;color:#fff;box-shadow:0 0 0 3px rgba(37,99,235,.15)}

.page-envio-detalle .env-rh-step--pendiente .env-rh-dot{background:#e2e8f0;color:#94a3b8}

.page-envio-detalle .env-rh-title{font-size:.72rem;font-weight:700;color:#0f172a;line-height:1.2}

.page-envio-detalle .env-rh-date{font-size:.65rem;color:#94a3b8;margin-top:.1rem;line-height:1.2}

.page-envio-detalle .env-rh-footer{

    margin-top:.45rem;padding-top:.4rem;border-top:1px dashed #dbeafe;

    font-size:.72rem;color:#475569;line-height:1.35;

}

.page-envio-detalle .env-acciones-bar{

    border-top:1px solid #eef2f0;padding:.55rem .8rem;background:#fafbfc;

}

@media (max-width:991.98px){

    .page-envio-detalle .env-panel-top .env-panel-seccion{border-right:0;border-bottom:1px solid #eef2f0}

    .page-envio-detalle .env-panel-top .env-panel-seccion:last-child{border-bottom:0}

}

.page-envio-detalle .env-det-grid .col-md-6{margin-bottom:.45rem}

.page-envio-detalle .env-det-value{font-size:.92rem;font-weight:600;color:#1e293b;line-height:1.35}

.page-envio-detalle .env-det-value--costo{font-size:1.05rem;color:#2c5530}

.page-envio-detalle .env-paso-item{padding:.45rem 0}

.page-envio-detalle .env-paso-marker{width:32px;height:32px;border-radius:10px}

.page-envio-detalle .env-paso-desc{font-size:.74rem;line-height:1.3;margin-bottom:.25rem!important}

.page-envio-detalle .env-paso-titulo{font-size:.86rem}

.page-envio-detalle .env-recorrido-progreso{margin-bottom:.5rem!important}

.page-envio-detalle .env-llegada-inline{

    border-top:1px dashed #d1fae5;margin-top:.5rem;padding-top:.65rem;

}

.page-envio-detalle .env-llegada-inline .row > [class*="col-"]{margin-bottom:.35rem}

.page-envio-detalle .env-det-label{

    font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b;font-weight:600;margin-bottom:.15rem;

}

.page-envio-detalle .env-recorrido-progreso .env-recorrido-pct{

    font-size:.85rem;font-weight:800;color:#2c5530;

}

.page-envio-detalle .env-recorrido-bar{

    height:8px;border-radius:999px;background:#e2e8f0;overflow:hidden;

}

.page-envio-detalle .env-recorrido-bar-fill{

    height:100%;border-radius:999px;

    background:linear-gradient(90deg,#34d399,#059669);

    transition:width .4s ease;

}

.page-envio-detalle .env-pasos-lista{list-style:none;padding:0;margin:0}

.page-envio-detalle .env-paso-item{

    display:flex;gap:.55rem;padding:.45rem 0;position:relative;

}

.page-envio-detalle .env-paso-item:not(:last-child){

    border-bottom:1px dashed #e2e8f0;

}

.page-envio-detalle .env-paso-marker{

    flex-shrink:0;width:32px;height:32px;border-radius:10px;

    display:flex;align-items:center;justify-content:center;position:relative;

    background:#f1f5f9;color:#94a3b8;

}

.page-envio-detalle .env-paso-marker-num{

    position:absolute;top:-6px;right:-6px;width:18px;height:18px;border-radius:50%;

    background:#cbd5e1;color:#fff;font-size:.65rem;font-weight:800;

    display:flex;align-items:center;justify-content:center;

}

.page-envio-detalle .env-paso-marker-icon{font-size:.82rem}

.page-envio-detalle .env-paso-item--hecho .env-paso-marker{

    background:linear-gradient(135deg,#d1fae5,#a7f3d0);color:#047857;

}

.page-envio-detalle .env-paso-item--hecho .env-paso-marker-num{background:#059669}

.page-envio-detalle .env-paso-item--activo .env-paso-marker{

    background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:#1d4ed8;

    box-shadow:0 0 0 4px rgba(37,99,235,.12);

}

.page-envio-detalle .env-paso-item--activo .env-paso-marker-num{background:#2563eb}

.page-envio-detalle .env-paso-titulo{font-weight:700;color:#0f172a;font-size:.86rem}

.page-envio-detalle .env-paso-desc{font-size:.74rem;color:#64748b;line-height:1.3;margin:0}

.page-envio-detalle .env-paso-fecha{font-size:.75rem;color:#94a3b8}

.page-envio-detalle .env-paso-fecha--activo{color:#334155;font-weight:600}

.page-envio-detalle .env-accion-info{

    padding:.85rem 1rem;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;

    font-size:.84rem;color:#475569;

}

.page-envio-detalle .env-accion-info i{color:#64748b}

.page-envio-detalle .env-rechazo-details summary:hover{color:#334155!important}

#mapaRecorridoEnvio{

    height:420px;width:100%;min-height:420px;border-radius:10px;

    border:1px solid #dee2e6;background:#e8eef4;z-index:1;

}

#mapaRecorridoEnvio.leaflet-container{font-family:inherit}

.leaflet-div-icon.ruta-parada-marker{

    width:auto!important;height:auto!important;margin:0!important;padding:0!important;

    background:transparent!important;border:none!important;

}

</style>

@endpush



@section('content')

@php

    $pedido = $asignacion->pedido;

    $estadoChip = $estadoVisual ?? ['clase' => 'env-estado-asignado', 'etiqueta' => 'En proceso', 'titulo' => ''];

    $iconoEstado = match (true) {

        ($pendienteAgricola ?? false) => 'fa-seedling',

        ($llegoDestino ?? false) => 'fa-check-circle',

        in_array($asignacion->estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true) => 'fa-shipping-fast',

        default => 'fa-truck-loading',

    };

@endphp



<div class="page-envio-detalle">

    <div class="env-hero">

        <div class="env-hero-top">

            <div>

                <div class="env-hero-codigo">Envío · Almacén → Planta</div>

                <div class="env-hero-titulo">{{ $asignacion->externo_envio_id }}</div>

                @if($pedido)

                    <div class="env-hero-meta">

                        {{ $pedido->detalles->first()?->cultivo_personalizado ?? 'Producto' }}

                        · {{ number_format($pedido->detalles->sum('cantidad'), 2) }} kg

                        @if($trayectoPartes['destino'] ?? null)

                            · {{ $trayectoPartes['destino'] }}

                        @endif

                    </div>

                @endif

            </div>

            <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">

                <span class="env-estado-chip {{ $estadoChip['clase'] }}" title="{{ $estadoChip['titulo'] ?? '' }}">

                    <i class="fas {{ $iconoEstado }}"></i>

                    {{ $estadoChip['etiqueta'] }}

                </span>

                <a href="{{ route('logistica.asignaciones.listado') }}" class="btn btn-outline-secondary btn-sm">

                    <i class="fas fa-arrow-left mr-1"></i>Volver

                </a>

                @if($puedeEditar ?? \App\Support\PedidoCatalogo::puedeEditarAsignacionEnvio($asignacion))

                    @can('asignaciones.update')

                    <a href="{{ route('logistica.asignaciones.edit', $asignacion) }}" class="btn btn-warning btn-sm">

                        <i class="fas fa-edit mr-1"></i>Editar

                    </a>

                    @endcan

                @endif

            </div>

        </div>

    </div>



    <section class="content pt-0">

        <div class="container-fluid">

            @php
                $simAgrActiva = \App\Support\SimulacionRutaCatalogo::simulacionActivaAgricola($asignacion);
                $urlTiempoRealAgr = $simAgrActiva
                    ? route('logistica.rutas-tiempo-real.show', ['tipo' => 'agricola', 'id' => $asignacion->envioasignacionmultipleid])
                    : null;
                $esMiAsignacion = (int) auth()->id() === (int) ($asignacion->transportista_usuarioid ?? 0);
                $conAccionesOperativas = in_array($asignacion->estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true)
                    && ! $simAgrActiva;
                if (in_array($asignacion->estado, ['asignado', 'asignada', 'pendiente', 'creada'], true)) {
                    $conAccionesOperativas = \App\Support\SimulacionRutaCatalogo::usuarioPuedeEmpezarAgricola(auth()->user(), $asignacion);
                }
                $hayAccionAgricola = ($pendienteAgricola ?? false) && ($puedeAceptarAgricola ?? false);
                $hayAccionTransportista = \App\Support\SimulacionRutaCatalogo::usuarioPuedeEmpezarAgricola(auth()->user(), $asignacion);
                $hayAccionLlegada = in_array($asignacion->estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true)
                    && ! $simAgrActiva
                    && (auth()->user()?->can('asignaciones.update') || $esMiAsignacion);
                $presEnv = ($pedido && $pedido->detalles->first())
                    ? \App\Support\PedidoCatalogo::presentacionDetalle($pedido->detalles->first())
                    : null;
            @endphp

            <div class="card env-det-card env-panel-unificado mb-0">

                <div class="card-body">

                    <div class="env-panel-split">

                        <div class="env-panel-top">

                        <div class="env-panel-seccion env-panel-seccion--logistica">

                            <div class="env-panel-seccion-head">

                                <span><i class="fas fa-truck text-success mr-1"></i>Logística</span>

                            </div>

                            <div class="env-kv-grid">

                                <div>

                                    <div class="env-det-label">Chofer</div>

                                    <div class="env-det-value">{{ trim(($asignacion->transportista?->nombre ?? '').' '.($asignacion->transportista?->apellido ?? '')) ?: 'Sin asignar' }}</div>

                                </div>

                                <div>

                                    <div class="env-det-label">Vehículo</div>

                                    <div class="env-det-value">{{ $asignacion->vehiculo_ref ?? '—' }}</div>

                                </div>

                                <div>

                                    <div class="env-det-label">Costo</div>

                                    <div class="env-det-value env-det-value--costo">{{ \App\Support\TransporteIngresoCatalogo::formatearCosto($asignacion->costo_bs !== null ? (float) $asignacion->costo_bs : null) }}</div>

                                </div>

                                <div>

                                    <div class="env-det-label">Asignación</div>

                                    <div class="env-det-value">{{ optional($asignacion->fecha_asignacion)->format('d/m/Y H:i') ?? '—' }}</div>

                                </div>

                                <div class="env-kv-grid--full" style="grid-column:1/-1;">

                                    <div class="env-det-label">Ruta</div>

                                    <div class="env-det-value">

                                        @if($trayectoPartes ?? null)

                                            @include('logistica.partials.trayecto-colores', ['trayectoPartes' => $trayectoPartes])

                                        @elseif($trayectoTexto ?? null)

                                            {{ $trayectoTexto }}

                                        @elseif($asignacion->ruta?->nombre)

                                            {{ $asignacion->ruta->nombre }}

                                        @else

                                            Sin ruta definida

                                        @endif

                                    </div>

                                </div>

                            </div>

                        </div>



                        @if($pedido)

                        <div class="env-panel-seccion env-panel-seccion--pedido">

                            <div class="env-panel-seccion-head">

                                <span><i class="fas fa-shopping-cart text-success mr-1"></i>Pedido</span>

                                <a href="{{ route('pedidos.show', $pedido) }}" class="env-link-pedido text-success">

                                    <i class="fas fa-external-link-alt mr-1"></i>Ver completo

                                </a>

                            </div>

                            <div class="env-kv-grid">

                                <div>

                                    <div class="env-det-label">Solicitud</div>

                                    <div class="env-det-value">{{ $pedido->numero_solicitud }}</div>

                                </div>

                                <div>

                                    <div class="env-det-label">Producto</div>

                                    <div class="env-det-value">{{ $pedido->detalles->first()?->cultivo_personalizado ?? '—' }}</div>

                                </div>

                                <div style="grid-column:1/-1;">

                                    <div class="env-det-label">Cantidad / empaque</div>

                                    <div class="env-det-value">{{ $presEnv['linea_corta'] ?? '—' }}</div>

                                    @if($pedido->detalles->count() > 1)

                                        <span class="small text-muted">+{{ $pedido->detalles->count() - 1 }} ítem(s) más</span>

                                    @endif

                                </div>

                            </div>

                        </div>

                        @endif

                        </div>



                    @if(! $llegoDestino)

                    <div class="env-acciones-bar">

                        <div class="env-panel-seccion-head mb-2">

                            <span><i class="fas fa-bolt text-warning mr-1"></i>Acciones</span>

                        </div>

                        @if($hayAccionAgricola)

                            @include('logistica.partials.accion-decision-agricola', [

                                'asignacion' => $asignacion,

                                'erroresStock' => $erroresStock ?? [],

                                'puedeAceptarAgricola' => $puedeAceptarAgricola,

                            ])

                        @endif

                        @if(in_array($asignacion->estado, ['asignado', 'asignada', 'pendiente', 'creada'], true))

                            @include('logistica.partials.accion-empezar-ruta', ['asignacion' => $asignacion, 'bloque' => true])

                        @elseif(in_array($asignacion->estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true) && ! $simAgrActiva)

                            <div class="{{ $hayAccionAgricola ? 'border-top pt-2 mt-1' : '' }}">

                                @include('logistica.partials.accion-llegada-destino', ['asignacion' => $asignacion])

                            </div>

                        @endif

                        @if($urlTiempoRealAgr)

                            @include('logistica.partials.accion-ver-tiempo-real', [
                                'urlTiempoReal' => $urlTiempoRealAgr,
                                'conBordeSuperior' => $hayAccionAgricola || $conAccionesOperativas,
                            ])

                        @endif

                        @include('logistica.partials.tarjeta-ingreso-transportista', [

                            'asignacion' => $asignacion,

                            'conBordeSuperior' => ($conAccionesOperativas || $hayAccionAgricola) && ! $urlTiempoRealAgr,

                        ])

                        @if(! $hayAccionAgricola && ! $hayAccionTransportista && ! $hayAccionLlegada && ! $urlTiempoRealAgr)

                            <div class="env-accion-info {{ ($conAccionesOperativas || $hayAccionAgricola) ? 'border-top pt-2 mt-2' : '' }}">

                                @if($pendienteAgricola ?? false)

                                    <i class="fas fa-hourglass-half mr-1"></i>

                                    Esperando que <strong>producción agrícola</strong> acepte y reserve el stock del almacén.

                                @elseif(in_array($asignacion->estado, ['asignado', 'asignada', 'pendiente', 'creada'], true))

                                    <i class="fas fa-truck-loading mr-1"></i>

                                    Pedido listo. El transportista debe pulsar <strong>Empezar ruta</strong> cuando salga cargado hacia planta.

                                @else

                                    <i class="fas fa-info-circle mr-1"></i>

                                    No hay acciones pendientes en este momento.

                                @endif

                            </div>

                        @endif

                    </div>

                    @endif

                    <div class="env-panel-recorrido-fila">

                        <div class="env-panel-seccion-head">

                            <span><i class="fas fa-route text-primary mr-1"></i>Recorrido del envío</span>

                            @if($urlTiempoRealAgr)
                                <a href="{{ $urlTiempoRealAgr }}" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:.7rem;">
                                    <i class="fas fa-satellite-dish mr-1"></i>GPS en vivo
                                </a>
                            @elseif(count($paradasMapa ?? []) >= 1)
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:.7rem;" data-toggle="modal" data-target="#modalMapaRecorrido">
                                    <i class="fas fa-map-marked-alt mr-1"></i>Ver mapa
                                </button>
                            @endif

                        </div>

                        @include('logistica.partials.recorrido-envio-pasos', [

                            'asignacion' => $asignacion,

                            'llegoDestino' => $llegoDestino,

                            'pendienteAgricola' => $pendienteAgricola ?? false,

                            'modo' => 'horizontal',

                        ])

                    </div>

                    </div>

                </div>

            </div>

        </div>

    </section>

</div>



@if(count($paradasMapa ?? []) >= 1)

<div class="modal fade" id="modalMapaRecorrido" tabindex="-1" role="dialog" aria-labelledby="modalMapaRecorridoLabel" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">

        <div class="modal-content" style="border-radius:14px;">

            <div class="modal-header">

                <h5 class="modal-title font-weight-bold" id="modalMapaRecorridoLabel">

                    <i class="fas fa-route text-success mr-2"></i>Recorrido del envío

                </h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">

                    <span aria-hidden="true">&times;</span>

                </button>

            </div>

            <div class="modal-body p-2">

                @if($trayectoPartes ?? null)

                <p class="small mb-2 px-1">@include('logistica.partials.trayecto-colores', ['trayectoPartes' => $trayectoPartes])</p>

                @endif

                <div id="mapaRecorridoEnvio"></div>

            </div>

        </div>

    </div>

</div>

@endif



@include('partials.modal-confirmar-accion')

@endsection



@if(count($paradasMapa ?? []) >= 1)

@push('scripts')

@include('logistica.partials.mapa-ruta-libs')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const paradas = @json($paradasMapa);

    const urlTrazado = @json($urlTrazadoRuta);

    const modal = document.getElementById('modalMapaRecorrido');

    if (!modal || !paradas.length) return;



    let mapa = null;

    let capas = null;



    function redimensionarMapa() {

        if (!mapa) return;

        mapa.invalidateSize({ animate: false });

    }



    function asegurarMapa() {

        const el = document.getElementById('mapaRecorridoEnvio');

        if (!el || !window.L) return null;



        if (mapa) {

            redimensionarMapa();

            return mapa;

        }



        mapa = L.map(el, { scrollWheelZoom: true }).setView([paradas[0].lat, paradas[0].lng], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {

            maxZoom: 19,

            attribution: '© OpenStreetMap',

        }).addTo(mapa);

        capas = L.layerGroup().addTo(mapa);

        redimensionarMapa();



        return mapa;

    }



    async function dibujarRecorrido() {

        if (!window.L) return;



        const mapaActivo = asegurarMapa();

        if (!mapaActivo || !capas) return;



        redimensionarMapa();

        capas.clearLayers();



        let routeResult = null;



        if (urlTrazado) {

            try {

                const res = await fetch(urlTrazado);

                const data = await res.json();

                if (data.geo) {

                    routeResult = {

                        geojson: data.geo,

                        straight: data.geo?.features?.[0]?.properties?.provider === 'straight',

                    };

                }

            } catch (e) {

                console.warn(e);

            }

        }



        if (!routeResult && paradas.length >= 2 && window.RutaPorCalles) {

            routeResult = await RutaPorCalles.fetchRoute(paradas);

        }



        redimensionarMapa();



        if (window.RutaPorCalles) {

            RutaPorCalles.drawOnMap(mapaActivo, capas, paradas, routeResult);

        } else {

            paradas.forEach(function (p, i) {

                L.marker([p.lat, p.lng]).addTo(capas).bindPopup(p.label || ('Parada ' + (i + 1)));

            });

        }



        [100, 300, 600].forEach(function (ms) {

            setTimeout(redimensionarMapa, ms);

        });

    }



    function alAbrirModal() {

        setTimeout(dibujarRecorrido, 200);

    }



    if (window.jQuery) {

        window.jQuery(modal).on('shown.bs.modal', alAbrirModal);

    } else {

        modal.addEventListener('shown.bs.modal', alAbrirModal);

    }

});

</script>

@endpush

@endif

