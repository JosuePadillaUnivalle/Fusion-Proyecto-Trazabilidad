@extends('layouts.app')

@section('title', 'Tipos de actividad | Fusion-Proyectos')
@section('page_title', 'Tipos de actividad')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Catálogos</a></li>
    <li class="breadcrumb-item active">Tipos de actividad</li>
@endsection

@push('styles')
@include('partials.modulo-catalogos-styles')
@endpush

@section('content')
@include('catalogos.partials.simple-show', [
    'item' => $item,
    'routePrefix' => 'tipo-actividad',
    'pk' => 'tipoactividadid',
    'singular' => 'tipo de actividad',
    'icono' => 'fa-tasks',
    'tieneDescripcion' => true,
])
@endsection