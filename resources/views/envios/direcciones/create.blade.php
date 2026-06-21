@extends('layouts.app')

@section('title', 'Nueva dirección | AgroFusion')
@section('page_title', 'Nueva dirección')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.direcciones') }}">Direcciones</a></li>
    <li class="breadcrumb-item active">Nueva</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
@endpush

@section('content')
<div class="modulo-env">
    <div class="card card-modulo-main">
        <div class="card-header bg-success text-white">
            <h3 class="card-title mb-0"><i class="fas fa-map-pin mr-2"></i>Registrar dirección</h3>
        </div>
        <form method="POST" action="{{ route('envios.direcciones.store') }}">
            @csrf
            <div class="card-body">
                @include('envios.partials.alertas')
                @include('envios.direcciones._form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Guardar</button>
                <a href="{{ route('envios.direcciones') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
