@extends('layouts.auth')

@section('title', 'Iniciar Sesión | AgroNexus')

@section('content')
<div class="form-header">
    <h2>¡Bienvenido!</h2>
    <p>Ingresa tus credenciales para acceder</p>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ $errors->first() }}</span>
    </div>
@endif

<form method="POST" action="{{ route('login.post') }}">
    @csrf

    <div class="form-group">
        <label for="email">Correo electrónico</label>
        <div class="input-wrapper">
            <input 
                id="email" 
                type="email" 
                name="email" 
                value="{{ old('email') }}" 
                required 
                autocomplete="email"
                autofocus
                class="form-control" 
                placeholder="tu@correo.com"
            >
            <i class="fas fa-envelope"></i>
        </div>
    </div>

    <div class="form-group">
        <label for="password">Contraseña</label>
        <div class="input-wrapper">
            <input 
                id="password" 
                type="password" 
                name="password" 
                required 
                autocomplete="current-password"
                class="form-control" 
                placeholder="••••••••"
            >
            <i class="fas fa-lock"></i>
        </div>
    </div>

    <div class="remember-row">
        <div class="checkbox-wrapper">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Recordarme</label>
        </div>
    </div>

    <button type="submit" class="btn-login">
        <i class="fas fa-sign-in-alt"></i>
        Iniciar Sesión
    </button>

    <div class="form-footer">
        <p>¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a></p>
    </div>
</form>
@endsection