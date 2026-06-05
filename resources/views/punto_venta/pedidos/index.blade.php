@extends('layouts.app')

@section('title', 'Pedidos de distribución')
@section('page_title', 'Pedidos de distribución')

@push('styles')
@include('punto_venta.partials.modulo-styles')
@endpush

@section('content')
    @if($puedeGestionarPlanta)
    <div class="alert alert-info py-2">
        <small><i class="fas fa-info-circle mr-1"></i>
        El minorista solicita producto desde su punto de venta. <strong>Revise stock</strong>, acepte la solicitud y marque el envío cuando salga de planta.</small>
    </div>
    @elseif($esMinorista)
    <div class="alert alert-info py-2">
        <small><i class="fas fa-info-circle mr-1"></i>
        Solicite producto de planta para su punto de venta. Planta revisará el pedido y, al recibirlo, confirme la recepción para ingresarlo a su inventario.</small>
    </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner"><h3>{{ $pendientes->count() }}</h3><p>{{ $puedeGestionarPlanta ? 'Pendientes de revisar' : 'En revisión planta' }}</p></div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-primary">
                <div class="inner"><h3>{{ $pedidos->where('estado', 'en_transito')->count() }}</h3><p>En tránsito</p></div>
                <div class="icon"><i class="fas fa-shipping-fast"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner"><h3>{{ $pedidos->where('estado', 'recibido')->count() }}</h3><p>Recibidos en PDV</p></div>
                <div class="icon"><i class="fas fa-check"></i></div>
            </div>
        </div>
    </div>

    <div class="card pdv-card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Solicitudes punto de venta ← planta"
            icono="fa-truck-loading"
            :registros="$pedidos->count()"
            filtros-target="#filtrosPedidosDistPanel"
            :nuevo-href="$puedeCrear ? route('punto-venta.pedidos.create') : null"
            nuevo-text="Nueva solicitud"
            nuevo-can="pedidos_distribucion.create"
        />

        <div id="filtrosPedidosDistPanel" class="collapse {{ request()->hasAny(['q','estado','puntoventaid']) ? 'show' : '' }}">
            @include('partials.modulo-filtros-form', [
                'action' => route('punto-venta.pedidos.index'),
                'campos' => [
                    ['name' => 'q', 'label' => 'Buscar', 'placeholder' => 'Solicitud, producto o PDV…', 'col' => 'col-md-4'],
                    ['name' => 'puntoventaid', 'label' => 'Punto de venta', 'type' => 'select', 'col' => 'col-md-3',
                        'options' => $puntosVenta->pluck('nombre', 'puntoventaid')->all()],
                    ['name' => 'estado', 'label' => 'Estado', 'type' => 'select', 'col' => 'col-md-3',
                        'options' => \App\Support\PedidoDistribucionCatalogo::etiquetasEstado()],
                ],
            ])
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
                                $pendientePlanta = \App\Support\PedidoDistribucionCatalogo::pendienteAprobacionPlanta($pedido);
                            @endphp
                            <tr class="{{ $pendientePlanta ? 'table-warning' : '' }}">
                                <td><span class="badge badge-dark">{{ $pedido->numero_solicitud }}</span></td>
                                <td>{{ $pedido->puntoVenta?->nombre ?? '—' }}</td>
                                <td>{{ $det?->producto_nombre ?? '—' }}</td>
                                <td>
                                    @if($det)
                                        {{ number_format($det->cantidad, 2) }}
                                        @if($unidad)<span class="text-muted small">{{ $unidad }}</span>@endif
                                    @else — @endif
                                </td>
                                <td><span class="badge badge-{{ $badge['clase'] }}">{{ $badge['etiqueta'] }}</span></td>
                                <td class="text-center text-nowrap">
                                    @if($puedeGestionarPlanta && $pendientePlanta)
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
