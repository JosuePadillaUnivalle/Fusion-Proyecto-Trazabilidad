@extends('layouts.app')

@section('title', 'Prioridades | Fusion-Proyectos')
@section('page_title', 'Prioridades')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Catálogos</a></li>
    <li class="breadcrumb-item active">Prioridades</li>
@endsection

@push('styles')
@include('partials.modulo-catalogos-styles')
@endpush

@section('content')
@include('catalogos.partials.simple-index', [
    'titulo' => 'Prioridades',
    'subtitulo' => 'Niveles de prioridad operativa',
    'icono' => 'fa-flag',
    'items' => $prioridades,
    'routePrefix' => 'prioridades',
    'pk' => 'prioridadid',
    'singular' => 'prioridad',
    'tieneDescripcion' => false,
    'kpiClass' => 'small-box-red',
])
@endsection