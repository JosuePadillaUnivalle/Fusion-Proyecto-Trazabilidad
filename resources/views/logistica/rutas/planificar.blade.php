@extends('layouts.app')

@section('title', 'Planificar rutas | AgroFusion')
@section('page_title', auth()->user()?->hasRole('transportista') ? 'Mis rutas' : 'Planificar rutas')

@push('styles')
@include('logistica.partials.ayuda-logistica-styles')
<style>
.log-hub-card{border:0;border-radius:14px;box-shadow:0 6px 20px rgba(18,38,63,.08);transition:transform .15s,box-shadow .15s;height:100%}
.log-hub-card:hover{transform:translateY(-3px);box-shadow:0 10px 28px rgba(18,38,63,.12)}
.log-hub-icon{width:52px;height:52px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:#fff}
</style>
@endpush

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <p class="text-muted mb-0">Elija cómo quiere armar o revisar las rutas de entrega del chofer.</p>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6 mb-3">
                <a href="{{ route('logistica.rutas.index') }}" class="text-decoration-none text-body">
                    <div class="card log-hub-card">
                        <div class="card-body d-flex align-items-start" style="gap:1rem;">
                            <div class="log-hub-icon bg-success"><i class="fas fa-list"></i></div>
                            <div>
                                <h5 class="font-weight-bold mb-1">Lista de rutas</h5>
                                <p class="text-muted small mb-2">Ver rutas existentes, crear manualmente, generar automática y cambiar estado (planificada, en camino, completada).</p>
                                <span class="text-success small font-weight-bold">Abrir listado <i class="fas fa-arrow-right ml-1"></i></span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @can('rutas_multi.create')
            <div class="col-md-6 mb-3">
                <a href="{{ route('logistica.rutas.mapa') }}" class="text-decoration-none text-body">
                    <div class="card log-hub-card">
                        <div class="card-body d-flex align-items-start" style="gap:1rem;">
                            <div class="log-hub-icon bg-primary"><i class="fas fa-map-marked-alt"></i></div>
                            <div>
                                <h5 class="font-weight-bold mb-1">Mapa de envíos</h5>
                                <p class="text-muted small mb-2">Seleccione entregas en el mapa o en la lista y cree una ruta visual con los puntos pendientes.</p>
                                <span class="text-primary small font-weight-bold">Abrir mapa <i class="fas fa-arrow-right ml-1"></i></span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @endcan
        </div>
    </div>
</section>
@endsection
