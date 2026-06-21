@extends('layouts.app')

@section('title', 'Pedido '.$pedido->numero_solicitud.' | AgroFusion')
@section('page_title', 'Detalle del pedido')

@push('styles')
@include('logistica.partials.mapa-ruta-styles')
<style>
.ped-show-card{border:0;border-radius:14px;box-shadow:0 4px 18px rgba(18,38,63,.08);margin-bottom:.85rem}
.ped-show-card .card-header{background:#fff;border-bottom:1px solid #eef2f0;padding:.75rem 1rem}
.ped-show-card .card-body{padding:.85rem 1rem}
.ped-show-label{font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b;font-weight:600;margin-bottom:.15rem}
.ped-show-value{font-size:.92rem;font-weight:600;color:#1e293b;line-height:1.35}
.ped-paso{display:flex;align-items:flex-start;gap:.55rem;padding:.45rem 0;position:relative}
.ped-paso+.ped-paso{border-top:1px dashed #e8edf2}
.ped-paso-icon{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:.72rem;color:#fff}
.ped-paso-icon.hecho{background:#059669}
.ped-paso-icon.activo{background:#0284c7;box-shadow:0 0 0 3px rgba(2,132,199,.15)}
.ped-paso-icon.pendiente{background:#cbd5e1;color:#64748b}
.ped-paso-titulo{font-weight:700;color:#1e293b;margin-bottom:.05rem;font-size:.88rem;line-height:1.25}
.ped-paso-det{font-size:.78rem;color:#64748b;margin:0;line-height:1.35}
.ped-tabla thead th{background:#f8fafc;border:0;font-size:.68rem;text-transform:uppercase;color:#64748b;padding:.55rem .85rem}
.ped-tabla tbody td{padding:.6rem .85rem;vertical-align:middle;font-size:.9rem}
.ped-tabla tfoot th{padding:.55rem .85rem;font-size:.88rem}
.ped-show-grid{margin-bottom:0}
.ped-enviado-scroll{
    max-height:clamp(110px,24vh,240px);
    overflow-y:auto;
    overscroll-behavior:contain;
}
.ped-enviado-scroll .ped-tabla thead th{
    position:sticky;top:0;z-index:2;
    box-shadow:0 1px 0 #eef2f0;
}
.ped-enviado-total{
    padding:.55rem .85rem;
    background:#f8fafc;
    border-top:1px solid #eef2f0;
    font-size:.88rem;font-weight:700;
    display:flex;justify-content:space-between;align-items:center;
}
#mapaPedido{height:220px;width:100%;min-height:220px;border-radius:0 0 14px 14px;background:#e8eef4}
.ped-datos-grid{display:grid;grid-template-columns:1fr 1fr;gap:.65rem .85rem}
@media (max-width:991.98px){.ped-datos-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@php
    $itemsCount = $pedido->detalles?->count() ?? 0;
    $totalKg = $pedido->detalles?->sum('cantidad') ?? 0;
    $envio = $pedido->envioAsignacion;
    $logisticaEnvio = \App\Support\EnvioPedidoService::datosLogistica($envio);
    $faseLogistica = \App\Support\PedidoCatalogo::faseLogistica($logisticaEnvio);
    $etiquetaEstadoVisible = $faseLogistica
        ? \App\Support\PedidoCatalogo::etiquetaFaseLogistica($faseLogistica)
        : \App\Support\PedidoCatalogo::etiquetaEstado($pedido->estado);
    $badgeEstado = match(true) {
        $faseLogistica === 'recibido_planta' => 'success',
        $faseLogistica === 'en_camino_planta' => 'info',
        $envio !== null => 'primary',
        default => 'secondary',
    };
    $pasoAsignado = $envio !== null;
    $pasoEnCamino = (bool) ($logisticaEnvio['cargado_en_ruta'] ?? false);
    $pasoRecibido = (bool) ($logisticaEnvio['recibido_planta'] ?? false);
    $plantaNombre = $trayectoPartes['destino'] ?? ($pedido->nombre_planta ?: null);
@endphp

<div class="content-header">
    <div class="container-fluid d-flex flex-wrap justify-content-between align-items-center">
        <div>
            <p class="text-muted mb-0">
                Solicitud <strong>{{ $pedido->numero_solicitud }}</strong>
                · <span class="badge badge-{{ $badgeEstado }}">{{ $etiquetaEstadoVisible }}</span>
            </p>
        </div>
        <div class="d-flex flex-wrap" style="gap:.5rem;">
            <a href="{{ route('logistica.asignaciones.listado') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left mr-1"></i>Volver a envíos
            </a>
            @if($envio)
            <a href="{{ route('logistica.asignaciones.show', $envio) }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-truck mr-1"></i>Ver envío
            </a>
            @endif
            @can('pedidos.update')
            <a href="{{ route('pedidos.edit', $pedido) }}" class="btn btn-warning btn-sm">
                <i class="fas fa-edit mr-1"></i>Editar
            </a>
            @endcan
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row ped-show-grid">
            <div class="col-xl-5 col-lg-6 mb-2">
                <div class="card ped-show-card">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold mb-0" style="font-size:1rem;">
                            <i class="fas fa-box-open text-success mr-2"></i>{{ ($pasoEnCamino || $pasoRecibido) ? '¿Qué se envió?' : '¿Qué se envía?' }}
                            @if($itemsCount > 1)
                                <span class="badge badge-light border ml-1" style="font-size:.65rem;vertical-align:middle;">{{ $itemsCount }} ítems</span>
                            @endif
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="ped-enviado-scroll">
                            <table class="table ped-tabla mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Producto / cultivo</th>
                                        <th>Cantidad / empaque</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pedido->detalles as $i => $det)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><strong>{{ $det->cultivo_personalizado }}</strong></td>
                                        <td>
                                            @php $pres = \App\Support\PedidoCatalogo::presentacionDetalle($det); @endphp
                                            <div>{{ $pres['linea_corta'] }}</div>
                                            @if(! $pres['empaque'] && $pres['unidades_fmt'])
                                                <small class="text-muted d-block">~{{ $pres['unidades_fmt'] }} unidades estimadas</small>
                                            @endif
                                        </td>
                                        <td class="text-muted">{{ \App\Support\PedidoCatalogo::observacionesUsuarioDetalle($det->observaciones) ?? '—' }}</td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">Sin productos registrados</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($itemsCount > 0)
                        <div class="ped-enviado-total">
                            <span>Total</span>
                            <span>{{ number_format($totalKg, 2) }} kg</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-3 mb-2">
                <div class="card ped-show-card">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold mb-0" style="font-size:1rem;">
                            <i class="fas fa-stream text-primary mr-2"></i>Seguimiento
                        </h3>
                    </div>
                    <div class="card-body py-2">
                        <div class="ped-paso">
                            <span class="ped-paso-icon hecho"><i class="fas fa-check"></i></span>
                            <div>
                                <div class="ped-paso-titulo">Pedido registrado</div>
                                <p class="ped-paso-det">{{ \Carbon\Carbon::parse($pedido->fechapedido)->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="ped-paso">
                            <span class="ped-paso-icon {{ $pasoAsignado ? ($pasoEnCamino || $pasoRecibido ? 'hecho' : 'activo') : 'pendiente' }}">
                                <i class="fas fa-check"></i>
                            </span>
                            <div>
                                <div class="ped-paso-titulo">Transportista asignado</div>
                                @if($logisticaEnvio)
                                <p class="ped-paso-det mb-0">
                                    {{ $logisticaEnvio['transportista_nombre'] }} · {{ $logisticaEnvio['vehiculo_nombre'] }} ({{ $logisticaEnvio['placa'] }})
                                </p>
                                @if($logisticaEnvio['fecha_asignacion'])
                                <p class="ped-paso-det">{{ $logisticaEnvio['fecha_asignacion']->format('d/m/Y H:i') }}</p>
                                @endif
                                @if(($logisticaEnvio['costo_bs'] ?? null) !== null)
                                <p class="ped-paso-det text-success font-weight-bold mb-0">
                                    Costo: {{ number_format($logisticaEnvio['costo_bs'], 2, ',', '.') }} Bs
                                </p>
                                @endif
                                @else
                                <p class="ped-paso-det">Aún sin chofer ni vehículo.</p>
                                @endif
                            </div>
                        </div>
                        <div class="ped-paso">
                            @php
                                $iconPasoCamino = $pasoRecibido ? 'hecho' : ($pasoEnCamino ? 'activo' : 'pendiente');
                            @endphp
                            <span class="ped-paso-icon {{ $iconPasoCamino }}">
                                <i class="fas {{ $pasoRecibido ? 'fa-check' : 'fa-shipping-fast' }}"></i>
                            </span>
                            <div>
                                <div class="ped-paso-titulo">En camino a planta</div>
                                <p class="ped-paso-det">
                                    @if($pasoRecibido)
                                        Tránsito completado. Mercadería entregada en planta.
                                    @elseif($pasoEnCamino)
                                        Mercadería cargada y en ruta.
                                    @else
                                        Pendiente de carga en almacén agrícola.
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="ped-paso">
                            <span class="ped-paso-icon {{ $pasoRecibido ? 'hecho' : 'pendiente' }}">
                                <i class="fas {{ $pasoRecibido ? 'fa-check' : 'fa-warehouse' }}"></i>
                            </span>
                            <div>
                                <div class="ped-paso-titulo">Recibido en planta</div>
                                <p class="ped-paso-det">
                                    @if($pasoRecibido)
                                        Entrega confirmada en {{ $plantaNombre ?? 'planta destino' }}.
                                    @else
                                        Esperando llegada a {{ $plantaNombre ?? 'planta destino' }}.
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($logisticaEnvio && ($mostrarConfirmarCarga ?? true))
                            @can('pedidos.update')
                                @if($logisticaEnvio['asignado'] && ! $logisticaEnvio['cargado_en_ruta'] && ! $logisticaEnvio['recibido_planta'])
                                <form method="POST" action="{{ route('pedidos.confirmar-carga-envio', $pedido) }}" class="mt-2 mb-0">
                                    @csrf
                                    <button type="button" class="btn btn-primary btn-block btn-sm"
                                            data-confirm-modal data-confirm-tone="success"
                                            data-confirm-title="Confirmar carga e iniciar ruta"
                                            data-confirm-message="¿Confirma que el pedido ya fue cargado y sale hacia planta?">
                                        <i class="fas fa-shipping-fast mr-1"></i>Confirmar carga e iniciar ruta
                                    </button>
                                </form>
                                @endif
                            @endcan
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-lg-3 mb-2">
                <div class="card ped-show-card">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold mb-0" style="font-size:1rem;">
                            <i class="fas fa-info-circle text-muted mr-2"></i>Datos generales
                        </h3>
                    </div>
                    <div class="card-body py-2">
                        <div class="ped-datos-grid">
                            <div>
                                <div class="ped-show-label">Fecha del pedido</div>
                                <div class="ped-show-value">{{ \Carbon\Carbon::parse($pedido->fechapedido)->format('d/m/Y') }}</div>
                            </div>
                            <div>
                                <div class="ped-show-label">Entrega deseada</div>
                                <div class="ped-show-value">{{ $pedido->fechaEntregaDeseada ? \Carbon\Carbon::parse($pedido->fechaEntregaDeseada)->format('d/m/Y') : 'No especificada' }}</div>
                            </div>
                            <div>
                                <div class="ped-show-label">Planta destino</div>
                                <div class="ped-show-value text-danger">{{ $plantaNombre ?? '—' }}</div>
                            </div>
                            @if(($trayectoPartes['recogidas'][0] ?? null))
                            <div>
                                <div class="ped-show-label">Origen (recogida)</div>
                                <div class="ped-show-value text-success">{{ $trayectoPartes['recogidas'][0] }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($pedido->observaciones)
                <div class="card ped-show-card mt-2 mb-0">
                    <div class="card-body py-2">
                        <div class="ped-show-label">Observaciones del pedido</div>
                        <p class="mb-0 text-muted small">{{ $pedido->observaciones }}</p>
                    </div>
                </div>
                @endif
            </div>

            @if($trayectoPartes ?? null)
            <div class="col-12 mb-0">
                <div class="card ped-show-card mb-0">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap py-2">
                        <h3 class="card-title font-weight-bold mb-0" style="font-size:1rem;">
                            <i class="fas fa-route text-info mr-2"></i>Ruta hacia planta
                        </h3>
                        <div class="ped-show-value mt-1 mt-md-0">
                            @include('logistica.partials.trayecto-colores', ['trayectoPartes' => $trayectoPartes])
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div id="mapaPedido"></div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</section>

@include('partials.modal-confirmar-accion')
@endsection

@if($trayectoPartes ?? null)
@push('scripts')
@include('logistica.partials.mapa-ruta-libs')
<script>
document.addEventListener('DOMContentLoaded', async function () {
    const paradas = @json($paradasMapa);
    const el = document.getElementById('mapaPedido');
    if (!el || !window.L) return;

    const puntos = paradas.length >= 1 ? paradas : [
        @if($pedido->origen_latitud && $pedido->origen_longitud)
        { lat: {{ $pedido->origen_latitud }}, lng: {{ $pedido->origen_longitud }}, orden: 1, label: @json($trayectoPartes['recogidas'][0] ?? 'Origen') },
        @endif
        @if($pedido->latitud && $pedido->longitud)
        { lat: {{ $pedido->latitud }}, lng: {{ $pedido->longitud }}, orden: 2, label: @json($trayectoPartes['destino'] ?? 'Planta') },
        @endif
    ].filter(Boolean);

    if (!puntos.length) return;

    const mapa = L.map(el).setView([puntos[0].lat, puntos[0].lng], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap',
    }).addTo(mapa);
    const capas = L.layerGroup().addTo(mapa);

    if (puntos.length >= 2 && window.RutaPorCalles) {
        const routeResult = await RutaPorCalles.fetchRoute(puntos);
        RutaPorCalles.drawOnMap(mapa, capas, puntos, routeResult);
    } else {
        L.marker([puntos[0].lat, puntos[0].lng]).addTo(capas).bindPopup(puntos[0].label || 'Ubicación');
    }

    [100, 300].forEach(function (ms) {
        setTimeout(function () { mapa.invalidateSize(); }, ms);
    });
});
</script>
@endpush
@endif
