@extends('layouts.app')

@section('title', 'Detalle certificación planta')
@section('page_title', 'Detalle de certificación')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('certificaciones-planta.index') }}">Certificaciones de planta</a></li>
    <li class="breadcrumb-item active">{{ $eval->loteProduccionPedido->codigo_lote ?? 'Evaluación #'.$eval->evaluacionfinalloteid }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card shadow-sm border-0" style="border-radius: 16px; overflow: hidden;">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3 px-4">
                    <strong class="text-primary" style="font-size: 1.05rem;">
                        <i class="fas fa-file-certificate mr-2"></i>Certificación de planta
                    </strong>
                    <a href="{{ route('certificaciones-planta.index') }}" class="btn btn-outline-secondary px-3 py-2" style="border-radius: 10px; font-weight: 600;">
                        <i class="fas fa-arrow-left mr-1"></i>Volver
                    </a>
                </div>
                <div class="card-body px-4 pb-4">
                    @include('certificaciones-planta.partials.detalle-contenido', ['eval' => $eval])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
