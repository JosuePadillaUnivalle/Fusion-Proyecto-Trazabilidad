@extends('layouts.app')

@section('title', 'Editar usuario | AgroFusion')
@section('page_title', 'Editar usuario')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('gestion.index') }}">Gestión de usuarios</a></li>
    <li class="breadcrumb-item"><a href="{{ route('gestion.show', $usuario) }}">{{ $usuario->nombre }} {{ $usuario->apellido }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@push('styles')
@include('partials.modulo-usuarios-styles')
<style>
.usu-edit-page { max-width: 960px; margin: 0 auto; }
.usu-edit-back {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: #475569;
    font-weight: 500;
    font-size: 0.9rem;
    margin-bottom: 1.25rem;
    text-decoration: none;
    transition: color 0.2s;
}
.usu-edit-back:hover { color: #2c5530; text-decoration: none; }
.usu-edit-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(15, 23, 42, 0.08);
    overflow: hidden;
    border: 1px solid #e8ecef;
}
.usu-edit-hero {
    background: linear-gradient(135deg, #1e3d22 0%, #2c5530 45%, #4a7c59 100%);
    color: #fff;
    padding: 1.75rem 2rem;
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
}
.usu-edit-avatar {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    border: 3px solid rgba(255, 255, 255, 0.45);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    flex-shrink: 0;
}
.usu-edit-hero-text h2 {
    font-size: 1.45rem;
    font-weight: 700;
    margin: 0 0 0.25rem;
}
.usu-edit-hero-text p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.95rem;
}
.usu-edit-hero-badge {
    margin-left: auto;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 999px;
    padding: 0.35rem 0.9rem;
    font-size: 0.8rem;
    font-weight: 600;
}
.usu-edit-body { padding: 2rem; }
.usu-edit-section {
    margin-bottom: 1.75rem;
    padding-bottom: 1.75rem;
    border-bottom: 1px solid #eef1f4;
}
.usu-edit-section-title {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #2c5530;
    margin-bottom: 1.1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.usu-edit-section-title i { opacity: 0.75; }
.usu-edit-field label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    margin-bottom: 0.4rem;
}
.usu-edit-input-wrap {
    position: relative;
}
.usu-edit-input-wrap > i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.9rem;
    z-index: 2;
    pointer-events: none;
}
.usu-edit-input-wrap .form-control {
    border-radius: 10px;
    border: 1px solid #dde3ea;
    padding: 0.6rem 0.85rem 0.6rem 2.5rem;
    height: auto;
    font-size: 0.95rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.usu-edit-input-wrap select.form-control {
    padding-left: 2.5rem;
    appearance: auto;
}
.usu-edit-input-wrap .form-control:focus {
    border-color: #4a7c59;
    box-shadow: 0 0 0 3px rgba(74, 124, 89, 0.15);
}
.usu-edit-error {
    display: block;
    color: #dc3545;
    font-size: 0.8rem;
    margin-top: 0.35rem;
}
.usu-edit-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.65rem;
    padding-top: 0.5rem;
    border-top: 1px solid #eef1f4;
    margin-top: 0.5rem;
}
.usu-edit-btn-save {
    background: linear-gradient(135deg, #2c5530, #4a7c59);
    border: none;
    border-radius: 10px;
    padding: 0.65rem 1.5rem;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(44, 85, 48, 0.25);
    transition: transform 0.15s, box-shadow 0.15s;
}
.usu-edit-btn-save:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(44, 85, 48, 0.3);
    background: linear-gradient(135deg, #254a28, #3d6b4c);
}
.usu-edit-btn-cancel {
    border-radius: 10px;
    padding: 0.65rem 1.25rem;
    font-weight: 500;
    border-color: #dde3ea;
    color: #64748b;
}
.usu-edit-btn-cancel:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #334155;
}
@media (max-width: 767px) {
    .usu-edit-hero { padding: 1.25rem; }
    .usu-edit-body { padding: 1.25rem; }
    .usu-edit-hero-badge { margin-left: 0; width: 100%; text-align: center; }
}
</style>
@endpush

@section('content')
<div class="modulo-usu usu-edit-page">

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-lg mb-3">
        <strong><i class="fas fa-exclamation-triangle mr-1"></i> Revisa el formulario.</strong>
        <ul class="mb-0 mt-2 pl-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
    @endif

    <a href="{{ route('gestion.show', $usuario) }}" class="usu-edit-back">
        <i class="fas fa-arrow-left"></i> Volver al detalle
    </a>

    <div class="usu-edit-card">
        <div class="usu-edit-hero">
            <div class="usu-edit-avatar">
                {{ strtoupper(substr($usuario->nombre, 0, 1).substr($usuario->apellido, 0, 1)) }}
            </div>
            <div class="usu-edit-hero-text">
                <h2>{{ $usuario->nombre }} {{ $usuario->apellido }}</h2>
                <p>{{ '@'.$usuario->nombreusuario }} · {{ $usuario->email }}</p>
            </div>
            <span class="usu-edit-hero-badge">
                <i class="fas fa-user-shield mr-1"></i>
                {{ ucfirst($usuario->roles->first()?->name ?? 'Sin rol') }}
            </span>
        </div>

        <div class="usu-edit-body">
            <form method="POST" action="{{ route('gestion.usuario.update', $usuario) }}">
                @csrf
                @method('PUT')

                @include('usuarios.partials.form-edit-fields', ['modoJefe' => $modoJefe ?? false])

                <div class="usu-edit-actions">
                    <button type="submit" class="btn btn-success usu-edit-btn-save">
                        <i class="fas fa-save mr-1"></i> Guardar cambios
                    </button>
                    <a href="{{ route('gestion.show', $usuario) }}" class="btn btn-outline-secondary usu-edit-btn-cancel">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
