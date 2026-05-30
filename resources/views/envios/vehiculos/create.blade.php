@extends('layouts.app')

@section('title', 'Nuevo vehículo | AgroFusion')
@section('page_title', 'Nuevo vehículo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.vehiculos') }}">Vehículos</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
@endpush

@section('content')
<div class="modulo-env">
    <div class="card card-modulo-main">
        <div class="card-header bg-success text-white">
            <h3 class="card-title mb-0"><i class="fas fa-truck mr-2"></i>Registrar vehículo</h3>
        </div>
        <form method="POST" action="{{ route('envios.vehiculos.store') }}">
            @csrf
            <div class="card-body">
                @include('envios.partials.alertas')
                @include('envios.vehiculos._form')
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Guardar</button>
                <a href="{{ route('envios.vehiculos') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
