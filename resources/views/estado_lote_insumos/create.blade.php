@extends('layouts.app')

@section('title', 'Nuevo estado de insumo | Fusion-Proyectos')
@section('page_title', 'Nuevo estado de insumo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Catálogos</a></li>
    <li class="breadcrumb-item active">Estados de aplicación de insumo</li>
@endsection

@push('styles')
@include('partials.modulo-catalogos-styles')
@endpush

@section('content')
@include('catalogos.partials.simple-form-page', [
    'esEdicion' => false,
    'item' => null,
    'routePrefix' => 'estado-lote-insumos',
    'singular' => 'Estado de insumo',
    'tieneDescripcion' => false,
    'formAction' => route('estado-lote-insumos.store'),
])
@endsection