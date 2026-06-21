@extends('layouts.app')

@section('title', $ruta->codigo.' | AgroFusion')
@section('page_title', 'Ruta de distribución')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@include('logistica.partials.mapa-ruta-styles')
<style>
.ruta-hero {
    border-radius: 16px;
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 45%, #fff 100%);
    border: 1px solid #bbf7d0;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
}
.ruta-hero-codigo { font-size: 1.35rem; font-weight: 800; color: #14532d; letter-spacing: .02em; }
.ruta-dist-card { border: 0; border-radius: 14px; box-shadow: 0 4px 18px rgba(18,38,63,.08); margin-bottom: 1.25rem; }
.ruta-dist-label { font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: #64748b; font-weight: 600; }
.ruta-dist-value { font-size: 1rem; font-weight: 600; color: #1e293b; }
.ruta-timeline { list-style: none; padding: 0; margin: 0; }
.ruta-timeline li {
    display: flex; gap: .85rem; padding: .85rem 0;
    border-bottom: 1px solid #f1f5f9; position: relative;
}
.ruta-timeline li:last-child { border-bottom: 0; }
.ruta-timeline-icon {
    width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; color: #fff; font-size: .9rem;
}
.ruta-timeline-icon--carga { background: linear-gradient(135deg, #16a34a, #22c55e); }
.ruta-timeline-icon--entrega { background: linear-gradient(135deg, #dc2626, #ef4444); }
.ruta-log-item {
    display: flex; align-items: flex-start; gap: .75rem;
    padding: .75rem 0; border-bottom: 1px solid #f1f5f9;
}
.ruta-log-item:last-child { border-bottom: 0; }
.ruta-log-icon {
    width: 32px; height: 32px; border-radius: 8px; background: #eff6ff; color: #2563eb;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
#mapaRutaDistShow { height: 400px; width: 100%; min-height: 400px; border-radius: 10px; border: 1px solid #dee2e6; background: #e8eef4; }
</style>
@endpush

@section('content')
<div class="ruta-hero d-flex flex-wrap justify-content-between align-items-start gap-3">
    <div>
        <div class="ruta-hero-codigo mb-1">{{ $ruta->codigo }}</div>
        @if($trayectoPartes)
        <p class="mb-2">@include('punto_venta.partials.trayecto-distribucion-colores', ['trayectoPartes' => $trayectoPartes])</p>
        @endif
        <span class="badge badge-{{ $badge['clase'] }} px-3 py-2" style="font-size:.85rem">{{ $badge['etiqueta'] }}</span>
    </div>
    <a href="{{ route('punto-venta.rutas.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left mr-1"></i>Volver al listado
    </a>
</div>

<section class="content px-0">
    <div class="container-fluid px-0">
        <div class="row">
            <div class="col-lg-8 mb-3">
                <div class="card ruta-dist-card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center border-0 pt-3">
                        <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-route text-success mr-2"></i>Recorrido</h3>
                        @if(!empty($urlTiempoReal))
                        <a href="{{ $urlTiempoReal }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-satellite-dish mr-1"></i>Ver en tiempo real
                        </a>
                        @elseif(count($paradasMapa) >= 1)
                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#modalMapaRutaDist">
                            <i class="fas fa-map-marked-alt mr-1"></i>Ver en mapa
                        </button>
                        @endif
                    </div>
                    <div class="card-body pt-0">
                        <ul class="ruta-timeline">
                            @foreach($ruta->paradas as $parada)
                            <li>
                                @if(in_array($parada->tipo, ['carga_mayorista', 'carga_planta'], true))
                                <span class="ruta-timeline-icon ruta-timeline-icon--carga"><i class="fas fa-warehouse"></i></span>
                                <div>
                                    <div class="font-weight-bold text-warning">{{ str_replace('Carga: ', '', $parada->destino) }}</div>
                                    <span class="badge badge-light border text-warning small">Carga en mayorista</span>
                                </div>
                                @else
                                <span class="ruta-timeline-icon ruta-timeline-icon--entrega"><i class="fas fa-store"></i></span>
                                <div>
                                    <div class="font-weight-bold text-danger">{{ str_replace('Entrega: ', '', $parada->destino) }}</div>
                                    @if($parada->pedido)
                                    <span class="text-muted small">{{ $parada->pedido->numero_solicitud }}</span>
                                    @endif
                                </div>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="card ruta-dist-card">
                    <div class="card-header bg-white border-0 pt-3">
                        <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-boxes text-primary mr-2"></i>Pedidos en esta ruta</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Solicitud</th>
                                        <th>Punto de venta</th>
                                        <th>Producto</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ruta->pedidos as $pedido)
                                    @php $det = $pedido->detalles->first(); $pb = \App\Support\PedidoDistribucionCatalogo::badgeEstado($pedido); @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('punto-venta.pedidos.show', $pedido) }}" class="font-weight-bold">{{ $pedido->numero_solicitud }}</a>
                                        </td>
                                        <td>{{ $pedido->puntoVenta?->nombre }}</td>
                                        <td>{{ $det?->producto_nombre }} <span class="text-muted">({{ $det ? number_format((float) $det->cantidad, 2) : '—' }} kg)</span></td>
                                        <td><span class="badge badge-{{ $pb['clase'] }}">{{ $pb['etiqueta'] }}</span></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="card ruta-dist-card">
                    <div class="card-header bg-white border-0 pt-3">
                        <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-truck text-info mr-2"></i>Logística</h3>
                    </div>
                    <div class="card-body pt-0">
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
                        <div class="ruta-log-item">
                            <span class="ruta-log-icon" style="background:#fef2f2;color:#dc2626"><i class="fas fa-industry"></i></span>
                            <div>
                                <div class="ruta-dist-label">Almacén de carga</div>
                                <div class="ruta-dist-value text-success">{{ $ruta->almacenOrigen?->nombre ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="ruta-log-item">
                            <span class="ruta-log-icon"><i class="fas fa-calendar-alt"></i></span>
                            <div>
                                <div class="ruta-dist-label">Salida</div>
                                <div class="ruta-dist-value">{{ optional($ruta->fecha_salida)->format('d/m/Y H:i') ?? '—' }}</div>
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

                @if((int) auth()->id() === (int) ($ruta->transportista_usuarioid ?? 0))
                <div class="card ruta-dist-card mt-3">
                    <div class="card-header bg-white border-0 pt-3">
                        <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-play-circle text-success mr-2"></i>Su ruta</h3>
                    </div>
                    <div class="card-body pt-0">
                        @include('logistica.partials.accion-empezar-ruta-distribucion', ['ruta' => $ruta])
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>

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
