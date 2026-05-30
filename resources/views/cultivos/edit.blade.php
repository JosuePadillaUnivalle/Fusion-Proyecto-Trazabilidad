@extends('layouts.app')

@section('title', 'Cultivos | Fusion-Proyectos')
@section('page_title', 'Cultivos')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Catálogos</a></li>
    <li class="breadcrumb-item active">Cultivos</li>
@endsection

@push('styles')
@include('partials.modulo-catalogos-styles')
@endpush

@section('content')
@include('catalogos.partials.simple-form-page', [
    'esEdicion' => true,
    'item' => $item,
    'routePrefix' => 'cultivos',
    'singular' => 'Cultivo',
    'tieneDescripcion' => false,
    'formAction' => route('cultivos.update', $item),
])
@endsection