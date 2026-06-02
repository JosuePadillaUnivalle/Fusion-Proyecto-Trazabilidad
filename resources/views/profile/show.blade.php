@extends('layouts.app')

@section('title', 'Mi Perfil | AgroFusion')
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
                            <i class="fas fa-id-card mr-2"></i>Mi perfil
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 border-right pr-lg-5 mb-4 mb-lg-0">
                                <div class="d-flex flex-column align-items-center text-center p-4 bg-light rounded shadow-sm">
                                    <div class="position-relative mb-3" id="avatarPreviewWrap">
                                        <img id="avatarPreview" class="profile-user-img img-fluid img-circle elevation-2"
                                            src="{{ $avatarUrl }}"
                                            alt="Avatar"
                                            data-avatar-fallback="{{ \App\Support\UsuarioAvatar::placeholder() }}"
                                            onerror="if(this.dataset.avatarFallback){this.onerror=null;this.src=this.dataset.avatarFallback;}"
                                            style="width: 140px; height: 140px; object-fit: cover; border: 4px solid #28a745;">
                                        <label for="imagen"
                                            class="position-absolute bg-success rounded-circle d-flex align-items-center justify-content-center text-white mb-0"
                                            style="width: 35px; height: 35px; bottom: 0; right: 0; border: 2px solid white; cursor: pointer;"
                                            title="Cambiar foto de perfil">
                                            <i class="fas fa-camera"></i>
                                        </label>
                                    </div>
                                    <p class="small text-muted text-center mb-0">Puedes cambiar solo tu foto de perfil</p>

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
                                                <small class="text-muted d-block">Correo electrónico</small>
                                                <span class="font-weight-bold text-dark text-break">{{ $user->email }}</span>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center mb-3 p-3 bg-white rounded shadow-sm">
                                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mr-3"
                                                style="width: 40px; height: 40px;">
                                                <i class="fas fa-phone text-success"></i>
                                            </div>
                                            <div>
                                                <small class="text-muted d-block">Teléfono</small>
                                                <span class="font-weight-bold text-dark">{{ $user->telefono ?? 'No registrado' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-8 pl-lg-4">
                                <div class="alert alert-light border mb-4">
                                    <i class="fas fa-lock text-muted mr-1"></i>
                                    Tu nombre, apellido, correo y teléfono son fijos y no pueden modificarse desde tu cuenta.
                                </div>

                                <form class="form-horizontal" method="POST" action="{{ route('profile.update') }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <h6 class="text-success border-bottom pb-2 mb-3">FOTO DE PERFIL</h6>

                                    <div class="form-group mb-4">
                                        <label for="imagen" class="font-weight-bold small text-uppercase text-muted">Imagen</label>
                                        <input type="file" class="form-control-file @error('imagen') is-invalid @enderror" id="imagen" name="imagen"
                                            accept="image/jpeg,image/png,image/webp,image/gif">
                                        @error('imagen')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        <small class="form-text text-muted mt-2" id="imagenFileName">Ningún archivo seleccionado</small>
                                        <small class="form-text text-muted d-block">JPG, PNG o WEBP. Máximo 2 MB.</small>
                                    </div>

                                    <h6 class="text-success border-bottom pb-2 mb-3 mt-2">NOMBRE DE USUARIO</h6>

                                    @if(! $user->nombreusuario_editado)
                                    <div class="form-group mb-4">
                                        <label for="nombreusuario" class="font-weight-bold small text-uppercase text-muted">
                                            Personalizar (solo una vez)
                                        </label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-light">@</span>
                                            </div>
                                            <input type="text" class="form-control @error('nombreusuario') is-invalid @enderror"
                                                id="nombreusuario" name="nombreusuario"
                                                value="{{ old('nombreusuario', $user->nombreusuario) }}"
                                                placeholder="tu_usuario" pattern="[a-zA-Z0-9._-]+" required>
                                        </div>
                                        @error('nombreusuario')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                        <small class="form-text text-muted">
                                            Puedes cambiarlo una sola vez. Letras, números, puntos, guiones y guiones bajos.
                                        </small>
                                    </div>
                                    @else
                                    <div class="form-group mb-4">
                                        <label class="font-weight-bold small text-uppercase text-muted d-block">Tu usuario</label>
                                        <p class="form-control-plaintext font-weight-bold mb-0">{{ '@'.$user->nombreusuario }}</p>
                                        <small class="text-muted">Ya utilizaste tu único cambio de nombre de usuario.</small>
                                    </div>
                                    @endif

                                    <div class="form-group d-flex justify-content-end mt-4 pt-3 border-top">
                                        <button type="reset" class="btn btn-light border mr-3 px-4">Cancelar</button>
                                        <button type="submit" class="btn btn-success px-5 font-weight-bold shadow-sm hover-elevate">
                                            <i class="fas fa-save mr-2"></i> Guardar cambios
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
        .hover-elevate { transition: transform 0.2s; }
        .hover-elevate:hover { transform: translateY(-2px); }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
    </style>
@endsection

@push('scripts')
<script>
$(function () {
    var $input = $('#imagen');
    var $preview = $('#avatarPreview');
    var $fileName = $('#imagenFileName');

    $input.on('change', function () {
        var file = this.files && this.files[0];
        if (!file) {
            $fileName.text('Ningún archivo seleccionado');
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            alert('La imagen no puede superar 2 MB.');
            this.value = '';
            $fileName.text('Ningún archivo seleccionado');
            return;
        }
        $fileName.text(file.name);
        var reader = new FileReader();
        reader.onload = function (e) {
            $preview.attr('src', e.target.result);
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endpush
