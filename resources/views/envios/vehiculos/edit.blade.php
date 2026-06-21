@extends('layouts.app')

@section('title', 'Editar vehículo | AgroFusion')
@section('page_title', 'Editar vehículo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.vehiculos') }}">Vehículos</a></li>
    <li class="breadcrumb-item"><a href="{{ route('envios.vehiculos.show', $vehiculo) }}">{{ $vehiculo->placa }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@push('styles')
@include('partials.modulo-envios-styles')
@endpush

@section('content')
<div class="modulo-env page-veh-form">
    <form method="POST" action="{{ route('envios.vehiculos.update', $vehiculo) }}">
        @csrf @method('PUT')
        <div class="mb-3">
            @include('envios.partials.alertas')
        </div>
        @include('envios.vehiculos._form', [
            'vehiculo' => $vehiculo,
            'showFormActions' => view('envios.vehiculos.partials.form-actions', [
                'cancelUrl' => route('envios.vehiculos.show', $vehiculo),
                'submitLabel' => 'Actualizar vehículo',
            ])->render(),
        ])
    </form>

    @include('envios.partials.tipos-vehiculo-catalogo', ['tipos' => $tiposCatalogo ?? collect()])
</div>
@endsection
