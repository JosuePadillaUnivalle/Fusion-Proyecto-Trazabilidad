@extends('layouts.app')

@section('title', 'Editar proceso | Fusion-Proyectos')
@section('page_title', 'Editar proceso de transformación')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('plantillas-transformacion.index') }}">Procesos de transformación</a></li>
    <li class="breadcrumb-item active">{{ $plantilla->nombre }}</li>
@endsection

@push('styles')@include('partials.modulo-produccion-styles')@endpush

@section('content')
<div class="modulo-prod">
    <div class="card card-outline card-success card-modulo-main elevation-1">
        <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-edit mr-2"></i>{{ $plantilla->nombre }}</h3></div>
        <form method="POST" action="{{ route('plantillas-transformacion.update', $plantilla) }}">
            @csrf @method('PUT')
            <div class="card-body">
                @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
                <div class="form-group">
                    <label>Nombre <span class="text-danger">*</span></label>
                    <input name="nombre" class="form-control" value="{{ old('nombre', $plantilla->nombre) }}" required maxlength="120">
                </div>
                <div class="form-group">
                    <label>Producto ejemplo</label>
                    <input name="producto_ejemplo" class="form-control" value="{{ old('producto_ejemplo', $plantilla->producto_ejemplo) }}" maxlength="100">
                </div>
                <div class="form-group">
                    <label>Palabras clave</label>
                    <input name="palabras_clave" class="form-control" value="{{ old('palabras_clave', implode(', ', $plantilla->palabrasClaveLista())) }}">
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion', $plantilla->descripcion) }}</textarea>
                </div>
                <hr>
                @php
                    $pasosOld = old('pasos');
                    $pasosIniciales = $pasosOld ?: $plantilla->pasos->map(fn ($p) => [
                        'procesoplantaid' => $p->procesoplantaid,
                        'maquinaplantaid' => $p->maquinaplantaid,
                        'notas' => $p->notas,
                    ])->all();
                @endphp
                @include('plantillas_transformacion.partials.form-pasos', compact('pasosIniciales'))
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('plantillas-transformacion.show', $plantilla) }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Guardar cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection
