@extends('layouts.app')



@section('title', 'Solicitudes de minoristas')

@section('page_title', 'Solicitudes de minoristas')



@push('styles')

@include('punto_venta.partials.modulo-styles')

<style>

.may-pedidos-page .may-band-header {

    display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between;

    gap: .75rem; margin-bottom: .85rem;

}

.may-pedidos-page .may-band-header h2 {

    font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0;

}

.may-pedidos-page .may-band-header p {

    font-size: .8rem; color: #64748b; margin: .2rem 0 0; max-width: 42rem; line-height: 1.4;

}

.may-pedidos-page .table td, .may-pedidos-page .table th {

    font-size: .82rem; vertical-align: middle; padding: .45rem .55rem;

}

.may-pedidos-page .table thead th {

    font-size: .68rem; text-transform: uppercase; letter-spacing: .04em; color: #64748b;

    border-top: 0; white-space: nowrap;

}

.may-pedidos-page .may-minorista-nombre { font-weight: 600; color: #334155; font-size: .8rem; }

.may-pedidos-page .may-destino-nombre { font-weight: 700; color: #0f172a; font-size: .82rem; }

.may-pedidos-page .may-destino-dir {

    display: block; font-size: .72rem; color: #64748b; line-height: 1.25; margin-top: .1rem;

}

.may-pedidos-page .may-producto-cell { font-weight: 600; color: #1e293b; }

.may-pedidos-page .may-cantidad-cell { font-variant-numeric: tabular-nums; white-space: nowrap; }

.may-pedidos-page tr.table-warning { --bs-table-bg: #fffbeb; }

</style>

@endpush



@section('content')

<div class="may-pedidos-page">

    <div class="may-band-header">

        <div>

            <h2><i class="fas fa-inbox text-teal mr-1"></i> Bandeja operativa mayorista</h2>

            <p>Solicitudes entrantes de minoristas y envíos que usted inicia hacia puntos de venta. Revise stock, confirme solicitudes o registre nuevos envíos desde Envíos → Nuevo envío.</p>

        </div>

        <a href="{{ route('almacen-mayorista.index') }}" class="btn btn-outline-success btn-sm">

            <i class="fas fa-warehouse mr-1"></i> Ver mis almacenes

        </a>

    </div>



    <div class="card pdv-card card-outline card-success card-modulo-main elevation-1">

        <x-modulo-index-header

            titulo="Solicitudes recibidas de minoristas"

            icono="fa-clipboard-list"

            :registros="$pedidos->count()"

            filtros-target="#filtrosMayPedidosPanel"

            :nuevo-href="null"

        />



        <div id="filtrosMayPedidosPanel" class="collapse {{ request()->hasAny(['q','estado_grupo','puntoventaid']) ? 'show' : '' }}">

            <div class="modulo-filtros-panel pdv-pedidos-filtros">

                <form method="GET" action="{{ route('punto-venta.pedidos.index', ['ctx' => 'mayorista']) }}" class="form-row align-items-end">

                    <input type="hidden" name="ctx" value="mayorista">

                    <div class="form-group col-lg-4 col-md-6 mb-2 mb-lg-0">

                        <label>Buscar</label>

                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Solicitud, producto, minorista, destino…">

                    </div>

                    <div class="form-group col-lg-3 col-md-6 mb-2 mb-lg-0">

                        @include('partials.selector-catalogo', [

                            'id' => 'may_filtro_pdv',

                            'name' => 'puntoventaid',

                            'value' => $filtroPdvId ?? '',

                            'labelSelected' => $filtroPdvNombre ?? '',

                            'endpoint' => route('catalogo-selector.puntos-venta'),

                            'title' => 'Filtrar por punto de venta',

                            'label' => 'Punto de venta destino',

                            'icon' => 'fa-store',

                            'searchPlaceholder' => 'Nombre o dirección…',

                            'allowEmpty' => true,

                            'emptyLabel' => 'Todos',

                            'placeholderEmpty' => 'Todos los destinos',

                            'variant' => 'filtros',

                        ])

                    </div>

                    <div class="form-group col-lg-3 col-md-6 mb-2 mb-lg-0">

                        <label>Estado</label>

                        <select name="estado_grupo" class="form-control">

                            <option value="">Todos</option>

                            @foreach(\App\Support\PedidoDistribucionCatalogo::etiquetasFiltroEstado() as $valor => $etiqueta)

                                <option value="{{ $valor }}" @selected(request('estado_grupo') === $valor)>{{ $etiqueta }}</option>

                            @endforeach

                        </select>

                    </div>

                    <div class="form-group col-lg-2 col-md-6 mb-2 mb-lg-0 d-flex align-items-end modulo-filtros-acciones">

                        <button type="submit" class="btn btn-success btn-filtro-modulo flex-fill">

                            <i class="fas fa-filter mr-1"></i> Filtrar

                        </button>

                    </div>

                </form>

            </div>

        </div>



        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover table-striped m-0">

                    <thead class="bg-light">

                        <tr>

                            <th>Solicitud</th>

                            <th>Minorista</th>

                            <th>Destino (PDV)</th>

                            <th>Producto</th>

                            <th>Cant.</th>

                            <th>Entrega</th>

                            <th>Estado</th>

                            <th class="text-center" style="min-width:90px;">Acción</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($pedidos as $pedido)

                            @php

                                $det = $pedido->detalles->first();

                                $badge = \App\Support\PedidoDistribucionCatalogo::badgeEstado($pedido);

                                $pendienteMayorista = \App\Support\PedidoDistribucionCatalogo::pendienteAprobacionMayorista($pedido);

                                $pres = $det?->presentacion;

                                $unidad = $pres?->etiquetaUnidad() ?? ($det?->insumo?->unidadMedida?->abreviatura ?? 'u.');

                                $pdv = $pedido->puntoVenta;

                                $destinoDir = $pdv?->resumenUbicacion() ?: ($pdv?->direccionParaMostrar() ?: null);

                            @endphp

                            <tr class="{{ $pendienteMayorista ? 'table-warning' : '' }}">

                                <td class="text-nowrap">

                                    <span class="pdv-solicitud-pill">

                                        <i class="fas fa-file-alt"></i>{{ $pedido->numero_solicitud }}

                                    </span>

                                    @if($pedido->tipo_solicitud === 'custom')

                                        <span class="badge badge-secondary ml-1" style="font-size:.62rem;">Nuevo</span>

                                    @endif

                                </td>

                                <td>

                                    <span class="may-minorista-nombre">{{ $pdv?->minorista?->nombre ?? '—' }}</span>

                                </td>

                                <td>

                                    <span class="may-destino-nombre">

                                        <i class="fas fa-map-marker-alt text-success mr-1" style="font-size:.7rem;"></i>

                                        {{ $pdv?->nombre ?? '—' }}

                                    </span>

                                    @if($destinoDir)

                                        <span class="may-destino-dir">{{ $destinoDir }}</span>

                                    @endif

                                </td>

                                <td class="may-producto-cell">{{ $det?->producto_nombre ?? '—' }}</td>

                                <td class="may-cantidad-cell">

                                    @if($det)

                                        {{ number_format($det->cantidad, 0) }}

                                        <span class="text-muted small">{{ $unidad }}</span>

                                    @else — @endif

                                </td>

                                <td class="text-nowrap small">

                                    {{ $pedido->fecha_entrega_deseada?->format('d/m/Y') ?? '—' }}

                                    @if($pedido->hora_entrega_deseada)

                                        <span class="text-muted">{{ substr((string) $pedido->hora_entrega_deseada, 0, 5) }}</span>

                                    @endif

                                </td>

                                <td>

                                    <span class="pdv-estado-pill pdv-estado-pill--{{ $badge['clase'] }}">

                                        <i class="fas {{ $badge['icono'] ?? 'fa-info-circle' }}"></i>

                                        {{ $badge['etiqueta'] }}

                                    </span>

                                </td>

                                <td class="text-center text-nowrap">

                                    @if($pendienteMayorista)

                                        <a href="{{ route('punto-venta.pedidos.show', ['pedido' => $pedido, 'ctx' => 'mayorista']) }}" class="btn btn-xs btn-warning">

                                            <i class="fas fa-clipboard-check"></i> Revisar

                                        </a>

                                    @else

                                        <a href="{{ route('punto-venta.pedidos.show', ['pedido' => $pedido, 'ctx' => 'mayorista']) }}" class="btn btn-xs btn-outline-primary">

                                            <i class="fas fa-eye"></i> Ver

                                        </a>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="text-center text-muted py-4">

                                    No hay solicitudes de minoristas en este momento.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection

