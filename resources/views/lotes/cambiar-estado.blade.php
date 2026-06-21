@extends('layouts.app')

@section('title', 'Cambiar estado — '.$lote->nombre)
@section('page_title', 'Cambiar estado del lote')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.index') }}">Lotes</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.show', $lote) }}">{{ $lote->nombre }}</a></li>
    <li class="breadcrumb-item active">Cambiar estado</li>
@endsection

@section('content')
@php
    use App\Support\EstadoLoteCatalogo;
    $meta = EstadoLoteCatalogo::ESTADOS[$slug] ?? null;
    $usuario = auth()->user();
    $nombreCompleto = trim(($usuario->nombre ?? '').' '.($usuario->apellido ?? ''));
    $rolLabel = ucfirst($usuario->role ?? 'Usuario');
@endphp
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-exchange-alt text-success mr-2"></i>
                    Cambiar estado: {{ $lote->nombre }}
                </h3>
            </div>
            <form action="{{ route('lotes.cambiar-estado.store', $lote) }}" method="POST">
                @csrf
                <input type="hidden" name="estado" value="{{ $slug }}">
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-light border mb-3">
                        <strong>Nuevo estado:</strong>
                        <span class="badge badge-success ml-1">{{ $meta['label'] ?? $slug }}</span>
                        @if(!empty($meta['descripcion']))
                            <p class="small text-muted mb-0 mt-2">{{ $meta['descripcion'] }}</p>
                        @endif
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-comment-alt mr-1"></i> Motivo del cambio <span class="text-danger">*</span></label>
                        <textarea name="motivo" class="form-control" rows="3" required maxlength="500"
                            placeholder="Describe por qué se cambia el estado del lote…">{{ old('motivo') }}</textarea>
                    </div>

                    <div class="card bg-light border-0">
                        <div class="card-body py-3">
                            <h6 class="text-muted text-uppercase small mb-2">Registro del cambio</h6>
                            <dl class="row mb-0 small">
                                <dt class="col-sm-4">Realizado por</dt>
                                <dd class="col-sm-8 mb-1">{{ $nombreCompleto ?: '—' }}</dd>
                                <dt class="col-sm-4">Rol</dt>
                                <dd class="col-sm-8 mb-1">{{ $rolLabel }}</dd>
                                <dt class="col-sm-4">Fecha</dt>
                                <dd class="col-sm-8 mb-0">{{ now()->format('d/m/Y H:i') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="{{ route('lotes.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i> Confirmar cambio
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
