@extends('layouts.app')

@section('title', 'Nueva unidad de medida | Fusion-Proyectos')
@section('page_title', 'Nueva unidad de medida')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Catálogos</a></li>
    <li class="breadcrumb-item active">Unidades de medida</li>
@endsection

@push('styles')
@include('partials.modulo-catalogos-styles')
@endpush

@section('content')
@include('catalogos.partials.simple-form-page', [
    'esEdicion' => false,
    'item' => null,
    'routePrefix' => 'unidades-medida',
    'singular' => 'Unidad de medida',
    'tieneDescripcion' => false,
    'formAction' => route('unidades-medida.store'),
])
@endsection