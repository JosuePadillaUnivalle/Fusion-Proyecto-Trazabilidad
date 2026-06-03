@extends('layouts.app')

@section('title', 'Editar Almacén | AgroFusion')
@section('page_title', 'Editar Almacén')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.index') }}">Almacenes</a></li>
    <li class="breadcrumb-item active">Editar</li>
@endsection

@section('content')
<div class="modulo-inv page-almacen-form">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card form-card card-modulo-main">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-edit mr-2"></i>Editar: {{ $almacen->nombre }}
                    </h3>
                </div>
                <form action="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.update', $almacen) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        @include('almacenes.partials.form')
                    </div>
                    <div class="card-footer d-flex justify-content-between bg-white">
                        <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-1"></i> Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
