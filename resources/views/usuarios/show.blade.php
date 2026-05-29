@extends('layouts.app')

@section('title', 'Detalle de usuario | Fusion-Proyectos')
@section('page_title', 'Detalle de usuario')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('gestion.index') }}">Gestión de usuarios</a></li>
    <li class="breadcrumb-item active">{{ $usuario->nombre }} {{ $usuario->apellido }}</li>
@endsection

@push('styles')
@include('partials.modulo-usuarios-styles')
<style>
.modulo-usu .detalle-hero {
    background: linear-gradient(135deg, #2c5530, #4a7c59);
    color: #fff;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
}
.modulo-usu .detalle-hero .avatar-lg {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
}
</style>
@endpush

@section('content')
<div class="modulo-usu">

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    <div class="detalle-hero d-flex flex-wrap align-items-center justify-content-between">
        <div class="d-flex align-items-center mb-2 mb-md-0">
            <div class="avatar-lg mr-3">
                {{ strtoupper(substr($usuario->nombre, 0, 1).substr($usuario->apellido, 0, 1)) }}
            </div>
            <div>
                <h2 class="mb-1 font-weight-bold">{{ $usuario->nombre }} {{ $usuario->apellido }}</h2>
                <p class="mb-0 opacity-90">{{ '@'.$usuario->nombreusuario }} · {{ $usuario->email }}</p>
            </div>
        </div>
        <div class="text-md-right">
            @if($usuario->activo)
                <span class="badge badge-light text-success px-3 py-2">Activo</span>
            @else
                <span class="badge badge-light text-secondary px-3 py-2">Inactivo</span>
            @endif
            @forelse($usuario->roles as $role)
                <span class="badge badge-light text-dark px-3 py-2 ml-1">{{ $role->name }}</span>
            @empty
                <span class="badge badge-light text-muted px-3 py-2 ml-1">Sin rol</span>
            @endforelse
        </div>
    </div>

    <div class="mb-3 d-flex flex-wrap" style="gap: 8px;">
        <a href="{{ route('gestion.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Listado
        </a>
        @can('usuarios.update')
        <a href="{{ route('gestion.edit', $usuario) }}" class="btn btn-warning btn-sm">
            <i class="fas fa-edit mr-1"></i> Editar
        </a>
        @endcan
    </div>

    <div class="row mb-3">
        <div class="col-md-4 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['lotes'] ?? 0 }}</h3>
                    <p>Lotes a cargo</p>
                </div>
                <div class="icon"><i class="fas fa-map-marked-alt"></i></div>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $stats['actividades'] ?? 0 }}</h3>
                    <p>Actividades</p>
                </div>
                <div class="icon"><i class="fas fa-tasks"></i></div>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="small-box small-box-purple">
                <div class="inner">
                    <h3>#{{ $usuario->usuarioid }}</h3>
                    <p>ID en sistema</p>
                </div>
                <div class="icon"><i class="fas fa-id-badge"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card card-modulo-main elevation-1 mb-3">
                <div class="card-header">
                    <h3 class="card-title mb-0"><i class="fas fa-id-card text-success mr-1"></i> Datos de la cuenta</h3>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-5 text-muted">Teléfono</dt>
                        <dd class="col-sm-7">{{ $usuario->telefono ?: '—' }}</dd>
                        <dt class="col-sm-5 text-muted">Registro</dt>
                        <dd class="col-sm-7">{{ $usuario->fecharegistro ? $usuario->fecharegistro->format('d/m/Y H:i') : '—' }}</dd>
                        <dt class="col-sm-5 text-muted">Último acceso</dt>
                        <dd class="col-sm-7">{{ $usuario->ultimologin ? $usuario->ultimologin->format('d/m/Y H:i') : '—' }}</dd>
                        <dt class="col-sm-5 text-muted">Almacén vinculado</dt>
                        <dd class="col-sm-7">{{ $usuario->almacen->nombre ?? '—' }}</dd>
                        @if($usuario->informacionadicional)
                        <dt class="col-sm-5 text-muted">Notas</dt>
                        <dd class="col-sm-7">{{ $usuario->informacionadicional }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card card-modulo-main elevation-1">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-map-marker-alt text-success mr-1"></i>
                        Lotes como responsable
                    </h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-modulo table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Lote</th>
                                <th>Cultivo</th>
                                <th>Estado</th>
                                <th style="width: 60px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lotesRecientes as $lote)
                            <tr>
                                <td><strong>{{ $lote->nombre }}</strong></td>
                                <td>{{ $lote->cultivo->nombre ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-light border text-capitalize">{{ $lote->estadoTipo->nombre ?? '—' }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('lotes.show', $lote) }}" class="btn btn-sm btn-outline-info" title="Ver lote">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Este usuario no tiene lotes asignados.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
