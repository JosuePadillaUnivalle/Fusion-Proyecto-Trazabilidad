@extends('layouts.app')
@push('styles')
@include('logistica.partials.ayuda-logistica-styles')
@include('logistica.partials.mapa-ruta-styles')
<style>
.x-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
.x-table thead th{background:#f2f7f3;border-bottom:0}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">{{ $ruta->nombre }}</h1>
            <p class="text-muted mb-0">Detalle de la ruta y sus paradas de entrega</p>
        </div>
        <a href="{{ route('logistica.rutas.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card x-card">
                    <div class="card-header"><h3 class="card-title mb-0">Resumen</h3></div>
                    <div class="card-body">
                        <p><strong>Chofer:</strong> {{ $ruta->transportista ? $ruta->transportista->nombre . ' ' . $ruta->transportista->apellido : 'Sin asignar' }}</p>
                        <p><strong>Situación:</strong>
                            @php
                                $badgeClass = $ruta->estado === 'completada' ? 'badge-success' : ($ruta->estado === 'en_ruta' ? 'badge-info' : ($ruta->estado === 'cancelada' ? 'badge-danger' : 'badge-warning'));
                            @endphp
                            @include('logistica.partials.etiqueta-estado', ['estado' => $ruta->estado, 'clase' => $badgeClass])
                        </p>
                        <p><strong>Salida:</strong> {{ optional($ruta->fecha_salida)->format('d/m/Y H:i') ?? '—' }}</p>
                        <p class="mb-0"><strong>Cierre:</strong> {{ optional($ruta->fecha_cierre)->format('d/m/Y H:i') ?? '—' }}</p>
                    </div>
                </div>

                @can('rutas_multi.update')
                <div class="card x-card">
                    <div class="card-header"><h3 class="card-title mb-0">Cambiar situación de la ruta</h3></div>
                    <div class="card-body">
                        <p class="small text-muted">Cuando el camión salga, elija <strong>En camino</strong>. Al terminar todas las entregas, <strong>Completada</strong>.</p>
                        <form method="POST" action="{{ route('logistica.rutas.update', $ruta) }}">
                            @csrf
                            @method('PATCH')
                            <div class="form-group">
                                <label>Nueva situación</label>
                                <select name="estado" class="form-control" required>
                                    <option value="planificada" @selected($ruta->estado === 'planificada')>Planificada (aún no sale)</option>
                                    <option value="en_ruta" @selected($ruta->estado === 'en_ruta')>En camino (repartiendo)</option>
                                    <option value="completada" @selected($ruta->estado === 'completada')>Completada (terminó)</option>
                                    <option value="cancelada" @selected($ruta->estado === 'cancelada')>Cancelada</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-save mr-1"></i> Guardar cambio
                            </button>
                        </form>
                    </div>
                </div>
                @endcan

                <div class="log-guia log-guia-compact">
                    <strong>Siguiente paso:</strong> en el menú <a href="{{ route('logistica.asignaciones.index') }}"><strong>Asignar envíos</strong></a>, use la asignación automática o confirme envío por envío.
                </div>
            </div>

            <div class="col-md-8">
                @if(count($paradasMapa) >= 1)
                <div class="card x-card mb-3">
                    <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-road mr-1"></i> Recorrido por calles</h3></div>
                    <div class="card-body p-2">
                        <div id="mapaRutaEntrega"></div>
                        <p class="ruta-mapa-leyenda mt-2 mb-0">La ruta sigue calles cuando hay coordenadas en los pedidos.</p>
                    </div>
                </div>
                @endif
                <div class="card x-card">
                    <div class="card-header"><h3 class="card-title mb-0">Paradas del recorrido</h3></div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover x-table">
                            <thead>
                                <tr>
                                    <th>Orden</th>
                                    <th>Lugar / cliente</th>
                                    <th>Código envío</th>
                                    <th>Pedido</th>
                                    <th>Situación</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ruta->paradas as $parada)
                                    @php
                                        $pBadge = $parada->estado === 'entregado' ? 'badge-success' : ($parada->estado === 'en_ruta' ? 'badge-info' : 'badge-warning');
                                    @endphp
                                    <tr>
                                        <td>{{ $parada->orden }}</td>
                                        <td>{{ $parada->destino ?? '—' }}</td>
                                        <td>{{ $parada->externo_envio_id ?? '—' }}</td>
                                        <td>{{ $parada->pedidoid ?? '—' }}</td>
                                        <td>@include('logistica.partials.etiqueta-estado', ['estado' => $parada->estado, 'clase' => $pBadge])</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Esta ruta no tiene paradas registradas.<br>
                                            <span class="small">Puede crear otra ruta con paradas o asignar envíos directamente al chofer.</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@if(count($paradasMapa) >= 1)
@push('scripts')
@include('logistica.partials.mapa-ruta-libs')
<script>
document.addEventListener('DOMContentLoaded', () => {
function iniciarMapaRuta() {
(async () => {
    const paradas = @json($paradasMapa);
    const urlTrazado = @json(route('logistica.rutas.trazado', $ruta));
    const el = document.getElementById('mapaRutaEntrega');
    if (!el || !window.L) return;
    const map = L.map(el).setView([paradas[0].lat, paradas[0].lng], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '© OSM' }).addTo(map);
    const capas = L.layerGroup().addTo(map);
    let routeResult = null;
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
    if (!routeResult && paradas.length >= 2) {
        routeResult = await RutaPorCalles.fetchRoute(paradas);
    }
    RutaPorCalles.drawOnMap(map, capas, paradas, routeResult);
    setTimeout(() => map.invalidateSize(), 150);
})();
}
if (window.L && window.RutaPorCalles) iniciarMapaRuta();
else {
    let n = 0;
    const t = setInterval(() => {
        if (window.L && window.RutaPorCalles) { clearInterval(t); iniciarMapaRuta(); }
        else if (++n > 80) clearInterval(t);
    }, 50);
}
});
</script>
@endpush
@endif
@endsection
