@extends('layouts.app')

@section('title', 'Ventas | Fusion-Proyectos')
@section('page_title', 'Gestión de Ventas')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Ventas</li>
@endsection

@push('styles')
@include('partials.modulo-ventas-styles')
@endpush

@section('content')
<div class="modulo-ven">

<div class="row mb-2">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total ventas</p>
                </div>
                <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                <span class="small-box-footer">Registros en el sistema</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>Bs. {{ number_format($stats['ingresos'], 0) }}</h3>
                    <p>Ingresos totales</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                <span class="small-box-footer">Suma del período filtrado</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3>{{ number_format($stats['kg_vendidos'], 0) }}<span style="font-size:1rem;"> kg</span></h3>
                    <p>Kg vendidos</p>
                </div>
                <div class="icon"><i class="fas fa-weight-hanging"></i></div>
                <span class="small-box-footer">Volumen comercializado</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-red">
                <div class="inner">
                    <h3>Bs. {{ number_format($stats['promedio'], 0) }}</h3>
                    <p>Promedio / venta</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
                <span class="small-box-footer">Ticket promedio</span>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Listado de ventas"
            icono="fa-shopping-cart"
            :registros="$ventas->total()"
            filtros-target="#filtrosVentasPanel"
            :view-toggle="true"
            view-default="cards"
            :nuevo-href="route('ventas.create')"
            nuevo-text="Nueva venta"
            nuevo-can="ventas.create"
        />

        <div id="filtrosVentasPanel" class="filtros-panel collapse {{ request()->hasAny(['buscar','cultivo_id','fecha_desde','fecha_hasta']) ? 'show' : '' }}">
            <form method="GET" action="{{ route('ventas.index') }}">
                <div class="row align-items-end">
                    <div class="col-lg-4 col-md-6 mb-2">
                        <label class="small text-muted mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}"
                            placeholder="Cliente, lote o cultivo…">
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2">
                        <label class="small text-muted mb-1">Cultivo</label>
                        <select name="cultivo_id" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($cultivosFiltro as $cultivo)
                                <option value="{{ $cultivo->cultivoid }}" @selected(request('cultivo_id') == $cultivo->cultivoid)>{{ $cultivo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2">
                        <label class="small text-muted mb-1">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2">
                        <label class="small text-muted mb-1">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ request('fecha_hasta') }}">
                    </div>
                    <div class="col-12">
                        <x-filtros-form-actions :limpiar-url="route('ventas.index', ['filtros_abiertos' => 1])" />
                    </div>
                </div>
            </form>
        </div>

        @if(request()->hasAny(['buscar','cultivo_id','fecha_desde','fecha_hasta']))
        <div class="px-3 pt-2 pb-0 filtros-activos">
            @if(request('buscar'))<span class="badge badge-light border mr-1"><i class="fas fa-search mr-1"></i>{{ request('buscar') }}</span>@endif
            @if(request('cultivo_id'))
                @php $cSel = $cultivosFiltro->firstWhere('cultivoid', (int) request('cultivo_id')); @endphp
                @if($cSel)<span class="badge badge-success mr-1">{{ $cSel->nombre }}</span>@endif
            @endif
            @if(request('fecha_desde'))<span class="badge badge-info mr-1">Desde {{ \Carbon\Carbon::parse(request('fecha_desde'))->format('d/m/Y') }}</span>@endif
            @if(request('fecha_hasta'))<span class="badge badge-info mr-1">Hasta {{ \Carbon\Carbon::parse(request('fecha_hasta'))->format('d/m/Y') }}</span>@endif
        </div>
        @endif

        <div class="card-body">
            <div id="cardView">
                @forelse($ventas as $v)
                    @php $total = ($v->cantidad ?? 0) * ($v->preciounitario ?? 0); @endphp
                    <div class="item-card-ven">
                        <div class="item-header">
                            <div>
                                <h5><i class="fas fa-user text-muted mr-1"></i>{{ $v->cliente ?? 'Sin cliente' }}</h5>
                                <small class="text-muted">
                                    <i class="far fa-calendar-alt mr-1"></i>
                                    {{ $v->fechaventa ? \Carbon\Carbon::parse($v->fechaventa)->format('d/m/Y') : '—' }}
                                    · #{{ $v->ventaid }}
                                </small>
                            </div>
                            <span class="item-total">Bs. {{ number_format($total, 2) }}</span>
                        </div>
                        <div class="item-body">
                            <div class="info-grid">
                                <div>
                                    <span class="info-label">Producción</span>
                                    <strong>
                                        @if($v->produccion)
                                            {{ $v->produccion->lote->nombre ?? 'Lote' }}
                                            @if($v->produccion->lote?->cultivo)
                                                <span class="badge badge-success ml-1">{{ $v->produccion->lote->cultivo->nombre }}</span>
                                            @endif
                                        @else — @endif
                                    </strong>
                                </div>
                                <div>
                                    <span class="info-label">Cantidad</span>
                                    <strong>{{ number_format($v->cantidad ?? 0, 2) }} {{ $v->unidadMedida->abreviatura ?? 'kg' }}</strong>
                                </div>
                                <div>
                                    <span class="info-label">Precio unit.</span>
                                    <strong>Bs. {{ number_format($v->preciounitario ?? 0, 2) }}</strong>
                                </div>
                            </div>
                        </div>
                        <div class="item-footer">
                            <small class="text-muted">
                                @if($v->produccion?->fechacosecha)
                                    <i class="fas fa-tractor mr-1"></i>Cosecha: {{ \Carbon\Carbon::parse($v->produccion->fechacosecha)->format('d/m/Y') }}
                                @endif
                            </small>
                            <div class="btn-actions">
                                <a href="{{ route('ventas.show', $v) }}" class="btn btn-sm btn-info" title="Ver detalle"><i class="fas fa-eye"></i></a>
                                @can('ventas.update')
                                <a href="{{ route('ventas.edit', $v) }}" class="btn btn-sm btn-warning" title="Editar"><i class="fas fa-edit"></i></a>
                                @endcan
                                @can('ventas.delete')
                                <form action="{{ route('ventas.destroy', $v) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar esta venta? Se devolverá el stock al almacén.')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-shopping-cart fa-3x mb-3 text-light"></i>
                        <p class="mb-2">No hay ventas con estos criterios.</p>
                        @can('ventas.create')
                        <a href="{{ route('ventas.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus mr-1"></i> Registrar venta</a>
                        @endcan
                    </div>
                @endforelse
            </div>

            <div id="tableView" style="display:none;">
                <div class="table-responsive">
                    <table class="table table-modulo table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cliente</th>
                                <th>Producción</th>
                                <th>Cultivo</th>
                                <th class="text-right">Cantidad</th>
                                <th class="text-right">P. unit.</th>
                                <th class="text-right">Total</th>
                                <th>Fecha</th>
                                <th class="text-center" style="width:130px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ventas as $v)
                                @php $total = ($v->cantidad ?? 0) * ($v->preciounitario ?? 0); @endphp
                                <tr>
                                    <td class="text-muted">#{{ $v->ventaid }}</td>
                                    <td><strong>{{ $v->cliente ?? '—' }}</strong></td>
                                    <td>{{ $v->produccion->lote->nombre ?? '—' }}</td>
                                    <td>
                                        @if($v->produccion?->lote?->cultivo)
                                            <span class="badge badge-success">{{ $v->produccion->lote->cultivo->nombre }}</span>
                                        @else — @endif
                                    </td>
                                    <td class="text-right">{{ number_format($v->cantidad ?? 0, 2) }} {{ $v->unidadMedida->abreviatura ?? 'kg' }}</td>
                                    <td class="text-right">Bs. {{ number_format($v->preciounitario ?? 0, 2) }}</td>
                                    <td class="text-right"><strong class="text-success">Bs. {{ number_format($total, 2) }}</strong></td>
                                    <td>{{ $v->fechaventa ? \Carbon\Carbon::parse($v->fechaventa)->format('d/m/Y') : '—' }}</td>
                                    <td class="text-center btn-actions">
                                        <a href="{{ route('ventas.show', $v) }}" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>
                                        @can('ventas.update')
                                        <a href="{{ route('ventas.edit', $v) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center py-4 text-muted">No hay ventas registradas</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        @if($ventas->hasPages())
        <div class="card-footer d-flex justify-content-center">
            {{ $ventas->links() }}
        </div>
        @endif
    </div>

    <div class="guia-venta mt-2">
        <strong><i class="fas fa-lightbulb mr-1"></i> Guía rápida</strong>
        Las ventas descuentan stock del almacén automáticamente. Usa los filtros para buscar por cliente, cultivo o fechas.
        El reporte analítico está en <a href="{{ route('reportes.ventas') }}">Reportes → Ventas</a>.
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    $('#btnCardView').on('click', function () {
        $(this).addClass('active').siblings().removeClass('active');
        $('#cardView').show();
        $('#tableView').hide();
    });
    $('#btnTableView').on('click', function () {
        $(this).addClass('active').siblings().removeClass('active');
        $('#tableView').show();
        $('#cardView').hide();
    });
});
</script>
@endpush
