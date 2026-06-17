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

    border-radius:16px;

    padding:1.25rem 1.5rem;

    margin-bottom:1.25rem;

    box-shadow:0 4px 20px rgba(15,23,42,.06);

}

.page-envio-detalle .env-hero-top{

    display:flex;flex-wrap:wrap;gap:1rem;justify-content:space-between;align-items:flex-start;

}

.page-envio-detalle .env-hero-codigo{

    font-size:.8rem;text-transform:uppercase;letter-spacing:.06em;color:#64748b;font-weight:600;

}

.page-envio-detalle .env-hero-titulo{font-size:1.35rem;font-weight:800;color:#0f172a;line-height:1.2}

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

    background:#fff;border-bottom:1px solid #f1f5f9;padding:.85rem 1.15rem;

}

.page-envio-detalle .env-det-card--accent > .card-header{

    background:linear-gradient(135deg,#f0fdf4,#ecfdf5);

}

.page-envio-detalle .env-det-card--info > .card-header{

    background:linear-gradient(135deg,#eff6ff,#dbeafe);

}

.page-envio-detalle .env-det-card--acciones > .card-header{

    background:linear-gradient(135deg,#fffbeb,#fef3c7);

    border-bottom-color:#fde68a;

}

.page-envio-detalle .env-det-label{

    font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b;font-weight:600;margin-bottom:.2rem;

}

.page-envio-detalle .env-det-value{font-size:1rem;font-weight:600;color:#1e293b}

.page-envio-detalle .env-det-value--costo{font-size:1.25rem;color:#059669}

.page-envio-detalle .env-det-grid .col-md-6{margin-bottom:1rem}

.page-envio-detalle .env-recorrido-progreso .env-recorrido-pct{

    font-size:.85rem;font-weight:800;color:#059669;

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

    display:flex;gap:.85rem;padding:.85rem 0;position:relative;

}

.page-envio-detalle .env-paso-item:not(:last-child){

    border-bottom:1px dashed #e2e8f0;

}

.page-envio-detalle .env-paso-marker{

    flex-shrink:0;width:42px;height:42px;border-radius:12px;

    display:flex;align-items:center;justify-content:center;position:relative;

    background:#f1f5f9;color:#94a3b8;

}

.page-envio-detalle .env-paso-marker-num{

    position:absolute;top:-6px;right:-6px;width:18px;height:18px;border-radius:50%;

    background:#cbd5e1;color:#fff;font-size:.65rem;font-weight:800;

    display:flex;align-items:center;justify-content:center;

}

.page-envio-detalle .env-paso-marker-icon{font-size:1rem}

.page-envio-detalle .env-paso-item--hecho .env-paso-marker{

    background:linear-gradient(135deg,#d1fae5,#a7f3d0);color:#047857;

}

.page-envio-detalle .env-paso-item--hecho .env-paso-marker-num{background:#059669}

.page-envio-detalle .env-paso-item--activo .env-paso-marker{

    background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:#1d4ed8;

    box-shadow:0 0 0 4px rgba(37,99,235,.12);

}

.page-envio-detalle .env-paso-item--activo .env-paso-marker-num{background:#2563eb}

.page-envio-detalle .env-paso-titulo{font-weight:700;color:#0f172a;font-size:.92rem}

.page-envio-detalle .env-paso-desc{font-size:.8rem;color:#64748b;line-height:1.45;margin:0}

.page-envio-detalle .env-paso-fecha{font-size:.75rem;color:#94a3b8}

.page-envio-detalle .env-paso-fecha--activo{color:#2563eb;font-weight:600}

.page-envio-detalle .env-decision-agricola{

    border:2px solid #6ee7b7;border-radius:12px;padding:1rem;

    background:linear-gradient(180deg,#f0fdf4,#fff);

}

.page-envio-detalle .env-decision-head{

    display:flex;gap:.75rem;align-items:flex-start;margin-bottom:.85rem;

}

.page-envio-detalle .env-decision-icon{

    width:38px;height:38px;border-radius:10px;flex-shrink:0;

    background:#059669;color:#fff;display:flex;align-items:center;justify-content:center;

}

.page-envio-detalle .env-paso-mini{

    display:flex;gap:.55rem;align-items:flex-start;font-size:.8rem;color:#475569;

    padding:.45rem .55rem;background:#f8fafc;border-radius:8px;

}

.page-envio-detalle .env-paso-num{

    flex-shrink:0;width:20px;height:20px;border-radius:50%;background:#059669;color:#fff;

    font-size:.68rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;

}

.page-envio-detalle .env-paso-num--blue{background:#2563eb}

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

                    <div class="text-muted small mt-1">

                        {{ $pedido->detalles->first()?->cultivo_personalizado ?? 'Producto' }}

                        · {{ number_format($pedido->detalles->sum('cantidad'), 2) }} kg

                        @if($trayectoPartes['destino'] ?? null)

                            · Destino: <strong>{{ $trayectoPartes['destino'] }}</strong>

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

            <div class="row">

                <div class="col-lg-8 mb-3">

                    <div class="card env-det-card env-det-card--accent mb-3">

                        <div class="card-header">

                            <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-truck text-success mr-2"></i>Logística</h3>

                        </div>

                        <div class="card-body env-det-grid">

                            <div class="row">

                                <div class="col-md-6">

                                    <div class="env-det-label">Chofer</div>

                                    <div class="env-det-value">

                                        {{ trim(($asignacion->transportista?->nombre ?? '').' '.($asignacion->transportista?->apellido ?? '')) ?: 'Sin asignar' }}

                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="env-det-label">Vehículo</div>

                                    <div class="env-det-value">{{ $asignacion->vehiculo_ref ?? '—' }}</div>

                                </div>

                                <div class="col-md-6">

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

                                <div class="col-md-6">

                                    <div class="env-det-label">Costo del servicio</div>

                                    <div class="env-det-value env-det-value--costo">

                                        {{ \App\Support\TransporteIngresoCatalogo::formatearCosto($asignacion->costo_bs !== null ? (float) $asignacion->costo_bs : null) }}

                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="env-det-label">Asignado por</div>

                                    <div class="env-det-value">

                                        {{ trim(($asignacion->asignadoPor?->nombre ?? '').' '.($asignacion->asignadoPor?->apellido ?? '')) ?: '—' }}

                                    </div>

                                </div>

                                <div class="col-md-6">

                                    <div class="env-det-label">Fecha de asignación</div>

                                    <div class="env-det-value">{{ optional($asignacion->fecha_asignacion)->format('d/m/Y H:i') ?? '—' }}</div>

                                </div>

                            </div>

                        </div>

                    </div>



                    @if($pedido)

                    <div class="card env-det-card">

                        <div class="card-header">

                            <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-shopping-cart text-success mr-2"></i>Pedido vinculado</h3>

                        </div>

                        <div class="card-body env-det-grid">

                            <div class="row">

                                <div class="col-md-6">

                                    <div class="env-det-label">Solicitud</div>

                                    <div class="env-det-value">{{ $pedido->numero_solicitud }}</div>

                                </div>

                                <div class="col-md-6">

                                    <div class="env-det-label">Planta destino</div>

                                    <div class="env-det-value">{{ $trayectoPartes['destino'] ?? ($pedido->nombre_planta ?: ($pedido->direccion_texto ?: '—')) }}</div>

                                </div>

                                <div class="col-md-6">

                                    <div class="env-det-label">Producto / cultivo</div>

                                    <div class="env-det-value">{{ $pedido->detalles->first()?->cultivo_personalizado ?? '—' }}</div>

                                </div>

                                <div class="col-md-6">

                                    <div class="env-det-label">Cantidad total</div>

                                    <div class="env-det-value">{{ number_format($pedido->detalles->sum('cantidad'), 2) }} kg</div>

                                </div>

                            </div>

                            <a href="{{ route('pedidos.show', $pedido) }}" class="btn btn-outline-success btn-sm mt-1">

                                <i class="fas fa-external-link-alt mr-1"></i>Ver pedido completo

                            </a>

                        </div>

                    </div>

                    @endif

                </div>



                <div class="col-lg-4 mb-3">

                    <div class="card env-det-card env-det-card--info mb-3">

                        <div class="card-header d-flex justify-content-between align-items-center">

                            <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-route text-primary mr-2"></i>Recorrido</h3>

                            @if(count($paradasMapa ?? []) >= 1)

                            <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#modalMapaRecorrido">

                                <i class="fas fa-map-marked-alt mr-1"></i>Mapa

                            </button>

                            @endif

                        </div>

                        <div class="card-body">

                            @include('logistica.partials.recorrido-envio-pasos', [

                                'asignacion' => $asignacion,

                                'llegoDestino' => $llegoDestino,

                                'pendienteAgricola' => $pendienteAgricola ?? false,

                            ])

                        </div>

                    </div>



                    @if($llegoDestino)

                    <div class="card env-det-card border-success">

                        <div class="card-header bg-success text-white">

                            <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-warehouse mr-2"></i>Llegada a destino</h3>

                        </div>

                        <div class="card-body">

                            <div class="mb-3">

                                <div class="env-det-label">Hora de llegada</div>

                                <div class="env-det-value text-success">{{ $asignacion->fecha_recepcion_planta?->format('d/m/Y H:i') ?? '—' }}</div>

                            </div>

                            <div class="mb-3">

                                <div class="env-det-label">Confirmado por</div>

                                <div class="env-det-value">

                                    {{ trim(($asignacion->recepcionConfirmadaPor?->nombre ?? '').' '.($asignacion->recepcionConfirmadaPor?->apellido ?? '')) ?: '—' }}

                                </div>

                            </div>

                            <div class="mb-0">

                                <div class="env-det-label">Almacén de recepción</div>

                                <div class="env-det-value">{{ $asignacion->almacen?->nombre ?? '—' }}</div>

                            </div>

                        </div>

                    </div>

                    @else

                    @php

                        $esMiAsignacion = (int) auth()->id() === (int) ($asignacion->transportista_usuarioid ?? 0);

                        $conAccionesOperativas = in_array($asignacion->estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true)

                            && ! \App\Support\SimulacionRutaCatalogo::simulacionActivaAgricola($asignacion);

                        if (in_array($asignacion->estado, ['asignado', 'asignada', 'pendiente', 'creada'], true)) {

                            $conAccionesOperativas = \App\Support\SimulacionRutaCatalogo::usuarioPuedeEmpezarAgricola(auth()->user(), $asignacion);

                        }

                        $hayAccionAgricola = ($pendienteAgricola ?? false) && ($puedeAceptarAgricola ?? false);

                        $hayAccionTransportista = \App\Support\SimulacionRutaCatalogo::usuarioPuedeEmpezarAgricola(auth()->user(), $asignacion)

                            || \App\Support\SimulacionRutaCatalogo::simulacionActivaAgricola($asignacion);

                        $hayAccionLlegada = in_array($asignacion->estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true)

                            && ! \App\Support\SimulacionRutaCatalogo::simulacionActivaAgricola($asignacion)

                            && (auth()->user()?->can('asignaciones.update') || $esMiAsignacion);

                    @endphp

                    <div class="card env-det-card env-det-card--acciones">

                        <div class="card-header">

                            <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-bolt text-warning mr-2"></i>Acciones</h3>

                        </div>

                        <div class="card-body">

                            @if($hayAccionAgricola)

                                @include('logistica.partials.accion-decision-agricola', [

                                    'asignacion' => $asignacion,

                                    'erroresStock' => $erroresStock ?? [],

                                    'puedeAceptarAgricola' => $puedeAceptarAgricola,

                                ])

                            @endif



                            @if(in_array($asignacion->estado, ['asignado', 'asignada', 'pendiente', 'creada'], true))

                                @include('logistica.partials.accion-empezar-ruta', ['asignacion' => $asignacion, 'bloque' => true])

                            @elseif(in_array($asignacion->estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true)

                                && ! \App\Support\SimulacionRutaCatalogo::simulacionActivaAgricola($asignacion))

                                <div class="{{ $hayAccionAgricola ? 'border-top pt-3 mt-1' : '' }}">

                                    @include('logistica.partials.accion-llegada-destino', ['asignacion' => $asignacion])

                                </div>

                            @endif



                            @include('logistica.partials.tarjeta-ingreso-transportista', [

                                'asignacion' => $asignacion,

                                'conBordeSuperior' => $conAccionesOperativas || $hayAccionAgricola,

                            ])



                            @if(! $hayAccionAgricola && ! $hayAccionTransportista && ! $hayAccionLlegada)

                                <div class="env-accion-info {{ ($conAccionesOperativas || $hayAccionAgricola) ? 'border-top pt-3 mt-3' : '' }}">

                                    @if($pendienteAgricola ?? false)

                                        <i class="fas fa-hourglass-half mr-1"></i>

                                        Esperando que <strong>producción agrícola</strong> acepte y reserve el stock del almacén.

                                    @elseif(in_array($asignacion->estado, ['asignado', 'asignada', 'pendiente', 'creada'], true))

                                        <i class="fas fa-truck-loading mr-1"></i>

                                        Pedido listo. El transportista debe pulsar <strong>Empezar ruta</strong> cuando salga cargado hacia planta.

                                    @elseif(\App\Support\SimulacionRutaCatalogo::simulacionActivaAgricola($asignacion))

                                        <i class="fas fa-route mr-1"></i>

                                        Ruta en curso. El progreso se actualiza automáticamente hasta completar el trayecto.

                                    @else

                                        <i class="fas fa-info-circle mr-1"></i>

                                        No hay acciones pendientes en este momento.

                                    @endif

                                </div>

                            @endif

                        </div>

                    </div>

                    @endif

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

