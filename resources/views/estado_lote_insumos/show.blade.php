@extends('layouts.app')

@section('title', 'Estados de aplicación de insumo | Fusion-Proyectos')
@section('page_title', 'Estados de aplicación de insumo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Catálogos</a></li>
    <li class="breadcrumb-item active">Estados de aplicación de insumo</li>
@endsection

@push('styles')
@include('partials.modulo-catalogos-styles')
@endpush

@section('content')
@include('catalogos.partials.simple-show', [
    'item' => $item,
    'routePrefix' => 'estado-lote-insumos',
    'pk' => 'estadoloteinsumoid',
    'singular' => 'estado de insumo',
    'icono' => 'fa-spray-can',
    'tieneDescripcion' => false,
])
@endsection