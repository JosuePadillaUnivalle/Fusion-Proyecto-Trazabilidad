@extends('layouts.app')

@section('title', 'Detalle envío '.$asignacion->externo_envio_id.' | AgroFusion')
@section('page_title', 'Detalle del envío')

@push('styles')
@include('logistica.partials.ayuda-logistica-styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
.env-det-card{border:0;border-radius:14px;box-shadow:0 4px 18px rgba(18,38,63,.08)}
.env-det-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b;font-weight:600}
.env-det-value{font-size:1rem;font-weight:600;color:#1e293b}
.env-timeline{list-style:none;padding:0;margin:0}
.env-timeline li{position:relative;padding:.65rem 0 .65rem 1.75rem;border-left:2px solid #e2e8f0;margin-left:.55rem}
.env-timeline li:last-child{border-left-color:transparent}
.env-timeline li::before{content:'';position:absolute;left:-7px;top:1rem;width:12px;height:12px;border-radius:50%;background:#cbd5e1}
.env-timeline li.hecho::before{background:#059669}
.env-timeline li.activo::before{background:#0284c7;box-shadow:0 0 0 4px rgba(2,132,199,.2)}
#mapaRecorridoEnvio{
    height:420px;
    width:100%;
    min-height:420px;
    border-radius:10px;
    border:1px solid #dee2e6;
    background:#e8eef4;
    z-index:1;
}
#mapaRecorridoEnvio.leaflet-container{font-family:inherit}
.leaflet-div-icon.ruta-parada-marker{
    width:auto!important;
    height:auto!important;
    margin:0!important;
    padding:0!important;
    background:transparent!important;
    border:none!important;
}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <p class="text-muted mb-0">
                Código <strong>{{ $asignacion->externo_envio_id }}</strong>
                @if($llegoDestino)
                    · <span class="text-success">Recibido en planta</span>
                @endif
            </p>
        </div>
        <div class="d-flex flex-wrap" style="gap:.5rem;">
            <a href="{{ route('logistica.asignaciones.listado') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i>Volver al listado
            </a>
            @if($puedeGestionar)
                @can('asignaciones.update')
                <a href="{{ route('logistica.asignaciones.edit', $asignacion) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit mr-1"></i>Editar
                </a>
                @endcan
            @endif
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 mb-3">
                <div class="card env-det-card mb-3">
                    <div class="card-header bg-white">
                        <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-truck text-success mr-2"></i>Logística</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="env-det-label">Chofer</div>
                                <div class="env-det-value">
                                    {{ trim(($asignacion->transportista?->nombre ?? '').' '.($asignacion->transportista?->apellido ?? '')) ?: 'Sin asignar' }}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="env-det-label">Vehículo</div>
                                <div class="env-det-value">{{ $asignacion->vehiculo_ref ?? '—' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
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
                            <div class="col-md-6 mb-3">
                                <div class="env-det-label">Situación</div>
                                <div>@include('logistica.partials.etiqueta-estado', ['estado' => $asignacion->estado, 'clase' => 'badge-warning'])</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="env-det-label">Asignado por</div>
                                <div class="env-det-value">
                                    {{ trim(($asignacion->asignadoPor?->nombre ?? '').' '.($asignacion->asignadoPor?->apellido ?? '')) ?: '—' }}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="env-det-label">Fecha de asignación</div>
                                <div class="env-det-value">{{ optional($asignacion->fecha_asignacion)->format('d/m/Y H:i') ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                @if($asignacion->pedido)
                <div class="card env-det-card">
                    <div class="card-header bg-white">
                        <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-shopping-cart text-success mr-2"></i>Pedido vinculado</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="env-det-label">Solicitud</div>
                                <div class="env-det-value">{{ $asignacion->pedido->numero_solicitud }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="env-det-label">Planta destino</div>
                                <div class="env-det-value">{{ $trayectoPartes['destino'] ?? ($asignacion->pedido->nombre_planta ?: ($asignacion->pedido->direccion_texto ?: '—')) }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="env-det-label">Producto / cultivo</div>
                                <div class="env-det-value">{{ $asignacion->pedido->detalles->first()?->cultivo_personalizado ?? '—' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="env-det-label">Cantidad total</div>
                                <div class="env-det-value">{{ number_format($asignacion->pedido->detalles->sum('cantidad'), 2) }} kg</div>
                            </div>
                        </div>
                        <a href="{{ route('pedidos.show', $asignacion->pedido) }}" class="btn btn-outline-success btn-sm">Ver pedido completo</a>
                    </div>
                </div>
                @endif
            </div>

            <div class="col-lg-4 mb-3">
                <div class="card env-det-card mb-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-route text-info mr-2"></i>Recorrido</h3>
                        @if(count($paradasMapa ?? []) >= 1)
                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#modalMapaRecorrido">
                            <i class="fas fa-map-marked-alt mr-1"></i>Ver en mapa
                        </button>
                        @endif
                    </div>
                    <div class="card-body">
                        @php
                            $pasoAsignado = in_array($asignacion->estado, ['asignado', 'asignada', 'pendiente', 'creada'], true) || $asignacion->fecha_asignacion;
                            $pasoCamino = in_array($asignacion->estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true) || $llegoDestino;
                            $pasoRecibido = $llegoDestino;
                        @endphp
                        <ul class="env-timeline">
                            <li class="{{ $pasoAsignado ? 'hecho' : '' }}">
                                <strong>Asignado</strong>
                                @if($asignacion->fecha_asignacion)
                                    <br><small class="text-muted">{{ $asignacion->fecha_asignacion->format('d/m/Y H:i') }}</small>
                                @endif
                            </li>
                            <li class="{{ $pasoCamino ? ($pasoRecibido ? 'hecho' : 'activo') : '' }}">
                                <strong>En transporte hacia planta</strong>
                            </li>
                            <li class="{{ $pasoRecibido ? 'hecho' : '' }}">
                                <strong>Recibido en planta</strong>
                                @if($asignacion->fecha_recepcion_planta)
                                    <br><small class="text-muted">{{ $asignacion->fecha_recepcion_planta->format('d/m/Y H:i') }}</small>
                                @endif
                            </li>
                        </ul>
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
                @elseif($puedeGestionar)
                <div class="card env-det-card">
                    <div class="card-header bg-white">
                        <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-bolt text-warning mr-2"></i>Acciones</h3>
                    </div>
                    <div class="card-body">
                        @if(in_array($asignacion->estado, ['asignado', 'asignada', 'pendiente', 'creada'], true))
                            @include('logistica.partials.accion-iniciar-transporte', ['asignacion' => $asignacion])
                        @elseif(in_array($asignacion->estado, ['en_transporte_planta', 'en_ruta', 'en_transito'], true))
                            @include('logistica.partials.accion-llegada-destino', ['asignacion' => $asignacion])
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

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
