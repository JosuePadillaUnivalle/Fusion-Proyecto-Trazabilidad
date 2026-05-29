@extends('layouts.app')

@section('title', 'Panel Almacén | AgroNexus')
@section('page_title', 'Panel Almacén')

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
        <h1 class="m-0">Panel Almacén</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 col-lg-4"><div class="card metric-card bg-success panel-card"><div class="card-body"><i class="fas fa-inbox icon"></i><h3 class="mb-1">{{ $stats['envios_recibidos'] }}</h3><p class="mb-0">Envíos recibidos</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card metric-card bg-warning panel-card"><div class="card-body"><i class="fas fa-clock icon"></i><h3 class="mb-1">{{ $stats['por_recibir'] }}</h3><p class="mb-0">Por recibir</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card metric-card bg-info panel-card"><div class="card-body"><i class="fas fa-boxes icon"></i><h3 class="mb-1">{{ $stats['inventario_total'] }}</h3><p class="mb-0">Inventario total</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card metric-card bg-primary panel-card"><div class="card-body"><i class="fas fa-calendar-day icon"></i><h3 class="mb-1">{{ $stats['recibidos_hoy'] }}</h3><p class="mb-0">Recibidos hoy</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card metric-card bg-teal panel-card"><div class="card-body"><i class="fas fa-arrow-down icon"></i><h3 class="mb-1">{{ $stats['ingresos_mes'] ?? 0 }}</h3><p class="mb-0">Ingresos del mes</p></div></div></div>
            <div class="col-md-6 col-lg-4"><div class="card metric-card bg-orange panel-card"><div class="card-body"><i class="fas fa-arrow-up icon"></i><h3 class="mb-1">{{ $stats['salidas_mes'] ?? 0 }}</h3><p class="mb-0">Salidas del mes</p></div></div></div>
        </div>

        <div class="card panel-card">
            <div class="card-header border-0"><h3 class="card-title font-weight-bold">Accesos rápidos</h3></div>
            <div class="card-body">
                <div class="row">
                <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-success btn-block quick-link" href="{{ route('envios.seguimiento') }}"><i class="fas fa-inbox"></i>Envíos recibidos</a></div>
                <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-info btn-block quick-link" href="{{ route('insumos.index') }}"><i class="fas fa-warehouse"></i>Inventario</a></div>
                @can('almacen.movimientos.view')
                <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-warning btn-block quick-link" href="{{ route('almacen-movimientos.index') }}"><i class="fas fa-exchange-alt"></i>Movimientos</a></div>
                @endcan
                @can('almacen.reportes.view')
                <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-primary btn-block quick-link" href="{{ route('almacen-movimientos.reportes') }}"><i class="fas fa-chart-bar"></i>Reportes almacén</a></div>
                @endcan
                <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-secondary btn-block quick-link" href="{{ route('logistica.documentos.index') }}"><i class="fas fa-file-signature"></i>Notas de entrega</a></div>
                <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-danger btn-block quick-link" href="{{ route('logistica.incidentes.create') }}"><i class="fas fa-exclamation-triangle"></i>Reportar incidente</a></div>
                <div class="col-md-6 col-lg-3 mb-2"><a class="btn btn-dark btn-block quick-link" href="{{ route('logistica.rutas.index') }}"><i class="fas fa-satellite-dish"></i>Monitorización</a></div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

