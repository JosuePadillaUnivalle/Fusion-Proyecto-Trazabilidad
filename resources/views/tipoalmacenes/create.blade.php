@extends('layouts.app')

@section('title', 'Nuevo tipo de almacén | Fusion-Proyectos')
@section('page_title', 'Nuevo tipo de almacén')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Catálogos</a></li>
    <li class="breadcrumb-item active">Tipos de almacén</li>
@endsection

@push('styles')
@include('partials.modulo-catalogos-styles')
@endpush

@section('content')
@include('catalogos.partials.simple-form-page', [
    'esEdicion' => false,
    'item' => null,
    'routePrefix' => 'tipoalmacenes',
    'singular' => 'Tipo de almacén',
    'tieneDescripcion' => true,
    'formAction' => route('tipoalmacenes.store'),
])
@endsection