@extends('layouts.app')

@section('title', 'Nuevo usuario | Fusion-Proyectos')
@section('page_title', 'Nuevo usuario')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('gestion.index') }}">Gestión de usuarios</a></li>
    <li class="breadcrumb-item active">Nuevo usuario</li>
@endsection

@push('styles')
@include('partials.modulo-usuarios-styles')
@endpush

@section('content')
<div class="modulo-usu">

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong><i class="fas fa-exclamation-triangle mr-1"></i> Revisa el formulario.</strong>
        <ul class="mb-0 mt-2 pl-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('gestion.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver al listado
        </a>
    </div>

    <div class="card card-outline card-success card-form-modulo elevation-1">
        <div class="card-header">
            <h3 class="card-title mb-0 text-white">
                <i class="fas fa-user-plus mr-1"></i> Crear nuevo usuario
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('gestion.usuario.store') }}">
                @csrf
                @include('usuarios.partials.form-fields', [
                    'mostrarGuias' => ! ($modoJefe ?? false),
                    'modoJefe' => $modoJefe ?? false,
                ])

                <div class="d-flex flex-wrap" style="gap: 8px;">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-user-plus mr-1"></i> Crear usuario
                    </button>
                    <a href="{{ route('gestion.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="reset" class="btn btn-outline-secondary">
                        <i class="fas fa-eraser mr-1"></i> Limpiar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
