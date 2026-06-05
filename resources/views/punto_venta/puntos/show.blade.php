@extends('layouts.app')

@section('title', $punto->nombre)
@section('page_title', $punto->nombre)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@include('punto_venta.partials.modulo-styles')
<style>.pdv-map-readonly { height: 260px; }</style>
@endpush

@section('content')
    <div class="row mb-3">
        <div class="col-12 d-flex flex-wrap pdv-acciones-grupo" style="gap:.5rem;">
            <a href="{{ route('punto-venta.puntos.index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
            @can('punto_venta.update')
            <a href="{{ route('punto-venta.puntos.edit', $punto) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-edit mr-1"></i> Editar</a>
            @endcan
            @can('punto_venta.delete')
            <form method="POST" action="{{ route('punto-venta.puntos.destroy', $punto) }}" class="d-inline"
                onsubmit="return confirm('¿Eliminar este punto de venta?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash mr-1"></i> Eliminar</button>
            </form>
            @endcan
        </div>
    </div>

    <div class="row align-items-start">
        <div class="col-lg-4">
            <div class="card pdv-card card-outline card-primary">
                <div class="card-header bg-white"><h3 class="card-title mb-0"><i class="fas fa-store text-success mr-1"></i> Detalle</h3></div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt class="text-muted small">Minorista</dt>
                        <dd class="mb-2">{{ $punto->nombreMinorista() }}</dd>
                        <dt class="text-muted small">Dirección</dt>
                        <dd class="mb-2">{{ $punto->direccion ?: '—' }}</dd>
                        <dt class="text-muted small">Coordenadas</dt>
                        <dd class="mb-2">
                            @if($punto->latitud && $punto->longitud)
                                {{ number_format($punto->latitud, 5) }}, {{ number_format($punto->longitud, 5) }}
                            @else — @endif
                        </dd>
                        <dt class="text-muted small">Estado</dt>
                        <dd class="mb-2">
                            <span class="badge badge-{{ $punto->activo ? 'success' : 'secondary' }}">
                                {{ $punto->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </dd>
                        @if($punto->observaciones)
                        <dt class="text-muted small">Observaciones</dt>
                        <dd class="mb-0">{{ $punto->observaciones }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            @if($punto->latitud && $punto->longitud)
            <div class="card pdv-card mb-3">
                <div class="card-header bg-white py-2"><h3 class="card-title mb-0 small font-weight-bold">Ubicación</h3></div>
                <div class="card-body pt-2">
                    <div id="pdvMapReadonly" class="pdv-map pdv-map-readonly"></div>
                </div>
            </div>
            @endif

            <div class="card pdv-card card-outline card-success mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0"><i class="fas fa-boxes mr-1"></i> Inventario</h3>
                    <span class="badge badge-light">{{ $insumos->count() }} productos</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-striped m-0">
                        <thead class="bg-light"><tr><th>Producto</th><th>Stock</th><th>Unidad</th></tr></thead>
                        <tbody>
                            @forelse($insumos as $insumo)
                                <tr>
                                    <td>{{ $insumo->nombre }}</td>
                                    <td>{{ number_format($insumo->stock, 2) }}</td>
                                    <td>{{ $insumo->unidadMedida?->abreviatura ?? $insumo->unidadMedida?->nombre ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">Sin productos. Reciba un pedido de distribución desde planta.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card pdv-card card-outline card-info">
                <div class="card-header bg-white d-flex justify-content-between">
                    <h3 class="card-title mb-0"><i class="fas fa-truck-loading mr-1"></i> Pedidos recientes</h3>
                    <a href="{{ route('punto-venta.pedidos.index', ['puntoventaid' => $punto->puntoventaid]) }}" class="btn btn-xs btn-outline-info">Ver todos</a>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm m-0">
                        <thead class="bg-light"><tr><th>Solicitud</th><th>Producto</th><th>Estado</th></tr></thead>
                        <tbody>
                            @forelse($pedidos as $ped)
                                @php $badge = \App\Support\PedidoDistribucionCatalogo::badgeEstado($ped); @endphp
                                <tr>
                                    <td><a href="{{ route('punto-venta.pedidos.show', $ped) }}">{{ $ped->numero_solicitud }}</a></td>
                                    <td>{{ $ped->detalles->first()?->producto_nombre ?? '—' }}</td>
                                    <td><span class="badge badge-{{ $badge['clase'] }}">{{ $badge['etiqueta'] }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">Sin pedidos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@if($punto->latitud && $punto->longitud)
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (!window.L) return;
    var lat = {{ $punto->latitud }};
    var lng = {{ $punto->longitud }};
    var map = L.map('pdvMapReadonly').setView([lat, lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OSM' }).addTo(map);
    L.marker([lat, lng]).addTo(map);
    setTimeout(function () { map.invalidateSize(); }, 200);
});
</script>
@endpush
@endif
