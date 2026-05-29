@extends('layouts.app')

@section('title', 'Nuevo Transportista | AgroNexus')
@section('page_title', 'Nuevo Transportista')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:#2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('orgtrack.transportistas.index') }}" style="color:#2c5530;">Transportistas</a></li>
    <li class="breadcrumb-item active">Crear</li>
@endsection

@push('styles')
<style>
.form-card { border:0; border-radius:16px; box-shadow:0 8px 28px rgba(18,38,63,.09); }
.form-card .card-header { background:linear-gradient(135deg,#2c5530,#4a7c59); border-radius:16px 16px 0 0 !important; padding:1.1rem 1.5rem; }
.form-card .card-header h3 { color:#fff; font-weight:700; margin:0; }
.form-card .card-header p { color:rgba(255,255,255,.75); margin:0; font-size:.85rem; }
.field-group { margin-bottom:1.1rem; }
.field-group label { font-weight:600; font-size:.83rem; color:#4a5568; margin-bottom:.35rem; text-transform:uppercase; letter-spacing:.04em; }
.form-control { border-radius:8px; border:1.5px solid #e2e8f0; padding:.55rem .85rem; font-size:.9rem; transition:border-color .2s,box-shadow .2s; }
.form-control:focus { border-color:#2c5530; box-shadow:0 0 0 3px rgba(44,85,48,.12); }
.form-control.is-invalid { border-color:#dc3545; }
.hint-text { font-size:.78rem; color:#9ca3af; margin-top:.25rem; }
.btn-guardar { background:linear-gradient(135deg,#2c5530,#4a7c59); border:0; border-radius:8px; padding:.55rem 1.6rem; font-weight:700; color:#fff; transition:opacity .2s; }
.btn-guardar:hover { opacity:.88; color:#fff; }
.btn-cancelar { border-radius:8px; padding:.55rem 1.2rem; color:#4a5568; border-color:#e2e8f0; }
.btn-cancelar:hover { background:#f8fafc; }
.section-divider { font-size:.72rem; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; font-weight:700; border-bottom:1px solid #f0f0f0; padding-bottom:.4rem; margin:1.5rem 0 1rem; }
.avatar-preview { width:64px; height:64px; border-radius:50%; background:linear-gradient(135deg,#2c5530,#4a7c59); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.5rem; font-weight:700; transition:background .3s; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="card form-card">
            <div class="card-header d-flex align-items-center" style="gap:1rem;">
                <div class="avatar-preview" id="avatarPreview">T</div>
                <div>
                    <h3 class="card-title">Registrar nuevo transportista</h3>
                    <p>Complete los datos del conductor para integrarlo al sistema.</p>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.flash-messages')

                <form action="{{ route('orgtrack.transportistas.store') }}" method="POST" id="form-create">
                    @csrf

                    <div class="section-divider">Información personal</div>

                    <div class="row">
                        <div class="col-md-6 field-group">
                            <label for="nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" id="nombre" name="nombre" class="form-control @error('nombre') is-invalid @enderror"
                                   value="{{ old('nombre') }}" placeholder="Ej: Juan" autocomplete="given-name" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 field-group">
                            <label for="apellido">Apellido</label>
                            <input type="text" id="apellido" name="apellido" class="form-control @error('apellido') is-invalid @enderror"
                                   value="{{ old('apellido') }}" placeholder="Ej: Pérez" autocomplete="family-name">
                            @error('apellido')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="section-divider">Datos de contacto</div>

                    <div class="row">
                        <div class="col-md-6 field-group">
                            <label for="email">Correo electrónico</label>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="correo@ejemplo.com" autocomplete="email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 field-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" class="form-control @error('telefono') is-invalid @enderror"
                                   value="{{ old('telefono') }}" placeholder="Ej: +591 70000000">
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="section-divider">Acceso al sistema</div>

                    <div class="row">
                        <div class="col-md-6 field-group">
                            <label for="nombreusuario">Nombre de usuario</label>
                            <input type="text" id="nombreusuario" name="nombreusuario"
                                   class="form-control @error('nombreusuario') is-invalid @enderror"
                                   value="{{ old('nombreusuario') }}" placeholder="Ej: juan.perez" autocomplete="username">
                            <div class="hint-text">Si se deja en blanco, se genera automáticamente.</div>
                            @error('nombreusuario')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 field-group">
                            <label for="informacionadicional">Notas / Licencia</label>
                            <input type="text" id="informacionadicional" name="informacionadicional"
                                   class="form-control @error('informacionadicional') is-invalid @enderror"
                                   value="{{ old('informacionadicional') }}" placeholder="Ej: Licencia cat. B - Nº 12345">
                            @error('informacionadicional')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                </form>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center" style="background:#fafbfc;border-top:1px solid #f0f0f0;border-radius:0 0 16px 16px;padding:1rem 1.5rem;">
                <a href="{{ route('orgtrack.transportistas.index') }}" class="btn btn-outline-secondary btn-cancelar">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
                <button type="submit" form="form-create" class="btn btn-guardar">
                    <i class="fas fa-save mr-1"></i> Guardar transportista
                </button>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    const nombreInput = document.getElementById('nombre');
    const apellidoInput = document.getElementById('apellido');
    const avatarEl = document.getElementById('avatarPreview');

    function updateAvatar() {
        const n = (nombreInput.value || '').trim();
        const a = (apellidoInput.value || '').trim();
        avatarEl.textContent = (n ? n[0].toUpperCase() : 'T') + (a ? a[0].toUpperCase() : '');
    }

    nombreInput.addEventListener('input', updateAvatar);
    apellidoInput.addEventListener('input', updateAvatar);
</script>
@endpush
