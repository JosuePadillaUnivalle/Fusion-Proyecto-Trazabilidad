@extends('layouts.app')

@section('title', 'Nueva máquina | AgroFusion')
@section('page_title', 'Nueva máquina de planta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('maquinas-planta.index') }}">Máquinas de planta</a></li>
    <li class="breadcrumb-item active">Nueva</li>
@endsection

@push('styles')
@include('partials.modulo-produccion-styles')
@include('maquinas_planta.partials.foto-maquina-styles')
@include('maquinas_planta.partials.form-maquina-styles')
@endpush

@section('content')
<div class="modulo-prod">
    <div class="maq-form-hero">
        <h2><i class="fas fa-industry mr-2"></i>Registrar equipo de planta</h2>
        <p>Defina la máquina, su foto y los rangos operativos que suele controlar.</p>
    </div>

    <form method="POST" action="{{ route('maquinas-planta.store') }}" enctype="multipart/form-data">
        @csrf
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-7 mb-3">
                <div class="maq-form-panel">
                    <div class="maq-form-panel__title"><i class="fas fa-info-circle mr-1"></i> Datos del equipo</div>
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input name="nombre" class="form-control" value="{{ old('nombre') }}" placeholder="Ej. Horno de secado TR-600" required maxlength="100">
                    </div>
                    <div class="form-row">
                        <div class="col-md-6 form-group">
                            <label>Código interno</label>
                            <input name="codigo" id="codigoMaquina" class="form-control" value="{{ old('codigo') }}" placeholder="Se genera del nombre" maxlength="60" autocomplete="off">
                            <small class="text-muted">Se sugiere automáticamente y puede editarlo; debe ser único.</small>
                        </div>
                        <div class="col-md-6 form-group d-flex align-items-end pb-1">
                            <input type="hidden" name="activo" value="0">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="activoMaquina" name="activo" value="1" @checked(old('activo', true))>
                                <label class="custom-control-label" for="activoMaquina">Disponible en línea de producción</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label>Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Ej. Aplica tratamiento térmico al producto antes del empaque.">{{ old('descripcion') }}</textarea>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 mb-3">
                <div class="maq-form-panel">
                    <div class="maq-form-panel__title"><i class="fas fa-camera mr-1"></i> Imagen del equipo</div>
                    <input type="file" name="imagen" id="imagenMaquina" class="form-control-file mb-2" accept="image/jpeg,image/png,image/webp,image/gif">
                    <small class="text-muted d-block mb-2">JPG, PNG o WebP · máx. 4 MB</small>
                    <div id="zonaPreviewFoto" class="zona-preview-foto zona-preview-foto--form">
                        <div id="previewPlaceholder" class="preview-placeholder">
                            <i class="fas fa-image fa-2x mb-2 d-block opacity-50"></i>
                            Vista previa de la foto
                        </div>
                        <img id="previewImagen" class="preview-imagen d-none" alt="Vista previa">
                    </div>
                </div>
            </div>
        </div>

        @include('maquinas_planta.partials.form-variables-sugeridas', ['maquina' => null, 'variablesCatalogo' => $variablesCatalogo ?? null])

        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('maquinas-planta.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Cancelar</a>
            <button type="submit" class="btn btn-success px-4"><i class="fas fa-save mr-1"></i> Guardar máquina</button>
        </div>
    </form>
</div>
@endsection

@include('maquinas_planta.partials.preview-foto-script')
@include('maquinas_planta.partials.codigo-auto-script')
