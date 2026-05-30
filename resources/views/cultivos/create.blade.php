@extends('layouts.app')

@section('title', 'Nuevo cultivo | Fusion-Proyectos')
@section('page_title', 'Nuevo cultivo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Catálogos</a></li>
    <li class="breadcrumb-item active">Nuevo cultivo</li>
@endsection

@push('styles')
@include('partials.modulo-catalogos-styles')
@endpush

@section('content')
@include('catalogos.partials.simple-form-page', [
    'esEdicion' => false,
    'item' => null,
    'routePrefix' => 'cultivos',
    'singular' => 'Cultivo',
    'tieneDescripcion' => false,
    'formAction' => route('cultivos.store'),
])
@endsection