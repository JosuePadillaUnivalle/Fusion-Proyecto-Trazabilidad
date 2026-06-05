@extends('layouts.app')

@section('title', 'Pedido #'.$pedido->pedidoid.' | AgroFusion')
@section('page_title', 'Detalle del pedido')

@push('styles')
@include('partials.logistica-modulo-styles')
@include('logistica.partials.mapa-ruta-styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
.pedido-show-wrap { padding: 0 .25rem; }
.pedido-main-card, .pedido-side-card {
    border: 0;
    border-radius: 14px;
    box-shadow: 0 6px 20px rgba(18,38,63,.08);
    overflow: hidden;
    margin-bottom: 1.25rem;
}
.pedido-main-card .card-header,
.pedido-side-card .card-header {
    background: #fff;
    border-bottom: 1px solid #eef2f0;
    padding: 1.1rem 1.35rem;
}
.pedido-main-card .card-body { padding: 1.35rem; }
.pedido-side-card .card-body { padding: 1.25rem 1.35rem; }
.pedido-kpi {
    background: #f8faf9;
    border: 1px solid #e8f0ea;
    border-radius: 12px;
    padding: 1rem 1.15rem;
    height: 100%;
}
.pedido-kpi small {
    display: block;
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #6c757d;
    margin-bottom: .35rem;
}
.pedido-kpi strong { font-size: 1rem; color: #1a252f; }
.pedido-kpi i {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: .5rem;
    color: #fff;
}
.pedido-table thead th {
    background: #f2f7f3;
    border: 0;
    font-size: .76rem;
    text-transform: uppercase;
    padding: .85rem 1rem;
}
.pedido-table tbody td { padding: .9rem 1rem; vertical-align: middle; }
#map { height: 380px; width: 100%; border-radius: 0 0 14px 14px; }

/* Timeline corregido */
.pedido-timeline {
    position: relative;
    padding: .5rem 0 .5rem 2rem;
    margin: 0;
    list-style: none;
}
.pedido-timeline::before {
    content: '';
    position: absolute;
    left: 11px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: #e2e8f0;
    border-radius: 2px;
}
.pedido-timeline-item {
    position: relative;
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
}
.pedido-timeline-item:last-child { margin-bottom: 0; }
.pedido-timeline-dot {
    position: absolute;
    left: -2rem;
    top: .15rem;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: .7rem;
    z-index: 1;
}
.pedido-timeline-date {
    display: inline-block;
    background: #2c5530;
    color: #fff;
    font-size: .75rem;
    font-weight: 700;
    padding: .3rem .75rem;
    border-radius: 20px;
    margin-bottom: .75rem;
}
.pedido-timeline-box {
    background: #fff;
    border: 1px solid #e8f0ea;
    border-radius: 10px;
    padding: 1rem 1.15rem;
}
.pedido-timeline-box h6 {
    margin: 0 0 .35rem;
    font-weight: 700;
    font-size: .95rem;
}
.pedido-timeline-box .time {
    font-size: .78rem;
    color: #94a3b8;
    display: block;
    margin-bottom: .5rem;
}
</style>
@endpush

@section('content')
@php
    $itemsCount = $pedido->detalles?->count() ?? 0;
    $totalKg = $pedido->detalles?->sum('cantidad') ?? 0;
    $transportista = $pedido->envioAsignacion?->transportista;
    $logisticaEnvio = \App\Support\EnvioPedidoService::datosLogistica($pedido->envioAsignacion);
    $faseLogistica = \App\Support\PedidoCatalogo::faseLogistica($logisticaEnvio);
    $badgeEstado = match(true) {
        $faseLogistica === 'en_camino_planta' => 'info',
        $faseLogistica === 'recibido_planta' => 'success',
        $pedido->estado === 'sin asignacion', $pedido->estado === 'pendiente' => 'info',
        $pedido->estado === 'confirmado' => 'success',
        $pedido->estado === 'en produccion' => 'warning',
        default => 'danger',
    };
    $etiquetaEstadoVisible = $faseLogistica
        ? \App\Support\PedidoCatalogo::etiquetaFaseLogistica($faseLogistica)
        : \App\Support\PedidoCatalogo::etiquetaEstado($pedido->estado);
@endphp

<section class="content pedido-show-wrap">
    <div class="container-fluid px-3 px-lg-4">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
            <div>
                <h4 class="mb-1 font-weight-bold">
                    <i class="fas fa-file-invoice text-success mr-2"></i>
                    Pedido #{{ $pedido->pedidoid }}
                </h4>
                <span class="text-muted">{{ $pedido->numero_solicitud }}</span>
            </div>
            <span class="badge badge-{{ $badgeEstado }} px-3 py-2 mt-2 mt-md-0">
                {{ $etiquetaEstadoVisible }}
            </span>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card pedido-main-card">
                    <div class="card-header">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-info-circle mr-2"></i>Información del pedido</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-4 col-6 mb-3">
                                <div class="pedido-kpi">
                                    <i class="fas fa-hashtag" style="background:#495057"></i>
                                    <small>Solicitud</small>
                                    <strong>{{ $pedido->numero_solicitud }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-3">
                                <div class="pedido-kpi">
                                    <i class="fas fa-seedling" style="background:#007bff"></i>
                                    <small>Planta</small>
                                    <strong>{{ $pedido->nombre_planta ?: '—' }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-3">
                                <div class="pedido-kpi">
                                    <i class="fas fa-weight-hanging" style="background:#fd7e14"></i>
                                    <small>Total</small>
                                    <strong>{{ number_format($totalKg, 2) }} kg</strong>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-3">
                                <div class="pedido-kpi">
                                    <i class="fas fa-list-ul" style="background:#17a2b8"></i>
                                    <small>Ítems</small>
                                    <strong>{{ $itemsCount }} ítem(s)</strong>
                                </div>
                            </div>
                            <div class="col-md-4 col-6 mb-3">
                                <div class="pedido-kpi">
                                    <i class="fas fa-calendar-alt" style="background:#28a745"></i>
                                    <small>Fecha pedido</small>
                                    <strong>{{ \Carbon\Carbon::parse($pedido->fechapedido)->format('d/m/Y') }}</strong>
                                </div>
                            </div>
                            @if($transportista)
                            <div class="col-md-4 col-6 mb-3">
                                <div class="pedido-kpi">
                                    <i class="fas fa-truck" style="background:#6f42c1"></i>
                                    <small>Transportista</small>
                                    <strong>{{ trim($transportista->nombre.' '.$transportista->apellido) }}</strong>
                                </div>
                            </div>
                            @endif
                            @if($logisticaEnvio)
                            <div class="col-md-4 col-6 mb-3">
                                <div class="pedido-kpi">
                                    <i class="fas fa-car" style="background:#20c997"></i>
                                    <small>Vehículo</small>
                                    <strong>{{ $logisticaEnvio['vehiculo_nombre'] }}</strong>
                                    <small class="text-muted d-block mt-1">Placa: {{ $logisticaEnvio['placa'] }}</small>
                                </div>
                            </div>
                            @endif
                        </div>

                        <h6 class="font-weight-bold mb-3"><i class="fas fa-clipboard-list mr-1 text-success"></i>Detalles</h6>
                        <div class="table-responsive mb-4">
                            <table class="table pedido-table mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Producto / cultivo</th>
                                        <th>Cantidad (kg)</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pedido->detalles as $i => $det)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><span class="badge badge-secondary px-2 py-1">{{ $det->cultivo_personalizado }}</span></td>
                                        <td><strong>{{ number_format($det->cantidad, 2) }}</strong> kg</td>
                                        <td class="text-muted">{{ $det->observaciones ?? '—' }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">Sin detalles</td></tr>
                                    @endforelse
                                </tbody>
                                @if($itemsCount > 0)
                                <tfoot>
                                    <tr class="bg-light">
                                        <th colspan="2" class="text-right">Total</th>
                                        <th colspan="2">{{ number_format($totalKg, 2) }} kg</th>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block mb-1">Fecha entrega deseada</small>
                                <strong>{{ $pedido->fechaEntregaDeseada ? \Carbon\Carbon::parse($pedido->fechaEntregaDeseada)->format('d/m/Y') : 'No especificada' }}</strong>
                            </div>
                            @if($pedido->direccion_texto)
                            <div class="col-md-6 mb-2">
                                <small class="text-muted d-block mb-1">Dirección</small>
                                <strong>{{ $pedido->direccion_texto }}</strong>
                            </div>
                            @endif
                            @if($pedido->observaciones)
                            <div class="col-12 mt-2">
                                <small class="text-muted d-block mb-1">Observaciones</small>
                                <div class="p-3 rounded" style="background:#f0f9ff;border-left:4px solid #17a2b8;">{{ $pedido->observaciones }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card pedido-main-card">
                    <div class="card-header">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-map mr-2"></i>Ruta de entrega</h5>
                    </div>
                    <div class="card-body p-0">
                        <div id="map"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                @include('partials.pedido-envio-logistica', [
                    'pedido' => $pedido,
                    'cardClass' => 'pedido-side-card card',
                    'mostrarConfirmarCarga' => true,
                ])

                @can('pedidos.update')
                <div class="card pedido-side-card">
                    <div class="card-header">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-edit mr-2"></i>Actualizar estado</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('pedidos.update', $pedido) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label class="small font-weight-bold">Estado</label>
                                <select name="estado" class="form-control">
                                    @foreach(\App\Support\PedidoCatalogo::opcionesEstadoEnSelector($pedido) as $estadoOpt => $etiqueta)
                                        <option value="{{ $estadoOpt }}" @selected($pedido->estado === $estadoOpt)>
                                            {{ $etiqueta }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success btn-block py-2">
                                <i class="fas fa-save mr-1"></i> Actualizar estado
                            </button>
                        </form>
                    </div>
                </div>
                @endcan

                <div class="card pedido-side-card">
                    <div class="card-header">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-cog mr-2"></i>Acciones</h5>
                    </div>
                    <div class="card-body d-flex flex-column" style="gap:.65rem;">
                        <a href="{{ route('pedidos.index') }}" class="btn btn-outline-secondary py-2">
                            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
                        </a>
                        @can('pedidos.update')
                        <a href="{{ route('pedidos.edit', $pedido) }}" class="btn btn-warning py-2">
                            <i class="fas fa-edit mr-1"></i> Editar pedido
                        </a>
                        @endcan
                        <a href="#" class="btn btn-info py-2" onclick="window.print(); return false;">
                            <i class="fas fa-print mr-1"></i> Imprimir
                        </a>
                        @can('pedidos.delete')
                        <form action="{{ route('pedidos.destroy', $pedido) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar este pedido?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-block py-2">
                                <i class="fas fa-trash mr-1"></i> Eliminar pedido
                            </button>
                        </form>
                        @endcan
                    </div>
                </div>

                <div class="card pedido-side-card">
                    <div class="card-header">
                        <h5 class="mb-0 font-weight-bold"><i class="fas fa-history mr-2"></i>Historial</h5>
                    </div>
                    <div class="card-body">
                        <ul class="pedido-timeline">
                            <li class="pedido-timeline-item">
                                <span class="pedido-timeline-date">
                                    {{ \Carbon\Carbon::parse($pedido->fechapedido)->format('d M Y') }}
                                </span>
                                <span class="pedido-timeline-dot bg-success"><i class="fas fa-plus"></i></span>
                                <div class="pedido-timeline-box">
                                    <span class="time"><i class="far fa-clock mr-1"></i>{{ \Carbon\Carbon::parse($pedido->fechapedido)->format('H:i') }}</span>
                                    <h6>Pedido creado</h6>
                                    <p class="mb-0 text-muted small">Registrado en el sistema de trazabilidad.</p>
                                </div>
                            </li>
                            @if($pedido->fecha_aceptacion_agricola)
                            <li class="pedido-timeline-item">
                                <span class="pedido-timeline-dot bg-primary"><i class="fas fa-leaf"></i></span>
                                <div class="pedido-timeline-box">
                                    <span class="time">{{ \Carbon\Carbon::parse($pedido->fecha_aceptacion_agricola)->format('d/m/Y H:i') }}</span>
                                    <h6>Aceptado por producción agrícola</h6>
                                    @if($pedido->aceptadoPor)
                                    <p class="mb-0 text-muted small">Por {{ $pedido->aceptadoPor->nombreusuario }}</p>
                                    @endif
                                </div>
                            </li>
                            @endif
                            @if($transportista)
                            <li class="pedido-timeline-item">
                                <span class="pedido-timeline-dot bg-info"><i class="fas fa-truck"></i></span>
                                <div class="pedido-timeline-box">
                                    <h6>Transportista asignado</h6>
                                    <p class="mb-0 text-muted small">
                                        {{ trim($transportista->nombre.' '.$transportista->apellido) }}
                                        @if($logisticaEnvio)
                                            · {{ $logisticaEnvio['vehiculo_nombre'] }} ({{ $logisticaEnvio['placa'] }})
                                        @endif
                                    </p>
                                </div>
                            </li>
                            @endif
                            @if($logisticaEnvio && $logisticaEnvio['cargado_en_ruta'])
                            <li class="pedido-timeline-item">
                                <span class="pedido-timeline-dot bg-primary"><i class="fas fa-shipping-fast"></i></span>
                                <div class="pedido-timeline-box">
                                    <h6>Pedido cargado — en camino a planta</h6>
                                    <p class="mb-0 text-muted small">{{ $logisticaEnvio['estado_etiqueta'] }}</p>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('partials.modal-confirmar-accion')
@endsection

@push('scripts')
@include('logistica.partials.mapa-ruta-libs')
<script>
document.addEventListener('DOMContentLoaded', async function() {
    const lat = {{ $pedido->latitud }};
    const lng = {{ $pedido->longitud }};
    const oLat = {{ $pedido->origen_latitud ?? 'null' }};
    const oLng = {{ $pedido->origen_longitud ?? 'null' }};
    const map = L.map('map').setView([lat, lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 19
    }).addTo(map);
    const capas = L.layerGroup().addTo(map);
    if (oLat != null && oLng != null && window.RutaPorCalles) {
        const waypoints = [
            { lat: oLat, lng: oLng, orden: 1, label: @json($pedido->origen_direccion ?? 'Origen') },
            { lat: lat, lng: lng, orden: 2, label: @json($pedido->direccion_texto ?? 'Destino') },
        ];
        const routeResult = await RutaPorCalles.fetchRoute(waypoints);
        RutaPorCalles.drawOnMap(map, capas, waypoints, routeResult);
    } else {
        L.marker([lat, lng]).addTo(capas).bindPopup(@json($pedido->direccion_texto ?? 'Destino')).openPopup();
    }
    setTimeout(() => map.invalidateSize(), 150);
});
</script>
@endpush
