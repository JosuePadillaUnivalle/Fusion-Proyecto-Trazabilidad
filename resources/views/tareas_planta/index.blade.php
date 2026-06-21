@extends('layouts.app')



@section('title', 'Mis tareas de transformación | AgroFusion')

@section('page_title', 'Mis tareas de transformación')



@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>

    <li class="breadcrumb-item active">Mis tareas</li>

@endsection



@push('styles')

<style>

.tp-hero{background:linear-gradient(135deg,#1e4620 0%,#2c5530 55%,#3d7a46 100%);border-radius:16px;color:#fff;padding:1.5rem 1.75rem;margin-bottom:1.25rem;box-shadow:0 10px 28px rgba(30,70,32,.22)}

.tp-hero h1{font-size:1.45rem;font-weight:700;margin:0}

.tp-hero p{opacity:.88;margin:.35rem 0 0;font-size:.92rem}

.tp-stat{display:flex;align-items:center;gap:.65rem;background:rgba(255,255,255,.14);border-radius:12px;padding:.55rem .9rem;font-size:.85rem;font-weight:600}

.tp-stat i{opacity:.85}

.tp-card{border:0;border-radius:14px;box-shadow:0 6px 22px rgba(18,38,63,.07);overflow:hidden}

.tp-card-hd{background:#fff;border-bottom:1px solid #eef2f0;padding:.85rem 1.15rem;font-weight:700;font-size:.95rem}

.tp-task{padding:1.1rem 1.15rem;border-bottom:1px solid #f0f3f1;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:1rem;transition:background .15s}

.tp-task:last-child{border-bottom:0}

.tp-task:hover{background:#f8fbf8}

.tp-task-icon{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#fef3c7,#fde68a);color:#b45309;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}

.tp-task-title{font-weight:700;color:#1f2937;font-size:1rem;margin:0}

.tp-task-sub{color:#6b7280;font-size:.82rem;margin:.2rem 0 0}

.tp-badge{display:inline-block;background:#ecfdf5;color:#047857;border-radius:999px;padding:.15rem .55rem;font-size:.72rem;font-weight:600;margin-right:.35rem}

.tp-btn-go{border-radius:10px;padding:.45rem 1rem;font-weight:700;box-shadow:0 4px 12px rgba(44,85,48,.25)}

.tp-empty{text-align:center;padding:2.5rem 1rem;color:#9ca3af}

.tp-empty i{font-size:2.2rem;margin-bottom:.75rem;opacity:.45}

.tp-done{padding:.75rem 1.15rem;border-bottom:1px solid #f0f3f1;display:flex;justify-content:space-between;align-items:center;gap:.75rem;font-size:.84rem}

.tp-done:last-child{border-bottom:0}

.tp-done-check{color:#10b981}

</style>

@endpush



@section('content')

<div class="content-header">

    <div class="container-fluid">

        <div class="tp-hero d-flex flex-wrap justify-content-between align-items-center" style="gap:1rem;">

            <div>

                <h1><i class="fas fa-industry mr-2"></i>Mis tareas de transformación</h1>

                <p>Etapa de maquinaria asignadas por el jefe de planta. Complete cada tarea cuando finalice el trabajo.</p>

            </div>

            <div class="d-flex flex-wrap" style="gap:.5rem;">

                <span class="tp-stat"><i class="fas fa-clock"></i>{{ $tareasPendientes->count() }} pendiente(s)</span>

                <span class="tp-stat"><i class="fas fa-check"></i>{{ $tareasCompletadas->count() }} completada(s)</span>

            </div>

        </div>

    </div>

</div>



<section class="content">

    <div class="container-fluid">

        @include('partials.dashboard-alertas')

        <div class="tp-card mb-3">

            <div class="tp-card-hd d-flex justify-content-between align-items-center">

                <span><i class="fas fa-tasks mr-2 text-warning"></i>Pendientes</span>

                <span class="badge badge-warning">{{ $tareasPendientes->count() }}</span>

            </div>

            <div class="card-body p-0">

                @forelse($tareasPendientes as $tarea)

                <div class="tp-task">

                    <div class="d-flex align-items-start" style="gap:.85rem;flex:1;min-width:220px;">

                        <div class="tp-task-icon"><i class="fas fa-cog"></i></div>

                        <div>

                            <p class="tp-task-title">{{ $tarea->proceso?->nombre }}</p>

                            <p class="tp-task-sub mb-1">

                                <span class="tp-badge"><i class="fas fa-tools mr-1"></i>{{ $tarea->maquina?->nombre }}</span>

                                <span class="tp-badge" style="background:#eff6ff;color:#1d4ed8;"><i class="fas fa-box mr-1"></i>{{ $tarea->loteProduccion?->codigo_lote }}</span>

                            </p>

                            <p class="tp-task-sub mb-0">

                                <i class="far fa-calendar-alt mr-1"></i>Asignado {{ optional($tarea->creado_en)->format('d/m/Y H:i') }}

                                @if($tarea->asignadoPor) · por {{ $tarea->asignadoPor->nombreCompleto() }} @endif

                            </p>

                            @if($tarea->observaciones)

                                <p class="tp-task-sub mt-1 mb-0"><i class="fas fa-comment-dots mr-1 text-secondary"></i>{{ $tarea->observaciones }}</p>

                            @endif

                        </div>

                    </div>

                    <a href="{{ route('tareas-planta.show', $tarea) }}" class="btn btn-success tp-btn-go">

                        <i class="fas fa-play-circle mr-1"></i>Ver y completar

                    </a>

                </div>

                @empty

                <div class="tp-empty">

                    <i class="fas fa-clipboard-check d-block"></i>

                    <strong>Sin tareas pendientes</strong>

                    <p class="mb-0 small">Cuando el jefe de planta le asigne trabajo, aparecerá aquí.</p>

                </div>

                @endforelse

            </div>

        </div>



        @if($tareasCompletadas->count())

        <div class="tp-card">

            <div class="tp-card-hd">

                <i class="fas fa-check-circle mr-2 text-success"></i>Completadas recientemente

            </div>

            <div class="card-body p-0">

                @foreach($tareasCompletadas as $tarea)

                <div class="tp-done">

                    <span>

                        <i class="fas fa-check-circle tp-done-check mr-1"></i>

                        <strong>{{ $tarea->proceso?->nombre }}</strong>

                        <span class="text-muted">· {{ $tarea->maquina?->nombre }} — {{ $tarea->loteProduccion?->codigo_lote }}</span>

                    </span>

                    <span class="text-muted">{{ optional($tarea->completada_en)->format('d/m/Y H:i') }}</span>

                </div>

                @endforeach

            </div>

        </div>

        @endif

    </div>

</section>

@endsection

