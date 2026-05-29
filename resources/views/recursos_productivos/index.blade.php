@extends('layouts.app')

@section('title', 'Vista consolidada de recursos | AgroNexus')
@section('page_title', 'Vista consolidada de recursos')

@push('styles')
<style>
    .rp-stat { border-radius:14px; padding:20px 22px; color:#fff; position:relative; overflow:hidden; transition:transform .18s,box-shadow .18s; }
    .rp-stat:hover { transform:translateY(-3px); }
    .rp-stat .s-icon { position:absolute; right:16px; top:50%; transform:translateY(-50%); font-size:2.2rem; opacity:.22; }
    .rp-stat .s-num  { font-size:2rem; font-weight:800; line-height:1; margin-bottom:4px; }
    .rp-stat .s-lbl  { font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.07em; opacity:.88; }
    .rp-stat .s-sub  { font-size:.72rem; opacity:.75; margin-top:6px; }
    .rp-stat.cultivos  { background:linear-gradient(135deg,#065f46,#059669); }
    .rp-stat.insumos   { background:linear-gradient(135deg,#0e7490,#06b6d4); }
    .rp-stat.criticos  { background:linear-gradient(135deg,#9f1239,#e11d48); }
    .rp-stat.valor     { background:linear-gradient(135deg,#78350f,#d97706); }

    .cultivo-row { display:flex; align-items:center; justify-content:space-between; padding:10px 14px; border-bottom:1px solid #f1f5f9; }
    .cultivo-row:last-child { border-bottom:none; }
    .cultivo-badge { background:#d1fae5; color:#065f46; font-size:.7rem; font-weight:700; padding:2px 8px; border-radius:20px; }

    .nivel-badge { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:20px; font-size:.7rem; font-weight:700; }
    .nivel-badge.normal  { background:#d1fae5; color:#065f46; }
    .nivel-badge.critico { background:#fee2e2; color:#b91c1c; }
    .nivel-badge.bajo    { background:#fef3c7; color:#92400e; }

    .section-head { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.09em; color:#94a3b8; padding:10px 16px 6px; display:flex; align-items:center; gap:8px; }
    .section-head i { color:#10b981; }
</style>
@endpush

@section('content')

{{-- Stat boxes --}}
<div class="row mb-4">
    <div class="col-6 col-md-3 mb-3">
        <div class="rp-stat cultivos">
            <div class="s-num">{{ $stats['cultivos'] }}</div>
            <div class="s-lbl">Cultivos registrados</div>
            <div class="s-sub">En la operación</div>
            <div class="s-icon"><i class="fas fa-seedling"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="rp-stat insumos">
            <div class="s-num">{{ $stats['insumos'] }}</div>
            <div class="s-lbl">Insumos registrados</div>
            <div class="s-sub">Catálogo disponible</div>
            <div class="s-icon"><i class="fas fa-boxes"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="rp-stat criticos">
            <div class="s-num">{{ $stats['criticos'] }}</div>
            <div class="s-lbl">En nivel crítico</div>
            <div class="s-sub">{{ $stats['criticos'] > 0 ? 'Filtrar críticos →' : 'Sin alertas' }}</div>
            <div class="s-icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-3">
        <div class="rp-stat valor">
            <div class="s-num" style="font-size:1.5rem;">Bs. {{ number_format($stats['valor_total'], 0) }}</div>
            <div class="s-lbl">Valor en stock</div>
            <div class="s-sub">Stock × precio unitario</div>
            <div class="s-icon"><i class="fas fa-coins"></i></div>
        </div>
    </div>
</div>

{{-- Main panel --}}
<div class="card">
    <div class="card-header d-flex align-items-center gap-2">
        <i class="fas fa-layer-group text-success mr-2"></i>
        <strong>Recursos productivos</strong>
        <span class="badge badge-secondary ml-1">{{ $stats['insumos'] }} insumos</span>
    </div>
    <div class="card-body p-0">
        <div class="row no-gutters">

            {{-- Cultivos --}}
            <div class="col-md-4" style="border-right:1px solid #e2e8f0;">
                <div class="section-head">
                    <i class="fas fa-leaf"></i>
                    CULTIVOS <span class="badge badge-pill badge-success ml-1">{{ $stats['cultivos'] }}</span>
                </div>
                @forelse($cultivos as $cultivo)
                <div class="cultivo-row">
                    <span style="font-size:.85rem; font-weight:500; color:#0f172a;">
                        <i class="fas fa-seedling text-success mr-2" style="font-size:.75rem;"></i>{{ $cultivo->nombre }}
                    </span>
                    <span class="cultivo-badge">Cultivo</span>
                </div>
                @empty
                <div class="text-center text-muted py-4 px-3" style="font-size:.83rem;">Sin cultivos registrados</div>
                @endforelse
            </div>

            {{-- Insumos --}}
            <div class="col-md-8">
                <div class="section-head">
                    <i class="fas fa-boxes"></i>
                    INSUMOS PARA ABASTECIMIENTO
                    <span class="badge badge-pill badge-info ml-1">{{ $stats['insumos'] }} ítems</span>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0" style="font-size:.83rem;">
                        <thead>
                            <tr>
                                <th>Insumo</th>
                                <th>Tipo</th>
                                <th>Nivel</th>
                                <th>Stock</th>
                                <th>Mínimo</th>
                                <th>Actor / Proveedor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($insumos as $insumo)
                            @php
                                $nivelClass = $insumo->stockBajo() ? 'critico' : 'normal';
                                $nivelLabel = $insumo->stockBajo() ? 'Crítico' : 'Normal';
                                $nivelIcon  = $insumo->stockBajo() ? 'fa-times-circle' : 'fa-check-circle';
                            @endphp
                            <tr>
                                <td>
                                    <span class="font-weight-600" style="color:{{ $insumo->stockBajo() ? '#b91c1c' : '#0f172a' }};">
                                        {{ $insumo->nombre }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ $insumo->tipo->nombre ?? '-' }}</td>
                                <td>
                                    <span class="nivel-badge {{ $nivelClass }}">
                                        <i class="fas {{ $nivelIcon }}"></i>{{ $nivelLabel }}
                                    </span>
                                </td>
                                <td>
                                    <span class="{{ $insumo->stockBajo() ? 'text-danger font-weight-700' : '' }}">
                                        {{ number_format($insumo->stock, 2) }}
                                        {{ $insumo->unidadMedida->abreviatura ?? 'u' }}
                                    </span>
                                </td>
                                <td class="text-muted">{{ number_format($insumo->stockminimo ?? 0, 2) }}</td>
                                <td>
                                    @if($insumo->actorAbastecimiento)
                                        <i class="fas fa-handshake text-muted mr-1" style="font-size:.7rem;"></i>
                                        {{ $insumo->actorAbastecimiento->nombre }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="fas fa-boxes fa-2x mb-2 d-block"></i>
                                    Sin insumos registrados
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
