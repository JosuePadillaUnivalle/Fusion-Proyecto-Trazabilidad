@extends('layouts.app')

@section('title', 'Editar Historial | AgroNexus')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('historial-estados-lote.index') }}" style="color: #2c5530;">Historial</a>
    </li>
    <li class="breadcrumb-item active">Editar Registro</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow border-0 rounded-lg">
                <div class="card-header bg-success text-white py-3"
                    style="background: linear-gradient(135deg, #2c5530, #4a7c59);">
                    <h4 class="mb-0 font-weight-bold"><i class="fas fa-edit mr-2"></i> Editar Registro de Historial</h4>
                </div>

                <div class="card-body p-4">

                    @if($errors->any())
                        <div class="alert alert-danger border-left-danger shadow-sm">
                            <ul class="mb-0 pl-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('historial-estados-lote.update', $registro) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-row">
                            {{-- Lote --}}
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-dark">Lote <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i
                                                class="fas fa-layer-group text-success"></i></span>
                                    </div>
                                    <select name="loteid" class="form-control border-left-0" required>
                                        @foreach($lotes as $l)
                                            <option value="{{ $l->loteid }}" {{ $l->loteid == $registro->loteid ? 'selected' : '' }}>
                                                {{ $l->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Tipo de Estado --}}
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-dark">Nuevo Estado <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i
                                                class="fas fa-tag text-info"></i></span>
                                    </div>
                                    <select name="estadolotetipoid" class="form-control border-left-0" required>
                                        @foreach($tiposEstado as $t)
                                            <option value="{{ $t->estadolotetipoid }}" {{ $t->estadolotetipoid == $registro->estadolotetipoid ? 'selected' : '' }}>
                                                {{ $t->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            {{-- Usuario --}}
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-dark">Registrado Por</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i
                                                class="fas fa-user text-primary"></i></span>
                                    </div>
                                    <select name="usuarioid" class="form-control border-left-0">
                                        <option value="">-- Sin usuario asignado --</option>
                                        @foreach($usuarios as $u)
                                            <option value="{{ $u->usuarioid }}" {{ $u->usuarioid == $registro->usuarioid ? 'selected' : '' }}>
                                                {{ $u->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Fecha --}}
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold text-dark">Fecha del Evento</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i
                                                class="far fa-calendar-alt text-secondary"></i></span>
                                    </div>
                                    <input type="datetime-local" name="fecha_cambio" class="form-control border-left-0"
                                        value="{{ $registro->fecha_cambio }}">
                                </div>
                            </div>
                        </div>

                        {{-- URL Imagen --}}
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark">URL de Evidencia / Imagen</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"><i
                                            class="fas fa-link text-muted"></i></span>
                                </div>
                                <input type="text" name="imagenurl" class="form-control border-left-0"
                                    placeholder="http://ejemplo.com/imagen.jpg" maxlength="250"
                                    value="{{ $registro->imagenurl }}">
                            </div>
                            <small class="form-text text-muted">Enlace directo a una imagen registrada en el sistema
                                (opcional).</small>
                        </div>

                        {{-- Observaciones --}}
                        <div class="form-group mb-4">
                            <label class="font-weight-bold text-dark">Observaciones</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light border-right-0"><i
                                            class="fas fa-comment-dots text-secondary"></i></span>
                                </div>
                                <textarea name="observaciones" class="form-control border-left-0" rows="3"
                                    placeholder="Detalles adicionales sobre el cambio de estado...">{{ $registro->observaciones }}</textarea>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('historial-estados-lote.index') }}"
                                class="btn btn-secondary rounded-pill px-4 mr-2">
                                <i class="fas fa-times mr-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm"
                                style="background-color: #2c5530; border-color: #2c5530;">
                                <i class="fas fa-save mr-1"></i> Actualizar Cambios
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection