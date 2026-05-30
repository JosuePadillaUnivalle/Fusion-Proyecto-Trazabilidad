@extends('layouts.app')

@section('title', 'Tipos de insumo | Fusion-Proyectos')
@section('page_title', 'Tipos de insumo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Catálogos</a></li>
    <li class="breadcrumb-item active">Tipos de insumo</li>
@endsection

@push('styles')
@include('partials.modulo-catalogos-styles')
@endpush

@section('content')
@include('catalogos.partials.simple-form-page', [
    'esEdicion' => true,
    'item' => $item,
    'routePrefix' => 'tipo-insumos',
    'singular' => 'Tipo de insumo',
    'tieneDescripcion' => false,
    'formAction' => route('tipo-insumos.update', $item),
])
@endsection