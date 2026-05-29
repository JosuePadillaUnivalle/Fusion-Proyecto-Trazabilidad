@extends('layouts.app')

@section('title', 'Editar Transportista | AgroNexus')
@section('page_title', 'Editar Transportista')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:#2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('orgtrack.transportistas.index') }}" style="color:#2c5530;">Transportistas</a></li>
    <li class="breadcrumb-item active">{{ $transportista->nombre }} {{ $transportista->apellido }}</li>
@endsection

@push('styles')
<style>
.form-card { border:0; border-radius:16px; box-shadow:0 8px 28px rgba(18,38,63,.09); }
.form-card .card-header { background:linear-gradient(135deg,#1a3a6b,#2c5fc8); border-radius:16px 16px 0 0 !important; padding:1.1rem 1.5rem; }
.form-card .card-header h3 { color:#fff; font-weight:700; margin:0; }
.form-card .card-header p { color:rgba(255,255,255,.75); margin:0; font-size:.85rem; }
.field-group { margin-bottom:1.1rem; }
.field-group label { font-weight:600; font-size:.83rem; color:#4a5568; margin-bottom:.35rem; text-transform:uppercase; letter-spacing:.04em; }
.form-control { border-radius:8px; border:1.5px solid #e2e8f0; padding:.55rem .85rem; font-size:.9rem; transition:border-color .2s,box-shadow .2s; }
.form-control:focus { border-color:#1a3a6b; box-shadow:0 0 0 3px rgba(26,58,107,.12); }
.form-control.is-invalid { border-color:#dc3545; }
.hint-text { font-size:.78rem; color:#9ca3af; margin-top:.25rem; }
.btn-actualizar { background:linear-gradient(135deg,#1a3a6b,#2c5fc8); border:0; border-radius:8px; padding:.55rem 1.6rem; font-weight:700; color:#fff; transition:opacity .2s; }
.btn-actualizar:hover { opacity:.88; color:#fff; }
.btn-cancelar { border-radius:8px; padding:.55rem 1.2rem; color:#4a5568; border-color:#e2e8f0; }
.section-divider { font-size:.72rem; text-transform:uppercase; letter-spacing:.07em; color:#9ca3af; font-weight:700; border-bottom:1px solid #f0f0f0; padding-bottom:.4rem; margin:1.5rem 0 1rem; }
.avatar-preview { width:64px; height:64px; border-radius:50%; background:linear-gradient(135deg,#1a3a6b,#2c5fc8); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.5rem; font-weight:700; }
.id-chip { background:#e8f0fe; color:#1a3a6b; border-radius:20px; padding:.2em .8em; font-size:.75rem; font-weight:700; }
.toggle-switch { display:flex; align-items:center; gap:.75rem; }
.toggle-switch input[type=checkbox] { width:1.1em; height:1.1em; accent-color:#2c5530; cursor:pointer; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="card form-card">
            <div class="card-header d-flex align-items-center" style="gap:1rem;">
                <div class="avatar-preview">
                    {{ strtoupper(substr($transportista->nombre ?? 'T', 0, 1)) }}{{ strtoupper(substr($transportista->apellido ?? '', 0, 1)) }}
                </div>
                <div>
                    <h3 class="card-title">{{ $transportista->nombre }} {{ $transportista->apellido }}</h3>
                    <p>
                        <span class="id-chip">ID #{{ $transportista->usuarioid }}</span>
                        &nbsp;·&nbsp;
                        @if($transportista->activo)
                            <span style="color:rgba(255,255,255,.9);"><i class="fas fa-circle fa-xs" style="color:#4cff87;"></i> Activo</span>
                        @else
                            <span style="color:rgba(255,255,255,.7);"><i class="fas fa-circle fa-xs" style="color:#ff6b6b;"></i> Inactivo</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="card-body p-4">
                @include('partials.flash-messages')

                <form action="{{ route('orgtrack.transportistas.update', $transportista->usuarioid) }}"
                      method="POST" id="form-edit">
                    @csrf
                    @method('PUT')

                    <div class="section-divider">Información personal</div>

                    <div class="row">
                        <div class="col-md-6 field-group">
                            <label for="nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" id="nombre" name="nombre"
                                   class="form-control @error('nombre') is-invalid @enderror"
                                   value="{{ old('nombre', $transportista->nombre) }}" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 field-group">
                            <label for="apellido">Apellido</label>
                            <input type="text" id="apellido" name="apellido"
                                   class="form-control @error('apellido') is-invalid @enderror"
                                   value="{{ old('apellido', $transportista->apellido) }}">
                            @error('apellido')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="section-divider">Datos de contacto</div>

                    <div class="row">
                        <div class="col-md-6 field-group">
                            <label for="email">Correo electrónico</label>
                            <input type="email" id="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email', $transportista->email) }}" placeholder="correo@ejemplo.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 field-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" id="telefono" name="telefono"
                                   class="form-control @error('telefono') is-invalid @enderror"
                                   value="{{ old('telefono', $transportista->telefono) }}" placeholder="+591 70000000">
                            @error('telefono')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="section-divider">Acceso y estado</div>

                    <div class="row">
                        <div class="col-md-6 field-group">
                            <label for="nombreusuario">Nombre de usuario</label>
                            <input type="text" id="nombreusuario" name="nombreusuario"
                                   class="form-control @error('nombreusuario') is-invalid @enderror"
                                   value="{{ old('nombreusuario', $transportista->nombreusuario) }}">
                            @error('nombreusuario')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 field-group">
                            <label for="informacionadicional">Notas / Licencia</label>
                            <input type="text" id="informacionadicional" name="informacionadicional"
                                   class="form-control @error('informacionadicional') is-invalid @enderror"
                                   value="{{ old('informacionadicional', $transportista->informacionadicional) }}"
                                   placeholder="Ej: Licencia cat. B - Nº 12345">
                            @error('informacionadicional')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="field-group">
                        <label>Estado del transportista</label>
                        <div class="toggle-switch">
                            <input type="hidden" name="activo" value="0">
                            <input type="checkbox" id="activo" name="activo" value="1"
                                   {{ old('activo', $transportista->activo) ? 'checked' : '' }}>
                            <label for="activo" style="font-size:.9rem;font-weight:500;cursor:pointer;margin:0;">
                                Habilitado para recibir asignaciones de envío
                            </label>
                        </div>
                        <div class="hint-text">Desactivarlo impedirá que aparezca en nuevas asignaciones.</div>
                    </div>

                </form>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center" style="background:#fafbfc;border-top:1px solid #f0f0f0;border-radius:0 0 16px 16px;padding:1rem 1.5rem;">
                <div class="d-flex align-items-center" style="gap:.5rem;">
                    <a href="{{ route('orgtrack.transportistas.index') }}" class="btn btn-outline-secondary btn-cancelar">
                        <i class="fas fa-arrow-left mr-1"></i> Volver
                    </a>
                    @can('transportistas.delete')
                    <form action="{{ route('orgtrack.transportistas.destroy', $transportista->usuarioid) }}"
                          method="POST" class="d-inline"
                          onsubmit="return confirm('¿Eliminar definitivamente a {{ addslashes($transportista->nombre) }}?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger" style="border-radius:8px;">
                            <i class="fas fa-trash-alt mr-1"></i> Eliminar
                        </button>
                    </form>
                    @endcan
                </div>
                <button type="submit" form="form-edit" class="btn btn-actualizar">
                    <i class="fas fa-save mr-1"></i> Actualizar
                </button>
            </div>
        </div>

    </div>
</div>
@endsection
