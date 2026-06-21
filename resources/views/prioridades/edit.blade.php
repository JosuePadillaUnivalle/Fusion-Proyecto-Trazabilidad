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
@include('catalogos.partials.simple-form-page', [
    'esEdicion' => true,
    'item' => $item,
    'routePrefix' => 'prioridades',
    'singular' => 'Prioridad',
    'tieneDescripcion' => false,
    'formAction' => route('prioridades.update', $item),
])
@endsection