@extends('layouts.app')

@section('title', 'Detalle máquina | Fusion-Proyectos')
@section('page_title', 'Detalle de la máquina')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('maquinas-planta.index') }}">Máquinas de planta</a></li>
    <li class="breadcrumb-item active">{{ $maquina->nombre }}</li>
@endsection

@push('styles')
@include('partials.modulo-produccion-styles')
@include('maquinas_planta.partials.foto-maquina-styles')
<style>
.modulo-prod .detalle-hero {
    background: linear-gradient(135deg, #2c5530, #4a7c59);
    color: #fff;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
}
.modulo-prod .detalle-hero-img {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 10px;
    border: 3px solid rgba(255,255,255,.35);
}
</style>
@endpush

@section('content')
<div class="modulo-prod">

    <div class="detalle-hero d-flex flex-wrap justify-content-between align-items-start">
        <div class="d-flex flex-wrap align-items-start">
            @if($maquina->imagenSrc())
                <button type="button" class="btn p-0 border-0 mr-3 mb-2"
                    data-toggle="modal" data-target="#modalFotoMaquina"
                    data-src="{{ $maquina->imagenSrc() }}" data-nombre="{{ $maquina->nombre }}"
                    title="Ampliar foto">
                    @include('maquinas_planta.partials.foto-maquina', ['maquina' => $maquina, 'size' => 'hero'])
                </button>
            @endif
            <div>
            <h2 class="mb-1 font-weight-bold">
                <i class="fas fa-cogs mr-2"></i>{{ $maquina->nombre }}
            </h2>
            @if($maquina->codigo)
                <span class="badge badge-light text-dark mr-1">{{ $maquina->codigo }}</span>
            @endif
            <p class="mb-0 mt-2 opacity-90">{{ $maquina->descripcionMostrar() }}</p>
            </div>
        </div>
        <div class="text-right mt-2 mt-md-0">
            @if($maquina->activo)
                <span class="badge badge-light text-success px-3 py-2">Activa</span>
            @else
                <span class="badge badge-warning text-dark px-3 py-2">En mantenimiento</span>
            @endif
            <div class="mt-2">
                <span class="badge badge-light px-3 py-2">
                    <i class="fas fa-tractor mr-1"></i> {{ $maquina->producciones_count }} cosechas vinculadas
                </span>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('maquinas-planta.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver al catálogo
        </a>
        @include('maquinas_planta.partials.btn-toggle-estado', ['maquina' => $maquina])
        <a href="{{ route('maquinas-planta.edit', $maquina) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit mr-1"></i> Editar máquina
        </a>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-list text-success mr-1"></i>
                Cosechas con esta máquina
                <span class="badge badge-light border text-muted ml-2">{{ $producciones->total() }} registros</span>
            </h3>
            <div class="card-tools">
                @include('partials.btn-filtros-toggle', ['target' => '#filtrosCosechasMaquina'])
            </div>
        </div>

        <div id="filtrosCosechasMaquina" class="filtros-panel collapse {{ request()->hasAny(['buscar','proceso','destino','fecha_desde','fecha_hasta']) ? 'show' : '' }}">
            <form method="GET" action="{{ route('maquinas-planta.show', $maquina) }}" class="p-3 border-bottom">
                <div class="row align-items-end">
                    <div class="col-lg-3 col-md-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}"
                               placeholder="Lote o proceso…">
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Proceso</label>
                        <select name="proceso" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($procesosFiltro ?? [] as $proc)
                                <option value="{{ $proc->procesoplantaid }}" @selected((string) request('proceso') === (string) $proc->procesoplantaid)>
                                    {{ $proc->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Destino</label>
                        <select name="destino" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($destinosFiltro ?? [] as $dest)
                                <option value="{{ $dest->destinoproduccionid }}" @selected((string) request('destino') === (string) $dest->destinoproduccionid)>
                                    {{ $dest->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Desde</label>
                        <input type="date" name="fecha_desde" class="form-control form-control-sm" value="{{ request('fecha_desde') }}">
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Hasta</label>
                        <input type="date" name="fecha_hasta" class="form-control form-control-sm" value="{{ request('fecha_hasta') }}">
                    </div>
                    <div class="col-lg-1 col-md-12 d-flex flex-wrap" style="gap: 6px;">
                        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-search"></i></button>
                        <a href="{{ route('maquinas-planta.show', $maquina) }}" class="btn btn-outline-secondary btn-sm" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th>Lote</th>
                        <th>Proceso</th>
                        <th>Cantidad</th>
                        <th>Fecha</th>
                        <th>Destino</th>
                        <th style="width: 80px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($producciones as $p)
                    <tr>
                        <td><strong>{{ $p->lote->nombre ?? '—' }}</strong></td>
                        <td>{{ $p->procesoPlanta->nombre ?? '—' }}</td>
                        <td>
                            <strong class="text-success">
                                {{ number_format($p->cantidad ?? 0, 2) }} {{ $p->unidadMedida->abreviatura ?? 'kg' }}
                            </strong>
                        </td>
                        <td>{{ $p->fechacosecha ? \Carbon\Carbon::parse($p->fechacosecha)->format('d/m/Y') : '—' }}</td>
                        <td>{{ $p->destino->nombre ?? '—' }}</td>
                        <td>
                            <a href="{{ route('producciones.show', $p) }}" class="btn btn-sm btn-outline-info" title="Ver cosecha">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            @if(request()->hasAny(['buscar','proceso','destino','fecha_desde','fecha_hasta']))
                                <i class="fas fa-filter mr-1"></i> Ninguna cosecha coincide con los filtros aplicados.
                            @else
                                Aún no hay cosechas registradas con esta máquina.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($producciones->hasPages())
            <div class="card-footer">{{ $producciones->links() }}</div>
        @endif
    </div>

    @if($maquina->imagenSrc())
        @include('maquinas_planta.partials.modal-foto-maquina')
    @endif
</div>
@endsection
