@extends('layouts.app')

@section('title', 'Gestión de usuarios | Fusion-Proyectos')
@section('page_title', 'Gestión de usuarios')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Gestión de usuarios</li>
@endsection

@push('styles')
@include('partials.modulo-usuarios-styles')
@endpush

@section('content')
<div class="modulo-usu">

<div class="row mb-2">
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-green">
                <div class="inner">
                    <h3>{{ $stats['total'] ?? 0 }}</h3>
                    <p>Total usuarios</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
                <span class="small-box-footer">Cuentas registradas</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-blue">
                <div class="inner">
                    <h3>{{ $stats['activos'] ?? 0 }}</h3>
                    <p>Activos</p>
                </div>
                <div class="icon"><i class="fas fa-user-check"></i></div>
                <span class="small-box-footer">Pueden iniciar sesión</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-yellow">
                <div class="inner">
                    <h3>{{ $stats['inactivos'] ?? 0 }}</h3>
                    <p>Inactivos</p>
                </div>
                <div class="icon"><i class="fas fa-user-slash"></i></div>
                <span class="small-box-footer">Acceso suspendido</span>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box small-box-purple">
                <div class="inner">
                    <h3>{{ $stats['roles'] ?? 0 }}</h3>
                    <p>Roles</p>
                </div>
                <div class="icon"><i class="fas fa-user-shield"></i></div>
                <span class="small-box-footer">Perfiles del sistema</span>
            </div>
        </div>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1 mb-4">
        <x-modulo-index-header
            titulo="Usuarios del sistema"
            icono="fa-users"
            :registros="$usuarios->total()"
            filtros-target="#filtrosUsuariosPanel"
            :nuevo-href="route('gestion.create')"
            nuevo-text="Nuevo usuario"
            nuevo-icon="fa-user-plus"
            nuevo-can="usuarios.create"
        />

        <div id="filtrosUsuariosPanel" class="filtros-panel collapse {{ request()->hasAny(['buscar','rol','estado']) ? 'show' : '' }}">
            <form method="GET" action="{{ route('gestion.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Buscar</label>
                        <input type="text" name="buscar" class="form-control form-control-sm" value="{{ request('buscar') }}"
                            placeholder="Nombre, correo, usuario o teléfono…">
                    </div>
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Rol</label>
                        <select name="rol" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id }}" @selected(request('rol') == $rol->id)>{{ $rol->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="small text-muted mb-1">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="activo" @selected(request('estado') === 'activo')>Activos</option>
                            <option value="inactivo" @selected(request('estado') === 'inactivo')>Inactivos</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <x-filtros-form-actions :limpiar-url="route('gestion.index', ['filtros_abiertos' => 1])" />
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 55px">#</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th style="width: 140px">Rol</th>
                        <th style="width: 90px" class="text-center">Estado</th>
                        <th style="width: 130px" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                    <tr>
                        <td class="text-muted font-weight-bold">#{{ $usuario->usuarioid }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle {{ $usuario->activo ? 'activo' : 'inactivo' }} mr-2">
                                    {{ strtoupper(substr($usuario->nombre, 0, 1).substr($usuario->apellido, 0, 1)) }}
                                </div>
                                <div>
                                    <strong>{{ $usuario->nombre }} {{ $usuario->apellido }}</strong>
                                    <div class="small text-muted">{{ '@'.$usuario->nombreusuario }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $usuario->email }}</td>
                        <td class="text-muted">{{ $usuario->telefono ?: '—' }}</td>
                        <td>
                            @forelse($usuario->roles as $role)
                                <span class="badge badge-light border">{{ $role->name }}</span>
                            @empty
                                <span class="badge badge-secondary">Sin rol</span>
                            @endforelse
                        </td>
                        <td class="text-center">
                            @if($usuario->activo)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td class="text-center btn-actions">
                            @can('usuarios.view')
                            <a href="{{ route('gestion.show', $usuario) }}" class="btn btn-sm btn-outline-info" title="Ver detalles">
                                <i class="fas fa-eye"></i>
                            </a>
                            @endcan
                            @can('usuarios.update')
                            <a href="{{ route('gestion.edit', $usuario) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            @can('usuarios.delete')
                            <form action="{{ route('gestion.usuario.destroy', $usuario) }}" method="POST" class="d-inline"
                                onsubmit="return confirm('¿Eliminar este usuario?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-users fa-3x mb-3 d-block"></i>
                            No hay usuarios que coincidan con los filtros.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($usuarios->hasPages())
        <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">
            <small class="text-muted mb-2 mb-md-0">
                Mostrando {{ $usuarios->firstItem() }}–{{ $usuarios->lastItem() }} de {{ $usuarios->total() }}
            </small>
            {{ $usuarios->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
