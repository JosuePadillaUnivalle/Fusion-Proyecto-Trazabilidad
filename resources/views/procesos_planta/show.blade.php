@extends('layouts.app')

@section('title', 'Detalle proceso | Fusion-Proyectos')
@section('page_title', 'Detalle del proceso')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procesos-planta.index') }}">Procesos de planta</a></li>
    <li class="breadcrumb-item active">{{ $proceso->nombre }}</li>
@endsection

@push('styles')
@include('partials.modulo-produccion-styles')
<style>
.modulo-prod .detalle-hero {
    background: linear-gradient(135deg, #2c5530, #4a7c59);
    color: #fff;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
}
</style>
@endpush

@section('content')
<div class="modulo-prod">

    <div class="detalle-hero d-flex flex-wrap justify-content-between align-items-start">
        <div>
            <h2 class="mb-1 font-weight-bold">
                <i class="fas fa-industry mr-2"></i>{{ $proceso->nombre }}
            </h2>
            <p class="mb-0 opacity-90">{{ $proceso->descripcion ?: 'Sin descripción' }}</p>
        </div>
        <div class="text-right mt-2 mt-md-0">
            @if($proceso->activo)
                <span class="badge badge-light text-success px-3 py-2">Activo</span>
            @else
                <span class="badge badge-light text-secondary px-3 py-2">Inactivo</span>
            @endif
            <div class="mt-2">
                <span class="badge badge-light px-3 py-2">
                    <i class="fas fa-tractor mr-1"></i> {{ $proceso->producciones_count }} cosechas vinculadas
                </span>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <a href="{{ route('procesos-planta.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Volver al catálogo
        </a>
    </div>

    <div class="card card-outline card-success card-modulo-main elevation-1">
        <div class="card-header">
            <h3 class="card-title mb-0">
                <i class="fas fa-list text-success mr-1"></i>
                Cosechas con este proceso
            </h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-modulo table-hover mb-0">
                <thead>
                    <tr>
                        <th>Lote</th>
                        <th>Cultivo</th>
                        <th>Cantidad</th>
                        <th>Fecha</th>
                        <th>Destino</th>
                        <th style="width: 80px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($produccionesRecientes as $p)
                    <tr>
                        <td><strong>{{ $p->lote->nombre ?? '—' }}</strong></td>
                        <td>
                            @if($p->lote?->cultivo)
                                <span class="badge badge-success">{{ $p->lote->cultivo->nombre }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <strong class="text-success">
                                {{ number_format($p->cantidad ?? 0, 2) }} {{ $p->unidadMedida->abreviatura ?? 'kg' }}
                            </strong>
                        </td>
                        <td>{{ $p->fechacosecha ? \Carbon\Carbon::parse($p->fechacosecha)->format('d/m/Y') : '—' }}</td>
                        <td>{{ $p->destino->nombre ?? '—' }}</td>
                        <td>
                            <a href="{{ route('producciones.show', $p) }}" class="btn btn-sm btn-outline-info" title="Ver cosecha">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Aún no hay cosechas registradas con este proceso.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
