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

@include('partials.form-errors-modal')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('.modulo-usu form');
    var password = document.getElementById('passwordhash');
    if (!form || !password) return;

    form.addEventListener('submit', function (e) {
        var valor = (password.value || '').trim();
        if (valor.length > 0 && valor.length < 5) {
            e.preventDefault();
            if (window.ModalConfirmar && typeof ModalConfirmar.aviso === 'function') {
                ModalConfirmar.aviso({
                    titulo: 'Contraseña muy corta',
                    mensaje: 'La contraseña debe tener al menos 5 caracteres.',
                    tono: 'warning',
                });
            }
            password.focus();
        }
    });
});
</script>
@endpush
