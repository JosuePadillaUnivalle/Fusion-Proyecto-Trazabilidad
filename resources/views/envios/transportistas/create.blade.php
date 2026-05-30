@extends('layouts.app')

@section('title', 'Nuevo transportista | AgroFusion')
@section('page_title', 'Nuevo transportista')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.transportistas') }}">Transportistas</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
@endpush

@section('content')
<div class="modulo-env">
    <div class="card card-modulo-main">
        <div class="card-header bg-success text-white">
            <h3 class="card-title mb-0"><i class="fas fa-user-plus mr-2"></i>Registrar transportista</h3>
        </div>
        <form method="POST" action="{{ route('envios.transportistas.store') }}">
            @csrf
            <div class="card-body">
                @include('envios.partials.alertas')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input name="nombre" class="form-control" value="{{ old('nombre') }}" required maxlength="100">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Apellido</label>
                            <input name="apellido" class="form-control" value="{{ old('apellido') }}" maxlength="100">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Correo <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Usuario de acceso</label>
                            <input name="nombreusuario" class="form-control" value="{{ old('nombreusuario') }}" placeholder="Opcional; por defecto parte del correo">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input name="telefono" class="form-control" value="{{ old('telefono') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Contraseña inicial</label>
                            <input type="password" name="password" class="form-control" placeholder="Por defecto: 12345">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Guardar</button>
                <a href="{{ route('envios.transportistas') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
