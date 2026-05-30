@extends('layouts.app')

@section('title', 'Registrar insumo | AgroFusion')
@section('page_title', 'Registrar insumo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('insumos.index') }}">Insumos</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@section('content')
@include('insumos.partials.form', [
    'formAction' => route('insumos.store'),
    'tituloFormulario' => 'Registrar insumo',
    'botonGuardar' => 'Guardar insumo',
    'tipos' => $tipos,
    'unidadesPorTipo' => $unidadesPorTipo,
])
@endsection
