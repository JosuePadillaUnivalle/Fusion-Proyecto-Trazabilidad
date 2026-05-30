@extends('layouts.app')

@section('title', 'Detalle Registro Planta — {{ $lote->nombre }} | AgroFusion')
@section('page_title', 'Detalle de lote en planta')

@push('styles')
<style>
.timeline{position:relative;padding-left:32px;}
.timeline::before{content:'';position:absolute;left:12px;top:0;bottom:0;width:2px;background:#e2e8f0;}
.tl-item{position:relative;margin-bottom:20px;}
.tl-dot{position:absolute;left:-27px;top:4px;width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;color:#fff;z-index:1;}
.tl-dot.ok {background:#10b981;}
.tl-dot.nok{background:#ef4444;}
.tl-card{background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;}
.tl-card.ok {border-left:3px solid #10b981;}
.tl-card.nok{border-left:3px solid #ef4444;}
.proceso-header{background:linear-gradient(135deg,#0d1117,#1e293b);color:#fff;border-radius:10px;padding:14px 18px;margin-bottom:16px;}
.var-chip{display:inline-flex;align-items:center;gap:4px;background:#f0fdf4;border:1px solid #bbf7d0;color:#065f46;padding:2px 8px;border-radius:20px;font-size:.72rem;font-weight:600;margin:2px;}
.stat-mini{border-radius:10px;padding:14px 16px;color:#fff;text-align:center;}
.stat-mini .num{font-size:1.6rem;font-weight:800;}
.stat-mini .lbl{font-size:.7rem;text-transform:uppercase;letter-spacing:.06em;opacity:.85;}
.stat-mini.total  {background:linear-gradient(135deg,#1d4ed8,#3b82f6);}
.stat-mini.ok     {background:linear-gradient(135deg,#065f46,#059669);}
.stat-mini.nok    {background:linear-gradient(135deg,#9f1239,#e11d48);}
.stat-mini.tiempo {background:linear-gradient(135deg,#78350f,#d97706);}
</style>
@endpush

@section('content')

{{-- Header del lote --}}
<div class="row mb-4">
    <div class="col-12">
        <div class="proceso-header d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <div style="font-size:.7rem;opacity:.6;text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px;">Lote en proceso de planta</div>
                <h2 style="font-size:1.4rem;font-weight:800;margin:0;">{{ $lote->nombre }}</h2>
                <div style="font-size:.82rem;opacity:.75;margin-top:4px;">
                    @if($lote->cultivo)<i class="fas fa-leaf mr-1"></i>{{ $lote->cultivo->nombre }} ·@endif
                    @if($lote->estadoTipo)<i class="fas fa-tag mr-1"></i>{{ $lote->estadoTipo->nombre }} ·@endif
                    <i class="fas fa-user mr-1"></i>{{ $lote->usuario?->nombre ?? 'N/D' }}
                </div>
            </div>
            <a href="{{ route('registro-planta.create') }}?lote={{ $lote->loteid }}"
               class="btn btn-success">
                <i class="fas fa-plus mr-1"></i>Agregar pasos
            </a>
        </div>
    </div>
</div>

{{-- Mini stats --}}
<div class="row mb-4">
    @php $totalMin = $registros->sum(function($r){ return $r->hora_inicio && $r->hora_fin ? $r->hora_inicio->diffInMinutes($r->hora_fin) : 0; }); @endphp
    <div class="col-6 col-md-3 mb-2">
        <div class="stat-mini total"><div class="num">{{ $registros->count() }}</div><div class="lbl">Pasos registrados</div></div>
    </div>
    <div class="col-6 col-md-3 mb-2">
        <div class="stat-mini ok"><div class="num">{{ $registros->where('cumple_estandar',true)->count() }}</div><div class="lbl">Cumplen estándar</div></div>
    </div>
    <div class="col-6 col-md-3 mb-2">
        <div class="stat-mini nok"><div class="num">{{ $registros->where('cumple_estandar',false)->count() }}</div><div class="lbl">No cumplen</div></div>
    </div>
    <div class="col-6 col-md-3 mb-2">
        <div class="stat-mini tiempo"><div class="num">{{ $totalMin }}</div><div class="lbl">Minutos en planta</div></div>
    </div>
</div>

<div class="row">
    {{-- Timeline de registros --}}
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-stream text-success mr-2"></i><strong>Historial de pasos registrados</strong>
            </div>
            <div class="card-body">
                @if($registros->isEmpty())
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-tasks fa-2x mb-2 d-block"></i>
                        No hay pasos registrados para este lote.
                    </div>
                @else
                <div class="timeline">
                    @foreach($registros as $reg)
                    @php
                        $ok = $reg->cumple_estandar;
                        $vars = $reg->variables_ingresadas ? json_decode($reg->variables_ingresadas, true) : [];
                        $durMin = ($reg->hora_inicio && $reg->hora_fin) ? $reg->hora_inicio->diffInMinutes($reg->hora_fin) : null;
                    @endphp
                    <div class="tl-item">
                        <div class="tl-dot {{ $ok ? 'ok' : 'nok' }}">
                            <i class="fas {{ $ok ? 'fa-check' : 'fa-times' }}"></i>
                        </div>
                        <div class="tl-card {{ $ok ? 'ok' : 'nok' }}">
                            <div class="d-flex align-items-start justify-content-between mb-1">
                                <div>
                                    <strong style="font-size:.88rem;">
                                        {{ $reg->procesoMaquina?->nombre ?? 'Paso' }}
                                    </strong>
                                    <span class="badge badge-{{ $ok ? 'success' : 'danger' }} ml-1" style="font-size:.68rem;">
                                        {{ $ok ? 'Cumple estándar' : 'No cumple' }}
                                    </span>
                                </div>
                                <small class="text-muted">{{ $reg->fecha_registro?->format('d/m/Y H:i') }}</small>
                            </div>
                            <div class="text-muted" style="font-size:.78rem; margin-bottom:6px;">
                                <i class="fas fa-cog mr-1"></i>{{ $reg->procesoMaquina?->maquina?->nombre ?? 'N/D' }}
                                @if($reg->procesoMaquina?->proceso)
                                    · <i class="fas fa-layer-group mr-1"></i>{{ $reg->procesoMaquina->proceso->nombre }}
                                @endif
                                @if($durMin !== null)
                                    · <i class="fas fa-clock mr-1"></i>{{ $durMin }} min
                                @endif
                                @if($reg->usuario)
                                    · <i class="fas fa-user mr-1"></i>{{ $reg->usuario->nombre }}
                                @endif
                            </div>
                            @if(!empty($vars))
                            <div class="mb-1">
                                @foreach($vars as $k => $v)
                                    @if($v !== null && $v !== '')
                                    <span class="var-chip"><i class="fas fa-chart-line"></i>{{ strtoupper($k) }}: {{ $v }}</span>
                                    @endif
                                @endforeach
                            </div>
                            @endif
                            @if($reg->observaciones)
                            <div class="text-muted" style="font-size:.78rem;">
                                <i class="fas fa-comment mr-1"></i>{{ $reg->observaciones }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right: Progreso por proceso --}}
    <div class="col-lg-4 mb-4">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-tasks text-success mr-2"></i><strong>Progreso por proceso</strong>
            </div>
            <div class="card-body p-0">
                @foreach($procesosConPasos as $proceso)
                <div style="padding:14px 16px; border-bottom:1px solid #f1f5f9;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="font-weight-600" style="font-size:.84rem;">{{ $proceso->nombre }}</span>
                        <span class="text-muted" style="font-size:.76rem;">
                            {{ $proceso->pasos_completados }}/{{ $proceso->total_pasos }}
                        </span>
                    </div>
                    @php $pct = $proceso->total_pasos > 0 ? round(($proceso->pasos_completados / $proceso->total_pasos) * 100) : 0; @endphp
                    <div class="progress" style="height:6px;border-radius:3px;">
                        <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                    </div>
                    <small class="text-muted" style="font-size:.72rem;">{{ $pct }}% completado</small>
                </div>
                @endforeach
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('registro-planta.index') }}" class="btn btn-outline-secondary btn-block">
                <i class="fas fa-arrow-left mr-1"></i>Volver al listado
            </a>
        </div>
    </div>
</div>
@endsection
