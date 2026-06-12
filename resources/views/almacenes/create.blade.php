@extends('layouts.app')

@section('title', 'Registrar Almacén | AgroFusion')
@section('page_title', 'Registrar Almacén')

@section('content')
<div class="modulo-inv page-almacen-form">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-9">
            <div class="card form-card card-modulo-main">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-warehouse mr-2"></i>Registrar almacén
                        <small class="d-block mt-1 font-weight-normal" style="opacity:.9">{{ $tituloModulo ?? 'Almacén' }}</small>
                    </h3>
                </div>
                <form action="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @include('almacenes.partials.form')
                    </div>
                    <div class="card-footer d-flex justify-content-between bg-white">
                        <a href="{{ route(($rutaPrefijo ?? 'almacen-agricola').'.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save mr-1"></i> Guardar almacén
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
