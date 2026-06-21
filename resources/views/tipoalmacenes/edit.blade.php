@extends('layouts.app')

@section('title', 'Tipos de almacén | Fusion-Proyectos')
@section('page_title', 'Tipos de almacén')

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
    'esEdicion' => true,
    'item' => $item,
    'routePrefix' => 'tipoalmacenes',
    'singular' => 'Tipo de almacén',
    'tieneDescripcion' => true,
    'formAction' => route('tipoalmacenes.update', $item),
])
@endsection