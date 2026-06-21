@extends('layouts.app')

@section('title', 'Estados de lote | Fusion-Proyectos')
@section('page_title', 'Estados de lote')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Catálogos</a></li>
    <li class="breadcrumb-item active">Estados de lote</li>
@endsection

@push('styles')
@include('partials.modulo-catalogos-styles')
@endpush

@section('content')
@include('catalogos.partials.simple-index', [
    'titulo' => 'Estados de lote',
    'subtitulo' => 'Estados del ciclo de vida de un lote',
    'icono' => 'fa-map-marker-alt',
    'items' => $tipos,
    'routePrefix' => 'estado-lote-tipos',
    'pk' => 'estadolotetipoid',
    'singular' => 'estado de lote',
    'tieneDescripcion' => true,
    'kpiClass' => 'small-box-red',
])
@endsection