@extends('layouts.app')

@section('title', 'Editar transportista | AgroFusion')
@section('page_title', 'Editar transportista')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.transportistas') }}">Transportistas</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
@endpush

@section('content')
<div class="modulo-env">
    <div class="card card-modulo-main">
        <div class="card-header">
            <h3 class="card-title mb-0"><i class="fas fa-edit text-success mr-2"></i>{{ $transportista->nombreCompleto() }}</h3>
        </div>
        <form method="POST" action="{{ route('envios.transportistas.update', $transportista) }}">
            @csrf @method('PUT')
            <div class="card-body">
                @include('envios.partials.alertas')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input name="nombre" class="form-control" value="{{ old('nombre', $transportista->nombre) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Apellido</label>
                            <input name="apellido" class="form-control" value="{{ old('apellido', $transportista->apellido) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Correo <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $transportista->email) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Usuario</label>
                            <input name="nombreusuario" class="form-control" value="{{ old('nombreusuario', $transportista->nombreusuario) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input name="telefono" class="form-control" value="{{ old('telefono', $transportista->telefono) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nueva contraseña</label>
                            <input type="password" name="password" class="form-control" placeholder="Dejar vacío para no cambiar">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <input type="hidden" name="activo" value="0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="activoTransportista" name="activo" value="1"
                                   @checked(old('activo', $transportista->activo))>
                            <label class="custom-control-label" for="activoTransportista">Transportista activo</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('envios.transportistas.show', $transportista) }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Actualizar</button>
            </div>
        </form>
    </div>
</div>
@endsection
