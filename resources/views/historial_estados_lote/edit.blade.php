@extends('layouts.app')

@section('title', 'Editar historial | Fusion-Proyectos')
@section('page_title', 'Editar registro de historial')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Catálogos</a></li>
    <li class="breadcrumb-item"><a href="{{ route('historial-estados-lote.index') }}">Historial de estados</a></li>
    <li class="breadcrumb-item"><a href="{{ route('historial-estados-lote.show', $registro) }}">#{{ $registro->historial_estado_id }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@push('styles')
@include('partials.modulo-catalogos-styles')
@endpush

@section('content')
@include('catalogos.partials.historial-form-page', [
    'esEdicion' => true,
    'registro' => $registro,
    'lotes' => $lotes,
    'tiposEstado' => $tiposEstado,
    'usuarios' => $usuarios,
    'formAction' => route('historial-estados-lote.update', $registro),
])
@endsection
