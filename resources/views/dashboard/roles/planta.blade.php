@extends('layouts.app')

@section('title', 'Panel Planta | AgroFusion')
@section('page_title', 'Panel Planta')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Panel Planta</li>
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
        <h1 class="m-0">Panel Planta</h1>
        <p class="text-muted mb-0">Resumen de logística (organización) y accesos a envíos, rutas, documentos e incidentes.</p>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @include('partials.dashboard-alertas')

        @if(auth()->user() && \App\Support\UsuarioRol::esOperarioPlanta(auth()->user()) && ($tareasPendientesCount ?? 0) > 0)
        <div class="alert alert-warning border-0 shadow-sm d-flex flex-wrap justify-content-between align-items-center mb-3" style="gap:.75rem;">
            <div>
                <strong><i class="fas fa-industry mr-1"></i>Tiene {{ $tareasPendientesCount }} tarea(s) de transformación pendiente(s)</strong>
                <span class="d-block small text-muted">El jefe de planta le asignó trabajo en maquinaria. Revise el detalle y márquelas como completadas.</span>
            </div>
            <a href="{{ route('tareas-planta.index') }}" class="btn btn-warning btn-sm font-weight-bold">
                <i class="fas fa-tasks mr-1"></i>Ver mis tareas
            </a>
        </div>
        @endif

        <div class="row">
            <div class="col-md-6 col-lg">
                <div class="card metric-card bg-success panel-card"><div class="card-body"><i class="fas fa-boxes icon"></i><h3 class="mb-1">{{ $stats['pedidos_totales'] }}</h3><p class="mb-0">Pedidos</p></div></div>
            </div>
            <div class="col-md-6 col-lg">
                <div class="card metric-card bg-primary panel-card"><div class="card-body"><i class="fas fa-user-tag icon"></i><h3 class="mb-1">{{ $stats['asignaciones'] }}</h3><p class="mb-0">Asignaciones</p></div></div>
            </div>
            <div class="col-md-6 col-lg">
                <div class="card metric-card bg-info panel-card"><div class="card-body"><i class="fas fa-route icon"></i><h3 class="mb-1">{{ $stats['rutas_activas'] }}</h3><p class="mb-0">Rutas activas</p></div></div>
            </div>
            <div class="col-md-6 col-lg">
                <div class="card metric-card bg-warning panel-card"><div class="card-body"><i class="fas fa-exclamation-triangle icon"></i><h3 class="mb-1">{{ $stats['incidentes_abiertos'] }}</h3><p class="mb-0">Incidentes</p></div></div>
            </div>
            <div class="col-md-6 col-lg">
                <div class="card metric-card bg-secondary panel-card"><div class="card-body"><i class="fas fa-file-signature icon"></i><h3 class="mb-1">{{ $stats['documentos'] }}</h3><p class="mb-0">Documentos</p></div></div>
            </div>
        </div>

        <div class="card panel-card">
            <div class="card-header border-0"><h3 class="card-title font-weight-bold">Accesos rápidos</h3></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-primary btn-block quick-link" href="{{ route('envios.mandar') }}"><i class="fas fa-plus-circle"></i>Crear envío</a></div>
                    <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-info btn-block quick-link" href="{{ route('envios.seguimiento') }}"><i class="fas fa-truck"></i>Mis envíos</a></div>
                    @can('pedidos.view')
                    <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-success btn-block quick-link" href="{{ route('pedidos.index') }}"><i class="fas fa-clipboard-list"></i>Gestión de pedidos</a></div>
                    @endcan
                    @can('lote_produccion.view')
                    <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-primary btn-block quick-link" href="{{ route('procesamiento.index') }}"><i class="fas fa-industry"></i>Procesamiento de Lote</a></div>
                    @endcan
                    @if(auth()->user() && \App\Support\UsuarioRol::esOperarioPlanta(auth()->user()))
                    <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-warning btn-block quick-link" href="{{ route('tareas-planta.index') }}"><i class="fas fa-tasks"></i>Mis tareas</a></div>
                    @endif
                    <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-warning btn-block quick-link" href="{{ route('logistica.asignaciones.index') }}"><i class="fas fa-user-tag"></i>Asignaciones</a></div>
                    <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-success btn-block quick-link" href="{{ route('logistica.rutas.index') }}"><i class="fas fa-route"></i>Rutas de entrega</a></div>
                    <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-secondary btn-block quick-link" href="{{ route('logistica.documentos.index') }}"><i class="fas fa-file-alt"></i>Documentos</a></div>
                    <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-danger btn-block quick-link" href="{{ route('logistica.incidentes.index') }}"><i class="fas fa-exclamation-circle"></i>Incidentes</a></div>
                    <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-dark btn-block quick-link" href="{{ route('ventas.index') }}"><i class="fas fa-receipt"></i>Notas de venta</a></div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

