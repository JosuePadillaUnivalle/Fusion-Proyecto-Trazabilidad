@extends('layouts.app')

@section('title', 'Información del lote | AgroFusion')
@section('page_title', $lote->nombre)

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('lotes.index') }}">Lotes</a></li>
    <li class="breadcrumb-item active">{{ $lote->nombre }}</li>
@endsection

@push('styles')
    @include('lotes.partials.detalle-styles')
@endpush

@section('content')
    @include('lotes.partials.detalle-header')
    @include('lotes.partials.detalle-stats')
    @include('lotes.partials.detalle-nav')

    <div class="row lote-content-grid">
        <div class="col-lg-6 mb-4 mb-lg-0">
            @include('lotes.partials.datos-lote-panel')
        </div>
        <div class="col-lg-6">
            @if($lote->imagenurl)
            <div class="lote-op-panel mb-4">
                <div class="lote-op-panel__head">
                    <div class="lote-op-panel__head-icon lote-op-panel__head-icon--photo"><i class="fas fa-image"></i></div>
                    <div>
                        <h3 class="lote-op-panel__title">Imagen del lote</h3>
                    </div>
                </div>
                <div class="lote-op-panel__photo">
                    <img src="{{ $lote->imagenurl }}" alt="Lote {{ $lote->nombre }}" class="img-fluid">
                </div>
            </div>
            @endif

            <div class="lote-op-panel">
                <div class="lote-op-panel__head">
                    <div class="lote-op-panel__head-icon"><i class="fas fa-chart-pie"></i></div>
                    <div>
                        <h3 class="lote-op-panel__title">Resumen operativo</h3>
                        <p class="lote-op-panel__subtitle">Actividad reciente en campo</p>
                    </div>
                </div>
                <div class="lote-op-grid">
                    <div class="lote-op-stat">
                        <div class="lote-op-stat__icon lote-op-stat__icon--green"><i class="fas fa-check-circle"></i></div>
                        <div class="lote-op-stat__val">{{ $estadisticas['actividades_completadas'] }}</div>
                        <div class="lote-op-stat__lbl">Completadas</div>
                    </div>
                    <div class="lote-op-stat">
                        <div class="lote-op-stat__icon lote-op-stat__icon--amber"><i class="fas fa-clock"></i></div>
                        <div class="lote-op-stat__val">{{ $estadisticas['actividades_pendientes'] }}</div>
                        <div class="lote-op-stat__lbl">Pendientes</div>
                    </div>
                    <div class="lote-op-stat">
                        <div class="lote-op-stat__icon lote-op-stat__icon--blue"><i class="fas fa-flask"></i></div>
                        <div class="lote-op-stat__val">{{ $estadisticas['total_insumos'] }}</div>
                        <div class="lote-op-stat__lbl">Aplicaciones</div>
                    </div>
                    <div class="lote-op-stat">
                        <div class="lote-op-stat__icon lote-op-stat__icon--teal"><i class="fas fa-leaf"></i></div>
                        <div class="lote-op-stat__val">{{ number_format($estadisticas['produccion_total'], 0) }}</div>
                        <div class="lote-op-stat__lbl">Kg producidos</div>
                    </div>
                </div>
                <a href="{{ route('lotes.trazabilidad', $lote) }}" class="lote-op-panel__cta">
                    <i class="fas fa-project-diagram"></i>
                    Ver trazabilidad completa
                    <i class="fas fa-arrow-right lote-op-panel__cta-arrow"></i>
                </a>
            </div>
        </div>
    </div>

    @include('lotes.partials.detalle-actions')
@endsection
