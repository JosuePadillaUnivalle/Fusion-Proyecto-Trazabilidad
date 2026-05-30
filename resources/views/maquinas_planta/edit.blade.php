@extends('layouts.app')

@section('title', 'Editar máquina | Fusion-Proyectos')
@section('page_title', 'Editar máquina')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('maquinas-planta.index') }}">Máquinas de planta</a></li>
    <li class="breadcrumb-item"><a href="{{ route('maquinas-planta.show', $maquina) }}">{{ $maquina->nombre }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@push('styles')
@include('partials.modulo-produccion-styles')
@include('maquinas_planta.partials.foto-maquina-styles')
@endpush

@section('content')
<div class="modulo-prod">
    <div class="card card-outline card-success card-modulo-main elevation-1">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
            <h3 class="card-title mb-0"><i class="fas fa-edit text-success mr-1"></i> Editar: {{ $maquina->nombre }}</h3>
            <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
                @include('maquinas_planta.partials.estado-maquina', ['maquina' => $maquina])
                @include('maquinas_planta.partials.btn-toggle-estado', ['maquina' => $maquina])
            </div>
        </div>

        <form method="POST" action="{{ route('maquinas-planta.update', $maquina) }}" enctype="multipart/form-data" id="formEditarMaquina">
            @csrf
            @method('PUT')
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
                            <input name="nombre" class="form-control" value="{{ old('nombre', $maquina->nombre) }}" required maxlength="100">
                        </div>
                        <div class="form-group">
                            <label>Código interno</label>
                            <input name="codigo" class="form-control" value="{{ old('codigo', $maquina->codigo) }}" maxlength="60">
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="descripcion" class="form-control" rows="3" placeholder="Breve descripción de la función en planta">{{ old('descripcion', $maquina->descripcionMostrar()) }}</textarea>
                        </div>
                        <div class="form-group mb-0">
                            <label class="d-block">Estado al guardar</label>
                            <input type="hidden" name="activo" value="0">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="activoMaquinaEdit" name="activo" value="1"
                                    @checked(old('activo', $maquina->activo))>
                                <label class="custom-control-label" for="activoMaquinaEdit">Máquina activa (disponible en registro)</label>
                            </div>
                            <small class="text-muted d-block mt-2">Para cambiar solo el estado sin guardar el resto, use el botón arriba a la derecha.</small>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="form-group mb-2">
                            <label><i class="fas fa-camera mr-1"></i> Foto <span class="text-muted font-weight-normal">(opcional)</span></label>
                            @if($maquina->imagenSrc())
                                <div class="custom-control custom-checkbox mb-2">
                                    <input type="checkbox" class="custom-control-input" id="quitarImagen" name="quitar_imagen" value="1">
                                    <label class="custom-control-label" for="quitarImagen">Quitar foto actual</label>
                                </div>
                            @endif
                            <input type="file" name="imagen" id="imagenMaquina" class="form-control-file" accept="image/jpeg,image/png,image/webp,image/gif">
                            <small class="text-muted d-block mt-1">Deje vacío para conservar la foto actual.</small>
                        </div>
                        <div id="zonaPreviewFoto" class="zona-preview-foto {{ $maquina->imagenSrc() ? 'has-foto' : '' }}">
                            <div id="previewPlaceholder" class="preview-placeholder {{ $maquina->imagenSrc() ? 'd-none' : '' }}">
                                <i class="fas fa-image fa-2x mb-2 d-block opacity-50"></i>
                                Sin foto
                            </div>
                            <img id="previewImagen" src="{{ $maquina->imagenSrc() ?? '' }}" alt="Vista previa"
                                 class="preview-imagen {{ $maquina->imagenSrc() ? '' : 'd-none' }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('maquinas-planta.show', $maquina) }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const input = document.getElementById('imagenMaquina');
    const preview = document.getElementById('previewImagen');
    const placeholder = document.getElementById('previewPlaceholder');
    const zona = document.getElementById('zonaPreviewFoto');
    const quitar = document.getElementById('quitarImagen');

    if (!input || !preview) return;

    function mostrarPreview(src) {
        preview.src = src;
        preview.classList.remove('d-none');
        placeholder?.classList.add('d-none');
        zona?.classList.add('has-foto');
    }

    function ocultarPreview() {
        preview.removeAttribute('src');
        preview.classList.add('d-none');
        placeholder?.classList.remove('d-none');
        zona?.classList.remove('has-foto');
    }

    input.addEventListener('change', function () {
        const file = input.files?.[0];
        if (!file) return;
        mostrarPreview(URL.createObjectURL(file));
        if (quitar) quitar.checked = false;
    });

    quitar?.addEventListener('change', function () {
        if (quitar.checked) {
            input.value = '';
            ocultarPreview();
        }
    });
})();
</script>
@endpush
