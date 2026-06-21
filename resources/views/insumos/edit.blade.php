@extends('layouts.app')

@section('title', 'Editar producto | AgroFusion')
@section('page_title', 'Editar producto')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    @if($modoProductoTerminado ?? false)
        <li class="breadcrumb-item"><a href="{{ $urlRetorno ?? '#' }}">Almacén</a></li>
    @else
        <li class="breadcrumb-item"><a href="{{ route('insumos.index') }}">Insumos</a></li>
    @endif
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
@if($modoProductoTerminado ?? false)
    @include('insumos.partials.form-producto-terminado', [
        'formAction' => route('insumos.update', $insumo),
        'formMethod' => 'PUT',
        'tituloFormulario' => 'Editar producto en almacén',
        'botonGuardar' => 'Guardar cambios',
        'insumo' => $insumo,
        'unidades' => $unidades ?? collect(),
        'urlRetorno' => $urlRetorno ?? route('insumos.index'),
    ])
@else
    @include('insumos.partials.form', [
        'formAction' => route('insumos.update', $insumo),
        'formMethod' => 'PUT',
        'tituloFormulario' => 'Editar insumo',
        'botonGuardar' => 'Guardar cambios',
        'insumo' => $insumo,
        'tipos' => $tipos,
        'unidadesPorTipo' => $unidadesPorTipo,
    ])
@endif
@endsection
