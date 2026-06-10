@extends('layouts.app')



@section('title', 'Detalle de tarea | AgroFusion')

@section('page_title', 'Detalle de tarea')



@section('breadcrumbs')

    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #2c5530;">Inicio</a></li>

    <li class="breadcrumb-item"><a href="{{ route('tareas-planta.index') }}">Mis tareas</a></li>

    <li class="breadcrumb-item active">Detalle</li>

@endsection



@push('styles')

<style>

.td-hero{background:#fff;border-radius:14px;box-shadow:0 6px 22px rgba(18,38,63,.07);padding:1.15rem 1.35rem;margin-bottom:1.25rem;border-left:5px solid #2c5530}

.td-hero h1{font-size:1.35rem;font-weight:700;color:#1f2937;margin:0}

.td-hero-meta{color:#6b7280;font-size:.88rem;margin:.35rem 0 0}

.td-card{border:0;border-radius:14px;box-shadow:0 6px 22px rgba(18,38,63,.07);overflow:hidden}

.td-card-hd{background:#fff;border-bottom:1px solid #eef2f0;padding:.9rem 1.15rem;font-weight:700}

.td-info-row{display:flex;padding:.65rem 0;border-bottom:1px solid #f3f4f6;font-size:.88rem}

.td-info-row:last-child{border-bottom:0}

.td-info-label{width:38%;color:#6b7280;font-weight:600;flex-shrink:0}

.td-info-value{flex:1;color:#1f2937}

.td-complete-hd{background:linear-gradient(135deg,#1e4620,#2c5530);color:#fff;padding:1rem 1.15rem;font-weight:700}

.td-time-box{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:.75rem 1rem;margin-bottom:.85rem}

.td-time-box .label{font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;color:#047857;font-weight:700;margin-bottom:.15rem}

.td-time-box .value{font-size:1rem;font-weight:700;color:#1f2937}

.td-btn-complete{border-radius:12px;padding:.7rem;font-weight:700;font-size:1rem;box-shadow:0 6px 16px rgba(44,85,48,.3)}

.td-status-pendiente{background:#fef3c7;color:#b45309;border-radius:999px;padding:.2rem .65rem;font-size:.78rem;font-weight:700}

.td-status-ok{background:#d1fae5;color:#047857;border-radius:999px;padding:.2rem .65rem;font-size:.78rem;font-weight:700}

</style>

@endpush



@section('content')

<div class="content-header">

    <div class="container-fluid">

        <div class="td-hero d-flex flex-wrap justify-content-between align-items-start" style="gap:.75rem;">

            <div>

                <h1><i class="fas fa-cog mr-2 text-success"></i>{{ $tarea->proceso?->nombre }}</h1>

                <p class="td-hero-meta mb-0">

                    <i class="fas fa-tools mr-1"></i>{{ $tarea->maquina?->nombre }}

                    <span class="mx-1">·</span>

                    <i class="fas fa-box mr-1"></i>Lote {{ $tarea->loteProduccion?->codigo_lote }}

                </p>

            </div>

            <a href="{{ route('tareas-planta.index') }}" class="btn btn-outline-secondary btn-sm" style="border-radius:10px;">

                <i class="fas fa-arrow-left mr-1"></i>Volver

            </a>

        </div>

    </div>

</div>



<section class="content">

    <div class="container-fluid">

        <div class="row">

            <div class="col-lg-7">

                <div class="td-card mb-3">

                    <div class="td-card-hd"><i class="fas fa-list-alt mr-2 text-success"></i>Instrucciones</div>

                    <div class="card-body px-3 py-2">

                        <div class="td-info-row">

                            <div class="td-info-label">Proceso</div>

                            <div class="td-info-value">{{ $tarea->proceso?->nombre }}</div>

                        </div>

                        <div class="td-info-row">

                            <div class="td-info-label">Maquinaria</div>

                            <div class="td-info-value">

                                {{ $tarea->maquina?->nombre }}@if($tarea->maquina?->codigo) <span class="text-muted">({{ $tarea->maquina->codigo }})</span>@endif

                            </div>

                        </div>

                        <div class="td-info-row">

                            <div class="td-info-label">Lote</div>

                            <div class="td-info-value">

                                {{ $tarea->loteProduccion?->codigo_lote }} — {{ $tarea->loteProduccion?->nombre }}

                                <a href="{{ route('procesamiento.show', $tarea->loteProduccion) }}" class="ml-1 font-weight-bold">Ver lote</a>

                            </div>

                        </div>

                        <div class="td-info-row">

                            <div class="td-info-label">Asignado por</div>

                            <div class="td-info-value">{{ $tarea->asignadoPor?->nombreCompleto() ?? '—' }}</div>

                        </div>

                        <div class="td-info-row">

                            <div class="td-info-label">Fecha asignación</div>

                            <div class="td-info-value">{{ optional($tarea->creado_en)->format('d/m/Y H:i') }}</div>

                        </div>

                        <div class="td-info-row">

                            <div class="td-info-label">Observaciones</div>

                            <div class="td-info-value">

                                @if($tarea->observaciones)

                                    {{ $tarea->observaciones }}

                                @else

                                    <span class="text-muted">Sin observaciones.</span>

                                @endif

                            </div>

                        </div>

                        <div class="td-info-row">

                            <div class="td-info-label">Estado</div>

                            <div class="td-info-value">

                                @if($tarea->estaPendiente())

                                    <span class="td-status-pendiente"><i class="fas fa-clock mr-1"></i>Pendiente</span>

                                @else

                                    <span class="td-status-ok"><i class="fas fa-check mr-1"></i>Completada</span>

                                @endif

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <div class="col-lg-5">

                @if($puedeCompletar)

                <div class="td-card">

                    <div class="td-complete-hd">

                        <i class="fas fa-check-double mr-1"></i>Marcar como completada

                    </div>

                    <div class="card-body">

                        <p class="small text-muted mb-3">

                            Al confirmar, se registrará automáticamente el horario de inicio (fecha de asignación) y el de fin (momento actual).

                        </p>

                        <div class="td-time-box">

                            <div class="label"><i class="far fa-play-circle mr-1"></i>Inicio</div>

                            <div class="value">{{ optional($tarea->creado_en)->format('d/m/Y H:i') }}</div>

                        </div>

                        <div class="td-time-box" style="background:#eff6ff;border-color:#bfdbfe;">

                            <div class="label" style="color:#1d4ed8;"><i class="far fa-stop-circle mr-1"></i>Fin (al completar)</div>

                            <div class="value">{{ now()->format('d/m/Y H:i') }}</div>

                        </div>

                        <form method="POST" action="{{ route('tareas-planta.completar', $tarea) }}" class="mb-0">

                            @csrf

                            <button type="button" class="btn btn-success btn-block td-btn-complete"

                                    data-confirm-modal

                                    data-confirm-tone="success"

                                    data-confirm-title="Completar tarea"

                                    data-confirm-message="¿Confirma que finalizó «{{ $tarea->proceso?->nombre }}» en {{ $tarea->maquina?->nombre }}?">

                                <i class="fas fa-check mr-1"></i>Completar tarea

                            </button>

                        </form>

                    </div>

                </div>

                @elseif($tarea->estaPendiente())

                <div class="alert alert-warning border-0 shadow-sm">Esta tarea ya no puede completarse porque el lote cerró su transformación.</div>

                @else

                <div class="td-card">

                    <div class="card-body text-center py-4">

                        <i class="fas fa-check-circle text-success mb-2" style="font-size:2rem;"></i>

                        <p class="mb-0 font-weight-bold text-success">Tarea completada</p>

                        <p class="text-muted small mb-0">{{ optional($tarea->completada_en)->format('d/m/Y H:i') }}</p>

                    </div>

                </div>

                @endif

            </div>

        </div>

    </div>

</section>



@include('partials.modal-confirmar-accion')

@endsection

