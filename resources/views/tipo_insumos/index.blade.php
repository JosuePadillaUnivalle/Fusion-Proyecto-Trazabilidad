@extends('layouts.app')

@section('title', 'Tipos de insumo | Fusion-Proyectos')
@section('page_title', 'Tipos de insumo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Catálogos</a></li>
    <li class="breadcrumb-item active">Tipos de insumo</li>
@endsection

@push('styles')
@include('partials.modulo-catalogos-styles')
@endpush

@section('content')
@include('catalogos.partials.simple-index', [
    'titulo' => 'Tipos de insumo',
    'subtitulo' => 'Categorías de insumos agrícolas',
    'icono' => 'fa-flask',
    'items' => $tipos,
    'routePrefix' => 'tipo-insumos',
    'pk' => 'tipoinsumoid',
    'singular' => 'tipo de insumo',
    'tieneDescripcion' => false,
    'kpiClass' => 'small-box-orange',
])
@endsection