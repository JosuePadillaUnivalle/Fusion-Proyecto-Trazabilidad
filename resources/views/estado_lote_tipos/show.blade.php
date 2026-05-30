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
@include('catalogos.partials.simple-show', [
    'item' => $item,
    'routePrefix' => 'estado-lote-tipos',
    'pk' => 'estadolotetipoid',
    'singular' => 'estado de lote',
    'icono' => 'fa-map-marker-alt',
    'tieneDescripcion' => true,
])
@endsection