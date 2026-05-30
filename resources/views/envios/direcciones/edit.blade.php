@extends('layouts.app')

@section('title', 'Editar dirección | AgroFusion')
@section('page_title', 'Editar dirección')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.direcciones') }}">Direcciones</a></li>
    <li class="breadcrumb-item active">{{ $direccion->nombre }}</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
@endpush

@section('content')
<div class="modulo-env">
    <div class="card card-modulo-main">
        <div class="card-header">
            <h3 class="card-title mb-0"><i class="fas fa-edit text-success mr-2"></i>{{ $direccion->nombre }}</h3>
        </div>
        <form method="POST" action="{{ route('envios.direcciones.update', $direccion) }}">
            @csrf @method('PUT')
            <div class="card-body">
                @include('envios.partials.alertas')
                @include('envios.direcciones._form', ['direccion' => $direccion])
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('envios.direcciones.show', $direccion) }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Actualizar</button>
            </div>
        </form>
    </div>
</div>
@endsection
