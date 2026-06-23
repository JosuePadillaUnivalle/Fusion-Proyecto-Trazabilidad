@extends('layouts.app')

@section('title', 'Nuevo proceso | AgroFusion')
@section('page_title', 'Nuevo proceso de transformación')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('plantillas-transformacion.index') }}">Procesos de transformación</a></li>
    <li class="breadcrumb-item active">Nueva</li>
@endsection

@push('styles')@include('partials.modulo-produccion-styles')@endpush

@section('content')
<div class="modulo-prod">
    <form method="POST" action="{{ route('plantillas-transformacion.store') }}">
        @csrf
        @if($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        @include('plantillas_transformacion.partials.form-proceso-layout', [
            'tituloHero' => 'Nuevo proceso de transformación',
            'nombreValor' => old('nombre'),
            'descripcionValor' => old('descripcion'),
            'pasosIniciales' => old('pasos', []),
        ])

        <div class="d-flex justify-content-between mt-3">
            <a href="{{ route('plantillas-transformacion.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left mr-1"></i> Cancelar</a>
            <button type="submit" class="btn btn-success px-4" id="btnGuardarProceso"><i class="fas fa-save mr-1"></i> Guardar proceso</button>
        </div>
    </form>
</div>
@endsection
