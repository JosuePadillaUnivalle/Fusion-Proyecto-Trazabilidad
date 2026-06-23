@php
    $rutaPrefijo = $rutaPrefijo ?? 'almacen-mayorista';
    $tituloModulo = $tituloModulo ?? 'Almacén mayorista';
    $unidad = $producto->unidadMedida?->abreviatura ?? $producto->unidadMedida?->nombre ?? '';
@endphp

@extends('layouts.app')

@section('title', $producto->nombre.' | Inventario')
@section('page_title', 'Producto en almacén')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route($rutaPrefijo.'.index') }}">Almacenes</a></li>
    <li class="breadcrumb-item"><a href="{{ route($rutaPrefijo.'.show', $almacen) }}">{{ $almacen->nombre }}</a></li>
    <li class="breadcrumb-item active">{{ $producto->nombre }}</li>
@endsection

@push('styles')
@include('partials.modulo-inventario-styles')
<style>
.page-inv-almacen .inv-hero {
    border: none;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 22px rgba(44, 85, 48, .12);
}
.page-inv-almacen .inv-hero-head {
    background: linear-gradient(135deg, #1e4620 0%, #2c5530 55%, #4a7c59 100%);
    color: #fff;
    padding: 1.35rem 1.5rem;
}
.page-inv-almacen .inv-badge-almacen {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    background: rgba(255,255,255,.18);
    border-radius: 999px;
    padding: .25rem .75rem;
    font-size: .8rem;
}
.page-inv-almacen .inv-img-wrap {
    background: linear-gradient(180deg, #f8faf8, #eef4ee);
    border-radius: 12px;
    padding: 1rem;
    border: 1px solid #e2ebe3;
    min-height: 260px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.page-inv-almacen .inv-img {
    max-width: 100%;
    max-height: 280px;
    object-fit: contain;
    border-radius: 10px;
}
.page-inv-almacen .inv-stock-card {
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    padding: 1.1rem 1.25rem;
    background: #fff;
}
.page-inv-almacen .inv-stock-card.estado-ok { border-color: #b8dfc4; background: #f3fbf5; }
.page-inv-almacen .inv-stock-card.estado-medio { border-color: #ffe8a3; background: #fffdf5; }
.page-inv-almacen .inv-stock-card.estado-bajo { border-color: #f5c2c7; background: #fff5f5; }
.page-inv-almacen .inv-stock-card.estado-agotado { border-color: #dee2e6; background: #f8f9fa; }
.page-inv-almacen .inv-stock-valor {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.1;
    color: #1e4620;
}
.page-inv-almacen .inv-progress {
    height: 8px;
    border-radius: 999px;
    background: #e9ecef;
    overflow: hidden;
}
.page-inv-almacen .inv-progress > span {
    display: block;
    height: 100%;
    border-radius: 999px;
    transition: width .3s ease;
}
.page-inv-almacen .inv-progress .bar-ok { background: linear-gradient(90deg, #28a745, #5cb85c); }
.page-inv-almacen .inv-progress .bar-medio { background: linear-gradient(90deg, #f0ad4e, #ffc107); }
.page-inv-almacen .inv-progress .bar-bajo { background: linear-gradient(90deg, #dc3545, #e35d6a); }
.page-inv-almacen .inv-progress .bar-agotado { background: #adb5bd; }
.page-inv-almacen .dato-label {
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #6c757d;
    margin-bottom: .15rem;
}
.page-inv-almacen .dato-valor { font-weight: 600; color: #212529; }
.page-inv-almacen .inv-minimo-hint {
    font-size: .85rem;
    color: #5a6c5e;
    background: #f0f7f1;
    border-radius: 8px;
    padding: .65rem .85rem;
    border: 1px solid #dce9de;
}
</style>
@endpush

@section('content')
<div class="page-inv-almacen page-almacen-show">
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card inv-hero mb-0">
                <div class="inv-hero-head d-flex flex-wrap align-items-center justify-content-between">
                    <div class="d-flex align-items-center mb-2 mb-md-0">
                        <span class="mr-3 d-inline-flex align-items-center justify-content-center rounded"
                              style="width:52px;height:52px;background:rgba(255,255,255,.2);font-size:1.35rem;">
                            <i class="fas fa-box-open"></i>
                        </span>
                        <div>
                            <h3 class="mb-1">{{ $producto->nombre }}</h3>
                            <span class="inv-badge-almacen">
                                <i class="fas fa-warehouse"></i> {{ $almacen->nombre }}
                            </span>
                        </div>
                    </div>
                    @can('inventario.update')
                    <a href="{{ route($rutaPrefijo.'.inventario.edit', [$almacen, $producto]) }}" class="btn btn-light btn-sm">
                        <i class="fas fa-edit mr-1"></i> Editar
                    </a>
                    @endcan
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-5 mb-4 mb-md-0">
                            <div class="inv-img-wrap">
                                <img src="{{ $producto->imagenSrc(480) }}"
                                     alt="{{ $producto->nombre }}"
                                     class="inv-img"
                                     loading="lazy">
                            </div>
                            <p class="text-muted small text-center mb-0 mt-2">
                                Producto terminado listo para distribución
                            </p>
                        </div>
                        <div class="col-md-7">
                            <div class="inv-stock-card estado-{{ $estadoStock['clase'] }} mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="dato-label">Disponible en depósito</div>
                                        <div class="inv-stock-valor">
                                            {{ number_format($producto->stock, 2) }}
                                            <span style="font-size:1rem;font-weight:600;">{{ $unidad }}</span>
                                        </div>
                                    </div>
                                    <span class="badge badge-pill px-3 py-2
                                        @if($estadoStock['clase'] === 'ok') badge-success
                                        @elseif($estadoStock['clase'] === 'medio') badge-warning text-dark
                                        @elseif($estadoStock['clase'] === 'bajo') badge-danger
                                        @else badge-secondary @endif">
                                        <i class="fas fa-{{ $estadoStock['icono'] }} mr-1"></i>
                                        {{ $estadoStock['etiqueta'] }}
                                    </span>
                                </div>
                                <div class="inv-progress mb-2">
                                    <span class="bar-{{ $estadoStock['clase'] }}" style="width: {{ $estadoStock['porcentaje'] }}%;"></span>
                                </div>
                                <p class="mb-0 small text-muted">{{ $estadoStock['mensaje'] }}</p>
                            </div>

                            <div class="inv-minimo-hint mb-3">
                                <i class="fas fa-bell mr-1 text-success"></i>
                                <strong>Stock mínimo:</strong> {{ number_format($estadoStock['umbral'], 2) }} {{ $unidad }}.
                                Cuando el disponible baje de ese valor, el producto se marcará en <strong>stock bajo</strong>.
                            </div>

                            @if(($empaquetajes ?? collect())->isNotEmpty())
                            <div class="mb-3">
                                <div class="dato-label mb-2">Empaquetado en almacén</div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0" style="font-size:.85rem;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Presentación</th>
                                                <th>Tipo envase</th>
                                                <th class="text-right">Unidades</th>
                                                <th class="text-right">Kg</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($empaquetajes as $row)
                                                @php $p = $row['presentacion']; @endphp
                                                <tr>
                                                    <td>{{ $p->nombre }}</td>
                                                    <td>{{ $p->tipoEmpaque?->nombre ?? ucfirst($p->tipo_envase ?? '—') }}</td>
                                                    <td class="text-right font-weight-bold">{{ number_format($row['unidades'], 0) }} {{ $p->etiquetaUnidad() }}</td>
                                                    <td class="text-right">{{ number_format($row['kg'], 2) }} kg</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif

                            @if($producto->descripcion)
                            <div class="mb-3">
                                <div class="dato-label">Descripción</div>
                                <div class="dato-valor font-weight-normal">{{ $producto->descripcion }}</div>
                            </div>
                            @endif

                            <div class="row">
                                <div class="col-sm-6 mb-2">
                                    <div class="dato-label">Unidad</div>
                                    <div class="dato-valor">{{ $producto->unidadMedida->nombre ?? '—' }}</div>
                                </div>
                                <div class="col-sm-6 mb-2">
                                    <div class="dato-label">Ámbito</div>
                                    <div class="dato-valor">{{ $tituloModulo }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white d-flex justify-content-between flex-wrap py-3">
                    <a href="{{ route($rutaPrefijo.'.show', $almacen) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Volver al almacén
                    </a>
                    <div class="d-flex flex-wrap gap-2">
                        @if(! empty($loteProduccionPedidoId))
                        <a href="{{ route('procesamiento.show', $loteProduccionPedidoId) }}" class="btn btn-outline-success">
                            <i class="fas fa-route mr-1"></i> Ver trazabilidad
                        </a>
                        @endif
                        @can('inventario.delete')
                    <form action="{{ route($rutaPrefijo.'.inventario.destroy', [$almacen, $producto]) }}" method="POST"
                          class="d-inline on-submit-confirm m-0"
                          data-confirm-title="¿Eliminar producto?"
                          data-confirm-text="Se quitará este producto del inventario de {{ $almacen->nombre }}.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">
                            <i class="fas fa-trash mr-1"></i> Eliminar del almacén
                        </button>
                    </form>
                    @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-3" style="border-radius:12px;">
                <div class="card-body">
                    <h6 class="text-uppercase text-muted small mb-3">Acciones rápidas</h6>
                    @can('inventario.update')
                    <a href="{{ route($rutaPrefijo.'.inventario.edit', [$almacen, $producto]) }}" class="btn btn-warning btn-block mb-2 text-white">
                        <i class="fas fa-edit mr-1"></i> Editar producto
                    </a>
                    @endcan
                    <a href="{{ route($rutaPrefijo.'.movimientos.create', 'ingreso') }}?almacenid={{ $almacen->almacenid }}" class="btn btn-outline-success btn-block mb-2">
                        <i class="fas fa-plus-circle mr-1"></i> Registrar ingreso
                    </a>
                    <a href="{{ route($rutaPrefijo.'.movimientos.create', 'salida') }}?almacenid={{ $almacen->almacenid }}" class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-minus-circle mr-1"></i> Registrar salida
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.on-submit-confirm').forEach(function (form) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var el = this;
        if (typeof Swal === 'undefined') {
            if (confirm(el.dataset.confirmText || '¿Confirmar?')) { el.submit(); }
            return;
        }
        Swal.fire({
            title: el.dataset.confirmTitle || '¿Eliminar?',
            text: el.dataset.confirmText || '',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function (r) { if (r.isConfirmed) el.submit(); });
    });
});
</script>
@endpush
