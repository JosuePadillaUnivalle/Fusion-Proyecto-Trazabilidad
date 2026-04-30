@extends('layouts.app')

@section('title', 'Panel Transportista | Fusion-Proyectos')
@section('page_title', 'Panel Transportista')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Panel Transportista</li>
@endsection
@push('styles')
<style>
.panel-card{border:0;border-radius:14px;box-shadow:0 8px 24px rgba(18,38,63,.08)}
.metric-card{border-radius:14px;color:#fff;position:relative;overflow:hidden}
.metric-card .icon{position:absolute;right:14px;top:10px;font-size:26px;opacity:.25}
.quick-link{border-radius:12px;padding:14px 16px;display:flex;align-items:center;gap:10px;font-weight:600}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Panel Transportista</h1>
        <p class="text-muted mb-0">Resumen de sus asignaciones, rutas e incidencias; enlace a seguimiento de envíos.</p>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 col-lg"><div class="card metric-card bg-primary panel-card"><div class="card-body"><i class="fas fa-clipboard-check icon"></i><h3 class="mb-1">{{ $stats['asignados'] }}</h3><p class="mb-0">Asignados</p></div></div></div>
            <div class="col-md-6 col-lg"><div class="card metric-card bg-warning panel-card"><div class="card-body"><i class="fas fa-dolly icon"></i><h3 class="mb-1">{{ $stats['por_recoger'] }}</h3><p class="mb-0">Por recoger</p></div></div></div>
            <div class="col-md-6 col-lg"><div class="card metric-card bg-info panel-card"><div class="card-body"><i class="fas fa-truck-moving icon"></i><h3 class="mb-1">{{ $stats['en_camino'] }}</h3><p class="mb-0">En camino</p></div></div></div>
            <div class="col-md-6 col-lg"><div class="card metric-card bg-success panel-card"><div class="card-body"><i class="fas fa-check-circle icon"></i><h3 class="mb-1">{{ $stats['entregados_hoy'] }}</h3><p class="mb-0">Entregados hoy</p></div></div></div>
            <div class="col-md-6 col-lg"><div class="card metric-card bg-secondary panel-card"><div class="card-body"><i class="fas fa-tachometer-alt icon"></i><h3 class="mb-1">{{ $stats['productividad'] }}%</h3><p class="mb-0">Productividad</p></div></div></div>
        </div>

        <div class="card panel-card">
            <div class="card-header border-0"><h3 class="card-title font-weight-bold">Accesos rápidos</h3></div>
            <div class="card-body">
                <div class="row">
                <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-info btn-block quick-link" href="{{ route('logistica.asignaciones.index') }}"><i class="fas fa-box"></i>Mis envíos</a></div>
                <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-success btn-block quick-link" href="{{ route('logistica.rutas.index') }}"><i class="fas fa-route"></i>Mis rutas</a></div>
                <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-secondary btn-block quick-link" href="{{ route('logistica.documentos.index') }}"><i class="fas fa-file-alt"></i>Documentos</a></div>
                <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-danger btn-block quick-link" href="{{ route('logistica.incidentes.index') }}"><i class="fas fa-exclamation-circle"></i>Incidentes</a></div>
                @can('reportes.view')
                <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-dark btn-block quick-link" href="{{ route('reportes.index') }}"><i class="fas fa-chart-line"></i>Reportes</a></div>
                @endcan
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

