@extends('layouts.app')

@section('title', 'Nuevo tipo de actividad | Fusion-Proyectos')
@section('page_title', 'Nuevo tipo de actividad')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Catálogos</a></li>
    <li class="breadcrumb-item active">Tipos de actividad</li>
@endsection

@push('styles')
@include('partials.modulo-catalogos-styles')
@endpush

@section('content')
@include('catalogos.partials.simple-form-page', [
    'esEdicion' => false,
    'item' => null,
    'routePrefix' => 'tipo-actividad',
    'singular' => 'Tipo de actividad',
    'tieneDescripcion' => true,
    'formAction' => route('tipo-actividad.store'),
])
@endsection