@extends('layouts.app')

@section('title', 'Inventario — Puntos de venta')
@section('page_title', 'Inventario')

@push('styles')
@include('punto_venta.partials.modulo-styles')
<style>
.pdv-inv-tabla { table-layout: fixed; width: 100%; }
.pdv-inv-tabla th,
.pdv-inv-tabla td { vertical-align: middle; padding: .65rem 1rem; }
.pdv-inv-tabla th:nth-child(1),
.pdv-inv-tabla td:nth-child(1) { width: 34%; }
.pdv-inv-tabla th:nth-child(2),
.pdv-inv-tabla td:nth-child(2) { width: 26%; }
.pdv-inv-tabla th:nth-child(3),
.pdv-inv-tabla td:nth-child(3) { width: 12%; }
.pdv-inv-tabla th:nth-child(4),
.pdv-inv-tabla td:nth-child(4) { width: 10%; }
.pdv-inv-tabla th:nth-child(5),
.pdv-inv-tabla td:nth-child(5) { width: 18%; }
.pdv-inv-tabla thead th { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
.pdv-inv-tabla td:nth-child(2) a { font-weight: 600; color: #047857; text-decoration: none; }
.pdv-inv-tabla td:nth-child(2) a:hover { text-decoration: underline; }
.pdv-inv-filtros-pdv .selector-catalogo-label,
.pdv-inv-filtros-pdv label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #6c757d; margin-bottom: .35rem; }
</style>
@endpush

@section('content')
<x-modulo-index-header
    titulo="Inventario de puntos de venta"
    icono="fa-boxes"
    :registros="$insumos->count()"
    filtros-target="#filtrosInventarioPdv"
/>

<div class="card pdv-card border-0 shadow-sm">
    <div class="modulo-filtros-panel collapse show" id="filtrosInventarioPdv">
        <form method="GET" action="{{ route('punto-venta.inventario.index') }}" class="form-row align-items-end pdv-inv-filtros-pdv">
            <div class="form-group col-md-4 mb-2 mb-md-0">
                @include('partials.selector-catalogo', [
                    'id' => 'inv_filtro_pdv',
                    'name' => 'puntoventaid',
                    'value' => $filtroPdv ?? '',
                    'labelSelected' => $filtroPdvNombre ?? '',
                    'endpoint' => route('catalogo-selector.puntos-venta'),
                    'title' => 'Filtrar por punto de venta',
                    'label' => 'Punto de venta',
                    'icon' => 'fa-store',
                    'searchPlaceholder' => 'Nombre, dirección o minorista…',
                    'searchLabel' => 'Buscar punto de venta',
                    'allowEmpty' => true,
                    'emptyLabel' => 'Todos mis puntos',
                    'placeholderEmpty' => 'Todos mis puntos',
                    'modalIcon' => 'fa-store',
                    'rowIcon' => 'fa-store',
                    'colNombre' => 'Punto de venta',
                    'colDetalle' => 'Minorista / ubicación',
                    'variant' => 'filtros',
                ])
            </div>
            <div class="form-group col-md-5 mb-2 mb-md-0">
                <label>Buscar producto</label>
                <input type="text" name="q" class="form-control form-control-sm" value="{{ $filtroQ }}" placeholder="Nombre o código trazabilidad…">
            </div>
            <div class="form-group col-md-3 mb-0 d-flex modulo-filtros-acciones">
                <button type="submit" class="btn btn-success btn-filtro-modulo"><i class="fas fa-search mr-1"></i> Filtrar</button>
                <a href="{{ route('punto-venta.inventario.index') }}" class="btn btn-light border btn-filtro-modulo">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover m-0 pdv-inv-tabla">
                <thead class="bg-light">
                    <tr>
                        <th>Producto</th>
                        <th>Punto de venta</th>
                        <th>Stock</th>
                        <th>Unidad</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($insumos as $insumo)
                        @php $punto = $insumo->punto_venta; @endphp
                        <tr>
                            <td>
                                <strong>{{ $insumo->nombre }}</strong>
                                @if($insumo->codigo_trazabilidad)
                                    <br><small class="text-muted">{{ $insumo->codigo_trazabilidad }}</small>
                                @endif
                            </td>
                            <td>
                                @if($punto)
                                    <a href="{{ route('punto-venta.puntos.show', $punto) }}">{{ $punto->nombre }}</a>
                                @else — @endif
                            </td>
                            <td>{{ number_format($insumo->stock, 2) }}</td>
                            <td>{{ $insumo->unidadMedida?->abreviatura ?? $insumo->unidadMedida?->nombre ?? '—' }}</td>
                            <td class="text-center text-nowrap pdv-inv-acciones">
                                @if($punto)
                                    @can('punto_venta.view')
                                    <a href="{{ route('punto-venta.puntos.show', $punto) }}" class="btn btn-xs btn-outline-primary" title="Ver punto de venta">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @endcan
                                    @can('punto_venta.update')
                                    <a href="{{ route('punto-venta.puntos.inventario.edit', [$punto, $insumo]) }}?return=inventario"
                                       class="btn btn-xs btn-outline-secondary" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @endcan
                                    @can('punto_venta.view')
                                    <button type="button" class="btn btn-xs btn-outline-success btn-qr-inventario"
                                            title="Ver QR"
                                            data-url="{{ route('punto-venta.puntos.inventario.qr', [$punto, $insumo]) }}"
                                            data-producto="{{ $insumo->nombre }}">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                    @endcan
                                    @can('punto_venta.delete')
                                    <form method="POST" action="{{ route('punto-venta.puntos.inventario.destroy', [$punto, $insumo]) }}" class="d-inline form-eliminar-insumo">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="return" value="inventario">
                                        <button type="button" class="btn btn-xs btn-outline-danger" title="Eliminar"
                                                data-confirm-modal data-confirm-title="Eliminar producto"
                                                data-confirm-message="¿Eliminar «{{ $insumo->nombre }}» del inventario?">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Sin productos en inventario.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@include('punto_venta.inventario.partials.modal-qr')
@include('partials.modal-confirmar-accion')
@endsection

@push('scripts')
@include('punto_venta.inventario.partials.qr-scripts')
@endpush
