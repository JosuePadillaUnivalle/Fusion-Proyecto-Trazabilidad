@extends('layouts.app')

@section('title', 'Nueva máquina | Fusion-Proyectos')
@section('page_title', 'Nueva máquina de planta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('maquinas-planta.index') }}">Máquinas de planta</a></li>
    <li class="breadcrumb-item active">Nueva</li>
@endsection

@push('styles')
@include('partials.modulo-produccion-styles')
@include('maquinas_planta.partials.foto-maquina-styles')
@endpush

@section('content')
<div class="modulo-prod">
    <div class="card card-outline card-success card-modulo-main elevation-1">
        <div class="card-header bg-success text-white">
            <h3 class="card-title mb-0"><i class="fas fa-cogs mr-2"></i>Registrar máquina de planta</h3>
        </div>

        <form method="POST" action="{{ route('maquinas-planta.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-7">
                        <div class="form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input name="nombre" class="form-control" value="{{ old('nombre') }}" placeholder="Ej. Selladora de empaque" required maxlength="100">
                        </div>
                        <div class="form-group">
                            <label>Código interno</label>
                            <input name="codigo" class="form-control" value="{{ old('codigo') }}" placeholder="Ej. SE-10, BD-500" maxlength="60">
                            <small class="text-muted">Opcional.</small>
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3" placeholder="Ej: Pesa lotes y verifica el peso en control de calidad.">{{ old('descripcion') }}</textarea>
                            <small class="text-muted">Breve: qué hace la máquina en la línea de planta.</small>
                        </div>
                        <input type="hidden" name="activo" value="0">
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="activoMaquina" name="activo" value="1" @checked(old('activo', true))>
                            <label class="custom-control-label" for="activoMaquina">Máquina activa (si no marca, quedará en mantenimiento)</label>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="form-group mb-2">
                            <label><i class="fas fa-camera mr-1"></i> Foto de la máquina <span class="text-muted font-weight-normal">(opcional)</span></label>
                            <input type="file" name="imagen" id="imagenMaquina" class="form-control-file" accept="image/jpeg,image/png,image/webp,image/gif">
                            <small class="text-muted d-block mt-1">Puede guardar sin foto y subirla después. JPG, PNG o WebP · máx. 4 MB.</small>
                        </div>
                        <div id="zonaPreviewFoto" class="zona-preview-foto">
                            <div id="previewPlaceholder" class="preview-placeholder">
                                <i class="fas fa-image fa-2x mb-2 d-block opacity-50"></i>
                                Sin foto por ahora
                            </div>
                            <img id="previewImagen" class="preview-imagen d-none" alt="Vista previa de la foto">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('maquinas-planta.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save mr-1"></i> Guardar máquina
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@include('maquinas_planta.partials.preview-foto-script')
