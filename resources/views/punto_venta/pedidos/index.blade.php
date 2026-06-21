@extends('layouts.app')

@section('title', 'Pedidos de distribución')
@section('page_title', 'Pedidos de distribución')

@push('styles')
@include('punto_venta.partials.modulo-styles')
@endpush

@section('content')
    @if($puedeGestionarMayorista ?? false)
    <div class="alert alert-info py-2">
        <small><i class="fas fa-info-circle mr-1"></i>
        El minorista solicita producto desde su punto de venta. <strong>Revise stock</strong>, acepte la solicitud y marque el envío cuando salga del centro mayorista.</small>
    </div>
    @elseif($esMinorista)
    <div class="alert alert-info py-2">
        <small><i class="fas fa-info-circle mr-1"></i>
        Solicite producto del centro mayorista para su punto de venta. El mayorista revisará el pedido y, al recibirlo, confirme la recepción para ingresarlo a su inventario.</small>
    </div>
    @endif

    <div class="row pdv-pedidos-resumen mb-1">
        <div class="col-12 col-md-4">
            <div class="small-box bg-warning mb-0">
                <div class="inner"><h3>{{ $pendientes->count() }}</h3><p>{{ ($puedeGestionarMayorista ?? false) ? 'Pendientes de revisar' : 'En revisión mayorista' }}</p></div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="small-box bg-primary mb-0">
                <div class="inner"><h3>{{ $enRutaTiempoReal->count() }}</h3><p>En ruta (tiempo real)</p></div>
                <div class="icon"><i class="fas fa-shipping-fast"></i></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="small-box bg-success mb-0">
                <div class="inner"><h3>{{ $pedidos->where('estado', 'recibido')->count() }}</h3><p>Recibidos en PDV</p></div>
                <div class="icon"><i class="fas fa-check"></i></div>
            </div>
        </div>
    </div>

    <div class="card pdv-card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Solicitudes punto de venta ← mayorista"
            icono="fa-truck-loading"
            :registros="$pedidos->count()"
            filtros-target="#filtrosPedidosDistPanel"
            :nuevo-href="$puedeCrear ? route('punto-venta.pedidos.create') : null"
            :nuevo-text="$esMinorista ? 'Solicitar producto' : 'Nueva solicitud'"
            nuevo-icon="fa-paper-plane"
        />

        <div id="filtrosPedidosDistPanel" class="collapse {{ request()->hasAny(['q','estado_grupo','puntoventaid']) ? 'show' : '' }}">
            <div class="modulo-filtros-panel pdv-pedidos-filtros">
                <form method="GET" action="{{ route('punto-venta.pedidos.index') }}" class="form-row align-items-end">
                    <div class="form-group col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <label>Buscar</label>
                        <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Solicitud, producto o PDV…">
                    </div>
                    <div class="form-group col-lg-3 col-md-6 mb-2 mb-lg-0">
                        @include('partials.selector-catalogo', [
                            'id' => 'ped_filtro_pdv',
                            'name' => 'puntoventaid',
                            'value' => $filtroPdvId ?? '',
                            'labelSelected' => $filtroPdvNombre ?? '',
                            'endpoint' => route('catalogo-selector.puntos-venta'),
                            'title' => 'Filtrar por punto de venta',
                            'label' => 'Punto de venta',
                            'icon' => 'fa-store',
                            'searchPlaceholder' => 'Nombre, dirección o minorista…',
                            'searchLabel' => 'Buscar punto de venta',
                            'allowEmpty' => true,
                            'emptyLabel' => 'Todos los puntos',
                            'placeholderEmpty' => 'Todos los puntos',
                            'modalIcon' => 'fa-store',
                            'rowIcon' => 'fa-store',
                            'colNombre' => 'Punto de venta',
                            'colDetalle' => 'Minorista / ubicación',
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
                    <div class="form-group col-lg-3 col-md-6 mb-2 mb-lg-0 d-flex align-items-end modulo-filtros-acciones">
                        <button type="submit" class="btn btn-success btn-filtro-modulo flex-fill">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        @if(request()->except('page'))
                        <a href="{{ route('punto-venta.pedidos.index') }}" class="btn btn-outline-secondary btn-filtro-modulo flex-fill">
                            <i class="fas fa-times mr-1"></i> Limpiar
                        </a>
                        @endif
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
                            <th>Punto de venta</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Estado</th>
                            <th class="text-center" style="min-width:140px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedidos as $pedido)
                            @php
                                $det = $pedido->detalles->first();
                                $badge = \App\Support\PedidoDistribucionCatalogo::badgeEstado($pedido);
                                $unidad = $det?->insumo?->unidadMedida?->abreviatura ?? '';
                                $pendienteMayorista = \App\Support\PedidoDistribucionCatalogo::pendienteAprobacionMayorista($pedido);
                            @endphp
                            <tr class="{{ $pendienteMayorista ? 'table-warning' : '' }}">
                                <td>
                                    <span class="pdv-solicitud-pill">
                                        <i class="fas fa-file-alt"></i>{{ $pedido->numero_solicitud }}
                                    </span>
                                </td>
                                <td>{{ $pedido->puntoVenta?->nombre ?? '—' }}</td>
                                <td>{{ $det?->producto_nombre ?? '—' }}</td>
                                <td>
                                    @if($det)
                                        {{ number_format($det->cantidad, 2) }}
                                        @if($unidad)<span class="text-muted small">{{ $unidad }}</span>@endif
                                    @else — @endif
                                </td>
                                <td>
                                    <span class="pdv-estado-pill pdv-estado-pill--{{ $badge['clase'] }}">
                                        <i class="fas {{ $badge['icono'] ?? 'fa-info-circle' }}"></i>
                                        {{ $badge['etiqueta'] }}
                                    </span>
                                </td>
                                <td class="text-center text-nowrap">
                                    @if(($puedeGestionarMayorista ?? false) && $pendienteMayorista)
                                        <a href="{{ route('punto-venta.pedidos.show', $pedido) }}" class="btn btn-xs btn-warning">
                                            <i class="fas fa-clipboard-check"></i> Revisar
                                        </a>
                                    @else
                                        <a href="{{ route('punto-venta.pedidos.show', $pedido) }}" class="btn btn-xs btn-outline-primary">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No hay pedidos de distribución.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
