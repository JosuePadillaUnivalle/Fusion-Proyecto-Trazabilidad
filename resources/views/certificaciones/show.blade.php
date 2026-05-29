@extends('layouts.app')

@section('title', 'Detalle certificado')
@section('page_title', 'Detalle del certificado')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('certificaciones.index') }}">Certificaciones</a></li>
    <li class="breadcrumb-item active">{{ $cert->codigo_certificado }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong><i class="fas fa-file-certificate text-success mr-2"></i>Certificado emitido</strong>
                    <a href="{{ route('certificaciones.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Volver
                    </a>
                </div>
                <div class="card-body">
                    @include('certificaciones.partials.detalle-contenido', ['cert' => $cert])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
