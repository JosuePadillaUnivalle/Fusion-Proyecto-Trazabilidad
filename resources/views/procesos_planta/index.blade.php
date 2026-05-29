@extends('layouts.app')

@section('title', 'Procesos de planta | Fusion-Proyectos')
@section('page_title', 'Procesos de planta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Procesos de planta</li>
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
        <strong><i class="fas fa-exclamation-triangle mr-1"></i> No se pudo guardar el proceso.</strong>
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
                    <p>Procesos registrados</p>
                </div>
                <div class="icon"><i class="fas fa-industry"></i></div>
                <span class="small-box-footer">Catálogo completo</span>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box small-box-teal">
                <div class="inner">
                    <h3>{{ $stats['activos'] ?? 0 }}</h3>
                    <p>Activos</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <span class="small-box-footer">Disponibles en registro</span>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3>{{ $stats['inactivos'] ?? 0 }}</h3>
                    <p>Inactivos</p>
                </div>
                <div class="icon"><i class="fas fa-pause-circle"></i></div>
                <span class="small-box-footer">Fuera de uso</span>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-form-modulo elevation-1 mb-3">
        <div class="card-header">
            <h3 class="card-title mb-0"><i class="fas fa-plus-circle mr-1"></i> Nuevo proceso</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('procesos-planta.store') }}">
                @csrf
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Nombre</label>
                        <input name="nombre" class="form-control" placeholder="Ej. Lavado, Secado…" value="{{ old('nombre') }}" required>
                    </div>
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Descripción</label>
                        <input name="descripcion" class="form-control" placeholder="Opcional" value="{{ old('descripcion') }}">
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="activoProceso" name="activo" value="1" checked>
                            <label class="custom-control-label" for="activoProceso">Activo</label>
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
                <i class="fas fa-industry text-success mr-1"></i>
                Catálogo de procesos
                <span class="badge badge-light border text-muted badge-registros ml-2">{{ $procesos->total() }} registros</span>
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-toggle="collapse" data-target="#filtrosProcesosPanel" title="Filtros">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </div>

        <div id="filtrosProcesosPanel" class="filtros-panel collapse {{ request()->hasAny(['buscar','estado']) ? 'show' : '' }}">
            <form method="GET" action="{{ route('procesos-planta.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}" placeholder="Nombre o descripción…">
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="activo" @selected(request('estado') === 'activo')>Activos</option>
                            <option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivos</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex" style="gap: 8px;">
                        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-search mr-1"></i> Filtrar</button>
                        <a href="{{ route('procesos-planta.index') }}" class="btn btn-outline-secondary btn-sm">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th style="width: 100px;">Estado</th>
                        <th style="width: 100px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($procesos as $proceso)
                    <tr>
                        <td><strong>{{ $proceso->nombre }}</strong></td>
                        <td class="text-muted">{{ $proceso->descripcion ?? '—' }}</td>
                        <td>
                            @if($proceso->activo)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td class="btn-actions">
                            <a href="{{ route('procesos-planta.show', $proceso) }}" class="btn btn-sm btn-outline-info" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </a>
                            <form method="POST" action="{{ route('procesos-planta.destroy', $proceso) }}" class="d-inline"
                                onsubmit="return confirm('¿Eliminar este proceso?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr id="filaVaciaProcesos">
                        <td colspan="4" class="text-center text-muted py-4">
                            <i class="fas fa-industry fa-2x mb-2 d-block"></i>
                            Sin procesos registrados. Agrega uno para vincularlo en el registro de producción.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($procesos->hasPages())
        <div class="card-footer">{{ $procesos->links() }}</div>
        @endif
    </div>
</div>
@endsection

