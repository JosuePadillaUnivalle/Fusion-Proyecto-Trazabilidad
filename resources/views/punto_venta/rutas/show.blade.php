@extends('layouts.app')

@section('title', $ruta->codigo.' | AgroFusion')
@section('page_title', 'Ruta de distribución')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@include('logistica.partials.mapa-ruta-styles')
<style>
.ruta-show-compact { --ruta-gap: .75rem; }
.ruta-hero {
    border-radius: 12px;
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 45%, #fff 100%);
    border: 1px solid #bbf7d0;
    padding: .85rem 1.1rem;
    margin-bottom: var(--ruta-gap);
}
.ruta-hero-codigo { font-size: 1.15rem; font-weight: 800; color: #14532d; letter-spacing: .02em; }
.ruta-dist-card { border: 0; border-radius: 12px; box-shadow: 0 2px 12px rgba(18,38,63,.06); margin-bottom: var(--ruta-gap); }
.ruta-dist-card .card-header { padding: .65rem 1rem .35rem; }
.ruta-dist-card .card-body { padding: .5rem 1rem .85rem; }
.ruta-dist-label { font-size: .68rem; text-transform: uppercase; letter-spacing: .05em; color: #64748b; font-weight: 600; }
.ruta-dist-value { font-size: .9rem; font-weight: 600; color: #1e293b; line-height: 1.3; }
.ruta-timeline { list-style: none; padding: 0; margin: 0; }
.ruta-timeline li {
    display: flex; gap: .65rem; padding: .55rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.ruta-timeline li:last-child { border-bottom: 0; }
.ruta-timeline-icon {
    width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; color: #fff; font-size: .8rem;
}
.ruta-timeline-icon--carga { background: linear-gradient(135deg, #16a34a, #22c55e); }
.ruta-timeline-icon--entrega { background: linear-gradient(135deg, #dc2626, #ef4444); }
.ruta-log-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .5rem .75rem;
}
.ruta-log-item {
    display: flex; align-items: flex-start; gap: .55rem;
    padding: .45rem 0;
}
.ruta-log-item--wide { grid-column: 1 / -1; }
.ruta-log-icon {
    width: 28px; height: 28px; border-radius: 7px; background: #eff6ff; color: #2563eb;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .75rem;
}
.ruta-pedidos-table th, .ruta-pedidos-table td { padding: .45rem .65rem; font-size: .82rem; vertical-align: middle; }
#mapaRutaDistShow { height: 320px; width: 100%; min-height: 320px; border-radius: 10px; border: 1px solid #dee2e6; background: #e8eef4; }
@media (max-width: 991px) {
    .ruta-log-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
@php
    use App\Support\RutaDistribucionNavegacion;
    use App\Support\RutaDistribucionCatalogo;
    $puedeOperarRuta = \App\Support\UsuarioRol::puedeMarcarEnRutaDistribucion(auth()->user(), $ruta);
    $esTrasladoPlanta = $ruta->esTrasladoPlantaMayorista();
    $esTransportista = \App\Support\UsuarioRol::esTransportista(auth()->user());
    $fechaSalida = RutaDistribucionNavegacion::fechaSalida($ruta);
    $etiquetaSalida = RutaDistribucionNavegacion::etiquetaSalida($ruta);
    $estadoOperativo = RutaDistribucionCatalogo::etiquetaOperativa($ruta);
    $numeroRecogida = 0;
@endphp
<div class="ruta-show-compact">
<div class="ruta-hero d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="ruta-hero-codigo mb-0">{{ $ruta->codigo }}</div>
        <span class="badge badge-{{ $badge['clase'] }} px-2 py-1">{{ $badge['etiqueta'] }}</span>
        @if($trayectoPartes)
        <span class="small text-muted">@include('punto_venta.partials.trayecto-distribucion-colores', ['trayectoPartes' => $trayectoPartes])</span>
        @endif
    </div>
    <a href="{{ route('punto-venta.rutas.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i>Volver al listado
    </a>
</div>

<section class="content px-0">
    <div class="container-fluid px-0">
        <div class="row">
            <div class="col-lg-7 mb-2">
                <div class="card ruta-dist-card mb-2">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center border-0">
                        <h3 class="card-title font-weight-bold mb-0" style="font-size:1rem"><i class="fas fa-route text-success mr-1"></i>Recorrido y pedidos</h3>
                        @if(! $esTrasladoPlanta && ! $esTransportista && !empty($urlTiempoReal))
                        <a href="{{ $urlTiempoReal }}" class="btn btn-sm btn-primary py-0">
                            <i class="fas fa-satellite-dish mr-1"></i>Tiempo real
                        </a>
                        @elseif(! $esTransportista && count($paradasMapa) >= 1)
                        <button type="button" class="btn btn-sm btn-outline-primary py-0" data-toggle="modal" data-target="#modalMapaRutaDist">
                            <i class="fas fa-map-marked-alt mr-1"></i>Mapa
                        </button>
                        @endif
                    </div>
                    <div class="card-body pt-0">
                        <ul class="ruta-timeline mb-2">
                            @foreach($ruta->paradas as $parada)
                            <li>
                                @if(in_array($parada->tipo, ['carga_mayorista', 'carga_planta'], true))
                                @php
                                    $numeroRecogida++;
                                    $presentacion = RutaDistribucionNavegacion::presentacionParadaCarga($parada, $numeroRecogida);
                                @endphp
                                <span class="ruta-timeline-icon ruta-timeline-icon--carga"><i class="fas fa-warehouse"></i></span>
                                <div>
                                    <div class="font-weight-bold text-success">{{ $presentacion['titulo'] }}</div>
                                    <div class="small text-muted">{{ $presentacion['subtitulo'] }}</div>
                                    <span class="badge badge-light border text-muted small mt-1">{{ $presentacion['badge'] }}</span>
                                </div>
                                @else
                                @php $presentacion = RutaDistribucionNavegacion::presentacionParadaEntrega($parada); @endphp
                                <span class="ruta-timeline-icon ruta-timeline-icon--entrega"><i class="fas fa-store"></i></span>
                                <div>
                                    <div class="font-weight-bold text-danger">{{ $presentacion['titulo'] }}</div>
                                    @if($presentacion['subtitulo'])
                                    <span class="text-muted small">{{ $presentacion['subtitulo'] }}</span>
                                    @endif
                                    @if($presentacion['badge'])
                                    <span class="badge badge-light border text-danger small mt-1">{{ $presentacion['badge'] }}</span>
                                    @endif
                                </div>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm table-hover mb-0 ruta-pedidos-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Solicitud</th>
                                        <th>Punto de venta</th>
                                        <th>Producto</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ruta->pedidos as $pedido)
                                    @php $det = $pedido->detalles->first(); $pb = \App\Support\PedidoDistribucionCatalogo::badgeEstado($pedido); @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('punto-venta.pedidos.show', $pedido) }}" class="font-weight-bold">{{ $pedido->numero_solicitud }}</a>
                                        </td>
                                        <td>{{ $pedido->puntoVenta?->nombre ?? '—' }}</td>
                                        <td>{{ $det?->producto_nombre }} <span class="text-muted">({{ $det ? number_format((float) $det->cantidad, 2) : '—' }} kg)</span></td>
                                        <td><span class="badge badge-{{ \App\Support\PedidoDistribucionCatalogo::badgeBootstrapClase($pb) }}">{{ $pb['etiqueta'] }}</span></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No hay pedidos vinculados a esta ruta.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 mb-2">
                <div class="card ruta-dist-card">
                    <div class="card-header bg-white border-0">
                        <h3 class="card-title font-weight-bold mb-0" style="font-size:1rem"><i class="fas fa-truck text-info mr-1"></i>Logística</h3>
                    </div>
                    <div class="card-body pt-0">
                        @if($estadoOperativo)
                        <div class="alert alert-warning py-2 px-3 small mb-3 mb-md-2">
                            <i class="fas fa-hourglass-half mr-1"></i>
                            <strong>{{ $estadoOperativo }}</strong>
                        </div>
                        @endif
                        <div class="ruta-log-grid">
                        <div class="ruta-log-item">
                            <span class="ruta-log-icon"><i class="fas fa-id-card"></i></span>
                            <div>
                                <div class="ruta-dist-label">Chofer</div>
                                <div class="ruta-dist-value">{{ trim(($ruta->transportista?->nombre ?? '').' '.($ruta->transportista?->apellido ?? '')) }}</div>
                            </div>
                        </div>
                        <div class="ruta-log-item">
                            <span class="ruta-log-icon"><i class="fas fa-truck"></i></span>
                            <div>
                                <div class="ruta-dist-label">Vehículo</div>
                                <div class="ruta-dist-value">{{ $ruta->vehiculo?->placa ?? ($ruta->transportista?->perfilTransportista?->vehiculo?->placa ?? '—') }}</div>
                            </div>
                        </div>
                        <div class="ruta-log-item">
                            <span class="ruta-log-icon" style="background:#ecfdf5;color:#059669"><i class="fas fa-coins"></i></span>
                            <div>
                                <div class="ruta-dist-label">Costo del servicio</div>
                                <div class="ruta-dist-value text-success font-weight-bold">
                                    {{ \App\Support\TransporteIngresoCatalogo::formatearCosto($ruta->costo_bs !== null ? (float) $ruta->costo_bs : null) }}
                                </div>
                            </div>
                        </div>
                        <div class="ruta-log-item ruta-log-item--wide">
                            <span class="ruta-log-icon" style="background:#fef2f2;color:#dc2626"><i class="fas fa-industry"></i></span>
                            <div>
                                <div class="ruta-dist-label">Almacén de carga</div>
                                <div class="ruta-dist-value">{{ RutaDistribucionNavegacion::nombreAlmacenCarga($ruta) }}</div>
                            </div>
                        </div>
                        <div class="ruta-log-item">
                            <span class="ruta-log-icon"><i class="fas fa-calendar-alt"></i></span>
                            <div>
                                <div class="ruta-dist-label">Salida</div>
                                <div class="ruta-dist-value {{ $fechaSalida ? '' : 'text-muted' }}">{{ $etiquetaSalida }}</div>
                            </div>
                        </div>
                        <div class="ruta-log-item">
                            <span class="ruta-log-icon"><i class="fas fa-user-check"></i></span>
                            <div>
                                <div class="ruta-dist-label">Planificada por</div>
                                <div class="ruta-dist-value">{{ trim(($ruta->creadoPor?->nombre ?? '').' '.($ruta->creadoPor?->apellido ?? '')) ?: '—' }}</div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>

                @if($puedeOperarRuta)
                <div class="card ruta-dist-card">
                    <div class="card-header bg-white border-0 py-2">
                        <h3 class="card-title font-weight-bold mb-0" style="font-size:1rem">
                            <i class="fas fa-play-circle text-success mr-1"></i>
                            {{ \App\Support\UsuarioRol::esAdminGlobal(auth()->user()) ? 'Iniciar ruta' : 'Su ruta' }}
                        </h3>
                    </div>
                    <div class="card-body pt-0 pb-3">
                        @if($esTrasladoPlanta)
                            @include('logistica.partials.accion-empezar-ruta-cierre-traslado', ['ruta' => $ruta, 'bloque' => true])
                        @else
                            @include('logistica.partials.accion-empezar-ruta-distribucion', ['ruta' => $ruta])
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
</div>

@if(count($paradasMapa) >= 1)
<div class="modal fade" id="modalMapaRutaDist" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:14px;">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-route text-success mr-2"></i>{{ $ruta->codigo }}</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-2">
                @if($trayectoPartes)
                <p class="small mb-2 px-1">@include('punto_venta.partials.trayecto-distribucion-colores', ['trayectoPartes' => $trayectoPartes])</p>
                @endif
                <div id="mapaRutaDistShow"></div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@if(count($paradasMapa) >= 1)
@push('scripts')
@include('logistica.partials.mapa-ruta-libs')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const paradas = @json($paradasMapa);
    const modal = document.getElementById('modalMapaRutaDist');
    if (!modal || !paradas.length || !window.L) return;

    let mapa = null;
    let capas = null;

    async function dibujar() {
        const el = document.getElementById('mapaRutaDistShow');
        if (!el) return;

        if (!mapa) {
            mapa = L.map(el).setView([paradas[0].lat, paradas[0].lng], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19, attribution: '© OpenStreetMap',
            }).addTo(mapa);
            capas = L.layerGroup().addTo(mapa);
        } else {
            capas.clearLayers();
            mapa.invalidateSize();
        }

        let routeResult = null;
        if (paradas.length >= 2 && window.RutaPorCalles) {
            routeResult = await RutaPorCalles.fetchRoute(paradas);
        }
        if (window.RutaPorCalles) {
            RutaPorCalles.drawOnMap(mapa, capas, paradas, routeResult);
        }
        setTimeout(function () { mapa.invalidateSize(); }, 200);
    }

    if (window.jQuery) {
        window.jQuery(modal).on('shown.bs.modal', function () { setTimeout(dibujar, 200); });
    }
});
</script>
@endpush
@endif
