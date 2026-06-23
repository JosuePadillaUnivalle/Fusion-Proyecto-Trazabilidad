@extends('layouts.auth')

@section('title', 'Iniciar Sesión | AgroFusion')

@section('content')
<div class="form-header">
    <h2>¡Bienvenido!</h2>
    <p>Ingresa tus credenciales para acceder</p>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ $errors->first() }}</span>
    </div>
@endif

<form method="POST" action="{{ route('login.post') }}" id="formLoginAgrofusion">
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
                autocomplete="username email"
                autocorrect="off"
                autocapitalize="none"
                spellcheck="false"
                @if(! old('email')) autofocus @endif
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
            <input type="checkbox" id="remember" name="remember" value="1" checked>
            <label for="remember">Recordarme</label>
        </div>
    </div>

    <button type="submit" class="btn-login" id="btnLoginAgrofusion">
        <i class="fas fa-sign-in-alt"></i>
        Iniciar Sesión
    </button>

    <div class="form-footer">
        <p>¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a></p>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('formLoginAgrofusion');
    var btn = document.getElementById('btnLoginAgrofusion');
    if (!form || !btn) return;

    form.addEventListener('submit', function () {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ingresando…';
    });
});
</script>
@endpush
