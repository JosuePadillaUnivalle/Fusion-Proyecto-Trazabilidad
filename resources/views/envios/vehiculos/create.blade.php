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
<div class="modulo-env page-veh-form">
    <form method="POST" action="{{ route('envios.vehiculos.store') }}">
        @csrf
        <div class="mb-3">
            @include('envios.partials.alertas')
        </div>
        @include('envios.vehiculos._form', [
            'showFormActions' => view('envios.vehiculos.partials.form-actions', [
                'cancelUrl' => route('envios.vehiculos'),
                'submitLabel' => 'Registrar vehículo',
            ])->render(),
        ])
    </form>
</div>
@endsection
