@extends('layouts.app')

@section('title', 'Máquinas de planta | Fusion-Proyectos')
@section('page_title', 'Máquinas de planta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Máquinas de planta</li>
@endsection

@push('styles')
@include('partials.modulo-produccion-styles')
@endpush

@section('content')
<div class="modulo-prod">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong><i class="fas fa-exclamation-triangle mr-1"></i> No se pudo guardar la máquina.</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    <div class="row mb-2">
        <div class="col-lg-4 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['total'] ?? 0 }}</h3>
                    <p>Máquinas registradas</p>
                </div>
                <div class="icon"><i class="fas fa-cogs"></i></div>
                <span class="small-box-footer">Equipo de planta</span>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $stats['con_codigo'] ?? 0 }}</h3>
                    <p>Con código</p>
                </div>
                <div class="icon"><i class="fas fa-barcode"></i></div>
                <span class="small-box-footer">Identificación interna</span>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            <div class="small-box small-box-teal">
                <div class="inner">
                    <h3>{{ $stats['activas'] ?? 0 }}</h3>
                    <p>Activas</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <span class="small-box-footer">Disponibles en registro</span>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-form-modulo elevation-1 mb-3">
        <div class="card-header">
            <h3 class="card-title mb-0"><i class="fas fa-plus-circle mr-1"></i> Nueva máquina</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('maquinas-planta.store') }}">
                @csrf
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Nombre</label>
                        <input name="nombre" class="form-control" placeholder="Nombre de máquina" value="{{ old('nombre') }}" required>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Código</label>
                        <input name="codigo" class="form-control" placeholder="EQ-001" value="{{ old('codigo') }}">
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Descripción</label>
                        <input name="descripcion" class="form-control" placeholder="Opcional" value="{{ old('descripcion') }}">
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="activoMaquina" name="activo" value="1" checked>
                            <label class="custom-control-label" for="activoMaquina">Activa</label>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-save"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-cogs text-success mr-1"></i>
                Catálogo de máquinas
                <span class="badge badge-light border text-muted badge-registros ml-2">{{ $maquinas->total() }} registros</span>
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#filtrosMaquinasPanel" title="Filtros">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </div>

        <div id="filtrosMaquinasPanel" class="filtros-panel collapse {{ request()->hasAny(['buscar','estado','con_codigo']) ? 'show' : '' }}">
            <form method="GET" action="{{ route('maquinas-planta.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}" placeholder="Nombre, código o descripción…">
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">Todas</option>
                            <option value="activa" @selected(request('estado') === 'activa')>Activas</option>
                            <option value="inactiva" @selected(request('estado') === 'inactiva')>Inactivas</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <div class="custom-control custom-checkbox mb-1">
                            <input type="checkbox" class="custom-control-input" id="filtroConCodigo" name="con_codigo" value="1" @checked(request()->boolean('con_codigo'))>
                            <label class="custom-control-label small" for="filtroConCodigo">Solo con código</label>
                        </div>
                    </div>
                    <div class="col-md-3 d-flex" style="gap: 8px;">
                        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-search mr-1"></i> Filtrar</button>
                        <a href="{{ route('maquinas-planta.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th style="width: 100px;">Estado</th>
                        <th style="width: 100px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($maquinas as $maquina)
                    <tr>
                        <td><strong>{{ $maquina->nombre }}</strong></td>
                        <td>
                            @if($maquina->codigo)
                                <span class="badge badge-light border">{{ $maquina->codigo }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $maquina->descripcion ?? '—' }}</td>
                        <td>
                            @if($maquina->activo)
                                <span class="badge badge-success">Activa</span>
                            @else
                                <span class="badge badge-secondary">Inactiva</span>
                            @endif
                        </td>
                        <td class="btn-actions">
                            <a href="{{ route('maquinas-planta.show', $maquina) }}" class="btn btn-sm btn-outline-info" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="POST" action="{{ route('maquinas-planta.destroy', $maquina) }}" class="d-inline"
                                onsubmit="return confirm('¿Eliminar esta máquina?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <i class="fas fa-cogs fa-2x mb-2 d-block"></i>
                            Sin máquinas registradas. Agrega una para seleccionar equipo durante el registro de producción.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($maquinas->hasPages())
        <div class="card-footer">{{ $maquinas->links() }}</div>
        @endif
    </div>
</div>
@endsection

