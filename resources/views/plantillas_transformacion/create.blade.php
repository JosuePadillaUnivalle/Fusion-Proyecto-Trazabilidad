@extends('layouts.app')

@section('title', 'Nuevo proceso | Fusion-Proyectos')
@section('page_title', 'Nuevo proceso de transformación')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('plantillas-transformacion.index') }}">Procesos de transformación</a></li>
    <li class="breadcrumb-item active">Nueva</li>
@endsection

@push('styles')@include('partials.modulo-produccion-styles')@endpush

@section('content')
<div class="modulo-prod">
    <div class="card card-outline card-success card-modulo-main elevation-1">
        <div class="card-header bg-success text-white">
            <h3 class="card-title mb-0"><i class="fas fa-project-diagram mr-2"></i>Nuevo proceso de transformación</h3>
        </div>
        <form method="POST" action="{{ route('plantillas-transformacion.store') }}">
            @csrf
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
                <div class="form-group">
                    <label>Nombre del proceso <span class="text-danger">*</span></label>
                    <input name="nombre" class="form-control" value="{{ old('nombre') }}" required maxlength="120" placeholder="Ej. Puré de papa">
                </div>
                <div class="form-group">
                    <label>Producto ejemplo</label>
                    <input name="producto_ejemplo" class="form-control" value="{{ old('producto_ejemplo') }}" maxlength="100" placeholder="Ej. Puré de papa">
                    <small class="text-muted">Palabras separadas por coma. Si el producto del lote las contiene, se asigna este proceso automáticamente.</small>
                </div>
                <div class="form-group">
                    <label>Palabras clave</label>
                    <input name="palabras_clave" class="form-control" value="{{ old('palabras_clave') }}" placeholder="puré, papa, pure (separadas por coma)">
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion') }}</textarea>
                </div>
                <hr>
                @include('plantillas_transformacion.partials.form-pasos', ['pasosIniciales' => old('pasos', [])])
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('plantillas-transformacion.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Cancelar</a>
                <button type="submit" class="btn btn-success" id="btnGuardarProceso"><i class="fas fa-save mr-1"></i> Guardar proceso</button>
            </div>
        </form>
    </div>
</div>
@endsection
