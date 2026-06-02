@extends('layouts.app')

@section('title', 'Mis trabajos | AgroFusion')
@section('page_title', 'Mis trabajos')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Inicio</li>
@endsection

@push('styles')
<style>
.metric-card { border:0; border-radius:14px; overflow:hidden; position:relative; box-shadow:0 6px 20px rgba(18,38,63,.1); }
.metric-card .card-body { padding:1.2rem 1.4rem; }
.metric-icon { position:absolute; right:14px; top:50%; transform:translateY(-50%); font-size:2.4rem; opacity:.18; }
.metric-label { font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; opacity:.8; font-weight:600; }
.metric-value { font-size:2rem; font-weight:800; line-height:1.1; }
.panel-card { border:0; border-radius:14px; box-shadow:0 6px 20px rgba(18,38,63,.08); }
</style>
@endpush

@section('content')

@include('partials.dashboard-alertas')

<div class="d-flex align-items-center mb-3" style="gap:.75rem;">
    <div style="width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,#2c5530,#4a7c59);display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#fff;">
        <i class="fas fa-tractor"></i>
    </div>
    <div>
        <div style="font-size:1.05rem;font-weight:700;color:#1a252f;">Hola, {{ auth()->user()->nombre }}</div>
        <div style="font-size:.8rem;color:#6c757d;">Tus lotes y actividades asignadas · {{ now()->format('d/m/Y') }}</div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-6 col-lg-3 mb-3">
        <div class="card metric-card text-white" style="background:linear-gradient(135deg,#2c5530,#4a7c59);">
            <div class="card-body">
                <i class="fas fa-map-marked-alt metric-icon"></i>
                <div class="metric-label">Lotes</div>
                <div class="metric-value">{{ $stats['lotes_asignados'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="card metric-card text-white" style="background:linear-gradient(135deg,#fd7e14,#ffc107);">
            <div class="card-body">
                <i class="fas fa-tasks metric-icon"></i>
                <div class="metric-label">Pendientes</div>
                <div class="metric-value">{{ $stats['actividades_pendientes'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="card metric-card text-white" style="background:linear-gradient(135deg,#17a2b8,#1e88e5);">
            <div class="card-body">
                <i class="fas fa-calendar-day metric-icon"></i>
                <div class="metric-label">Hoy</div>
                <div class="metric-value">{{ $stats['actividades_hoy'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3 mb-3">
        <div class="card metric-card text-white" style="background:linear-gradient(135deg,#28a745,#20c997);">
            <div class="card-body">
                <i class="fas fa-check-circle metric-icon"></i>
                <div class="metric-label">Completadas</div>
                <div class="metric-value">{{ $stats['completadas_mes'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7 mb-3">
        <div class="card panel-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Actividades pendientes</h3>
                <a href="{{ route('actividades.index') }}" class="btn btn-sm btn-outline-success">Ver todas</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Actividad</th><th>Lote</th><th>Fecha</th><th></th></tr></thead>
                    <tbody>
                        @forelse($actividadesPendientes as $act)
                        <tr>
                            <td>{{ $act->descripcion }}</td>
                            <td>{{ $act->lote?->nombre ?? '—' }}</td>
                            <td>{{ $act->fechainicio?->format('d/m/Y') ?? '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('actividades.show', $act) }}" class="btn btn-xs btn-success btn-sm">Reportar</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No tienes actividades pendientes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5 mb-3">
        <div class="card panel-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Mis lotes</h3>
                <a href="{{ route('lotes.index') }}" class="btn btn-sm btn-outline-success">Ver lotes</a>
            </div>
            <ul class="list-group list-group-flush">
                @forelse($lotesRecientes as $lote)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $lote->nombre }}</strong>
                        <div class="small text-muted">{{ $lote->cultivo?->nombre ?? 'Sin cultivo' }} · {{ $lote->estadoTipo?->nombre ?? '—' }}</div>
                    </div>
                    <a href="{{ route('lotes.show', $lote) }}" class="btn btn-sm btn-outline-secondary">Ver</a>
                </li>
                @empty
                <li class="list-group-item text-muted text-center">Aún no tienes lotes asignados.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
