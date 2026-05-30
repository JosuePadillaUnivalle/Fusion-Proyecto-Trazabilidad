@extends('layouts.app')

@section('title', 'Nuevo proceso | Fusion-Proyectos')
@section('page_title', 'Nuevo proceso de planta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procesos-planta.index') }}">Procesos de planta</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@push('styles')
@include('partials.modulo-produccion-styles')
@endpush

@section('content')
<div class="modulo-prod">
    <div class="card card-outline card-success card-modulo-main elevation-1">
        <div class="card-header bg-success text-white">
            <h3 class="card-title mb-0"><i class="fas fa-industry mr-2"></i>Registrar proceso de planta</h3>
        </div>

        <form method="POST" action="{{ route('procesos-planta.store') }}">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-group">
                    <label>Nombre <span class="text-danger">*</span></label>
                    <input name="nombre" class="form-control" value="{{ old('nombre') }}" placeholder="Ej. Lavado y selección, Empaque…" required maxlength="100">
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="4" placeholder="Qué hace este proceso en la línea de planta…">{{ old('descripcion') }}</textarea>
                </div>
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" id="activoProceso" name="activo" value="1" @checked(old('activo', true))>
                    <label class="custom-control-label" for="activoProceso">Proceso activo (disponible al registrar cosechas)</label>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('procesos-planta.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save mr-1"></i> Guardar proceso
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
