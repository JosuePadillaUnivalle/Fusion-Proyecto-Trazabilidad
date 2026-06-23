@extends('layouts.app')

@section('title', 'Nueva variable estándar | AgroFusion')
@section('page_title', 'Nueva variable estándar')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('variables-estandar.index') }}">Variables estándar</a></li>
    <li class="breadcrumb-item active">Crear</li>
@endsection

@push('styles')@include('partials.modulo-produccion-styles')@endpush

@section('content')
<div class="modulo-prod">
    <div class="card card-outline card-success card-modulo-main elevation-1">
        <div class="card-header"><h3 class="card-title mb-0"><i class="fas fa-plus mr-2"></i>Nueva variable</h3></div>
        <form method="POST" action="{{ route('variables-estandar.store') }}">
            @csrf
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                @include('variables_estandar.partials.form-fields')
            </div>
            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('variables-estandar.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection
