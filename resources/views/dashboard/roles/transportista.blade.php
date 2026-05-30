@extends('layouts.app')

@section('title', 'Panel Transportista | AgroFusion')
@section('page_title', 'Panel Transportista')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color:#2c5530;">Inicio</a></li>
    <li class="breadcrumb-item active">Panel Transportista</li>
@endsection

@push('styles')
<style>
.metric-card { border:0; border-radius:14px; overflow:hidden; position:relative; box-shadow:0 6px 20px rgba(18,38,63,.1); }
.metric-card .card-body { padding:1.2rem 1.4rem; }
.metric-icon { position:absolute; right:14px; top:50%; transform:translateY(-50%); font-size:2.4rem; opacity:.18; }
.metric-label { font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; opacity:.8; font-weight:600; }
.metric-value { font-size:2rem; font-weight:800; line-height:1.1; }
.metric-sub { font-size:.78rem; opacity:.75; }
.panel-card { border:0; border-radius:14px; box-shadow:0 6px 20px rgba(18,38,63,.08); }
.panel-card .card-header { background:#fff; border-bottom:1px solid #f0f0f0; border-radius:14px 14px 0 0 !important; padding:.9rem 1.2rem; }
.panel-card .card-title { font-weight:700; color:#1a252f; margin:0; }
.quick-btn { border-radius:10px; padding:.7rem 1rem; display:flex; align-items:center; gap:.6rem; font-weight:600; font-size:.9rem; transition:transform .15s,box-shadow .15s; border:0; }
.quick-btn:hover { transform:translateY(-2px); box-shadow:0 6px 16px rgba(0,0,0,.15); }
.x-table thead th { background:#f2f7f3; border-bottom:0; font-size:.77rem; text-transform:uppercase; letter-spacing:.04em; color:#4a7c59; font-weight:700; }
.x-table tbody td { vertical-align:middle; font-size:.86rem; }
.progress-bar-custom { height:6px; border-radius:3px; }
.status-dot { width:8px; height:8px; border-radius:50%; display:inline-block; margin-right:5px; }
.badge-estado { border-radius:20px; font-size:.73rem; padding:.28em .75em; font-weight:600; }
.empty-row td { text-align:center; color:#adb5bd; padding:2rem; font-size:.85rem; }
.info-chip { display:inline-flex; align-items:center; gap:.4rem; background:#f2f7f3; border-radius:8px; padding:.35rem .75rem; font-size:.82rem; color:#2c5530; font-weight:600; }
</style>
@endpush

@section('content')

{{-- Bienvenida --}}
<div class="d-flex align-items-center mb-3" style="gap:.75rem;">
    <div style="width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,#fd7e14,#ffc107);display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:#fff;">
        <i class="fas fa-truck-moving"></i>
    </div>
    <div>
        <div style="font-size:1.05rem;font-weight:700;color:#1a252f;">Bienvenido, {{ auth()->user()->nombre ?? 'Transportista' }}</div>
        <div style="font-size:.8rem;color:#6c757d;">Panel operativo · {{ now()->format('d \d\e F, Y') }}</div>
    </div>
</div>

{{-- Métricas --}}
<div class="row mb-3">
    <div class="col-6 col-lg mb-3">
        <div class="card metric-card text-white" style="background:linear-gradient(135deg,#2c5530,#4a7c59);">
            <div class="card-body">
                <i class="fas fa-clipboard-check metric-icon"></i>
                <div class="metric-label">Asignados</div>
                <div class="metric-value">{{ $stats['asignados'] }}</div>
                <div class="metric-sub">envíos en total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg mb-3">
        <div class="card metric-card text-white" style="background:linear-gradient(135deg,#fd7e14,#ffc107);">
            <div class="card-body">
                <i class="fas fa-box metric-icon"></i>
                <div class="metric-label">Por recoger</div>
                <div class="metric-value">{{ $stats['por_recoger'] }}</div>
                <div class="metric-sub">pendientes de pickup</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg mb-3">
        <div class="card metric-card text-white" style="background:linear-gradient(135deg,#17a2b8,#1e88e5);">
            <div class="card-body">
                <i class="fas fa-truck-moving metric-icon"></i>
                <div class="metric-label">En camino</div>
                <div class="metric-value">{{ $stats['en_camino'] }}</div>
                <div class="metric-sub">en tránsito ahora</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg mb-3">
        <div class="card metric-card text-white" style="background:linear-gradient(135deg,#28a745,#20c997);">
            <div class="card-body">
                <i class="fas fa-check-circle metric-icon"></i>
                <div class="metric-label">Entregados hoy</div>
                <div class="metric-value">{{ $stats['entregados_hoy'] }}</div>
                <div class="metric-sub">completados hoy</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg mb-3">
        <div class="card metric-card text-white" style="background:linear-gradient(135deg,#6f42c1,#e91e63);">
            <div class="card-body">
                <i class="fas fa-tachometer-alt metric-icon"></i>
                <div class="metric-label">Productividad</div>
                <div class="metric-value">{{ $stats['productividad'] }}<span style="font-size:1rem;">%</span></div>
                <div class="metric-sub">tasa de completación</div>
            </div>
        </div>
    </div>
</div>

{{-- Barra productividad --}}
@if($stats['asignados'] > 0)
<div class="card panel-card mb-3">
    <div class="card-body py-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span style="font-size:.83rem;font-weight:600;color:#4a5568;">Progreso general</span>
            <span class="info-chip"><i class="fas fa-chart-pie fa-xs"></i>{{ $stats['productividad'] }}% completado</span>
        </div>
        <div class="progress" style="height:10px;border-radius:5px;background:#e8f5e8;">
            <div class="progress-bar" role="progressbar"
                 style="width:{{ $stats['productividad'] }}%;background:linear-gradient(90deg,#2c5530,#28a745);border-radius:5px;"
                 aria-valuenow="{{ $stats['productividad'] }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <div class="d-flex justify-content-between mt-1">
            <small class="text-muted">{{ $stats['asignados'] }} asignados</small>
            <small class="text-muted">
                @if($stats['incidentes_abiertos'] > 0)
                    <span class="text-danger"><i class="fas fa-exclamation-triangle fa-xs"></i> {{ $stats['incidentes_abiertos'] }} incidente(s) abiertos</span>
                @else
                    <span class="text-success"><i class="fas fa-shield-alt fa-xs"></i> Sin incidentes</span>
                @endif
            </small>
        </div>
    </div>
</div>
@endif

<div class="row">
    {{-- Mis últimas asignaciones --}}
    <div class="col-lg-7 mb-3">
        <div class="card panel-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-box mr-2" style="color:#2c5530;"></i>Mis últimas asignaciones</h3>
                <a href="{{ route('logistica.asignaciones.index') }}" class="btn btn-sm btn-outline-success" style="border-radius:8px;font-size:.78rem;">Ver todas</a>
            </div>
            <div class="card-body p-0">
                <table class="table x-table mb-0">
                    <thead>
                        <tr>
                            <th>Envío</th>
                            <th>Vehículo</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mis_asignaciones as $a)
                        <tr>
                            <td>
                                <span style="font-family:monospace;font-size:.82rem;background:#f2f7f3;padding:.15em .5em;border-radius:4px;color:#2c5530;">
                                    {{ $a->externo_envio_id ?? '#'.$a->id }}
                                </span>
                            </td>
                            <td style="color:#4a5568;">{{ $a->vehiculo_ref ?? '—' }}</td>
                            <td>
                                @php
                                    $colores = ['entregado'=>'success','en_ruta'=>'info','asignado'=>'warning','cancelado'=>'danger'];
                                    $color = $colores[$a->estado] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $color }} badge-estado">{{ ucfirst(str_replace('_',' ',$a->estado)) }}</span>
                            </td>
                            <td style="color:#9ca3af;font-size:.78rem;">{{ optional($a->fecha_asignacion)->format('d/m/Y') ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr class="empty-row">
                            <td colspan="4"><i class="fas fa-inbox mr-1"></i>Sin asignaciones registradas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Mis rutas --}}
    <div class="col-lg-5 mb-3">
        <div class="card panel-card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-route mr-2" style="color:#2c5530;"></i>Mis rutas</h3>
                <a href="{{ route('logistica.rutas.index') }}" class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:.78rem;">Ver todas</a>
            </div>
            <div class="card-body p-0">
                <table class="table x-table mb-0">
                    <thead>
                        <tr>
                            <th>Ruta</th>
                            <th>Paradas</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($mis_rutas as $ruta)
                        <tr>
                            <td style="font-weight:600;">{{ Str::limit($ruta->nombre, 22) }}</td>
                            <td><span class="badge badge-secondary" style="border-radius:12px;">{{ $ruta->paradas_count ?? 0 }}</span></td>
                            <td>
                                @php
                                    $rc = ['completada'=>'success','en_ruta'=>'info','cancelada'=>'danger','planificada'=>'warning'];
                                    $rc2 = $rc[$ruta->estado] ?? 'secondary';
                                @endphp
                                <span class="badge badge-{{ $rc2 }} badge-estado">{{ ucfirst(str_replace('_',' ',$ruta->estado)) }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr class="empty-row">
                            <td colspan="3"><i class="fas fa-map-signs mr-1"></i>Sin rutas asignadas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Accesos rápidos --}}
<div class="card panel-card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-bolt mr-2" style="color:#fd7e14;"></i>Accesos rápidos</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-6 col-md-3 mb-2">
                <a class="btn btn-info btn-block quick-btn" href="{{ route('logistica.asignaciones.index') }}">
                    <i class="fas fa-box"></i><span>Mis envíos</span>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-2">
                <a class="btn btn-success btn-block quick-btn" href="{{ route('logistica.rutas.index') }}">
                    <i class="fas fa-route"></i><span>Mis rutas</span>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-2">
                <a class="btn btn-secondary btn-block quick-btn" href="{{ route('logistica.documentos.index') }}">
                    <i class="fas fa-file-alt"></i><span>Documentos</span>
                </a>
            </div>
            <div class="col-6 col-md-3 mb-2">
                <a class="btn btn-danger btn-block quick-btn" href="{{ route('logistica.incidentes.index') }}">
                    <i class="fas fa-exclamation-circle"></i><span>Incidentes</span>
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
