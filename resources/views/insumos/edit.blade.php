@extends('layouts.app')

@section('title', 'Editar insumo | AgroFusion')
@section('page_title', 'Editar insumo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('insumos.index') }}">Insumos</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
@include('insumos.partials.form', [
    'formAction' => route('insumos.update', $insumo),
    'formMethod' => 'PUT',
    'tituloFormulario' => 'Editar insumo',
    'botonGuardar' => 'Guardar cambios',
    'insumo' => $insumo,
    'tipos' => $tipos,
    'unidadesPorTipo' => $unidadesPorTipo,
])
@endsection
