@extends('layouts.app')

@section('title', 'Mi Perfil | AgroNexus')
@section('page_title', 'Configuración de Cuenta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Mi Perfil</li>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-success shadow-lg border-0">
                    <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                        <h3 class="card-title text-success font-weight-bold" style="font-size: 1.5rem;">
                            <i class="fas fa-id-card mr-2"></i>Información Personal y Seguridad
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Columna Izquierda: Datos del Usuario (Tarjeta Visual) -->
                            <div class="col-lg-4 border-right pr-lg-5 mb-4 mb-lg-0">
                                <div
                                    class="d-flex flex-column align-items-center text-center p-4 bg-light rounded shadow-sm">
                                    <div class="position-relative mb-3">
                                        <img class="profile-user-img img-fluid img-circle elevation-2"
                                            src="{{ $user->imagenurl ? $user->imagenurl : asset('images/user.png') }}"
                                            alt="Avatar"
                                            style="width: 140px; height: 140px; object-fit: cover; border: 4px solid #28a745;">
                                        <div class="position-absolute bg-success rounded-circle d-flex align-items-center justify-content-center text-white"
                                            style="width: 35px; height: 35px; bottom: 0; right: 0; border: 2px solid white;">
                                            <i class="fas fa-camera"></i>
                                        </div>
                                    </div>

                                    <h3 class="profile-username font-weight-bold text-dark mb-1">{{ $user->nombre }}
                                        {{ $user->apellido }}
                                    </h3>
                                    <p class="text-muted mb-2">{{ '@' . $user->nombreusuario }}</p>
                                    <span class="badge badge-pill badge-success px-3 py-1 mb-4" style="font-size: 0.9rem;">
                                        <i class="fas fa-user-shield mr-1"></i>
                                        {{ $user->getRoleNames()->first() ?? 'Usuario' }}
                                    </span>

                                    <div class="w-100 text-left mt-3">
                                        <div class="d-flex align-items-center mb-3 p-3 bg-white rounded shadow-sm">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mr-3"
                                                style="width: 40px; height: 40px;">
                                                <i class="fas fa-envelope text-success"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Correo Electrónico</small>
                                                <span
                                                    class="font-weight-bold text-dark text-break">{{ $user->email }}</span>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center mb-3 p-3 bg-white rounded shadow-sm">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mr-3"
                                                style="width: 40px; height: 40px;">
                                                <i class="fas fa-phone text-success"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Teléfono</small>
                                                <span
                                                    class="font-weight-bold text-dark">{{ $user->telefono ?? 'No registrado' }}</span>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>

                            <!-- Columna Derecha: Formulario de Edición -->
                            <div class="col-lg-8 pl-lg-4">
                                <div class="mb-4 d-flex justify-content-between align-items-center">
                                    <h5 class="text-dark font-weight-bold mb-0">Actualizar Datos</h5>
                                    <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Los campos marcados
                                        con * son obligatorios</small>
                                </div>

                                @if(session('success'))
                                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-left-success">
                                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle bg-success text-white mr-3 d-flex align-items-center justify-content-center rounded-circle"
                                                style="width: 30px; height: 30px;">
                                                <i class="fas fa-check"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 font-weight-bold">¡Éxito!</h6>
                                                <span>{{ session('success') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <form class="form-horizontal" method="POST" action="{{ route('profile.update') }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <h6 class="text-success border-bottom pb-2 mb-3 mt-4">INFORMACIÓN BÁSICA</h6>

                                    <div class="form-row">
                                        <div class="form-group col-md-6 mb-4">
                                            <label for="nombre"
                                                class="font-weight-bold small text-uppercase text-muted">Nombre</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light border-right-0"><i
                                                            class="fas fa-user text-muted"></i></span>
                                                </div>
                                                <input type="text" class="form-control border-left-0" id="nombre"
                                                    name="nombre" value="{{ old('nombre', $user->nombre) }}" required
                                                    placeholder="Tu nombre">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6 mb-4">
                                            <label for="apellido"
                                                class="font-weight-bold small text-uppercase text-muted">Apellido</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light border-right-0"><i
                                                            class="fas fa-user text-muted"></i></span>
                                                </div>
                                                <input type="text" class="form-control border-left-0" id="apellido"
                                                    name="apellido" value="{{ old('apellido', $user->apellido) }}" required
                                                    placeholder="Tu apellido">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-md-6 mb-4">
                                            <label for="email"
                                                class="font-weight-bold small text-uppercase text-muted">Correo
                                                Electrónico</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light border-right-0"><i
                                                            class="fas fa-envelope text-muted"></i></span>
                                                </div>
                                                <input type="email" class="form-control border-left-0" id="email"
                                                    name="email" value="{{ old('email', $user->email) }}" required
                                                    placeholder="correo@ejemplo.com">
                                            </div>
                                        </div>
                                        <div class="form-group col-md-6 mb-4">
                                            <label for="telefono"
                                                class="font-weight-bold small text-uppercase text-muted">Teléfono
                                                Móvil</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light border-right-0"><i
                                                            class="fas fa-phone text-muted"></i></span>
                                                </div>
                                                <input type="text" class="form-control border-left-0" id="telefono"
                                                    name="telefono" value="{{ old('telefono', $user->telefono) }}"
                                                    placeholder="+591 ...">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-12 mb-4">
                                            <label for="imagen"
                                                class="font-weight-bold small text-uppercase text-muted">Foto de
                                                Perfil</label>
                                            <div class="custom-file">
                                                <input type="file" class="custom-file-input" id="imagen" name="imagen"
                                                    accept="image/*">
                                                <label class="custom-file-label" for="imagen">Seleccionar archivo...</label>
                                            </div>
                                            <small class="form-text text-muted mt-2">Formatos: JPG, PNG, JPEG. Máx
                                                2MB.</small>
                                        </div>
                                    </div>

                                    <h6 class="text-success border-bottom pb-2 mb-3 mt-2">SEGURIDAD DE LA CUENTA</h6>

                                    <div class="form-row">
                                        <div class="form-group col-md-6 mb-3">
                                            <label for="password"
                                                class="font-weight-bold small text-uppercase text-muted">Nueva
                                                Contraseña</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light border-right-0"><i
                                                            class="fas fa-lock text-muted"></i></span>
                                                </div>
                                                <input type="password" class="form-control border-left-0" id="password"
                                                    name="password" placeholder="••••••••">
                                            </div>
                                            <small class="form-text text-muted mt-2">Déjalo en blanco si no quieres cambiar
                                                tu contraseña actual.</small>
                                        </div>
                                        <div class="form-group col-md-6 mb-3">
                                            <label for="password_confirmation"
                                                class="font-weight-bold small text-uppercase text-muted">Confirmar
                                                Contraseña</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text bg-light border-right-0"><i
                                                            class="fas fa-check-double text-muted"></i></span>
                                                </div>
                                                <input type="password" class="form-control border-left-0"
                                                    id="password_confirmation" name="password_confirmation"
                                                    placeholder="••••••••">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group d-flex justify-content-end mt-4 pt-3 border-top">
                                        <button type="reset" class="btn btn-light border mr-3 px-4">Cancelar</button>
                                        <button type="submit"
                                            class="btn btn-success px-5 font-weight-bold shadow-sm hover-elevate">
                                            <i class="fas fa-save mr-2"></i> Guardar Cambios
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .hover-elevate {
            transition: transform 0.2s;
        }

        .hover-elevate:hover {
            transform: translateY(-2px);
        }

        .border-left-success {
            border-left: 4px solid #28a745;
        }

        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
    </style>
@endsection