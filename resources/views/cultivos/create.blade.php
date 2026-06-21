@extends('layouts.app')

@section('title', 'Nuevo cultivo | AgroFusion')
@section('page_title', 'Nuevo cultivo')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('cultivos.index') }}">Cultivos</a></li>
    <li class="breadcrumb-item active">Nuevo</li>
@endsection

@push('styles')
@include('cultivos.partials.estilos')
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <a href="{{ route('cultivos.index') }}" class="btn btn-outline-secondary btn-sm cv-btn mb-2">
            <i class="fas fa-arrow-left mr-1"></i>Volver al listado
        </a>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(!empty($retornoLote))
        <div class="alert alert-info border-0 shadow-sm mb-3" style="border-radius:12px;">
            <i class="fas fa-info-circle mr-1"></i>
            Al guardar, el cultivo se asignará al formulario de lote y esta pestaña se cerrará.
        </div>
        @endif

        @include('cultivos.partials.form', [
            'cultivo' => null,
            'esEdicion' => false,
            'formAction' => route('cultivos.store'),
            'camposOcultos' => !empty($retornoLote) ? [
                'retorno' => 'lote',
                'selector' => $selectorLoteId ?? 'lote_cultivo',
            ] : [],
        ])
    </div>
</section>
@endsection
