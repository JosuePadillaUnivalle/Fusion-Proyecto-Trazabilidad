@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-users-cog mr-2"></i>
                    Gestión de Usuarios
                </h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">Inicio</a></li>
                    <li class="breadcrumb-item active">Usuarios</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        {{-- MENSAJES DE ALERTA --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="icon fas fa-check mr-2"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <h5><i class="icon fas fa-ban mr-2"></i>Error</h5>
                <ul class="mb-0 pl-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ESTADÍSTICAS --}}
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $usuarios->total() }}</h3>
                        <p>Total Usuarios</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $usuarios->where('activo', 1)->count() }}</h3>
                        <p>Usuarios Activos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $usuarios->where('activo', 0)->count() }}</h3>
                        <p>Usuarios Inactivos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-times"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $roles->count() }}</h3>
                        <p>Roles Disponibles</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-tag"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLA DE USUARIOS --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>
                    Listado de Usuarios
                </h3>
                @can('usuarios.create')
                    <div class="card-tools">
                        <a href="{{ route('gestion.index') }}#userForm" class="btn btn-success btn-sm">
                            <i class="fas fa-user-plus mr-1"></i>
                            Nuevo Usuario
                        </a>
                    </div>
                @endcan
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped m-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 60px">#ID</th>
                                <th>
                                    <i class="fas fa-user mr-1"></i>Usuario
                                </th>
                                <th>
                                    <i class="fas fa-envelope mr-1"></i>Email
                                </th>
                                <th>
                                    <i class="fas fa-phone mr-1"></i>Teléfono
                                </th>
                                <th style="width: 150px">
                                    <i class="fas fa-user-shield mr-1"></i>Rol
                                </th>
                                <th style="width: 80px" class="text-center">
                                    <i class="fas fa-toggle-on mr-1"></i>Estado
                                </th>
                                <th style="width: 150px" class="text-center">
                                    <i class="fas fa-cog mr-1"></i>Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuarios as $usuario)
                                <tr>
                                    <td class="font-weight-bold text-primary">
                                        #{{ $usuario->usuarioid }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar mr-3">
                                                <div class="avatar-circle {{ $usuario->activo ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ strtoupper(substr($usuario->nombre, 0, 1) . substr($usuario->apellido, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">
                                                    {{ $usuario->nombre }} {{ $usuario->apellido }}
                                                </div>
                                                <small class="text-muted">
                                                    {{ '@' . $usuario->nombreusuario }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <i class="fas fa-envelope text-muted mr-1"></i>
                                        {{ $usuario->email }}
                                    </td>
                                    <td>
                                        @if($usuario->telefono)
                                            <i class="fas fa-phone text-muted mr-1"></i>
                                            {{ $usuario->telefono }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($usuario->roles->isNotEmpty())
                                            @foreach($usuario->roles as $role)
                                                <span class="badge badge-info">
                                                    <i class="fas fa-user-shield mr-1"></i>
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-user mr-1"></i>
                                                Sin rol
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($usuario->activo)
                                            <span class="badge badge-success badge-pill">
                                                <i class="fas fa-check"></i> Activo
                                            </span>
                                        @else
                                            <span class="badge badge-danger badge-pill">
                                                <i class="fas fa-times"></i> Inactivo
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            @can('usuarios.update')
                                                <a href="{{ url('gestion-usuarios?editarUsuario=' . $usuario->usuarioid) }}" 
                                                   class="btn btn-warning"
                                                   title="Editar usuario">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('usuarios.delete')
                                                <form action="{{ route('gestion.usuario.destroy', $usuario) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('¿Está seguro de eliminar este usuario?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-danger"
                                                            title="Eliminar usuario">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-users fa-3x mb-3"></i>
                                            <p class="h5">No hay usuarios registrados</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($usuarios->isNotEmpty())
            <div class="card-footer clearfix">
                <div class="float-left">
                    <small class="text-muted">
                        Mostrando {{ $usuarios->firstItem() }} - {{ $usuarios->lastItem() }} de {{ $usuarios->total() }} usuarios
                    </small>
                </div>
                <div class="float-right">
                    {{ $usuarios->links() }}
                </div>
            </div>
            @endif
        </div>

        {{-- FORMULARIO CREAR/EDITAR USUARIO --}}
        @canany(['usuarios.create','usuarios.update'])
        <div class="card {{ $editarUsuario ? 'card-warning' : 'card-success' }} card-outline" id="userForm">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas {{ $editarUsuario ? 'fa-user-edit' : 'fa-user-plus' }} mr-2"></i>
                    {{ $editarUsuario ? 'Editar Usuario' : 'Crear Nuevo Usuario' }}
                </h3>
                @if($editarUsuario)
                    <div class="card-tools">
                        <a href="{{ route('gestion.index') }}" class="btn btn-tool">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                @endif
            </div>

            <div class="card-body">
                <form method="POST"
                      action="{{ $editarUsuario
                                ? route('gestion.usuario.update', $editarUsuario)
                                : route('gestion.usuario.store') }}">
                    @csrf
                    @if($editarUsuario)
                        @method('PUT')
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombre">
                                    <i class="fas fa-user text-primary mr-1"></i>
                                    Nombre <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('nombre') is-invalid @enderror"
                                       id="nombre"
                                       name="nombre"
                                       value="{{ $editarUsuario->nombre ?? old('nombre') }}"
                                       placeholder="Ingrese el nombre"
                                       required>
                                @error('nombre')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="apellido">
                                    <i class="fas fa-user text-primary mr-1"></i>
                                    Apellido <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('apellido') is-invalid @enderror"
                                       id="apellido"
                                       name="apellido"
                                       value="{{ $editarUsuario->apellido ?? old('apellido') }}"
                                       placeholder="Ingrese el apellido"
                                       required>
                                @error('apellido')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope text-info mr-1"></i>
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ $editarUsuario->email ?? old('email') }}"
                                       placeholder="correo@ejemplo.com"
                                       required>
                                @error('email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nombreusuario">
                                    <i class="fas fa-at text-info mr-1"></i>
                                    Nombre de Usuario <span class="text-danger">*</span>
                                </label>
                                <input type="text"
                                       class="form-control @error('nombreusuario') is-invalid @enderror"
                                       id="nombreusuario"
                                       name="nombreusuario"
                                       value="{{ $editarUsuario->nombreusuario ?? old('nombreusuario') }}"
                                       placeholder="usuario123"
                                       required>
                                @error('nombreusuario')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telefono">
                                    <i class="fas fa-phone text-success mr-1"></i>
                                    Teléfono
                                </label>
                                <input type="text"
                                       class="form-control @error('telefono') is-invalid @enderror"
                                       id="telefono"
                                       name="telefono"
                                       value="{{ $editarUsuario->telefono ?? old('telefono') }}"
                                       placeholder="+591 12345678">
                                @error('telefono')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="passwordhash">
                                    <i class="fas fa-lock text-warning mr-1"></i>
                                    Contraseña
                                    @if($editarUsuario)
                                        <small class="text-muted">(dejar vacío para mantener actual)</small>
                                    @else
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>
                                <input type="password"
                                       class="form-control @error('passwordhash') is-invalid @enderror"
                                       id="passwordhash"
                                       name="passwordhash"
                                       placeholder="••••••••"
                                       {{ $editarUsuario ? '' : 'required' }}>
                                @error('passwordhash')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rolid">
                                    <i class="fas fa-user-shield text-primary mr-1"></i>
                                    Rol del Usuario
                                </label>
                                <select name="rolid" 
                                        id="rolid"
                                        class="form-control @error('rolid') is-invalid @enderror">
                                    <option value="">Sin Rol Asignado</option>
                                    @foreach($roles as $rol)
                                        <option value="{{ $rol->id }}"
                                            @if(old('rolid') == $rol->id) selected @endif
                                            @if($editarUsuario && $editarUsuario->roles->contains('id', $rol->id))
                                                selected
                                            @endif>
                                            {{ $rol->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('rolid')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="activo">
                                    <i class="fas fa-toggle-on text-success mr-1"></i>
                                    Estado del Usuario
                                </label>
                                <select name="activo" 
                                        id="activo"
                                        class="form-control @error('activo') is-invalid @enderror">
                                    <option value="1" 
                                        @if(old('activo', $editarUsuario->activo ?? 1) == 1) selected @endif>
                                        Activo
                                    </option>
                                    <option value="0" 
                                        @if(old('activo', $editarUsuario->activo ?? 1) == 0) selected @endif>
                                        Inactivo
                                    </option>
                                </select>
                                @error('activo')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn {{ $editarUsuario ? 'btn-warning' : 'btn-success' }} btn-lg">
                                <i class="fas {{ $editarUsuario ? 'fa-save' : 'fa-user-plus' }} mr-2"></i>
                                {{ $editarUsuario ? 'Actualizar Usuario' : 'Crear Usuario' }}
                            </button>

                            @if($editarUsuario)
                                <a href="{{ route('gestion.index') }}" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-times mr-2"></i>
                                    Cancelar Edición
                                </a>
                            @else
                                <button type="reset" class="btn btn-secondary btn-lg">
                                    <i class="fas fa-eraser mr-2"></i>
                                    Limpiar Formulario
                                </button>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endcanany

    </div>
</section>

@push('styles')
<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 14px;
    }

    .small-box {
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    }

    .small-box .icon {
        font-size: 70px;
        position: absolute;
        right: 15px;
        top: 15px;
        transition: all .3s linear;
        color: rgba(0,0,0,.15);
    }

    .small-box:hover .icon {
        transform: scale(1.1);
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .badge-pill {
        padding: 0.35em 0.65em;
    }

    .card-primary.card-outline {
        border-top: 3px solid #007bff;
    }

    .card-success.card-outline {
        border-top: 3px solid #28a745;
    }

    .card-warning.card-outline {
        border-top: 3px solid #ffc107;
    }

    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
    }

    .form-group label {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .alert {
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-scroll to form when editing
    @if($editarUsuario)
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('userForm').scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        });
    @endif

    // Auto-hide success messages
    setTimeout(function() {
        $('.alert-success').fadeOut('slow');
    }, 5000);
</script>
@endpush
@endsection