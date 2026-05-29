@extends('layouts.app')

@section('title', 'Editar usuario | Fusion-Proyectos')
@section('page_title', 'Editar usuario')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('gestion.index') }}">Gestión de usuarios</a></li>
    <li class="breadcrumb-item"><a href="{{ route('gestion.show', $usuario) }}">{{ $usuario->nombre }} {{ $usuario->apellido }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
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
        <a href="{{ route('gestion.show', $usuario) }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver al detalle
        </a>
    </div>

    <div class="card card-outline card-warning card-form-modulo card-edit elevation-1">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-user-edit mr-1"></i> Editar: {{ $usuario->nombre }} {{ $usuario->apellido }}
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('gestion.usuario.update', $usuario) }}">
                @csrf
                @method('PUT')
                @include('usuarios.partials.form-fields', ['mostrarGuias' => false])

                <div class="d-flex flex-wrap" style="gap: 8px;">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Guardar cambios
                    </button>
                    <a href="{{ route('gestion.show', $usuario) }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
