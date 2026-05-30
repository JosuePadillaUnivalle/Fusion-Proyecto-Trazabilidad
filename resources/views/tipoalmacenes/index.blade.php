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
@include('catalogos.partials.simple-index', [
    'titulo' => 'Tipos de almacén',
    'subtitulo' => 'Clasificación de almacenes logísticos',
    'icono' => 'fa-warehouse',
    'items' => $tipos,
    'routePrefix' => 'tipoalmacenes',
    'pk' => 'tipoalmacenid',
    'singular' => 'tipo de almacén',
    'tieneDescripcion' => true,
    'kpiClass' => 'small-box-blue',
])
@endsection