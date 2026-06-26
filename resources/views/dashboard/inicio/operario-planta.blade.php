@extends('layouts.app')

@section('title', 'Inicio | AgroFusion')
@section('page_title', 'Inicio')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Inicio</li>
@endsection

@push('styles')
@include('dashboard.partials.inicio-estilos')
@include('dashboard.partials.operario-planta-inicio-estilos')
@endpush

@section('content')
@php
    $tareas = $tareasPendientes ?? collect();
    $totalPend = (int) ($tareasPendientesCount ?? $tareas->count());
    $user = auth()->user();
    $iniciales = strtoupper(substr($user->nombre ?? 'O', 0, 1).substr($user->apellido ?? 'P', 0, 1));
@endphp

<section class="content px-0 inicio-operario-planta">
    <div class="container-fluid px-0">

        <div class="inicio-dash-hero">
            <div class="inicio-dash-hero__row">
                <div class="iop-hero-user">
                    <span class="iop-hero-avatar" aria-hidden="true">{{ $iniciales }}</span>
                    <div>
                        <div class="inicio-dash-hero__title mb-1">Hola, {{ $user->nombre }}</div>
                        <p class="inicio-dash-hero__sub mb-0">Tu panel de trabajo en planta — etapas y lotes asignados por el jefe.</p>
                        <div class="iop-hero-chips">
                            <span class="iop-hero-chip"><i class="fas fa-boxes"></i> {{ $stats['lotes_asignados'] }} lote{{ $stats['lotes_asignados'] === 1 ? '' : 's' }}</span>
                            <span class="iop-hero-chip"><i class="fas fa-clock"></i> {{ $stats['tareas_pendientes'] }} pendiente{{ $stats['tareas_pendientes'] === 1 ? '' : 's' }}</span>
                            <span class="iop-hero-chip"><i class="fas fa-check"></i> {{ $stats['tareas_completadas'] }} completada{{ $stats['tareas_completadas'] === 1 ? '' : 's' }}</span>
                        </div>
                    </div>
                </div>
                @if($totalPend > 0)
                <a href="{{ route('tareas-planta.index') }}" class="inicio-dash-link-panel">
                    <i class="fas fa-bolt"></i> Ir a mis tareas
                </a>
                @endif
            </div>
        </div>

        @include('partials.dashboard-alertas')

        <div class="inicio-kpi-row">
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#15803d,#22c55e)">
                <i class="fas fa-boxes inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['lotes_asignados'] }}</div>
                <p class="inicio-kpi__lbl">Lotes asignados</p>
            </div>
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#c2410c,#f59e0b)">
                <i class="fas fa-clock inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['tareas_pendientes'] }}</div>
                <p class="inicio-kpi__lbl">Por hacer</p>
            </div>
            <div class="inicio-kpi" style="background:linear-gradient(135deg,#0369a1,#0ea5e9)">
                <i class="fas fa-check-circle inicio-kpi__icon"></i>
                <div class="inicio-kpi__val">{{ $stats['tareas_completadas'] }}</div>
                <p class="inicio-kpi__lbl">Completadas</p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="iop-card iop-card--tareas h-100 mb-0">
                    <div class="iop-card__head">
                        <div>
                            <h2><i class="fas fa-tasks text-warning mr-2"></i>Tareas pendientes</h2>
                            <p>Etapas de transformación que debes completar</p>
                        </div>
                        @if($totalPend > 0)
                            <span class="badge badge-warning px-3 py-2">{{ $totalPend }}</span>
                        @endif
                    </div>
                    <div class="iop-card__body">
                        @forelse($tareas as $tarea)
                            <a href="{{ route('tareas-planta.show', $tarea) }}" class="iop-tarea">
                                <span class="iop-tarea__icon"><i class="fas fa-cog"></i></span>
                                <span class="iop-tarea__main">
                                    <strong>{{ $tarea->proceso?->nombre ?? 'Etapa' }}</strong>
                                    <span class="iop-tarea__meta">
                                        <i class="fas fa-tools mr-1"></i>{{ $tarea->maquina?->nombre ?? '—' }}
                                        <span class="mx-1">·</span>
                                        <i class="fas fa-barcode mr-1"></i>{{ $tarea->loteProduccion?->codigo_lote ?? '—' }}
                                    </span>
                                </span>
                                <span class="iop-tarea__cta">Completar <i class="fas fa-chevron-right ml-1"></i></span>
                            </a>
                        @empty
                            <div class="iop-al-dia">
                                <div class="iop-al-dia__icon"><i class="fas fa-check"></i></div>
                                <h3>Estás al día</h3>
                                <p>No tienes etapas pendientes. Cuando el jefe te asigne una nueva tarea, aparecerá aquí.</p>
                            </div>
                        @endforelse
                    </div>
                    @if($totalPend > 0)
                        <div class="iop-card__body--padded border-top bg-light text-center">
                            <a href="{{ route('tareas-planta.index') }}" class="btn btn-sm btn-success font-weight-bold px-4">
                                <i class="fas fa-list mr-1"></i> Ver todas mis tareas
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-lg-4">
                <div class="iop-card iop-card--accesos mb-0 h-100">
                    <div class="iop-card__head">
                        <div>
                            <h2><i class="fas fa-bolt text-success mr-2"></i>Accesos rápidos</h2>
                            <p>Ir directo a lo que necesitas</p>
                        </div>
                    </div>
                    <div class="iop-accesos">
                        <a href="{{ route('tareas-planta.index') }}" class="iop-acceso">
                            <span class="iop-acceso__icon"><i class="fas fa-clipboard-list"></i></span>
                            <span>
                                <span class="iop-acceso__lbl">Mis tareas</span>
                                <span class="iop-acceso__sub">Transformación asignada</span>
                            </span>
                        </a>
                        <a href="{{ route('procesamiento.index') }}" class="iop-acceso">
                            <span class="iop-acceso__icon"><i class="fas fa-industry"></i></span>
                            <span>
                                <span class="iop-acceso__lbl">Procesamiento</span>
                                <span class="iop-acceso__sub">Lotes con tus etapas</span>
                            </span>
                        </a>
                        @can('almacen.movimientos.view')
                        <a href="{{ route('almacen-planta.index') }}" class="iop-acceso">
                            <span class="iop-acceso__icon"><i class="fas fa-warehouse"></i></span>
                            <span>
                                <span class="iop-acceso__lbl">Almacén de planta</span>
                                <span class="iop-acceso__sub">Inventario y movimientos</span>
                            </span>
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="iop-card iop-card--lotes mb-0">
            <div class="iop-card__head">
                <div>
                    <h2><i class="fas fa-layer-group text-success mr-2"></i>Mis lotes</h2>
                    <p>Lotes donde tienes al menos una etapa asignada</p>
                </div>
                <a href="{{ route('procesamiento.index') }}" class="btn btn-sm btn-outline-success font-weight-bold">
                    Ver listado completo
                </a>
            </div>
            @if($lotesRecientes->isNotEmpty())
                <div class="iop-lotes-grid">
                    @foreach($lotesRecientes as $lote)
                        <a href="{{ route('procesamiento.show', $lote) }}" class="iop-lote">
                            <span class="iop-lote__code">{{ $lote->codigo_lote }}</span>
                            <span class="iop-lote__name">{{ $lote->nombre }}</span>
                            <span class="iop-lote__line">
                                <i class="fas fa-project-diagram mr-1"></i>{{ $lote->plantillaTransformacion?->nombre ?? 'Sin línea de proceso' }}
                            </span>
                            <span class="iop-lote__foot">
                                <span>Ver detalle</span>
                                <i class="fas fa-arrow-right"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="iop-empty">
                    <i class="fas fa-inbox"></i>
                    <p class="mb-0">Aún no tienes lotes asignados.<br>Cuando el jefe de planta te designe una etapa, el lote aparecerá aquí.</p>
                </div>
            @endif
        </div>

    </div>
</section>
@endsection
