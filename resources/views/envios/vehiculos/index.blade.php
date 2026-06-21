@extends('layouts.app')

@section('title', 'Vehículos | AgroFusion')
@section('page_title', 'Vehículos')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.seguimiento') }}">Envíos</a></li>
    <li class="breadcrumb-item active">Vehículos</li>
@endsection

@php
    $estadoSvc = app(\App\Services\VehiculoFlotaEstadoService::class);
    $mapaEnRuta = $mapaEnRuta ?? $estadoSvc->mapaEnRuta();
    $filtroActivo = request('estado', '');
@endphp

@push('styles')
@include('partials.modulo-envios-styles')
<style>
.page-flota-veh .flota-kpis { margin-bottom: 1rem; }
.page-flota-veh .flota-kpi {
    border-radius: 10px;
    padding: .85rem 1rem;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 78px;
    box-shadow: 0 2px 10px rgba(0,0,0,.08);
    text-decoration: none;
    transition: transform .15s ease, box-shadow .15s ease;
}
.page-flota-veh .flota-kpi:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.12); color: #fff; text-decoration: none; }
.page-flota-veh .flota-kpi.is-active { outline: 3px solid rgba(255,255,255,.75); outline-offset: -3px; }
.page-flota-veh .flota-kpi h3 { margin: 0; font-size: 1.65rem; font-weight: 800; line-height: 1; }
.page-flota-veh .flota-kpi p { margin: 0; font-size: .8rem; opacity: .92; }
.page-flota-veh .flota-kpi i { font-size: 2rem; opacity: .35; }
.page-flota-veh .flota-kpi--total { background: linear-gradient(135deg, #fd7e14, #e67e22); }
.page-flota-veh .flota-kpi--operativo { background: linear-gradient(135deg, #2c5530, #4a7c59); }
.page-flota-veh .flota-kpi--mant { background: linear-gradient(135deg, #f39c12, #e0a800); color: #1a252f; }
.page-flota-veh .flota-kpi--ruta { background: linear-gradient(135deg, #007bff, #17a2b8); }

.page-flota-veh .veh-flota-table thead th {
    background: #f4f6f8;
    border-bottom: 2px solid #e2e8f0;
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #64748b;
    font-weight: 700;
    white-space: nowrap;
    padding: .75rem .85rem;
}
.page-flota-veh .veh-flota-table tbody td {
    vertical-align: middle;
    padding: .9rem .85rem;
    border-top: 1px solid #f1f5f9;
    font-size: .875rem;
}
.page-flota-veh .veh-flota-table tbody tr:hover { background: #f8fafc; }
.page-flota-veh .veh-placa {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    font-weight: 800;
    letter-spacing: .03em;
    color: #1e293b;
}
.page-flota-veh .veh-placa__icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    background: #eef2f7;
    color: #475569;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .9rem;
}
.page-flota-veh .veh-marca { font-weight: 600; color: #334155; }
.page-flota-veh .veh-sub { font-size: .78rem; color: #94a3b8; }
.page-flota-veh .veh-cap-chip {
    display: inline-block;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: .15rem .45rem;
    font-size: .75rem;
    color: #475569;
    margin: 1px 2px 1px 0;
}
.page-flota-veh .veh-transporte-chip {
    font-size: .7rem;
    font-weight: 600;
    margin: 2px 2px 0 0;
}
.page-flota-veh .crud-acciones--inline { gap: 5px; }
.page-flota-veh .crud-acciones--inline .btn { padding: .28rem .5rem; }
.page-flota-veh .veh-acciones-grupo { gap: 5px; }
.page-flota-veh .veh-acciones-grupo .btn { padding: .28rem .5rem; line-height: 1.2; }
.page-flota-veh .veh-col-acciones { width: 168px; white-space: nowrap; }
</style>
@endpush

@section('content')
<div class="modulo-env page-flota-veh page-env-vehiculos">
    @include('envios.partials.alertas')

    <div class="row flota-kpis">
        <div class="col-lg-3 col-6 mb-2">
            <a href="{{ route('envios.vehiculos') }}" class="flota-kpi flota-kpi--total w-100 {{ $filtroActivo === '' ? 'is-active' : '' }}">
                <div><h3>{{ $stats['total'] ?? 0 }}</h3><p>Total en flota</p></div>
                <i class="fas fa-truck"></i>
            </a>
        </div>
        <div class="col-lg-3 col-6 mb-2">
            <a href="{{ route('envios.vehiculos', ['estado' => 'operativo']) }}" class="flota-kpi flota-kpi--operativo w-100 {{ $filtroActivo === 'operativo' ? 'is-active' : '' }}">
                <div><h3>{{ $stats['operativo'] ?? 0 }}</h3><p>Operativos</p></div>
                <i class="fas fa-check-circle"></i>
            </a>
        </div>
        <div class="col-lg-3 col-6 mb-2">
            <a href="{{ route('envios.vehiculos', ['estado' => 'mantenimiento']) }}" class="flota-kpi flota-kpi--mant w-100 {{ $filtroActivo === 'mantenimiento' ? 'is-active' : '' }}">
                <div><h3>{{ $stats['mantenimiento'] ?? 0 }}</h3><p>En mantenimiento</p></div>
                <i class="fas fa-wrench"></i>
            </a>
        </div>
        <div class="col-lg-3 col-6 mb-2">
            <a href="{{ route('envios.vehiculos', ['estado' => 'en_ruta']) }}" class="flota-kpi flota-kpi--ruta w-100 {{ $filtroActivo === 'en_ruta' ? 'is-active' : '' }}">
                <div><h3>{{ $stats['en_ruta'] ?? 0 }}</h3><p>En ruta ahora</p></div>
                <i class="fas fa-route"></i>
            </a>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <x-modulo-index-header
            titulo="Flota logística"
            icono="fa-truck-moving"
            :registros="$vehiculos->total()"
            filtros-target="#filtrosVehiculos"
            :nuevo-href="route('envios.vehiculos.create')"
            nuevo-text="Nuevo vehículo"
            nuevo-can="vehiculos.create"
        />

        <div id="filtrosVehiculos" class="filtros-panel collapse {{ request()->hasAny(['buscar','estado','ambito_flota']) ? 'show' : '' }}">
            <form method="GET" action="{{ route('envios.vehiculos') }}">
                <div class="row align-items-end">
                    <div class="col-lg-5 col-md-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Buscar</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            </div>
                            <input type="text" name="buscar" class="form-control" value="{{ request('buscar') }}" placeholder="Placa, marca o modelo">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Categoría</label>
                        <select name="ambito_flota" class="form-control form-control-sm">
                            <option value="">Todas</option>
                            @foreach(\App\Support\TransportistaFlotaCatalogo::etiquetas() as $valor => $etiqueta)
                            <option value="{{ $valor }}" @selected(request('ambito_flota') === $valor)>{{ \App\Support\TransportistaFlotaCatalogo::categoriaCorta($valor) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($estadosFiltro ?? [] as $valor => $etiqueta)
                                <option value="{{ $valor }}" @selected(request('estado') === $valor)>{{ $etiqueta }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <x-filtros-form-actions :limpiar-url="route('envios.vehiculos', ['filtros_abiertos' => 1])" />
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table veh-flota-table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Vehículo</th>
                        <th>Categoría</th>
                        <th>Tipo / transporte</th>
                        <th>Estado</th>
                        <th>Capacidad</th>
                        <th class="text-center veh-col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehiculos as $v)
                    @php
                        $capSvc = app(\App\Services\TransporteCapacidadService::class);
                        $cap = $capSvc->capacidadEfectiva($v);
                        $ambitoV = $v->ambito_flota ?? \App\Support\TransportistaFlotaCatalogo::AGRICOLA;
                        $marcaModelo = trim(($v->marca ?? '').' '.($v->modelo ?? ''));
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('envios.vehiculos.show', $v) }}" class="veh-placa text-decoration-none">
                                <span class="veh-placa__icon"><i class="fas fa-truck"></i></span>
                                <span>
                                    {{ $v->placa }}
                                    @if($marcaModelo)
                                    <span class="d-block veh-sub">{{ $marcaModelo }}@if($v->anio) · {{ $v->anio }}@endif</span>
                                    @endif
                                </span>
                            </a>
                        </td>
                        <td>
                            <span class="badge {{ \App\Support\TransportistaFlotaCatalogo::badgeClase($ambitoV) }} badge-estado">
                                {{ \App\Support\TransportistaFlotaCatalogo::categoriaCorta($ambitoV) }}
                            </span>
                        </td>
                        <td>
                            <span class="veh-marca d-block">{{ $v->tipoVehiculo?->nombre ?? '—' }}</span>
                            @php
                                $tt = $v->tiposTransporteEfectivos()->first();
                            @endphp
                            @if($tt)
                                @include('envios.vehiculos.partials.transporte-badge', [
                                    'nombre' => $tt->nombre,
                                    'codigo' => $tt->codigo,
                                    'size' => 'sm',
                                ])
                            @else
                                <span class="veh-sub">Sin tipo de transporte</span>
                            @endif
                        </td>
                        <td>
                            <span class="{{ $estadoSvc->badgeClaseVisual($v, $mapaEnRuta) }}">
                                {{ $estadoSvc->etiquetaVisual($v, $mapaEnRuta) }}
                            </span>
                        </td>
                        <td>
                            @if($cap['kg'] > 0 || $cap['m3'] > 0)
                                @if($cap['kg'] > 0)<span class="veh-cap-chip">{{ number_format($cap['kg'], 0) }} kg</span>@endif
                                @if($cap['m3'] > 0)<span class="veh-cap-chip">{{ number_format($cap['m3'], 1) }} m³</span>@endif
                                @if($cap['licencia_requerida'])<span class="veh-cap-chip">Lic. {{ $cap['licencia_requerida'] }}</span>@endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center veh-col-acciones">
                            <div class="veh-acciones-grupo d-inline-flex align-items-center flex-nowrap justify-content-center">
                                @include('envios.partials.crud-acciones', [
                                    'showRoute' => route('envios.vehiculos.show', $v),
                                    'editRoute' => route('envios.vehiculos.edit', $v),
                                    'destroyRoute' => route('envios.vehiculos.destroy', $v),
                                    'entityName' => 'el vehículo '.$v->placa,
                                    'readPermission' => 'vehiculos.view',
                                    'updatePermission' => 'vehiculos.update',
                                    'deletePermission' => 'vehiculos.delete',
                                ])
                                @can('vehiculos.update')
                                    @include('envios.vehiculos.partials.btn-toggle-mantenimiento', [
                                        'vehiculo' => $v,
                                        'compact' => true,
                                    ])
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-truck fa-2x mb-2 text-light d-block"></i>
                            No hay vehículos que coincidan.
                            <a href="{{ route('envios.vehiculos.create') }}" class="d-block mt-2">Registrar vehículo</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vehiculos->hasPages())
            <div class="card-footer bg-white">{{ $vehiculos->links() }}</div>
        @endif

        @if(($tiposCatalogo ?? collect())->isNotEmpty())
            @include('envios.vehiculos.partials.catalogo-flota-trigger', ['tipos' => $tiposCatalogo])
        @endif
    </div>
</div>

@include('partials.modal-confirmar-accion')
@endsection
