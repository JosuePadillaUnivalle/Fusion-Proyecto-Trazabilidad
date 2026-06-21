@extends('layouts.app')

@section('title', 'Nuevo historial | Fusion-Proyectos')
@section('page_title', 'Nuevo registro de historial')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Catálogos</a></li>
    <li class="breadcrumb-item"><a href="{{ route('historial-estados-lote.index') }}">Historial de estados</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@push('styles')
@include('partials.modulo-catalogos-styles')
@endpush

@section('content')
@include('catalogos.partials.historial-form-page', [
    'esEdicion' => false,
    'registro' => null,
    'lotes' => $lotes,
    'tiposEstado' => $tiposEstado,
    'usuarios' => $usuarios,
    'formAction' => route('historial-estados-lote.store'),
])
@endsection
