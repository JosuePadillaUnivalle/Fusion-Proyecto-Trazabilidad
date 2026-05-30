@extends('layouts.app')

@section('title', 'Cosechas | AgroFusion')
@section('page_title', 'Registro de Cosechas')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Registro de Cosechas</li>
@endsection

@push('styles')
@include('partials.modulo-produccion-styles')
@endpush

@section('content')
<div class="modulo-prod">

    <div class="row mb-2">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['total'] ?? $producciones->total() }}</h3>
                    <p>Total cosechas</p>
                </div>
                <div class="icon"><i class="fas fa-tractor"></i></div>
                <span class="small-box-footer">Registros en el sistema</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ number_format($stats['kg_total'] ?? 0, 0) }}<span style="font-size: 1rem;"> kg</span></h3>
                    <p>Kg totales</p>
                </div>
                <div class="icon"><i class="fas fa-weight-hanging"></i></div>
                <span class="small-box-footer">Volumen acumulado</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3>{{ $stats['lotes'] ?? 0 }}</h3>
                    <p>Lotes productivos</p>
                </div>
                <div class="icon"><i class="fas fa-map-marked-alt"></i></div>
                <span class="small-box-footer">Lotes con cosecha</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-purple">
                <div class="inner">
                    <h3>{{ number_format($stats['promedio'] ?? 0, 0) }}<span style="font-size: 1rem;"> kg</span></h3>
                    <p>Promedio / cosecha</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
                <span class="small-box-footer">Rendimiento medio</span>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Registro de Cosechas"
            icono="fa-tractor"
            :registros="$producciones->total()"
            filtros-target="#filtrosProduccionPanel"
            :view-toggle="true"
            view-default="cards"
            :nuevo-href="route('producciones.create')"
            nuevo-text="Nueva cosecha"
        />

        <div id="filtrosProduccionPanel" class="filtros-panel collapse {{ request()->hasAny(['buscar','loteid','destinoid','fecha_desde','fecha_hasta']) ? 'show' : '' }}">
            <form method="GET" action="{{ route('producciones.index') }}">
                <div class="row">
                    <div class="col-lg-4 col-md-6 mb-2">
                        <label class="small text-muted mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}" placeholder="Lote, cultivo, destino…">
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2">
                        <label class="small text-muted mb-1">Lote</label>
                        <select name="loteid" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($lotesFiltro ?? [] as $l)
                                <option value="{{ $l->loteid }}" @selected(request('loteid') == $l->loteid)>{{ $l->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2">
                        <label class="small text-muted mb-1">Destino</label>
                        <select name="destinoid" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($destinosFiltro ?? [] as $d)
                                <option value="{{ $d->destinoproduccionid }}" @selected(request('destinoid') == $d->destinoproduccionid)>{{ $d->nombre }}</option>
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
                    <div class="col-lg-12">
                        <x-filtros-form-actions :limpiar-url="route('producciones.index', ['filtros_abiertos' => 1])" />
                    </div>
                </div>
            </form>
        </div>

        <div id="cardView" class="card-body">
            @forelse($producciones as $p)
            <div class="item-card-prod">
                <div class="item-header">
                    <div>
                        <h5 class="mb-0 font-weight-bold">
                            <i class="fas fa-map-marker-alt text-success mr-1"></i>{{ $p->lote->nombre ?? 'Sin lote' }}
                        </h5>
                        <small class="text-muted">
                            <i class="fas fa-calendar mr-1"></i>
                            {{ $p->fechacosecha ? \Carbon\Carbon::parse($p->fechacosecha)->format('d/m/Y') : '-' }}
                        </small>
                    </div>
                    <span class="cantidad-destacada">
                        {{ number_format($p->cantidad ?? 0, 2) }} {{ $p->unidadMedida->abreviatura ?? 'kg' }}
                    </span>
                </div>
                <div class="item-body">
                    <div class="row">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <span class="info-label"><i class="fas fa-seedling mr-1"></i> Cultivo</span>
                            @if($p->lote && $p->lote->cultivo)
                                <span class="badge badge-success">{{ $p->lote->cultivo->nombre }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <span class="info-label"><i class="fas fa-warehouse mr-1"></i> Destino</span>
                            <span class="badge badge-info">{{ $p->destino->nombre ?? 'No especificado' }}</span>
                        </div>
                        <div class="col-md-4">
                            <span class="info-label"><i class="fas fa-balance-scale mr-1"></i> Unidad</span>
                            <span>{{ $p->unidadMedida->nombre ?? '—' }}</span>
                        </div>
                    </div>
                </div>
                <div class="item-footer">
                    <small class="text-muted">
                        @if($p->lote)
                            <i class="fas fa-ruler-combined mr-1"></i> {{ $p->lote->superficie ?? 0 }} ha
                        @endif
                    </small>
                    <div class="btn-actions">
                        <a href="{{ route('producciones.show', $p) }}" class="btn btn-sm btn-outline-info" title="Ver">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('producciones.edit', $p) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('producciones.destroy', $p) }}" method="POST" class="d-inline"
                            onsubmit="return confirm('¿Eliminar este registro de producción?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <i class="fas fa-tractor fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No hay producciones registradas</h5>
                <a href="{{ route('producciones.create') }}" class="btn btn-success mt-2">
                    <i class="fas fa-plus mr-1"></i> Registrar primera cosecha
                </a>
            </div>
            @endforelse
        </div>

        <div id="tableView" class="card-body p-0" style="display: none;">
            <div class="table-responsive">
                <table class="table table-modulo table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Lote</th>
                            <th>Cultivo</th>
                            <th>Cantidad</th>
                            <th>Fecha</th>
                            <th>Destino</th>
                            <th style="width: 130px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($producciones as $p)
                        <tr>
                            <td><strong>{{ $p->lote->nombre ?? '—' }}</strong></td>
                            <td>
                                @if($p->lote && $p->lote->cultivo)
                                    <span class="badge badge-success">{{ $p->lote->cultivo->nombre }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <strong class="text-success">
                                    {{ number_format($p->cantidad ?? 0, 2) }} {{ $p->unidadMedida->abreviatura ?? 'kg' }}
                                </strong>
                            </td>
                            <td>{{ $p->fechacosecha ? \Carbon\Carbon::parse($p->fechacosecha)->format('d/m/Y') : '—' }}</td>
                            <td><span class="badge badge-info">{{ $p->destino->nombre ?? '—' }}</span></td>
                            <td class="btn-actions">
                                <a href="{{ route('producciones.show', $p) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('producciones.edit', $p) }}" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('producciones.destroy', $p) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('¿Eliminar este registro de producción?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No hay producciones registradas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($producciones->hasPages())
        <div class="card-footer d-flex justify-content-center">
            {{ $producciones->links() }}
        </div>
        @endif
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
