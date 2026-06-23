@extends('layouts.app')

@section('title', $punto->nombre)
@section('page_title', $punto->nombre)

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@include('punto_venta.partials.modulo-styles')
<style>
.pdv-map-readonly { height: 260px; }
.pdv-inv-acciones .btn { min-width: 34px; }
.pdv-inv-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: .85rem; padding: 1rem 1.15rem; }
.pdv-inv-card {
    border: 1px solid #e8edf2;
    border-radius: 14px;
    background: #fff;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: .65rem;
}
.pdv-inv-card__name { font-weight: 700; color: #1e293b; font-size: .95rem; margin: 0; }
.pdv-inv-card__code { font-size: .72rem; color: #94a3b8; font-family: ui-monospace, monospace; }
.pdv-inv-card__stock-row { display: flex; align-items: baseline; justify-content: space-between; gap: .5rem; }
.pdv-inv-card__stock-val { font-size: 1.35rem; font-weight: 800; color: #047857; line-height: 1; }
.pdv-inv-bar { height: 6px; border-radius: 999px; background: #ecfdf5; overflow: hidden; }
.pdv-inv-bar__fill { height: 100%; border-radius: 999px; background: linear-gradient(90deg, #059669, #34d399); }
.pdv-inv-bar__fill--bajo { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.pdv-inv-card__actions { display: flex; flex-wrap: wrap; gap: .35rem; padding-top: .5rem; border-top: 1px solid #f1f5f9; }
#modalQrInventario .qr-box {
    display: flex; align-items: center; justify-content: center;
    min-height: 220px; background: #f8faf9; border-radius: 12px; border: 2px dashed #a7f3d0;
}
#modalQrInventario .qr-url {
    font-size: .72rem; word-break: break-all; color: #64748b;
}
</style>
@endpush

@section('content')
    <div class="row mb-3">
        <div class="col-12 d-flex flex-wrap pdv-acciones-grupo" style="gap:.5rem;">
            <a href="{{ route('punto-venta.puntos.index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
            @can('punto_venta.update')
            <a href="{{ route('punto-venta.puntos.edit', $punto) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-edit mr-1"></i> Editar</a>
            @endcan
            @can('punto_venta.delete')
            <form method="POST" action="{{ route('punto-venta.puntos.destroy', $punto) }}" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-outline-danger btn-sm"
                    data-confirm-modal
                    data-confirm-title="Eliminar punto de venta"
                    data-confirm-message="¿Eliminar «{{ $punto->nombre }}»? Esta acción no se puede deshacer."
                    data-confirm-tone="danger">
                    <i class="fas fa-trash mr-1"></i> Eliminar
                </button>
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
                @if($insumos->isEmpty())
                <div class="card-body text-center text-muted py-4">
                    Sin productos. Reciba un pedido de distribución desde planta.
                </div>
                @else
                <div class="pdv-inv-grid">
                    @foreach($insumos as $insumo)
                        @php
                            $unidad = $insumo->unidadMedida?->abreviatura ?? $insumo->unidadMedida?->nombre ?? '';
                            $minimo = (float) ($insumo->stockminimo ?? 0);
                            $stock = (float) $insumo->stock;
                            $pct = $minimo > 0 ? min(100, round(($stock / max($minimo * 2, 1)) * 100)) : min(100, $stock > 0 ? 100 : 0);
                            $bajo = $minimo > 0 && $stock <= $minimo;
                        @endphp
                        <article class="pdv-inv-card">
                            <div>
                                <h4 class="pdv-inv-card__name">{{ $insumo->nombre }}</h4>
                                @if($insumo->codigo_trazabilidad)
                                <div class="pdv-inv-card__code">{{ $insumo->codigo_trazabilidad }}</div>
                                @endif
                            </div>
                            <div class="pdv-inv-card__stock-row">
                                <span class="pdv-inv-card__stock-val">{{ number_format($stock, 2) }}</span>
                                @if($unidad)<span class="pdv-unidad-badge pdv-unidad-badge--inline">{{ $unidad }}</span>@endif
                            </div>
                            <div class="pdv-inv-bar" title="{{ $bajo ? 'Stock bajo' : 'Nivel de stock' }}">
                                <div class="pdv-inv-bar__fill{{ $bajo ? ' pdv-inv-bar__fill--bajo' : '' }}" style="width: {{ $pct }}%"></div>
                            </div>
                            @if($bajo)
                            <span class="badge badge-warning align-self-start"><i class="fas fa-exclamation-triangle mr-1"></i>Stock bajo</span>
                            @endif
                            <div class="pdv-inv-card__actions pdv-inv-acciones">
                                @can('punto_venta.update')
                                <a href="{{ route('punto-venta.puntos.inventario.edit', [$punto, $insumo]) }}"
                                   class="btn btn-xs btn-outline-secondary" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('punto_venta.view')
                                <button type="button"
                                        class="btn btn-xs btn-outline-success btn-qr-inventario"
                                        title="Código QR trazabilidad"
                                        data-url="{{ route('punto-venta.puntos.inventario.qr', [$punto, $insumo]) }}"
                                        data-producto="{{ $insumo->nombre }}">
                                    <i class="fas fa-qrcode"></i>
                                </button>
                                @endcan
                                @can('punto_venta.delete')
                                <form method="POST"
                                      action="{{ route('punto-venta.puntos.inventario.destroy', [$punto, $insumo]) }}"
                                      class="d-inline form-eliminar-insumo">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            class="btn btn-xs btn-outline-danger"
                                            title="Eliminar"
                                            data-confirm-modal
                                            data-confirm-title="Eliminar producto"
                                            data-confirm-message="¿Eliminar «{{ $insumo->nombre }}» del inventario de este punto de venta?">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </article>
                    @endforeach
                </div>
                @endif
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

    <div class="modal fade" id="modalQrInventario" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
                <div class="modal-header border-0 py-3 px-4" style="background: linear-gradient(135deg, #1e4620, #2c5530); color: #fff;">
                    <h5 class="modal-title font-weight-bold mb-0">
                        <i class="fas fa-qrcode mr-2"></i><span id="modalQrTitulo">Trazabilidad</span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="opacity: .9;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 py-4 text-center">
                    <p class="text-muted small mb-3">Escanea el código QR para ver la trazabilidad completa.</p>
                    <div id="qrInventarioCanvas" class="qr-box mb-3"></div>
                    <p class="qr-url mb-2" id="modalQrUrl"></p>
                    <a href="#" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success" id="modalQrAbrir">
                        <i class="fas fa-external-link-alt mr-1"></i> Abrir trazabilidad
                    </a>
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

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var qrInstance = null;
    var canvas = document.getElementById('qrInventarioCanvas');
    var modal = document.getElementById('modalQrInventario');

    document.querySelectorAll('.btn-qr-inventario').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var endpoint = btn.getAttribute('data-url');
            var producto = btn.getAttribute('data-producto') || 'Producto';
            document.getElementById('modalQrTitulo').textContent = producto;
            canvas.innerHTML = '<div class="text-muted py-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';

            if (window.jQuery) window.jQuery(modal).modal('show');

            fetch(endpoint, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    canvas.innerHTML = '';
                    qrInstance = new QRCode(canvas, {
                        text: data.url,
                        width: 200,
                        height: 200,
                        colorDark: '#1e4620',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.M
                    });
                    document.getElementById('modalQrUrl').textContent = data.url;
                    var link = document.getElementById('modalQrAbrir');
                    link.href = data.url;
                })
                .catch(function () {
                    canvas.innerHTML = '<p class="text-danger small mb-0">No se pudo generar el código QR.</p>';
                });
        });
    });

    if (modal && window.jQuery) {
        window.jQuery(modal).on('hidden.bs.modal', function () {
            canvas.innerHTML = '';
            qrInstance = null;
        });
    }
});
</script>
@endpush
