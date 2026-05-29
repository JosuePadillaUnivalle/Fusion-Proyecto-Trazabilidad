@extends('layouts.app')

@section('title', 'Registro a Planta | AgroNexus')
@section('page_title', 'Registro a Planta')

@push('styles')
<style>
.rp-stat{border-radius:14px;padding:20px 22px;color:#fff;position:relative;overflow:hidden;transition:transform .18s;}
.rp-stat:hover{transform:translateY(-3px);}
.rp-stat .s-icon{position:absolute;right:16px;top:50%;transform:translateY(-50%);font-size:2.2rem;opacity:.22;}
.rp-stat .s-num{font-size:2rem;font-weight:800;line-height:1;margin-bottom:4px;}
.rp-stat .s-lbl{font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.07em;opacity:.88;}
.rp-stat.lotes   {background:linear-gradient(135deg,#065f46,#059669);}
.rp-stat.pasos   {background:linear-gradient(135deg,#1d4ed8,#3b82f6);}
.rp-stat.ok      {background:linear-gradient(135deg,#0e7490,#06b6d4);}
.rp-stat.nok     {background:linear-gradient(135deg,#9f1239,#e11d48);}

.lote-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:16px 18px;margin-bottom:10px;transition:border-color .15s,box-shadow .15s;}
.lote-card:hover{border-color:#10b981;box-shadow:0 4px 14px rgba(16,185,129,.1);}
.estado-dot{width:10px;height:10px;border-radius:50%;display:inline-block;margin-right:6px;}
.estado-ok {background:#10b981;}
.estado-nok{background:#ef4444;}
.estado-mix{background:#f59e0b;}
.progress-thin{height:6px;border-radius:3px;}
.badge-proceso{background:#f0fdf4;color:#065f46;border:1px solid #bbf7d0;font-size:.72rem;font-weight:600;padding:2px 8px;border-radius:20px;}
</style>
@endpush

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

{{-- Stat boxes --}}
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="rp-stat lotes">
            <div class="s-num">{{ $stats['lotes_en_proceso'] }}</div>
            <div class="s-lbl">Lotes en planta</div>
            <div class="s-icon"><i class="fas fa-industry"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="rp-stat pasos">
            <div class="s-num">{{ $stats['pasos_completados'] }}</div>
            <div class="s-lbl">Pasos completados</div>
            <div class="s-icon"><i class="fas fa-tasks"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="rp-stat ok">
            <div class="s-num">{{ $stats['cumplen_estandar'] }}</div>
            <div class="s-lbl">Cumplen estándar</div>
            <div class="s-icon"><i class="fas fa-check-double"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="rp-stat nok">
            <div class="s-num">{{ $stats['no_cumplen'] }}</div>
            <div class="s-lbl">No cumplen</div>
            <div class="s-icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Left: Lotes registrados --}}
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div><i class="fas fa-clipboard-list text-success mr-2"></i><strong>Lotes en proceso de planta</strong></div>
                <a href="{{ route('registro-planta.create') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus mr-1"></i>Nuevo registro
                </a>
            </div>
            <div class="card-body p-0">
                @forelse($lotesConRegistro as $lote)
                @php
                    $ultimo = $lote->ultimo_registro;
                    $estadoClass = $lote->cumple_todos ? 'ok' : 'nok';
                @endphp
                <div class="lote-card mx-3 mt-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="estado-dot estado-{{ $estadoClass }}"></span>
                                <strong style="font-size:.92rem;">{{ $lote->nombre }}</strong>
                                @if($lote->cultivo)
                                    <span class="badge-proceso"><i class="fas fa-leaf mr-1"></i>{{ $lote->cultivo->nombre }}</span>
                                @endif
                            </div>
                            <div class="text-muted" style="font-size:.78rem;">
                                <i class="fas fa-check-circle mr-1 text-success"></i>
                                {{ $lote->pasos_completados }} paso(s) registrado(s)
                                @if($ultimo)
                                    · Último: {{ $ultimo->fecha_registro?->diffForHumans() }}
                                    · Operador: {{ $ultimo->usuario?->nombre ?? 'N/D' }}
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('registro-planta.show', $lote->loteid) }}"
                           class="btn btn-sm btn-outline-success ml-3">
                            <i class="fas fa-eye mr-1"></i>Ver detalle
                        </a>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-5">
                    <i class="fas fa-industry fa-2x mb-3 d-block"></i>
                    <p class="mb-2">No hay lotes registrados en planta aún.</p>
                    <a href="{{ route('registro-planta.create') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus mr-1"></i>Registrar primer lote
                    </a>
                </div>
                @endforelse
            </div>
            @if($lotesConRegistro->isNotEmpty())
            <div class="card-footer">
                <small class="text-muted">{{ $lotesConRegistro->count() }} lote(s) con actividad en planta</small>
            </div>
            @endif
        </div>
    </div>

    {{-- Right: Procesos disponibles --}}
    <div class="col-lg-4 mb-4">
        <div class="card mb-3">
            <div class="card-header">
                <i class="fas fa-cogs text-success mr-2"></i><strong>Procesos disponibles</strong>
            </div>
            <div class="card-body p-0">
                @foreach($procesos as $proceso)
                <div style="padding:12px 16px; border-bottom:1px solid #f1f5f9; font-size:.84rem;">
                    <div class="font-weight-600">{{ $proceso->nombre }}</div>
                    @if($proceso->descripcion)
                    <div class="text-muted" style="font-size:.76rem;">{{ $proceso->descripcion }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-bolt text-warning mr-2"></i><strong>Acción rápida</strong>
            </div>
            <div class="card-body">
                <p class="text-muted" style="font-size:.82rem; margin-bottom:12px;">
                    Seleccioná un lote y un proceso para registrar su paso por planta.
                </p>
                <a href="{{ route('registro-planta.create') }}" class="btn btn-success btn-block">
                    <i class="fas fa-industry mr-2"></i>Registrar ingreso a planta
                </a>
                <a href="{{ route('procesos-planta.index') }}" class="btn btn-outline-secondary btn-block mt-2">
                    <i class="fas fa-cogs mr-2"></i>Configurar procesos
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
