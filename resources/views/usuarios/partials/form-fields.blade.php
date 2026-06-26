@php
    $usuarioForm = $usuario ?? null;
    $mostrarGuias = ($mostrarGuias ?? false) && ! ($modoJefe ?? false);
    $modoJefe = $modoJefe ?? false;
@endphp

@if($mostrarGuias)
<div class="guia-campo mb-4">
    <strong>Antes de guardar:</strong> cada persona necesita correo único, nombre de usuario único y un rol
    (por ejemplo <em>agricultor</em> para responsables de lotes, <em>admin</em> para administración).
</div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="nombre">Nombre <span class="text-danger">*</span></label>
            @if($mostrarGuias)
            <div class="guia-campo mb-2">
                <strong>Identidad.</strong> Nombre real de la persona; aparece en lotes y reportes como responsable.
            </div>
            @endif
            <input type="text" class="form-control @error('nombre') is-invalid @enderror" id="nombre" name="nombre"
                value="{{ old('nombre', $usuarioForm->nombre ?? '') }}" required>
            @error('nombre')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="apellido">Apellido <span class="text-danger">*</span></label>
            @if($mostrarGuias)
            <div class="guia-campo mb-2">
                <strong>Apellidos</strong> completos para listados y trazabilidad de operaciones.
            </div>
            @endif
            <input type="text" class="form-control @error('apellido') is-invalid @enderror" id="apellido" name="apellido"
                value="{{ old('apellido', $usuarioForm->apellido ?? '') }}" required>
            @error('apellido')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="email">Correo electrónico <span class="text-danger">*</span></label>
            @if($mostrarGuias)
            <div class="guia-campo mb-2">
                <strong>Acceso y notificaciones.</strong> Debe ser único en el sistema; se usa para recuperación y contacto.
            </div>
            @endif
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                value="{{ old('email', $usuarioForm->email ?? '') }}" placeholder="correo@empresa.com" required>
            @error('email')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    @if(! $modoJefe || $usuarioForm)
    <div class="col-md-6">
        <div class="form-group">
            <label for="nombreusuario">Nombre de usuario @if(! $usuarioForm || ! ($usuarioForm->nombreusuario_editado ?? false))<span class="text-danger">*</span>@endif</label>
            @if($mostrarGuias)
            <div class="guia-campo mb-2">
                <strong>Login.</strong> Texto corto sin espacios (ej. <code>jperez</code>). También debe ser único.
            </div>
            @endif
            @if($modoJefe && $usuarioForm && ($usuarioForm->nombreusuario_editado ?? false))
            <input type="text" class="form-control" value="{{ $usuarioForm->nombreusuario }}" readonly disabled>
            @else
            <input type="text" class="form-control @error('nombreusuario') is-invalid @enderror" id="nombreusuario" name="nombreusuario"
                value="{{ old('nombreusuario', $usuarioForm->nombreusuario ?? '') }}" placeholder="usuario" {{ $modoJefe && ! $usuarioForm ? '' : 'required' }}>
            @error('nombreusuario')<span class="invalid-feedback">{{ $message }}</span>@enderror
            @endif
        </div>
    </div>
    @endif
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="telefono">Teléfono</label>
            @if($mostrarGuias)
            <div class="guia-campo mb-2">
                <strong>Opcional.</strong> Número de contacto operativo (campo, planta, logística).
            </div>
            @endif
            <input type="tel" class="form-control js-telefono-bo @error('telefono') is-invalid @enderror" id="telefono" name="telefono"
                value="{{ old('telefono', $usuarioForm->telefono ?? '') }}" placeholder="+591 71234567"
                inputmode="tel" autocomplete="tel">
            @error('telefono')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="passwordhash">
                Contraseña
                @if($usuarioForm)
                    <small class="text-muted">(vacío = mantener actual)</small>
                @else
                    <span class="text-danger">*</span>
                @endif
            </label>
            @if($mostrarGuias)
            <div class="guia-campo mb-2">
                <strong>Seguridad.</strong> Mínimo 8 caracteres recomendados. El usuario la usará para entrar al sistema.
            </div>
            @endif
            <input type="password" class="form-control @error('passwordhash') is-invalid @enderror" id="passwordhash" name="passwordhash"
                {{ $usuarioForm ? '' : 'required' }} minlength="5" autocomplete="new-password">
            @error('passwordhash')<span class="invalid-feedback d-none">{{ $message }}</span>@enderror
        </div>
    </div>
</div>

@unless($modoJefe)
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="rolid">Rol</label>
            @if($mostrarGuias)
            <div class="guia-campo mb-2">
                <strong>Permisos.</strong>
                <em>agricultor</em> → campo, inventario, envíos y almacén;
                <em>planta</em> → procesamiento y despacho;
                <em>transportista</em> → rutas y entregas;
                <em>admin</em> → gestión completa del sistema.
            </div>
            @endif
            @php
                $rolActualNombre = $usuarioForm?->roles->first()?->name;
                $rolDefaultId = old('rolid');
                if ($rolDefaultId === null && $rolActualNombre) {
                    $rolDefaultId = $roles->first(
                        fn ($r) => strtolower($r->name) === strtolower($rolActualNombre)
                    )?->id ?? '';
                }
            @endphp
            <select name="rolid" id="rolid" class="form-control @error('rolid') is-invalid @enderror">
                <option value="">Sin rol asignado</option>
                @foreach($roles as $rol)
                    <option value="{{ $rol->id }}" @selected($rolDefaultId == $rol->id)>
                        {{ ucfirst($rol->name) }}
                    </option>
                @endforeach
            </select>
            @error('rolid')<span class="invalid-feedback">{{ $message }}</span>@enderror
        </div>
    </div>
</div>
@endunless

@include('partials.telefono-bo-input-script')
