@extends('layouts.auth')

@section('title', 'Solicitud enviada | AgroFusion')

@section('content')
<div class="form-header text-center">
    <div style="display:flex;justify-content:center;font-size:3rem;color:#10b981;margin-bottom:12px;"><i class="fas fa-check-circle"></i></div>
    <h2>Solicitud enviada</h2>
    <p>Tu registro fue recibido y está <strong>pendiente de aprobación</strong>.</p>
    <p class="text-muted" style="font-size:.9rem;">Un administrador revisará tus datos y te notificará cuando puedas iniciar sesión.</p>
    <a href="{{ route('login') }}" class="btn-login" style="display:inline-flex;margin-top:20px;text-decoration:none;">
        <i class="fas fa-sign-in-alt"></i> Ir al inicio de sesión
    </a>
</div>
@endsection
