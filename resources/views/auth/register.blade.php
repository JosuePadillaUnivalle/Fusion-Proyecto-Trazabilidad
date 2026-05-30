@extends('layouts.auth')

@section('title', 'Registro | AgroFusion')

@push('styles')
<style>
    .form-side {
        overflow-y: auto;
        padding: 40px 60px;
    }
    
    .form-row {
        display: flex;
        gap: 20px;
    }
    
    .form-row .form-group {
        flex: 1;
    }
    
    @media (max-width: 992px) {
        .form-row {
            flex-direction: column;
            gap: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="form-header">
    <h2>Crear Cuenta</h2>
    <p>Únete a AgroFusion y optimiza tu producción</p>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ $errors->first() }}</span>
    </div>
@endif

<form method="POST" action="{{ route('register.post') }}">
    @csrf

    <div class="form-row">
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <div class="input-wrapper">
                <input 
                    id="nombre" 
                    type="text" 
                    name="nombre" 
                    value="{{ old('nombre') }}" 
                    required 
                    class="form-control" 
                    placeholder="Juan"
                >
                <i class="fas fa-user"></i>
            </div>
        </div>

        <div class="form-group">
            <label for="apellido">Apellido</label>
            <div class="input-wrapper">
                <input 
                    id="apellido" 
                    type="text" 
                    name="apellido" 
                    value="{{ old('apellido') }}" 
                    required 
                    class="form-control" 
                    placeholder="Pérez"
                >
                <i class="fas fa-user"></i>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="email">Correo electrónico</label>
        <div class="input-wrapper">
            <input 
                id="email" 
                type="email" 
                name="email" 
                value="{{ old('email') }}" 
                required 
                class="form-control" 
                placeholder="tu@correo.com"
            >
            <i class="fas fa-envelope"></i>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="nombreusuario">Usuario</label>
            <div class="input-wrapper">
                <input 
                    id="nombreusuario" 
                    type="text" 
                    name="nombreusuario" 
                    value="{{ old('nombreusuario') }}" 
                    required 
                    class="form-control" 
                    placeholder="juanperez"
                >
                <i class="fas fa-at"></i>
            </div>
        </div>

        <div class="form-group">
            <label for="telefono">Teléfono <small style="color:#adb5bd">(opcional)</small></label>
            <div class="input-wrapper">
                <input 
                    id="telefono" 
                    type="text" 
                    name="telefono" 
                    value="{{ old('telefono') }}" 
                    class="form-control" 
                    placeholder="70000000"
                >
                <i class="fas fa-phone"></i>
            </div>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="password">Contraseña</label>
            <div class="input-wrapper">
                <input 
                    id="password" 
                    type="password" 
                    name="password" 
                    required 
                    class="form-control" 
                    placeholder="••••••••"
                >
                <i class="fas fa-lock"></i>
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmar</label>
            <div class="input-wrapper">
                <input 
                    id="password_confirmation" 
                    type="password" 
                    name="password_confirmation" 
                    required 
                    class="form-control" 
                    placeholder="••••••••"
                >
                <i class="fas fa-lock"></i>
            </div>
        </div>
    </div>

    <button type="submit" class="btn-login" style="margin-top: 10px;">
        <i class="fas fa-user-plus"></i>
        Crear Cuenta
    </button>

    <div class="form-footer">
        <p>¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a></p>
    </div>
</form>
@endsection