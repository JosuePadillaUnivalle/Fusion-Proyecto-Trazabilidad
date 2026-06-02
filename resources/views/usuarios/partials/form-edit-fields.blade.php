@php
    $rolActualNombre = $usuario->roles->first()?->name;
    $rolDefaultId = old('rolid');
    if ($rolDefaultId === null && $rolActualNombre) {
        $rolDefaultId = $roles->first(
            fn ($r) => strtolower($r->name) === strtolower($rolActualNombre)
        )?->id ?? '';
    }
@endphp

<section class="usu-edit-section">
    <h4 class="usu-edit-section-title">
        <i class="fas fa-id-card"></i> Identidad
    </h4>
    <div class="row">
        <div class="col-md-6">
            <div class="usu-edit-field">
                <label for="nombre">Nombre <span class="text-danger">*</span></label>
                <div class="usu-edit-input-wrap">
                    <i class="fas fa-user"></i>
                    <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre"
                        value="{{ old('nombre', $usuario->nombre) }}" required placeholder="Nombre">
                </div>
                @error('nombre')<span class="usu-edit-error">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="usu-edit-field">
                <label for="apellido">Apellido <span class="text-danger">*</span></label>
                <div class="usu-edit-input-wrap">
                    <i class="fas fa-user"></i>
                    <input type="text" class="form-control @error('apellido') is-invalid @enderror" id="apellido" name="apellido"
                        value="{{ old('apellido', $usuario->apellido) }}" required placeholder="Apellido">
                </div>
                @error('apellido')<span class="usu-edit-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>
</section>

<section class="usu-edit-section">
    <h4 class="usu-edit-section-title">
        <i class="fas fa-at"></i> Acceso y contacto
    </h4>
    <div class="row">
        <div class="col-md-6">
            <div class="usu-edit-field">
                <label for="email">Correo electrónico <span class="text-danger">*</span></label>
                <div class="usu-edit-input-wrap">
                    <i class="fas fa-envelope"></i>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                        value="{{ old('email', $usuario->email) }}" required placeholder="correo@empresa.com">
                </div>
                @error('email')<span class="usu-edit-error">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="usu-edit-field">
                <label for="nombreusuario">Nombre de usuario <span class="text-danger">*</span></label>
                <div class="usu-edit-input-wrap">
                    <i class="fas fa-at"></i>
                    <input type="text" class="form-control @error('nombreusuario') is-invalid @enderror" id="nombreusuario" name="nombreusuario"
                        value="{{ old('nombreusuario', $usuario->nombreusuario) }}" required placeholder="usuario">
                </div>
                @error('nombreusuario')<span class="usu-edit-error">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="usu-edit-field mb-md-0">
                <label for="telefono">Teléfono</label>
                <div class="usu-edit-input-wrap">
                    <i class="fas fa-phone"></i>
                    <input type="text" class="form-control @error('telefono') is-invalid @enderror" id="telefono" name="telefono"
                        value="{{ old('telefono', $usuario->telefono) }}" placeholder="+591 …">
                </div>
                @error('telefono')<span class="usu-edit-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>
</section>

<section class="usu-edit-section mb-0">
    <h4 class="usu-edit-section-title">
        <i class="fas fa-user-shield"></i> Rol en el sistema
    </h4>
    <div class="row">
        <div class="col-md-6">
            <div class="usu-edit-field mb-0">
                <label for="rolid">Rol asignado</label>
                <div class="usu-edit-input-wrap">
                    <i class="fas fa-shield-alt"></i>
                    <select name="rolid" id="rolid" class="form-control @error('rolid') is-invalid @enderror">
                        <option value="">Sin rol asignado</option>
                        @foreach($roles as $rol)
                            <option value="{{ $rol->id }}" @selected($rolDefaultId == $rol->id)>
                                {{ ucfirst($rol->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('rolid')<span class="usu-edit-error">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>
</section>
