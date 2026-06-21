@extends('layouts.app')

@section('title', 'Editar cultivo | AgroFusion')
@section('page_title', 'Editar cultivo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cultivos.index') }}">Cultivos</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cultivos.show', $item) }}">{{ $item->nombre }}</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@push('styles')
@include('cultivos.partials.estilos')
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <a href="{{ route('cultivos.show', $item) }}" class="btn btn-outline-secondary btn-sm cv-btn mb-2">
            <i class="fas fa-arrow-left mr-1"></i>Volver al detalle
        </a>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @include('cultivos.partials.form', [
            'cultivo' => $item,
            'esEdicion' => true,
            'formAction' => route('cultivos.update', $item),
        ])
    </div>
</section>
@endsection
