@extends('layouts.app')

@section('title', 'Nuevo estado de lote | Fusion-Proyectos')
@section('page_title', 'Nuevo estado de lote')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Catálogos</a></li>
    <li class="breadcrumb-item active">Estados de lote</li>
@endsection

@push('styles')
@include('partials.modulo-catalogos-styles')
@endpush

@section('content')
@include('catalogos.partials.simple-form-page', [
    'esEdicion' => false,
    'item' => null,
    'routePrefix' => 'estado-lote-tipos',
    'singular' => 'Estado de lote',
    'tieneDescripcion' => true,
    'formAction' => route('estado-lote-tipos.store'),
])
@endsection